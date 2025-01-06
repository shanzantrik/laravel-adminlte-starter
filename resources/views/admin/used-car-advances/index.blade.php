@extends('layouts.admin')

@section('title', 'Used Car Advances')

@section('main')
@if(session('success'))
<div class="alert alert-success">
  {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger">
  {{ session('error') }}
</div>
@endif
<div class="row">
  <div class="col-6"></div>
  <div class="col-6">
    <a href="{{ route('admin.used-car-advances.create') }}" class="mt-3 btn btn-primary float-right">
      <i class="fas fa-plus mr-1"></i> {{ __('Used Car Advance') }}
    </a>
  </div>
</div>

<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Used Car Advances</h3>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('admin.used-car-advances.index') }}" class="mb-3">
      <div class="input-group">
        <input type="text" name="search" class="form-control"
          placeholder="Search by Order Number, RO/Job Number, Vehicle No, Car Details, Amount, Customer, or Sales Executive"
          value="{{ request('search') }}">
        <div class="input-group-append">
          <button class="btn btn-primary" type="submit">Search</button>
          <a href="{{ route('admin.used-car-advances.index') }}" class="btn btn-secondary ml-2">Clear</a>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered table-sm">
        <thead>
          <tr>
            <th>Order Booking Number</th>
            <th>RO/Job Number</th>
            <th>Vehicle Reg. No.</th>
            <th>Car Details</th>
            <th>Total Amount</th>
            <th>Amount Paid</th>
            <th>Balance</th>
            <th>Customer</th>
            <th>Sales Executive</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($usedCarAdvances as $usedCarAdvance)
          <tr>
            <td>{{ $usedCarAdvance->order_booking_number }}</td>
            <td>{{ $usedCarAdvance->ro_job_number }}</td>
            <td>{{ $usedCarAdvance->vehicle_registration_no }}</td>
            <td>
              {{ $usedCarAdvance->car_maker }} {{ $usedCarAdvance->car_model }}<br>
              <small class="text-muted">Color: {{ $usedCarAdvance->car_color }}</small>
            </td>
            <td>₹{{ number_format($usedCarAdvance->total_amount, 2) }}</td>
            <td>₹{{ number_format($usedCarAdvance->amount_paid, 2) }}</td>
            <td>₹{{ number_format($usedCarAdvance->balance, 2) }}</td>
            <td>{{ $usedCarAdvance->customer->name ?? 'N/A' }}</td>
            <td>{{ $usedCarAdvance->so_name ?? 'N/A' }}</td>
            <td>
              <a href="{{ route('admin.used-car-advances.edit', $usedCarAdvance) }}"
                class="btn btn-sm btn-primary">Edit</a>
              <a href="{{ route('admin.used-car-advances.receipt', $usedCarAdvance) }}" class="btn btn-sm btn-info"
                target="_blank">
                <i class="fas fa-print"></i> Receipt
              </a>
              <form action="{{ route('admin.used-car-advances.destroy', $usedCarAdvance) }}" method="POST"
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
            <td colspan="10" class="text-center">No Used Car Advances Found</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <nav aria-label="Page navigation">
      <div class="d-flex justify-content-between align-items-center">
        <div>Showing {{ $usedCarAdvances->firstItem() }} to {{ $usedCarAdvances->lastItem() }} of {{
          $usedCarAdvances->total() }} results</div>
        <ul class="pagination mb-0">
          {{ $usedCarAdvances->appends(['search' => $search])->onEachSide(1)->links() }}
        </ul>
      </div>
    </nav>
  </div>
</div>
@endsection
