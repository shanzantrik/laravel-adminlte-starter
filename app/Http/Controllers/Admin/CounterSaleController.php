<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CounterSale;
use App\Models\PaymentCounterSale;
use App\Models\Customer;
use Illuminate\Http\Request;
use NumberFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CounterSaleController extends Controller
{
  private function validateCounterSale(Request $request)
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
      'invoice_number' => 'required|string|max:255',
      'total_amount' => 'required|numeric|min:0',
      'payments' => 'required|array|min:1',
      'payments.*.payment_by' => 'required|string',
      'payments.*.payment_date' => 'required|date',
      'payments.*.amount' => 'required|numeric|min:0',
    ];

    return $request->validate($rules);
  }

  public function index(Request $request)
  {
    validate_permission('counter_sales.read');

    $search = $request->input('search');
    $query = CounterSale::with('customer');

    if ($search) {
      $query->where('order_booking_number', 'like', '%' . $search . '%')
        ->orWhere('invoice_number', 'like', '%' . $search . '%')
        ->orWhere('total_amount', 'like', '%' . $search . '%')
        ->orWhereHas('customer', function ($q) use ($search) {
          $q->where('name', 'like', '%' . $search . '%');
        })
        ->orderBy('created_at', 'desc');
    }

    $counterSales = $query->paginate(10);

    return view('admin.counter-sales.index', compact('counterSales', 'search'));
  }

  public function create()
  {
    validate_permission('counter_sales.create');

    $customers = Customer::all();
    return view('admin.counter-sales.create', compact('customers'));
  }

  public function store(Request $request)
  {
    validate_permission('counter_sales.create');

    DB::beginTransaction();
    try {
      \Log::info('Counter Sale Request:', $request->all());

      // Validate the request
      $validated = $this->validateCounterSale($request);
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
        'invoice_number',
        'total_amount'
      ]);

      $counterSale = CounterSale::create($data);

      // Process payments
      $this->processPayments($request, $counterSale);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.counter-sales.receipt', $counterSale);
      }

      return redirect()->route('admin.counter-sales.index')
        ->with('success', 'Counter Sale created successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Counter Sale Error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
      return redirect()->back()->withInput()
        ->with('error', 'Error creating Counter Sale: ' . $e->getMessage());
    }
  }

  private function processPayments(Request $request, CounterSale $counterSale)
  {
    $amountPaid = 0;
    foreach ($request->input('payments', []) as $payment) {
      $paymentData = [
        'counter_sale_id' => $counterSale->id,
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

      PaymentCounterSale::create($paymentData);
      $amountPaid += $payment['amount'];
    }

    $counterSale->update([
      'amount_paid' => $amountPaid,
      'balance' => $counterSale->total_amount - $amountPaid,
    ]);
  }

  public function edit(CounterSale $counterSale)
  {
    validate_permission('counter_sales.update');

    $customers = Customer::all();
    $counterSale->load(['payments', 'customer']);
    $balance = $counterSale->total_amount - $counterSale->amount_paid;

    return view('admin.counter-sales.edit', compact('counterSale', 'customers', 'balance'));
  }

  public function update(Request $request, CounterSale $counterSale)
  {
    validate_permission('counter_sales.update');

    DB::beginTransaction();
    try {
      // Validate the request
      $validated = $this->validateCounterSale($request);

      $data = $request->only([
        'customer_id',
        'order_booking_number',
        'invoice_number',
        'total_amount'
      ]);

      $counterSale->update($data);

      // Delete existing payments
      $counterSale->payments()->delete();

      // Process new payments using the shared method
      $this->processPayments($request, $counterSale);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.counter-sales.receipt', $counterSale);
      }

      return redirect()->route('admin.counter-sales.index')
        ->with('success', 'Counter Sale updated successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()->withInput()
        ->with('error', 'Error updating Counter Sale: ' . $e->getMessage());
    }
  }

  public function destroy(CounterSale $counterSale)
  {
    validate_permission('counter_sales.delete');

    $counterSale->delete();
    return redirect()->route('admin.counter-sales.index')
      ->with('success', 'Counter Sale deleted successfully!');
  }

  public function receipt(CounterSale $counterSale)
  {
    $counterSale->load(['customer', 'payments']);

    // Convert amount to words
    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    $amountInWords = $f->format($counterSale->amount_paid);

    return view('admin.counter-sales.receipt', compact('counterSale', 'amountInWords'));
  }
}
