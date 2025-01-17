@extends('layouts.admin')

@section('title', 'New Vehicle Sales')

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
    <a href="{{ route('admin.new-vehicle-sales.create') }}" class="mt-3 btn btn-primary float-right">
      <i class="fas fa-plus mr-1"></i> {{ __('New Vehicle Sale') }}
    </a>
  </div>
</div>

<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">New Vehicle Sales</h3>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('admin.new-vehicle-sales.index') }}" class="mb-3">
      <div class="input-group">
        <input type="text" name="search" class="form-control"
          placeholder="Search by Order Number, Amount, Customer, or Sales Executive" value="{{ request('search') }}">
        <div class="input-group-append">
          <button class="btn btn-primary" type="submit">Search</button>
          <a href="{{ route('admin.new-vehicle-sales.index') }}" class="btn btn-secondary ml-2">Clear</a>
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
            <th>Sales Executive</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($newVehicleSales as $newVehicleSale)
          <tr>
            <td>{{ $newVehicleSale->order_booking_number }}</td>
            <td>₹{{ number_format($newVehicleSale->total_amount, 2) }}</td>
            <td>₹{{ number_format($newVehicleSale->amount_paid, 2) }}</td>
            <td>₹{{ number_format($newVehicleSale->balance, 2) }}</td>
            <td>{{ $newVehicleSale->customer->name ?? 'N/A' }}</td>
            <td>{{ $newVehicleSale->so_name ?? 'N/A' }}</td>
            <td>
              <a href="{{ route('admin.new-vehicle-sales.edit', $newVehicleSale) }}"
                class="btn btn-sm btn-primary">Edit</a>
              <a href="{{ route('admin.new-vehicle-sales.receipt', $newVehicleSale) }}" class="btn btn-sm btn-info"
                target="_blank">
                <i class="fas fa-print"></i> Receipt
              </a>
              <form action="{{ route('admin.new-vehicle-sales.destroy', $newVehicleSale) }}" method="POST"
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
            <td colspan="5" class="text-center">No New Vehicle Sales Found</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <nav aria-label="Page navigation">
      <div class="d-flex justify-content-between align-items-center">
        <div>Showing {{ $newVehicleSales->firstItem() }} to {{ $newVehicleSales->lastItem() }} of {{
          $newVehicleSales->total() }} results</div>
        <ul class="pagination mb-0">
          {{ $newVehicleSales->appends(['search' => $search])->onEachSide(1)->links() }}
        </ul>
      </div>
    </nav>
  </div>
</div>
@endsection
