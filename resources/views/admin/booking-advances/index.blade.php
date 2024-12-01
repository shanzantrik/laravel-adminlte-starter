@extends('layouts.admin')

@section('title', 'Booking Advances')

@section('main')
<div class="row">
  <div class="col-6"></div>
  <div class="col-6">
    <a href="{{ route('admin.booking-advances.create') }}" class="mt-3 btn btn-primary float-right">
      <i class="fas fa-plus mr-1"></i> {{ __('New Booking Advance') }}
    </a>
  </div>
</div>

<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Booking Advances</h3>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('admin.booking-advances.index') }}" class="mb-3">
      <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Search by Order Number, Amount, or Customer"
          value="{{ request('search') }}">
        <div class="input-group-append">
          <button class="btn btn-primary" type="submit">Search</button>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered table-sm">
        <thead>
          <tr>
            <th>Order Booking Number</th>
            <th>Total Amount</th>
            <th>Amount Paid</th>
            <th>Balance</th>
            <th>Customer</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($bookingAdvances as $advance)
          <tr>
            <td>{{ $advance->order_booking_number }}</td>
            <td>{{ $advance->total_amount }}</td>
            <td>{{ $advance->amount_paid }}</td>
            <td>{{ $advance->balance }}</td>
            <td>{{ $advance->customer->name ?? 'N/A' }}</td>
            <td>
              <a href="{{ route('admin.booking-advances.edit', $advance) }}" class="btn btn-sm btn-primary">Edit</a>
              <a href="{{ route('admin.booking-advances.receipt', $advance) }}" class="btn btn-sm btn-info"
                target="_blank">
                <i class="fas fa-print"></i> Receipt
              </a>
              <form action="{{ route('admin.booking-advances.destroy', $advance) }}" method="POST"
                style="display: inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger"
                  onclick="return confirm('Are you sure?')">Delete</button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center">No Booking Advances Found</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
      {{ $bookingAdvances->appends(['search' => $search])->links() }}
    </div>
  </div>
</div>
@endsection
