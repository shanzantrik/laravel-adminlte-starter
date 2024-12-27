<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewVehicleSale;
use App\Models\PaymentNewVehicle;
use App\Models\Customer;
use Illuminate\Http\Request;
use NumberFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NewVehicleSaleController extends Controller
{
  private function validateNewVehicleSale(Request $request)
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
      // ... other validation rules
    ];

    return $request->validate($rules);
  }

  public function index(Request $request)
  {
    validate_permission('new_vehicle_sales.read');

    // Handle search functionality
    $search = $request->input('search');
    $query = NewVehicleSale::with('customer');

    if ($search) {
      $query->where('order_booking_number', 'like', '%' . $search . '%')
        ->orWhere('total_amount', 'like', '%' . $search . '%')
        ->orWhereHas('customer', function ($q) use ($search) {
          $q->where('name', 'like', '%' . $search . '%');
        })
        ->orWhere('sales_exec_name', 'like', '%' . $search . '%')
        ->orderBy('created_at', 'desc');
    }

    $newVehicleSales = $query->paginate(10);

    return view('admin.new-vehicle-sales.index', compact('newVehicleSales', 'search'));
  }

  public function create()
  {
    validate_permission('new_vehicle_sales.create');

    $customers = Customer::all();
    return view('admin.new-vehicle-sales.create', compact('customers'));
  }

  public function store(Request $request)
  {
    validate_permission('new_vehicle_sales.create');

    DB::beginTransaction();
    try {
      // Validate the request
      $validated = $this->validateNewVehicleSale($request);

      // Check if we need to create a new customer
      if (empty($request->customer_id) && $request->customer_name && $request->customer_phone_no) {
        $customer = Customer::create([
          'name' => $request->customer_name,
          'phone_no' => $request->customer_phone_no,
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

      $newVehicleSale = NewVehicleSale::create($data);

      // Process payments
      $amountPaid = 0;
      foreach ($request->input('payments', []) as $payment) {
        $paymentData = [
          'new_vehicle_sale_id' => $newVehicleSale->id, // Assuming 'new_vehicle_sale_id' is the foreign key in the payments table
          'payment_by' => $payment['payment_by'],
          'payment_date' => $payment['payment_date'],
          'amount' => $payment['amount'],
          'reference_number' => null,
          'bank_name' => null,
          'approved_by' => null,
          'discount_note_no' => null,
          'approved_note_no' => null,
          'institution_name' => null,
          'credit_instrument' => null,
        ];

        // Add additional fields based on the payment method
        switch ($payment['payment_by']) {
          case 'cheque':
            $paymentData['reference_number'] = $payment['reference_number'] ?? null;
            $paymentData['bank_name'] = $payment['bank_name'] ?? null;
            break;
          case 'bank_transfer':
            $paymentData['reference_number'] = $payment['reference_number'] ?? null;
            $paymentData['bank_name'] = $payment['bank_name'] ?? null;
            break;
          case 'card':
            $paymentData['reference_number'] = $payment['reference_number'] ?? null;
            break;
          case 'advance_adjustment':
            $paymentData['reference_number'] = $payment['reference_number'] ?? null;
            break;
          case 'discount':
            $paymentData['approved_by'] = $payment['approved_by'] ?? null;
            $paymentData['discount_note_no'] = $payment['discount_note_no'] ?? null;
            break;
          case 'credit_individual':
            $paymentData['approved_by'] = $payment['approved_by'] ?? null;
            $paymentData['approved_note_no'] = $payment['approved_note_no'] ?? null;
            break;
          case 'credit_institutional':
            $paymentData['approved_by'] = $payment['approved_by'] ?? null;
            $paymentData['institution_name'] = $payment['institution_name'] ?? null;
            $paymentData['credit_instrument'] = $payment['credit_instrument'] ?? null;
            $paymentData['reference_number'] = $payment['reference_number'] ?? null;
            break;
        }

        PaymentNewVehicle::create($paymentData);
        $amountPaid += $payment['amount'];
      }

      // Update amount paid and balance
      $newVehicleSale->update([
        'amount_paid' => $amountPaid,
        'balance' => $newVehicleSale->total_amount - $amountPaid,
      ]);

      DB::commit();

      if ($request->input('action') === 'save_generate_receipt') {
        return redirect()->route('admin.new-vehicle-sales.receipt', $newVehicleSale);
      }

      return redirect()
        ->route('admin.new-vehicle-sales.index')
        ->with('success', 'New Vehicle Sale created successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'Error creating New Vehicle Sale: ' . $e->getMessage());
    }
  }

  public function edit(NewVehicleSale $newVehicleSale)
  {
    validate_permission('new_vehicle_sales.update');

    $customers = Customer::all();
    $newVehicleSale->load(['payments', 'customer']);
    $balance = $newVehicleSale->total_amount - $newVehicleSale->amount_paid;

    return view('admin.new-vehicle-sales.edit', compact('newVehicleSale', 'customers', 'balance'));
  }

  public function update(Request $request, NewVehicleSale $newVehicleSale)
  {
    validate_permission('new_vehicle_sales.update');

    $data = $request->only(['customer_id', 'order_booking_number', 'total_amount', 'so_name']);
    $newVehicleSale->update($data);

    $newVehicleSale->payments()->delete();

    $payments = $request->input('payments', []);
    $amountPaid = 0;
    foreach ($payments as $index => $payment) {

      $paymentData = [
        'new_vehicle_sale_id' => $newVehicleSale->id,
        'payment_by' => $payment['payment_by'],
        'payment_date' => $payment['payment_date'],
        'amount' => $payment['amount'],
        'reference_number' => null,
        'bank_name' => null,
        'approved_by' => null,
        'discount_note_no' => null,
        'approved_note_no' => null,
        'institution_name' => null,
        'credit_instrument' => null,
      ];

      // Add additional fields based on the payment method
      switch ($payment['payment_by']) {
        case 'cheque':
          $paymentData['reference_number'] = $payment['reference_number'] ?? null;
          $paymentData['bank_name'] = $payment['bank_name'] ?? null;
          break;
        case 'bank_transfer':
          $paymentData['reference_number'] = $payment['reference_number'] ?? null;
          $paymentData['bank_name'] = $payment['bank_name'] ?? null;
          break;
        case 'card':
          $paymentData['reference_number'] = $payment['reference_number'] ?? null;
          break;
        case 'advance_adjustment':
          $paymentData['reference_number'] = $payment['reference_number'] ?? null;
          break;
        case 'discount':
          $paymentData['approved_by'] = $payment['approved_by'] ?? null;
          $paymentData['discount_note_no'] = $payment['discount_note_no'] ?? null;
          break;
        case 'credit_individual':
          $paymentData['approved_by'] = $payment['approved_by'] ?? null;
          $paymentData['approved_note_no'] = $payment['approved_note_no'] ?? null;
          break;
        case 'credit_institutional':
          $paymentData['approved_by'] = $payment['approved_by'] ?? null;
          $paymentData['institution_name'] = $payment['institution_name'] ?? null;
          $paymentData['credit_instrument'] = $payment['credit_instrument'] ?? null;
          $paymentData['reference_number'] = $payment['reference_number'] ?? null;
          break;
      }
      echo "Payment Data After Processing: ";

      PaymentNewVehicle::create($paymentData);
      $amountPaid += $payment['amount'];
    }

    // Update amount paid and balance
    $newVehicleSale->update([
      'amount_paid' => $amountPaid,
      'balance' => $data['total_amount'] - $amountPaid
    ]);
    if ($request->input('action') === 'save_generate_receipt') {
      // Redirect to the receipt view
      return redirect()->route('admin.new-vehicle-sales.receipt', $newVehicleSale);
    }

    // Redirect to a simple confirmation page or index
    return redirect()->route('admin.new-vehicle-sales.index')
      ->with('success', 'New Vehicle Sale updated successfully!');
  }

  public function destroy(NewVehicleSale $newVehicleSale)
  {
    validate_permission('new_vehicle_sales.delete');

    $newVehicleSale->delete();
    return redirect()->route('admin.new-vehicle-sales.index')->with('success', 'New Vehicle Sale deleted successfully!');
  }

  public function receipt(NewVehicleSale $newVehicleSale)
  {
    $newVehicleSale->load(['customer', 'payments']);

    // Convert amount to words
    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    $amountInWords = $f->format($newVehicleSale->amount_paid);

    return view('admin.new-vehicle-sales.receipt', compact('newVehicleSale', 'amountInWords'));
  }
}
