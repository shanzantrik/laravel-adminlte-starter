<div class="form-group">
  <label for="invoice_number">Invoice Number</label>
  <input type="text" name="invoice_number" id="invoice_number" class="form-control"
    value="{{ old('invoice_number', $sale->invoice_number ?? '') }}" required>
</div>

<div class="form-group">
  <label for="customer_id">Customer</label>
  <select name="customer_id" id="customer_id" class="form-control" required>
    <option value="">Select Customer</option>
    @foreach($customers as $customer)
    <option value="{{ $customer->id }}" {{ (old('customer_id', $sale->customer_id ?? '') == $customer->id) ? 'selected'
      : '' }}>
      {{ $customer->name }} - {{ $customer->phone_no }}
    </option>
    @endforeach
  </select>
</div>

<div class="form-group">
  <label for="vehicle_model">Vehicle Model</label>
  <input type="text" name="vehicle_model" id="vehicle_model" class="form-control"
    value="{{ old('vehicle_model', $sale->vehicle_model ?? '') }}" required>
</div>

<div class="form-group">
  <label for="chassis_number">Chassis Number</label>
  <input type="text" name="chassis_number" id="chassis_number" class="form-control"
    value="{{ old('chassis_number', $sale->chassis_number ?? '') }}" required>
</div>

<div class="form-group">
  <label for="engine_number">Engine Number</label>
  <input type="text" name="engine_number" id="engine_number" class="form-control"
    value="{{ old('engine_number', $sale->engine_number ?? '') }}" required>
</div>

<div class="form-group">
  <label for="color">Color</label>
  <input type="text" name="color" id="color" class="form-control" value="{{ old('color', $sale->color ?? '') }}"
    required>
</div>

<div class="form-group">
  <label for="amount">Amount</label>
  <input type="number" step="0.01" name="amount" id="amount" class="form-control"
    value="{{ old('amount', $sale->amount ?? '') }}" required>
</div>

<div class="form-group">
  <label for="payment_method">Payment Method</label>
  <select name="payment_method" id="payment_method" class="form-control" required>
    <option value="">Select Payment Method</option>
    <option value="cash" {{ (old('payment_method', $sale->payment_method ?? '') == 'cash') ? 'selected' : '' }}>Cash
    </option>
    <option value="card" {{ (old('payment_method', $sale->payment_method ?? '') == 'card') ? 'selected' : '' }}>Card
    </option>
    <option value="bank_transfer" {{ (old('payment_method', $sale->payment_method ?? '') == 'bank_transfer') ?
      'selected' : '' }}>Bank Transfer</option>
  </select>
</div>

<div class="form-group">
  <label for="remarks">Remarks</label>
  <textarea name="remarks" id="remarks" class="form-control">{{ old('remarks', $sale->remarks ?? '') }}</textarea>
</div>
