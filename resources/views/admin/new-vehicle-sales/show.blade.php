@extends('layouts.admin')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Vehicle Sale Details</h1>
      </div>
      <div class="col-sm-6">
        <div class="float-right">
          @permission('new_vehicle_sales.update')
          <a href="{{ route('admin.new-vehicle-sales.edit', $sale) }}" class="btn btn-primary">
            Edit Sale
          </a>
          @endpermission
        </div>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <dl class="row">
              <dt class="col-sm-4">Invoice Number</dt>
              <dd class="col-sm-8">{{ $sale->invoice_number }}</dd>

              <dt class="col-sm-4">Customer Name</dt>
              <dd class="col-sm-8">{{ $sale->customer->name }}</dd>

              <dt class="col-sm-4">Vehicle Model</dt>
              <dd class="col-sm-8">{{ $sale->vehicle_model }}</dd>

              <dt class="col-sm-4">Chassis Number</dt>
              <dd class="col-sm-8">{{ $sale->chassis_number }}</dd>

              <dt class="col-sm-4">Engine Number</dt>
              <dd class="col-sm-8">{{ $sale->engine_number }}</dd>
            </dl>
          </div>
          <div class="col-md-6">
            <dl class="row">
              <dt class="col-sm-4">Color</dt>
              <dd class="col-sm-8">{{ $sale->color }}</dd>

              <dt class="col-sm-4">Amount</dt>
              <dd class="col-sm-8">{{ number_format($sale->amount, 2) }}</dd>

              <dt class="col-sm-4">Payment Method</dt>
              <dd class="col-sm-8">{{ ucfirst($sale->payment_method) }}</dd>

              <dt class="col-sm-4">Payment Status</dt>
              <dd class="col-sm-8">{{ ucfirst($sale->payment_status) }}</dd>

              <dt class="col-sm-4">Remarks</dt>
              <dd class="col-sm-8">{{ $sale->remarks ?? 'N/A' }}</dd>
            </dl>
          </div>
        </div>
        <div class="mt-4">
          <a href="{{ route('admin.new-vehicle-sales.index') }}" class="btn btn-link">Back to List</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
