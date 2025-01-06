<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsedCarSale;
use App\Models\PaymentUsedCarSale;
use App\Models\Customer;
use Illuminate\Http\Request;
use NumberFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UsedCarSaleController extends Controller
{
  private function validateUsedCarSale(Request $request)
  {
    $rules = [
      'customer_id' => 'nullable|exists:customers,id',
      'customer_name' => 'required_without:customer_id|string|max:255',
      'customer_phone_no' => [
        'required_without:customer_id',
        'string',
        'regex:/^\d{10}$/',
        Rule::unique('customers', 'phone_no')->ignore($request->customer_id),
      ],
      'customer_pan_no' => [
        'required_without:customer_id',
        'string',
        Rule::unique('customers', 'pan_number')->ignore($request->customer_id),
      ],
      'order_booking_number' => 'required|string|max:255',
      'vehicle_registration_no' => 'required|string|max:255',
      'car_maker' => 'required|string|max:255',
      'car_model' => 'required|string|max:255',
      'car_color' => 'required|string|max:255',
      'total_amount' => 'required|numeric|min:0',
      'so_name' => 'required|string|max:255',
      'payments' => 'required|array|min:1',
      'payments.*.payment_by' => 'required|string',
      'payments.*.payment_date' => 'required|date',
      'payments.*.amount' => 'required|numeric|min:0',
    ];

    return $request->validate($rules);
  }

  public function index(Request $request)
  {
    validate_permission('used_car_sales.read');

    $search = $request->input('search');
    $query = UsedCarSale::with('customer');

    if ($search) {
      $query->where('order_booking_number', 'like', '%' . $search . '%')
        ->orWhere('vehicle_registration_no', 'like', '%' . $search . '%')
        ->orWhere('car_maker', 'like', '%' . $search . '%')
        ->orWhere('car_model', 'like', '%' . $search . '%')
        ->orWhere('total_amount', 'like', '%' . $search . '%')
        ->orWhereHas('customer', function ($q) use ($search) {
          $q->where('name', 'like', '%' . $search . '%');
        })
        ->orWhere('so_name', 'like', '%' . $search . '%')
        ->orderBy('created_at', 'desc');
    }

    $usedCarSales = $query->paginate(10);

    return view('admin.used-car-sales.index', compact('usedCarSales', 'search'));
  }

  public function create()
  {
    validate_permission('used_car_sales.create');

    $customers = Customer::all();
    return view('admin.used-car-sales.create', compact('customers'));
  }

  public function store(Request $request)
  {
    validate_permission('used_car_sales.create');

    DB::beginTransaction();
    try {
      \Log::info('Used Car Sale Request:', $request->all());

      // Validate the request
      $validated = $this->validateUsedCarSale($request);
      \Log::info('Validation passed');

      // Check if we need to create a new customer
      if (empty($request->customer_id) && $request->customer_name && $request->customer_phone_no) {
        $customer = Customer::create([
          'name' => $request->customer_name,
          'phone_no' => $request->customer_phone_no,
          'pan_number' => $request->customer_pan_no,
        ]);
        $request->merge(['customer_id' => $customer->id]);
      }

      $data = $request->only([
        'customer_id',
        'order_booking_number',
        'vehicle_registration_no',
        'car_maker',
        'car_model',
        'car_color',
        'total_amount',
        'so_name'
      ]);

      $usedCarSale = UsedCarSale::create($data);

      // Process payments
      $this->processPayments($request, $usedCarSale);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.used-car-sales.receipt', $usedCarSale);
      }

      return redirect()->route('admin.used-car-sales.index')
        ->with('success', 'Used Car Sale created successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Used Car Sale Error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
      return redirect()->back()->withInput()
        ->with('error', 'Error creating Used Car Sale: ' . $e->getMessage());
    }
  }

  private function processPayments(Request $request, UsedCarSale $usedCarSale)
  {
    $amountPaid = 0;
    foreach ($request->input('payments', []) as $payment) {
      $paymentData = [
        'used_car_sale_id' => $usedCarSale->id,
        'payment_by' => $payment['payment_by'],
        'payment_date' => $payment['payment_date'],
        'amount' => $payment['amount'],
        'reference_number' => $payment['reference_number'] ?? null,
        'bank_name' => $payment['bank_name'] ?? null,
        'approved_by' => $payment['approved_by'] ?? null,
        'discount_note_no' => $payment['discount_note_no'] ?? null,
        'approved_note_no' => $payment['approved_note_no'] ?? null,
        'institution_name' => $payment['institution_name'] ?? null,
        'credit_instrument' => $payment['credit_instrument'] ?? null,
      ];

      PaymentUsedCarSale::create($paymentData);
      $amountPaid += $payment['amount'];
    }

    $usedCarSale->update([
      'amount_paid' => $amountPaid,
      'balance' => $usedCarSale->total_amount - $amountPaid,
    ]);
  }

  public function edit(UsedCarSale $usedCarSale)
  {
    validate_permission('used_car_sales.update');

    $customers = Customer::all();
    $usedCarSale->load(['payments', 'customer']);
    $balance = $usedCarSale->total_amount - $usedCarSale->amount_paid;

    return view('admin.used-car-sales.edit', compact('usedCarSale', 'customers', 'balance'));
  }

  public function update(Request $request, UsedCarSale $usedCarSale)
  {
    validate_permission('used_car_sales.update');

    DB::beginTransaction();
    try {
      // Validate the request
      $validated = $this->validateUsedCarSale($request);

      $data = $request->only([
        'customer_id',
        'order_booking_number',
        'vehicle_registration_no',
        'car_maker',
        'car_model',
        'car_color',
        'total_amount',
        'so_name'
      ]);

      $usedCarSale->update($data);

      // Delete existing payments
      $usedCarSale->payments()->delete();

      // Process new payments using the shared method
      $this->processPayments($request, $usedCarSale);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.used-car-sales.receipt', $usedCarSale);
      }

      return redirect()->route('admin.used-car-sales.index')
        ->with('success', 'Used Car Sale updated successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()->withInput()
        ->with('error', 'Error updating Used Car Sale: ' . $e->getMessage());
    }
  }

  public function destroy(UsedCarSale $usedCarSale)
  {
    validate_permission('used_car_sales.delete');

    $usedCarSale->delete();
    return redirect()->route('admin.used-car-sales.index')
      ->with('success', 'Used Car Sale deleted successfully!');
  }

  public function receipt(UsedCarSale $usedCarSale)
  {
    $usedCarSale->load(['customer', 'payments']);

    // Convert amount to words
    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    $amountInWords = $f->format($usedCarSale->amount_paid);

    return view('admin.used-car-sales.receipt', compact('usedCarSale', 'amountInWords'));
  }
}
