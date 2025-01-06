@extends('layouts.admin')

@section('title', 'Counter Sales')

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
    <a href="{{ route('admin.counter-sales.create') }}" class="mt-3 btn btn-primary float-right">
      <i class="fas fa-plus mr-1"></i> {{ __('Counter Sale') }}
    </a>
  </div>
</div>

<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Counter Sales</h3>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('admin.counter-sales.index') }}" class="mb-3">
      <div class="input-group">
        <input type="text" name="search" class="form-control"
          placeholder="Search by Order Number, Invoice Number, Amount, or Customer" value="{{ request('search') }}">
        <div class="input-group-append">
          <button class="btn btn-primary" type="submit">Search</button>
          <a href="{{ route('admin.counter-sales.index') }}" class="btn btn-secondary ml-2">Clear</a>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered table-sm">
        <thead>
          <tr>
            <th>Order Booking Number</th>
            <th>Invoice Number</th>
            <th>Total Amount</th>
            <th>Amount Paid</th>
            <th>Balance</th>
            <th>Customer</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($counterSales as $counterSale)
          <tr>
            <td>{{ $counterSale->order_booking_number }}</td>
            <td>{{ $counterSale->invoice_number }}</td>
            <td>₹{{ number_format($counterSale->total_amount, 2) }}</td>
            <td>₹{{ number_format($counterSale->amount_paid, 2) }}</td>
            <td>₹{{ number_format($counterSale->balance, 2) }}</td>
            <td>{{ $counterSale->customer->name ?? 'N/A' }}</td>
            <td>
              <a href="{{ route('admin.counter-sales.edit', $counterSale) }}" class="btn btn-sm btn-primary">Edit</a>
              <a href="{{ route('admin.counter-sales.receipt', $counterSale) }}" class="btn btn-sm btn-info"
                target="_blank">
                <i class="fas fa-print"></i> Receipt
              </a>
              <form action="{{ route('admin.counter-sales.destroy', $counterSale) }}" method="POST"
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
            <td colspan="7" class="text-center">No Counter Sales Found</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <nav aria-label="Page navigation">
      <div class="d-flex justify-content-between align-items-center">
        <div>Showing {{ $counterSales->firstItem() }} to {{ $counterSales->lastItem() }} of {{
          $counterSales->total() }} results</div>
        <ul class="pagination mb-0">
          {{ $counterSales->appends(['search' => $search])->onEachSide(1)->links() }}
        </ul>
      </div>
    </nav>
  </div>
</div>
@endsection
