<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingAdvance;
use App\Models\Payment;
use App\Models\Customer;
use Illuminate\Http\Request;

class BookingAdvanceController extends Controller
{
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
                });
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

        $data = $request->only(['customer_id', 'order_booking_number', 'total_amount']);
        $bookingAdvance = BookingAdvance::create($data);

        // $payments = $request->input('payments', []);
        $amountPaid = 0;
        foreach ($request->input('payments', []) as $payment) {
            $paymentData = [
                'booking_advance_id' => $bookingAdvance->id,
                'payment_by' => $payment['payment_by'],
                'payment_date' => $payment['payment_date'],
                'amount' => $payment['amount'],
                'reference_number' => $payment['reference_number'] ?? $payment['neft_ref_no'] ?? $payment['card_transaction_id'] ?? $payment['advance_adjustment_ref'] ?? null,
                'bank_name' => $payment['bank_name'] ?? null
            ];

            // Add additional fields based on the payment method
            if ($payment['payment_by'] === 'cheque') {
                $paymentData['reference_number'] = $payment['reference_number'] ?? null;  // Ensure it's passed from the form
                $paymentData['bank_name'] = $payment['bank_name'] ?? null;
            } elseif ($payment['payment_by'] === 'bank_transfer') {
                $paymentData['reference_number'] = $payment['neft_ref_no'] ?? null;
                $paymentData['bank_name'] = $payment['bank_name'] ?? null;
            } elseif ($payment['payment_by'] === 'card') {
                $paymentData['reference_number'] = $payment['card_transaction_id'] ?? null;
            } elseif ($payment['payment_by'] === 'advance_adjustment') {
                $paymentData['reference_number'] = $payment['advance_adjustment_ref'] ?? null;
            }
            Payment::create($paymentData);
            $amountPaid += $payment['amount'];
        }

        // Update amount paid and balance

        $bookingAdvance->update([
            'amount_paid' => $amountPaid,
            'balance' => $bookingAdvance->total_amount - $amountPaid,
        ]);

        return redirect()->route('admin.booking-advances.index')->with('success', 'Booking Advance created successfully!');
    }

    public function edit(BookingAdvance $bookingAdvance)
    {
        validate_permission('booking_advances.update');

        $customers = Customer::all();
        $bookingAdvance->load('payments');
        //dd($bookingAdvance->payments->toArray());
        return view('admin.booking-advances.edit', compact('bookingAdvance', 'customers'));
    }

    public function update(Request $request, BookingAdvance $bookingAdvance)
    {
        validate_permission('booking_advances.update');

        $data = $request->only(['customer_id', 'order_booking_number', 'total_amount']);
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
                'reference_number' => $payment['reference_number'] ?? $payment['neft_ref_no'] ?? $payment['card_transaction_id'] ?? $payment['advance_adjustment_ref'] ?? null,
                'bank_name' => $payment['bank_name'] ?? null
            ];

            // Add additional fields based on the payment method
            if ($payment['payment_by'] === 'cheque') {
                $paymentData['reference_number'] = $payment['reference_number'] ?? null;
                $paymentData['bank_name'] = $payment['bank_name'] ?? null;
            } elseif ($payment['payment_by'] === 'bank_transfer') {
                $paymentData['reference_number'] = $payment['neft_ref_no'] ?? null;
                $paymentData['bank_name'] = $payment['bank_name'] ?? null;
            } elseif ($payment['payment_by'] === 'card') {
                $paymentData['reference_number'] = $payment['card_transaction_id'] ?? null;
            } elseif ($payment['payment_by'] === 'advance_adjustment') {
                $paymentData['reference_number'] = $payment['advance_adjustment_ref'] ?? null;
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

        return redirect()->route('admin.booking-advances.index')->with('success', 'Booking Advance updated successfully!');
    }

    public function destroy(BookingAdvance $bookingAdvance)
    {
        validate_permission('booking_advances.delete');

        $bookingAdvance->delete();
        return redirect()->route('admin.booking-advances.index')->with('success', 'Booking Advance deleted successfully!');
    }
}
