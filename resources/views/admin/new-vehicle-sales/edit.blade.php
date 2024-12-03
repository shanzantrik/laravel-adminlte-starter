@extends('layouts.admin')

@section('title', 'Edit Vehicle Sale')

@section('main')
<div class="row">
  <div class="col-12">
    <div class="card mt-3">
      <div class="card-header">
        <h3 class="card-title">Edit Vehicle Sale</h3>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.new-vehicle-sales.update', $sale) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="form-group">
            <label for="invoice_number">Invoice Number</label>
            <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror"
              id="invoice_number" value="{{ old('invoice_number', $sale->invoice_number) }}">
            @error('invoice_number')
            <small class="error invalid-feedback">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group">
            <label for="customer_id">Customer</label>
            <select name="customer_id" class="form-control @error('customer_id') is-invalid @enderror" id="customer_id">
              <option value="">Select Customer</option>
              @foreach($customers as $customer)
              <option value="{{ $customer->id }}" {{ old('customer_id', $sale->customer_id) == $customer->id ?
                'selected' : '' }}>
                {{ $customer->name }}
              </option>
              @endforeach
            </select>
            @error('customer_id')
            <small class="error invalid-feedback">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group">
            <label for="vehicle_model">Vehicle Model</label>
            <input type="text" name="vehicle_model" class="form-control @error('vehicle_model') is-invalid @enderror"
              id="vehicle_model" value="{{ old('vehicle_model', $sale->vehicle_model) }}">
            @error('vehicle_model')
            <small class="error invalid-feedback">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group">
            <label for="chassis_number">Chassis Number</label>
            <input type="text" name="chassis_number" class="form-control @error('chassis_number') is-invalid @enderror"
              id="chassis_number" value="{{ old('chassis_number', $sale->chassis_number) }}">
            @error('chassis_number')
            <small class="error invalid-feedback">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group">
            <label for="engine_number">Engine Number</label>
            <input type="text" name="engine_number" class="form-control @error('engine_number') is-invalid @enderror"
              id="engine_number" value="{{ old('engine_number', $sale->engine_number) }}">
            @error('engine_number')
            <small class="error invalid-feedback">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group">
            <label for="color">Color</label>
            <input type="text" name="color" class="form-control @error('color') is-invalid @enderror" id="color"
              value="{{ old('color', $sale->color) }}">
            @error('color')
            <small class="error invalid-feedback">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" id="amount"
              value="{{ old('amount', $sale->amount) }}">
            @error('amount')
            <small class="error invalid-feedback">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group">
            <label for="payment_method">Payment Method</label>
            <input type="text" name="payment_method" class="form-control @error('payment_method') is-invalid @enderror"
              id="payment_method" value="{{ old('payment_method', $sale->payment_method) }}">
            @error('payment_method')
            <small class="error invalid-feedback">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror"
              id="remarks">{{ old('remarks', $sale->remarks) }}</textarea>
            @error('remarks')
            <small class="error invalid-feedback">{{ $message }}</small>
            @enderror
          </div>

          <button type="submit" class="btn btn-primary">Update Sale</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
