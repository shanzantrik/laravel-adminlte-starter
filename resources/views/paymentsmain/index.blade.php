@extends('layouts.admin')

@section('main')
<div class="container">
  <h2>Payments Main</h2>
  <a href="{{ route('paymentsmain.create') }}" class="btn btn-primary mb-3">Add New Payment</a>

  @if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Payment Type</th>
        <th>Amount</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($payments as $payment)
      <tr>
        <td>{{ $payment->id }}</td>
        <td>{{ ucfirst($payment->payment_type) }}</td>
        <td>{{ number_format($payment->amount, 2) }}</td>
        <td>
          <a href="{{ route('paymentsmain.edit', $payment->id) }}" class="btn btn-sm btn-warning">Edit</a>
          <form action="{{ route('paymentsmain.destroy', $payment->id) }}" method="POST" style="display:inline;">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger"
              onclick="return confirm('Delete this payment?')">Delete</button>
          </form>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="4">No Payments Found</td>
      </tr>
      @endforelse
    </tbody>
  </table>
  {{ $payments->links() }}
</div>
@endsection
