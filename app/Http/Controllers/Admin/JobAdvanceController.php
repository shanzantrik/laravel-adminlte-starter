<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobAdvance;
use App\Models\PaymentJobAdvance;
use App\Models\Customer;
use Illuminate\Http\Request;
use NumberFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JobAdvanceController extends Controller
{
  private function validateJobAdvance(Request $request)
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
    validate_permission('job_advances.read');

    $search = $request->input('search');
    $query = JobAdvance::with('customer');

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

    $jobAdvances = $query->paginate(10);

    return view('admin.job-advances.index', compact('jobAdvances', 'search'));
  }

  public function create()
  {
    validate_permission('job_advances.create');

    $customers = Customer::all();
    return view('admin.job-advances.create', compact('customers'));
  }

  public function store(Request $request)
  {
    validate_permission('job_advances.create');

    DB::beginTransaction();
    try {
      \Log::info('Job Advance Request:', $request->all());

      // Validate the request
      $validated = $this->validateJobAdvance($request);
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

      $jobAdvance = JobAdvance::create($data);

      // Process payments
      $this->processPayments($request, $jobAdvance);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.job-advances.receipt', $jobAdvance);
      }

      return redirect()->route('admin.job-advances.index')
        ->with('success', 'Job Advance created successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Job Advance Error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
      return redirect()->back()->withInput()
        ->with('error', 'Error creating Job Advance: ' . $e->getMessage());
    }
  }

  private function processPayments(Request $request, JobAdvance $jobAdvance)
  {
    $amountPaid = 0;
    foreach ($request->input('payments', []) as $payment) {
      $paymentData = [
        'job_advance_id' => $jobAdvance->id,
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

      PaymentJobAdvance::create($paymentData);
      $amountPaid += $payment['amount'];
    }

    $jobAdvance->update([
      'amount_paid' => $amountPaid,
      'balance' => $jobAdvance->total_amount - $amountPaid,
    ]);
  }

  public function edit(JobAdvance $jobAdvance)
  {
    validate_permission('job_advances.update');

    $customers = Customer::all();
    $jobAdvance->load(['payments', 'customer']);
    $balance = $jobAdvance->total_amount - $jobAdvance->amount_paid;

    return view('admin.job-advances.edit', compact('jobAdvance', 'customers', 'balance'));
  }

  public function update(Request $request, JobAdvance $jobAdvance)
  {
    validate_permission('job_advances.update');

    DB::beginTransaction();
    try {
      // Validate the request
      $validated = $this->validateJobAdvance($request);

      $data = $request->only([
        'customer_id',
        'order_booking_number',
        'ro_job_number',
        'vehicle_registration_no',
        'total_amount',
        'so_name'
      ]);

      $jobAdvance->update($data);

      // Delete existing payments
      $jobAdvance->payments()->delete();

      // Process new payments using the shared method
      $this->processPayments($request, $jobAdvance);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.job-advances.receipt', $jobAdvance);
      }

      return redirect()->route('admin.job-advances.index')
        ->with('success', 'Job Advance updated successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()->withInput()
        ->with('error', 'Error updating Job Advance: ' . $e->getMessage());
    }
  }

  public function destroy(JobAdvance $jobAdvance)
  {
    validate_permission('job_advances.delete');

    $jobAdvance->delete();
    return redirect()->route('admin.job-advances.index')
      ->with('success', 'Job Advance deleted successfully!');
  }

  public function receipt(JobAdvance $jobAdvance)
  {
    $jobAdvance->load(['customer', 'payments']);

    // Convert amount to words
    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    $amountInWords = $f->format($jobAdvance->amount_paid);

    return view('admin.job-advances.receipt', compact('jobAdvance', 'amountInWords'));
  }
}
