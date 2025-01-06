<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExtendedWarranty;
use App\Models\PaymentExtendedWarranty;
use App\Models\Customer;
use Illuminate\Http\Request;
use NumberFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExtendedWarrantyController extends Controller
{
  private function validateExtendedWarranty(Request $request)
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
      'policy_number' => 'required|string|max:255',
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
    validate_permission('extended_warranties.read');

    $search = $request->input('search');
    $query = ExtendedWarranty::with('customer');

    if ($search) {
      $query->where('policy_number', 'like', '%' . $search . '%')
        ->orWhere('vehicle_registration_no', 'like', '%' . $search . '%')
        ->orWhere('total_amount', 'like', '%' . $search . '%')
        ->orWhereHas('customer', function ($q) use ($search) {
          $q->where('name', 'like', '%' . $search . '%');
        })
        ->orWhere('so_name', 'like', '%' . $search . '%')
        ->orderBy('created_at', 'desc');
    }

    $extendedWarranties = $query->paginate(10);

    return view('admin.extended-warranties.index', compact('extendedWarranties', 'search'));
  }

  public function create()
  {
    validate_permission('extended_warranties.create');

    $customers = Customer::all();
    return view('admin.extended-warranties.create', compact('customers'));
  }

  public function store(Request $request)
  {
    validate_permission('extended_warranties.create');

    DB::beginTransaction();
    try {
      // Validate the request
      $validated = $this->validateExtendedWarranty($request);

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
        'policy_number',
        'vehicle_registration_no',
        'total_amount',
        'so_name'
      ]);

      $extendedWarranty = ExtendedWarranty::create($data);

      // Process payments
      $this->processPayments($request, $extendedWarranty);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.extended-warranties.receipt', $extendedWarranty);
      }

      return redirect()->route('admin.extended-warranties.index')
        ->with('success', 'Extended Warranty created successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()->withInput()
        ->with('error', 'Error creating Extended Warranty: ' . $e->getMessage());
    }
  }

  private function processPayments(Request $request, ExtendedWarranty $extendedWarranty)
  {
    $amountPaid = 0;
    foreach ($request->input('payments', []) as $payment) {
      $paymentData = [
        'extended_warranty_id' => $extendedWarranty->id,
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

      PaymentExtendedWarranty::create($paymentData);
      $amountPaid += $payment['amount'];
    }

    $extendedWarranty->update([
      'amount_paid' => $amountPaid,
      'balance' => $extendedWarranty->total_amount - $amountPaid,
    ]);
  }

  public function edit(ExtendedWarranty $extendedWarranty)
  {
    validate_permission('extended_warranties.update');

    $customers = Customer::all();
    $extendedWarranty->load(['payments', 'customer']);
    $balance = $extendedWarranty->total_amount - $extendedWarranty->amount_paid;

    return view('admin.extended-warranties.edit', compact('extendedWarranty', 'customers', 'balance'));
  }

  public function update(Request $request, ExtendedWarranty $extendedWarranty)
  {
    validate_permission('extended_warranties.update');

    DB::beginTransaction();
    try {
      // Validate the request
      $validated = $this->validateExtendedWarranty($request);

      $data = $request->only([
        'customer_id',
        'policy_number',
        'vehicle_registration_no',
        'total_amount',
        'so_name'
      ]);

      $extendedWarranty->update($data);

      // Delete existing payments
      $extendedWarranty->payments()->delete();

      // Process new payments using the shared method
      $this->processPayments($request, $extendedWarranty);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.extended-warranties.receipt', $extendedWarranty);
      }

      return redirect()->route('admin.extended-warranties.index')
        ->with('success', 'Extended Warranty updated successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()->withInput()
        ->with('error', 'Error updating Extended Warranty: ' . $e->getMessage());
    }
  }

  public function destroy(ExtendedWarranty $extendedWarranty)
  {
    validate_permission('extended_warranties.delete');

    $extendedWarranty->delete();
    return redirect()->route('admin.extended-warranties.index')
      ->with('success', 'Extended Warranty deleted successfully!');
  }

  public function receipt(ExtendedWarranty $extendedWarranty)
  {
    $extendedWarranty->load(['customer', 'payments']);

    // Convert amount to words
    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    $amountInWords = $f->format($extendedWarranty->amount_paid);

    return view('admin.extended-warranties.receipt', compact('extendedWarranty', 'amountInWords'));
  }
}
