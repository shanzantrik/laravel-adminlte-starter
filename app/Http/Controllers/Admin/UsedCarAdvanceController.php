<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsedCarAdvance;
use App\Models\PaymentUsedCarAdvance;
use App\Models\Customer;
use Illuminate\Http\Request;
use NumberFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UsedCarAdvanceController extends Controller
{
  private function validateUsedCarAdvance(Request $request)
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
    validate_permission('used_car_advances.read');

    $search = $request->input('search');
    $query = UsedCarAdvance::with('customer');

    if ($search) {
      $query->where('order_booking_number', 'like', '%' . $search . '%')
        ->orWhere('ro_job_number', 'like', '%' . $search . '%')
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

    $usedCarAdvances = $query->paginate(10);

    return view('admin.used-car-advances.index', compact('usedCarAdvances', 'search'));
  }

  public function create()
  {
    validate_permission('used_car_advances.create');

    $customers = Customer::all();
    return view('admin.used-car-advances.create', compact('customers'));
  }

  public function store(Request $request)
  {
    validate_permission('used_car_advances.create');

    DB::beginTransaction();
    try {
      \Log::info('Used Car Advance Request:', $request->all());

      // Validate the request
      $validated = $this->validateUsedCarAdvance($request);
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
        'ro_job_number',
        'vehicle_registration_no',
        'car_maker',
        'car_model',
        'car_color',
        'total_amount',
        'so_name'
      ]);

      $usedCarAdvance = UsedCarAdvance::create($data);

      // Process payments
      $this->processPayments($request, $usedCarAdvance);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.used-car-advances.receipt', $usedCarAdvance);
      }

      return redirect()->route('admin.used-car-advances.index')
        ->with('success', 'Used Car Advance created successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Used Car Advance Error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
      return redirect()->back()->withInput()
        ->with('error', 'Error creating Used Car Advance: ' . $e->getMessage());
    }
  }

  private function processPayments(Request $request, UsedCarAdvance $usedCarAdvance)
  {
    $amountPaid = 0;
    foreach ($request->input('payments', []) as $payment) {
      $paymentData = [
        'used_car_advance_id' => $usedCarAdvance->id,
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

      PaymentUsedCarAdvance::create($paymentData);
      $amountPaid += $payment['amount'];
    }

    $usedCarAdvance->update([
      'amount_paid' => $amountPaid,
      'balance' => $usedCarAdvance->total_amount - $amountPaid,
    ]);
  }

  public function edit(UsedCarAdvance $usedCarAdvance)
  {
    validate_permission('used_car_advances.update');

    $customers = Customer::all();
    $usedCarAdvance->load(['payments', 'customer']);
    $balance = $usedCarAdvance->total_amount - $usedCarAdvance->amount_paid;

    return view('admin.used-car-advances.edit', compact('usedCarAdvance', 'customers', 'balance'));
  }

  public function update(Request $request, UsedCarAdvance $usedCarAdvance)
  {
    validate_permission('used_car_advances.update');

    DB::beginTransaction();
    try {
      // Validate the request
      $validated = $this->validateUsedCarAdvance($request);

      $data = $request->only([
        'customer_id',
        'order_booking_number',
        'ro_job_number',
        'vehicle_registration_no',
        'car_maker',
        'car_model',
        'car_color',
        'total_amount',
        'so_name'
      ]);

      $usedCarAdvance->update($data);

      // Delete existing payments
      $usedCarAdvance->payments()->delete();

      // Process new payments using the shared method
      $this->processPayments($request, $usedCarAdvance);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.used-car-advances.receipt', $usedCarAdvance);
      }

      return redirect()->route('admin.used-car-advances.index')
        ->with('success', 'Used Car Advance updated successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()->withInput()
        ->with('error', 'Error updating Used Car Advance: ' . $e->getMessage());
    }
  }

  public function destroy(UsedCarAdvance $usedCarAdvance)
  {
    validate_permission('used_car_advances.delete');

    $usedCarAdvance->delete();
    return redirect()->route('admin.used-car-advances.index')
      ->with('success', 'Used Car Advance deleted successfully!');
  }

  public function receipt(UsedCarAdvance $usedCarAdvance)
  {
    $usedCarAdvance->load(['customer', 'payments']);

    // Convert amount to words
    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    $amountInWords = $f->format($usedCarAdvance->amount_paid);

    return view('admin.used-car-advances.receipt', compact('usedCarAdvance', 'amountInWords'));
  }
}
