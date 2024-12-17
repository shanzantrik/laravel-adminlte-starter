<?php

namespace App\Http\Controllers\Admin;

use App\Models\Paymentmain;
use App\Models\PaymentCheque;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentMainController extends Controller
{
  public function index()
  {
    $payments = Paymentmain::with('cheques')->latest()->paginate(10);
    return view('paymentsmain.index', compact('payments'));
  }
  public function create()
  {
    return view('paymentsmain.create');
  }
  public function store(Request $request)
  {
    $validated = $request->validate([
      'payment_type' => 'required|string|in:cash,cheque',
      'amount' => 'required|numeric|min:0',
      'denominations' => 'nullable|array',
      'no_of_cheques' => 'nullable|integer|min:1',
      'cheques' => 'nullable|array',
      'cheques.*.number' => 'required_with:cheques|string',
      'cheques.*.date' => 'required_with:cheques|date',
    ]);

    $payment = Paymentmain::create([
      'payment_type' => $validated['payment_type'],
      'amount' => $validated['amount'],
      'denominations' => $validated['payment_type'] === 'cash' ? $validated['denominations'] : null,
      'no_of_cheques' => $validated['payment_type'] === 'cheque' ? $validated['no_of_cheques'] : null,
    ]);

    if ($validated['payment_type'] === 'cheque' && isset($validated['cheques'])) {
      foreach ($validated['cheques'] as $cheque) {
        PaymentCheque::create([
          'payment_id' => $payment->id,
          'cheque_number' => $cheque['number'],
          'cheque_date' => $cheque['date'],
        ]);
        // if ($validated['payment_type'] === 'cheque' && isset($validated['cheques'])) {
        //   foreach ($validated['cheques'] as $cheque) {
        //     PaymentCheque::create([
        //       'payment_id' => $payment->id,
        //       'cheque_number' => $cheque['number'],
        //       'cheque_date' => $cheque['date'],
        //     ]);
        //   }
        // }
      }
    }

    return redirect()->route('paymentsmain.index')->with('success', 'Payment created successfully!');
  }

  public function edit(Paymentmain $payment)
  {
    return view('paymentsmain.edit', compact('payment'));
  }

  public function update(Request $request, Paymentmain $payment)
  {
    $validated = $request->validate([
      'payment_type' => 'required|string|in:cash,cheque',
      'amount' => 'required|numeric|min:0',
      'denominations' => 'nullable|array',
      'no_of_cheques' => 'nullable|integer|min:1',
      'cheques' => 'nullable|array',
      'cheques.*.number' => 'required_with:cheques|string',
      'cheques.*.date' => 'required_with:cheques|date',
    ]);

    $payment->update([
      'payment_type' => $validated['payment_type'],
      'amount' => $validated['amount'],
      'denominations' => $validated['payment_type'] === 'cash' ? $validated['denominations'] : null,
      'no_of_cheques' => $validated['payment_type'] === 'cheque' ? $validated['no_of_cheques'] : null,
    ]);

    $payment->cheques()->delete();

    if ($validated['payment_type'] === 'cheque' && isset($validated['cheques'])) {
      foreach ($validated['cheques'] as $cheque) {
        PaymentCheque::create([
          'payment_id' => $payment->id,
          'cheque_number' => $cheque['number'],
          'cheque_date' => $cheque['date'],
        ]);
      }
    }

    return redirect()->route('paymentsmain.index')->with('success', 'Payment updated successfully!');
  }

  public function destroy(Paymentmain $payment)
  {
    $payment->delete();
    return redirect()->route('paymentsmain.index')->with('success', 'Payment deleted successfully!');
  }
}
