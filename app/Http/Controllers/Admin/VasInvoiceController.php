<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VasInvoice;
use App\Models\PaymentVasInvoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use NumberFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VasInvoiceController extends Controller
{
  private function validateVasInvoice(Request $request)
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
      'invoice_number' => 'required|string|max:255',
      'order_booking_number' => 'required|string|max:255',
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
    validate_permission('vas_invoices.read');

    $search = $request->input('search');
    $query = VasInvoice::with('customer');

    if ($search) {
      $query->where('order_booking_number', 'like', '%' . $search . '%')
        ->orWhere('total_amount', 'like', '%' . $search . '%')
        ->orWhereHas('customer', function ($q) use ($search) {
          $q->where('name', 'like', '%' . $search . '%');
        })
        ->orWhere('so_name', 'like', '%' . $search . '%')
        ->orderBy('created_at', 'desc');
    }

    $vasInvoices = $query->paginate(10);

    return view('admin.vas-invoices.index', compact('vasInvoices', 'search'));
  }

  public function create()
  {
    validate_permission('vas_invoices.create');

    $customers = Customer::all();
    return view('admin.vas-invoices.create', compact('customers'));
  }

  public function store(Request $request)
  {
    validate_permission('vas_invoices.create');

    DB::beginTransaction();
    try {
      \Log::info('VAS Invoice Request:', $request->all());

      // Validate the request
      $validated = $this->validateVasInvoice($request);
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
        'total_amount',
        'so_name'
      ]);

      $vasInvoice = VasInvoice::create($data);

      // Process payments
      $this->processPayments($request, $vasInvoice);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.vas-invoices.receipt', $vasInvoice);
      }

      return redirect()->route('admin.vas-invoices.index')
        ->with('success', 'VAS Invoice created successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('VAS Invoice Error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
      return redirect()->back()->withInput()
        ->with('error', 'Error creating VAS Invoice: ' . $e->getMessage());
    }
  }

  private function processPayments(Request $request, VasInvoice $vasInvoice)
  {
    $amountPaid = 0;
    foreach ($request->input('payments', []) as $payment) {
      $paymentData = [
        'vas_invoice_id' => $vasInvoice->id,
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

      PaymentVasInvoice::create($paymentData);
      $amountPaid += $payment['amount'];
    }

    $vasInvoice->update([
      'amount_paid' => $amountPaid,
      'balance' => $vasInvoice->total_amount - $amountPaid,
    ]);
  }

  public function edit(VasInvoice $vasInvoice)
  {
    validate_permission('vas_invoices.update');

    $customers = Customer::all();
    $vasInvoice->load(['payments', 'customer']);
    $balance = $vasInvoice->total_amount - $vasInvoice->amount_paid;

    return view('admin.vas-invoices.edit', compact('vasInvoice', 'customers', 'balance'));
  }

  public function update(Request $request, VasInvoice $vasInvoice)
  {
    validate_permission('vas_invoices.update');

    DB::beginTransaction();
    try {
      // Validate the request
      $validated = $this->validateVasInvoice($request);

      $data = $request->only([
        'customer_id',
        'order_booking_number',
        'invoice_number',
        'total_amount',
        'so_name'
      ]);

      $vasInvoice->update($data);

      // Delete existing payments
      $vasInvoice->payments()->delete();

      // Process new payments using the shared method
      $this->processPayments($request, $vasInvoice);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.vas-invoices.receipt', $vasInvoice);
      }

      return redirect()->route('admin.vas-invoices.index')
        ->with('success', 'VAS Invoice updated successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()->withInput()
        ->with('error', 'Error updating VAS Invoice: ' . $e->getMessage());
    }
  }

  public function destroy(VasInvoice $vasInvoice)
  {
    validate_permission('vas_invoices.delete');

    $vasInvoice->delete();
    return redirect()->route('admin.vas-invoices.index')
      ->with('success', 'VAS Invoice deleted successfully!');
  }

  public function receipt(VasInvoice $vasInvoice)
  {
    $vasInvoice->load(['customer', 'payments']);

    // Convert amount to words
    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    $amountInWords = $f->format($vasInvoice->amount_paid);

    return view('admin.vas-invoices.receipt', compact('vasInvoice', 'amountInWords'));
  }
}
