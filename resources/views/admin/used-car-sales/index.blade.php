@extends('layouts.admin')

@section('title', 'Used Car Sales')

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
    <a href="{{ route('admin.used-car-sales.create') }}" class="mt-3 btn btn-primary float-right">
      <i class="fas fa-plus mr-1"></i> {{ __('Used Car Sale') }}
    </a>
  </div>
</div>

<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Used Car Sales</h3>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('admin.used-car-sales.index') }}" class="mb-3">
      <div class="input-group">
        <input type="text" name="search" class="form-control"
          placeholder="Search by Order Number, Vehicle No, Car Details, Amount, Customer, or Sales Executive"
          value="{{ request('search') }}">
        <div class="input-group-append">
          <button class="btn btn-primary" type="submit">Search</button>
          <a href="{{ route('admin.used-car-sales.index') }}" class="btn btn-secondary ml-2">Clear</a>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered table-sm">
        <thead>
          <tr>
            <th>Order Booking Number</th>
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
          @forelse ($usedCarSales as $usedCarSale)
          <tr>
            <td>{{ $usedCarSale->order_booking_number }}</td>
            <td>{{ $usedCarSale->vehicle_registration_no }}</td>
            <td>
              {{ $usedCarSale->car_maker }} {{ $usedCarSale->car_model }}<br>
              <small class="text-muted">Color: {{ $usedCarSale->car_color }}</small>
            </td>
            <td>₹{{ number_format($usedCarSale->total_amount, 2) }}</td>
            <td>₹{{ number_format($usedCarSale->amount_paid, 2) }}</td>
            <td>₹{{ number_format($usedCarSale->balance, 2) }}</td>
            <td>{{ $usedCarSale->customer->name ?? 'N/A' }}</td>
            <td>{{ $usedCarSale->so_name ?? 'N/A' }}</td>
            <td>
              <a href="{{ route('admin.used-car-sales.edit', $usedCarSale) }}" class="btn btn-sm btn-primary">Edit</a>
              <a href="{{ route('admin.used-car-sales.receipt', $usedCarSale) }}" class="btn btn-sm btn-info"
                target="_blank">
                <i class="fas fa-print"></i> Receipt
              </a>
              <form action="{{ route('admin.used-car-sales.destroy', $usedCarSale) }}" method="POST"
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
            <td colspan="9" class="text-center">No Used Car Sales Found</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <nav aria-label="Page navigation">
      <div class="d-flex justify-content-between align-items-center">
        <div>Showing {{ $usedCarSales->firstItem() }} to {{ $usedCarSales->lastItem() }} of {{
          $usedCarSales->total() }} results</div>
        <ul class="pagination mb-0">
          {{ $usedCarSales->appends(['search' => $search])->onEachSide(1)->links() }}
        </ul>
      </div>
    </nav>
  </div>
</div>
@endsection
