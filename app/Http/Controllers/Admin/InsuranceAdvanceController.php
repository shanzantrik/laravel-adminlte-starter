<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InsuranceAdvance;
use App\Models\PaymentInsuranceAdvance;
use App\Models\Customer;
use Illuminate\Http\Request;
use NumberFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InsuranceAdvanceController extends Controller
{
  private function validateInsuranceAdvance(Request $request)
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
      'proposal_policy_number' => 'required|string|max:255',
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
    validate_permission('insurance_advances.read');

    $search = $request->input('search');
    $query = InsuranceAdvance::with('customer');

    if ($search) {
      $query->where('order_booking_number', 'like', '%' . $search . '%')
        ->orWhere('proposal_policy_number', 'like', '%' . $search . '%')
        ->orWhere('total_amount', 'like', '%' . $search . '%')
        ->orWhereHas('customer', function ($q) use ($search) {
          $q->where('name', 'like', '%' . $search . '%');
        })
        ->orWhere('so_name', 'like', '%' . $search . '%')
        ->orderBy('created_at', 'desc');
    }

    $insuranceAdvances = $query->paginate(10);

    return view('admin.insurance-advances.index', compact('insuranceAdvances', 'search'));
  }

  public function create()
  {
    validate_permission('insurance_advances.create');

    $customers = Customer::all();
    return view('admin.insurance-advances.create', compact('customers'));
  }

  public function store(Request $request)
  {
    validate_permission('insurance_advances.create');

    DB::beginTransaction();
    try {
      // Validate the request
      $validated = $this->validateInsuranceAdvance($request);

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
        'proposal_policy_number',
        'total_amount',
        'so_name'
      ]);

      $insuranceAdvance = InsuranceAdvance::create($data);

      // Process payments
      $this->processPayments($request, $insuranceAdvance);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.insurance-advances.receipt', $insuranceAdvance);
      }

      return redirect()->route('admin.insurance-advances.index')
        ->with('success', 'Insurance Advance created successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()->withInput()
        ->with('error', 'Error creating Insurance Advance: ' . $e->getMessage());
    }
  }

  private function processPayments(Request $request, InsuranceAdvance $insuranceAdvance)
  {
    $amountPaid = 0;
    foreach ($request->input('payments', []) as $payment) {
      $paymentData = [
        'insurance_advance_id' => $insuranceAdvance->id,
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

      PaymentInsuranceAdvance::create($paymentData);
      $amountPaid += $payment['amount'];
    }

    $insuranceAdvance->update([
      'amount_paid' => $amountPaid,
      'balance' => $insuranceAdvance->total_amount - $amountPaid,
    ]);
  }

  public function edit(InsuranceAdvance $insuranceAdvance)
  {
    validate_permission('insurance_advances.update');

    $customers = Customer::all();
    $insuranceAdvance->load(['payments', 'customer']);
    $balance = $insuranceAdvance->total_amount - $insuranceAdvance->amount_paid;

    return view('admin.insurance-advances.edit', compact('insuranceAdvance', 'customers', 'balance'));
  }

  public function update(Request $request, InsuranceAdvance $insuranceAdvance)
  {
    validate_permission('insurance_advances.update');

    DB::beginTransaction();
    try {
      // Validate the request
      $validated = $this->validateInsuranceAdvance($request);

      $data = $request->only([
        'customer_id',
        'order_booking_number',
        'proposal_policy_number',
        'total_amount',
        'so_name'
      ]);

      $insuranceAdvance->update($data);

      // Delete existing payments
      $insuranceAdvance->payments()->delete();

      // Process new payments using the shared method
      $this->processPayments($request, $insuranceAdvance);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.insurance-advances.receipt', $insuranceAdvance);
      }

      return redirect()->route('admin.insurance-advances.index')
        ->with('success', 'Insurance Advance updated successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()->withInput()
        ->with('error', 'Error updating Insurance Advance: ' . $e->getMessage());
    }
  }

  public function destroy(InsuranceAdvance $insuranceAdvance)
  {
    validate_permission('insurance_advances.delete');

    $insuranceAdvance->delete();
    return redirect()->route('admin.insurance-advances.index')
      ->with('success', 'Insurance Advance deleted successfully!');
  }

  public function receipt(InsuranceAdvance $insuranceAdvance)
  {
    $insuranceAdvance->load(['customer', 'payments']);

    // Convert amount to words
    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    $amountInWords = $f->format($insuranceAdvance->amount_paid);

    return view('admin.insurance-advances.receipt', compact('insuranceAdvance', 'amountInWords'));
  }
}
