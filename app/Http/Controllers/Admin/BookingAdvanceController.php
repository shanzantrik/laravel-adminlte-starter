<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingAdvance;
use App\Models\Payment;
use App\Models\Customer;
use Illuminate\Http\Request;
use NumberFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingAdvanceController extends Controller
{
    private function validateBookingAdvance(Request $request)
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
            'total_amount' => 'required|numeric|min:0',
            'sales_exec_name' => 'required|string|max:255',
            // ... other validation rules
        ];

        return $request->validate($rules);
    }

    public function index(Request $request)
    {
        validate_permission('booking_advances.read');

        // Handle search functionality
        $search = $request->input('search');
        $query = BookingAdvance::with('customer');

        if ($search) {
            $query->where('order_booking_number', 'like', '%' . $search . '%')
                ->orWhere('total_amount', 'like', '%' . $search . '%')
                ->orWhereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                ->orWhere('sales_exec_name', 'like', '%' . $search . '%')
                ->orderBy('created_at', 'desc');
        }

        $bookingAdvances = $query->paginate(10);

        return view('admin.booking-advances.index', compact('bookingAdvances', 'search'));
    }

    public function create()
    {
        validate_permission('booking_advances.create');

        $customers = Customer::all();
        return view('admin.booking-advances.create', compact('customers'));
    }

    public function store(Request $request)
    {
        validate_permission('booking_advances.create');

        DB::beginTransaction();
        try {
            // Validate the request
            $validated = $this->validateBookingAdvance($request);

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
                'total_amount',
                'sales_exec_name'
            ]);

            $bookingAdvance = BookingAdvance::create($data);

            // Process payments
            $amountPaid = 0;
            foreach ($request->input('payments', []) as $payment) {
                $paymentData = [
                    'booking_advance_id' => $bookingAdvance->id,
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

                Payment::create($paymentData);
                $amountPaid += $payment['amount'];
            }

            // Update amount paid and balance
            $bookingAdvance->update([
                'amount_paid' => $amountPaid,
                'balance' => $bookingAdvance->total_amount - $amountPaid,
            ]);

            DB::commit();

            if ($request->input('action') === 'save_generate_receipt') {
                return redirect()->route('admin.booking-advances.receipt', $bookingAdvance);
            }

            return redirect()
                ->route('admin.booking-advances.index')
                ->with('success', 'Booking Advance created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error creating booking advance: ' . $e->getMessage());
        }
    }

    public function edit(BookingAdvance $bookingAdvance)
    {
        validate_permission('booking_advances.update');

        $customers = Customer::all();
        $bookingAdvance->load(['payments', 'customer']);
        $balance = $bookingAdvance->total_amount - $bookingAdvance->amount_paid;

        return view('admin.booking-advances.edit', compact('bookingAdvance', 'customers', 'balance'));
    }

    public function update(Request $request, BookingAdvance $bookingAdvance)
    {
        validate_permission('booking_advances.update');

        $data = $request->only(['customer_id', 'order_booking_number', 'total_amount', 'sales_exec_name']);
        $bookingAdvance->update($data);

        $bookingAdvance->payments()->delete();

        $payments = $request->input('payments', []);
        $amountPaid = 0;
        foreach ($payments as $index => $payment) {

            $paymentData = [
                'booking_advance_id' => $bookingAdvance->id,
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

            Payment::create($paymentData);
            $amountPaid += $payment['amount'];
        }

        // Update amount paid and balance
        $bookingAdvance->update([
            'amount_paid' => $amountPaid,
            'balance' => $data['total_amount'] - $amountPaid
        ]);
        if ($request->input('action') === 'save_generate_receipt') {
            // Redirect to the receipt view
            return redirect()->route('admin.booking-advances.receipt', $bookingAdvance);
        }

        // Redirect to a simple confirmation page or index
        return redirect()->route('admin.booking-advances.index')
            ->with('success', 'Booking Advance updated successfully!');
    }

    public function destroy(BookingAdvance $bookingAdvance)
    {
        validate_permission('booking_advances.delete');

        $bookingAdvance->delete();
        return redirect()->route('admin.booking-advances.index')->with('success', 'Booking Advance deleted successfully!');
    }

    public function receipt(BookingAdvance $bookingAdvance)
    {
        $bookingAdvance->load(['customer', 'payments']);

        // Convert amount to words
        $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
        $amountInWords = $f->format($bookingAdvance->amount_paid);

        return view('admin.booking-advances.receipt', compact('bookingAdvance', 'amountInWords'));
    }
}
