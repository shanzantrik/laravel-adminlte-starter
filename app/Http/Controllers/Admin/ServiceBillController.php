<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceBill;
use App\Models\PaymentServiceBill;
use App\Models\Customer;
use Illuminate\Http\Request;
use NumberFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServiceBillController extends Controller
{
  private function validateServiceBill(Request $request)
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
      'ro_job_number' => 'required|string|max:255',
      'vehicle_registration_no' => 'required|string|max:255',
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
    validate_permission('service_bills.read');

    $search = $request->input('search');
    $query = ServiceBill::with('customer');

    if ($search) {
      $query->where('order_booking_number', 'like', '%' . $search . '%')
        ->orWhere('ro_job_number', 'like', '%' . $search . '%')
        ->orWhere('vehicle_registration_no', 'like', '%' . $search . '%')
        ->orWhere('total_amount', 'like', '%' . $search . '%')
        ->orWhereHas('customer', function ($q) use ($search) {
          $q->where('name', 'like', '%' . $search . '%');
        })
        ->orWhere('so_name', 'like', '%' . $search . '%')
        ->orderBy('created_at', 'desc');
    }

    $serviceBills = $query->paginate(10);

    return view('admin.service-bills.index', compact('serviceBills', 'search'));
  }

  public function create()
  {
    validate_permission('service_bills.create');

    $customers = Customer::all();
    return view('admin.service-bills.create', compact('customers'));
  }

  public function store(Request $request)
  {
    validate_permission('service_bills.create');

    DB::beginTransaction();
    try {
      \Log::info('Service Bill Request:', $request->all());

      // Validate the request
      $validated = $this->validateServiceBill($request);
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
        'total_amount',
        'so_name'
      ]);

      $serviceBill = ServiceBill::create($data);

      // Process payments
      $this->processPayments($request, $serviceBill);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.service-bills.receipt', $serviceBill);
      }

      return redirect()->route('admin.service-bills.index')
        ->with('success', 'Service Bill created successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Service Bill Error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
      return redirect()->back()->withInput()
        ->with('error', 'Error creating Service Bill: ' . $e->getMessage());
    }
  }

  private function processPayments(Request $request, ServiceBill $serviceBill)
  {
    $amountPaid = 0;
    foreach ($request->input('payments', []) as $payment) {
      $paymentData = [
        'service_bill_id' => $serviceBill->id,
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

      PaymentServiceBill::create($paymentData);
      $amountPaid += $payment['amount'];
    }

    $serviceBill->update([
      'amount_paid' => $amountPaid,
      'balance' => $serviceBill->total_amount - $amountPaid,
    ]);
  }

  public function edit(ServiceBill $serviceBill)
  {
    validate_permission('service_bills.update');

    $customers = Customer::all();
    $serviceBill->load(['payments', 'customer']);
    $balance = $serviceBill->total_amount - $serviceBill->amount_paid;

    return view('admin.service-bills.edit', compact('serviceBill', 'customers', 'balance'));
  }

  public function update(Request $request, ServiceBill $serviceBill)
  {
    validate_permission('service_bills.update');

    DB::beginTransaction();
    try {
      // Validate the request
      $validated = $this->validateServiceBill($request);

      $data = $request->only([
        'customer_id',
        'order_booking_number',
        'ro_job_number',
        'vehicle_registration_no',
        'total_amount',
        'so_name'
      ]);

      $serviceBill->update($data);

      // Delete existing payments
      $serviceBill->payments()->delete();

      // Process new payments using the shared method
      $this->processPayments($request, $serviceBill);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.service-bills.receipt', $serviceBill);
      }

      return redirect()->route('admin.service-bills.index')
        ->with('success', 'Service Bill updated successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()->withInput()
        ->with('error', 'Error updating Service Bill: ' . $e->getMessage());
    }
  }

  public function destroy(ServiceBill $serviceBill)
  {
    validate_permission('service_bills.delete');

    $serviceBill->delete();
    return redirect()->route('admin.service-bills.index')
      ->with('success', 'Service Bill deleted successfully!');
  }

  public function receipt(ServiceBill $serviceBill)
  {
    $serviceBill->load(['customer', 'payments']);

    // Convert amount to words
    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    $amountInWords = $f->format($serviceBill->amount_paid);

    return view('admin.service-bills.receipt', compact('serviceBill', 'amountInWords'));
  }
}
