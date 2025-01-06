<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InsurancePolicy;
use App\Models\PaymentInsurancePolicy;
use App\Models\Customer;
use Illuminate\Http\Request;
use NumberFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InsurancePolicyController extends Controller
{
  private function validateInsurancePolicy(Request $request)
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
    validate_permission('insurance_policies.read');

    $search = $request->input('search');
    $query = InsurancePolicy::with('customer');

    if ($search) {
      $query->where('proposal_policy_number', 'like', '%' . $search . '%')
        ->orWhere('total_amount', 'like', '%' . $search . '%')
        ->orWhereHas('customer', function ($q) use ($search) {
          $q->where('name', 'like', '%' . $search . '%');
        })
        ->orWhere('so_name', 'like', '%' . $search . '%')
        ->orderBy('created_at', 'desc');
    }

    $insurancePolicies = $query->paginate(10);

    return view('admin.insurance-policies.index', compact('insurancePolicies', 'search'));
  }

  public function create()
  {
    validate_permission('insurance_policies.create');

    $customers = Customer::all();
    return view('admin.insurance-policies.create', compact('customers'));
  }

  public function store(Request $request)
  {
    validate_permission('insurance_policies.create');

    DB::beginTransaction();
    try {
      // Validate the request
      $validated = $this->validateInsurancePolicy($request);

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
        'proposal_policy_number',
        'total_amount',
        'so_name'
      ]);

      $insurancePolicy = InsurancePolicy::create($data);

      // Process payments
      $this->processPayments($request, $insurancePolicy);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.insurance-policies.receipt', $insurancePolicy);
      }

      return redirect()->route('admin.insurance-policies.index')
        ->with('success', 'Insurance Policy created successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()->withInput()
        ->with('error', 'Error creating Insurance Policy: ' . $e->getMessage());
    }
  }

  private function processPayments(Request $request, InsurancePolicy $insurancePolicy)
  {
    $amountPaid = 0;
    foreach ($request->input('payments', []) as $payment) {
      $paymentData = [
        'insurance_policy_id' => $insurancePolicy->id,
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

      PaymentInsurancePolicy::create($paymentData);
      $amountPaid += $payment['amount'];
    }

    $insurancePolicy->update([
      'amount_paid' => $amountPaid,
      'balance' => $insurancePolicy->total_amount - $amountPaid,
    ]);
  }

  public function edit(InsurancePolicy $insurancePolicy)
  {
    validate_permission('insurance_policies.update');

    $customers = Customer::all();
    $insurancePolicy->load(['payments', 'customer']);
    $balance = $insurancePolicy->total_amount - $insurancePolicy->amount_paid;

    return view('admin.insurance-policies.edit', compact('insurancePolicy', 'customers', 'balance'));
  }

  public function update(Request $request, InsurancePolicy $insurancePolicy)
  {
    validate_permission('insurance_policies.update');

    DB::beginTransaction();
    try {
      // Validate the request
      $validated = $this->validateInsurancePolicy($request);

      $data = $request->only([
        'customer_id',
        'proposal_policy_number',
        'total_amount',
        'so_name'
      ]);

      $insurancePolicy->update($data);

      // Delete existing payments
      $insurancePolicy->payments()->delete();

      // Process new payments using the shared method
      $this->processPayments($request, $insurancePolicy);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.insurance-policies.receipt', $insurancePolicy);
      }

      return redirect()->route('admin.insurance-policies.index')
        ->with('success', 'Insurance Policy updated successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()->withInput()
        ->with('error', 'Error updating Insurance Policy: ' . $e->getMessage());
    }
  }

  public function destroy(InsurancePolicy $insurancePolicy)
  {
    validate_permission('insurance_policies.delete');

    $insurancePolicy->delete();
    return redirect()->route('admin.insurance-policies.index')
      ->with('success', 'Insurance Policy deleted successfully!');
  }

  public function receipt(InsurancePolicy $insurancePolicy)
  {
    $insurancePolicy->load(['customer', 'payments']);

    // Convert amount to words
    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    $amountInWords = $f->format($insurancePolicy->amount_paid);

    return view('admin.insurance-policies.receipt', compact('insurancePolicy', 'amountInWords'));
  }
}
