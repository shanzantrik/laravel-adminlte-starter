<div class="row">
  <div class="col-md-8">
    <!-- Phone Number Search - Full Width -->
    <div class="form-group position-relative">
      <label for="customer_search">Phone Number</label>
      <input type="text" id="customer_search" class="form-control"
        placeholder="Search customer by phone number by entering first 3 digits or more">
      <input type="hidden" id="customer_id" name="customer_id">
      <input type="hidden" id="customer_phone_no" name="customer_phone_no"
        value="{{ old('customer_phone_no', isset($usedCarSale->customer) ? $usedCarSale->customer->phone_no : '') }}">
      <ul id="customer_results" class="list-group position-absolute w-100" style="z-index: 1000; display: none;">
      </ul>
    </div>

    <!-- Customer Name and PAN in one row -->
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label for="customer_name">Customer Name</label>
          <input type="text" id="customer_name" name="customer_name" class="form-control" readonly
            value="{{ old('customer_name', isset($usedCarSale->customer) ? $usedCarSale->customer->name : '') }}">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label for="customer_pan_no">PAN Number</label>
          <input type="text" id="customer_pan_no" name="customer_pan_no" class="form-control" readonly
            value="{{ old('customer_pan_no', isset($usedCarSale->customer) ? $usedCarSale->customer->pan_number : '') }}">
        </div>
      </div>
    </div>

    <!-- Order Booking Number -->
    <div class="form-group">
      <label for="order_booking_number">{{ __('Order Booking Number*') }}</label>
      <input type="text" name="order_booking_number"
        class="form-control @error('order_booking_number') is-invalid @enderror" id="order_booking_number"
        placeholder="Enter booking number"
        value="{{ old('order_booking_number', $usedCarSale->order_booking_number ?? '') }}" required>
      @error('order_booking_number')
      <small class="error invalid-feedback" role="alert">{{ $message }}</small>
      @enderror
    </div>

    <!-- Vehicle Registration Number -->
    <div class="form-group">
      <label for="vehicle_registration_no">{{ __('Vehicle Registration Number*') }}</label>
      <input type="text" name="vehicle_registration_no"
        class="form-control @error('vehicle_registration_no') is-invalid @enderror" id="vehicle_registration_no"
        placeholder="Enter vehicle registration number"
        value="{{ old('vehicle_registration_no', $usedCarSale->vehicle_registration_no ?? '') }}" required>
      @error('vehicle_registration_no')
      <small class="error invalid-feedback" role="alert">{{ $message }}</small>
      @enderror
    </div>

    <!-- Car Details in one row -->
    <div class="row">
      <div class="col-md-4">
        <div class="form-group">
          <label for="car_maker">{{ __('Car Maker*') }}</label>
          <input type="text" name="car_maker" class="form-control @error('car_maker') is-invalid @enderror"
            id="car_maker" placeholder="Enter car maker" value="{{ old('car_maker', $usedCarSale->car_maker ?? '') }}"
            required>
          @error('car_maker')
          <small class="error invalid-feedback" role="alert">{{ $message }}</small>
          @enderror
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label for="car_model">{{ __('Car Model*') }}</label>
          <input type="text" name="car_model" class="form-control @error('car_model') is-invalid @enderror"
            id="car_model" placeholder="Enter car model" value="{{ old('car_model', $usedCarSale->car_model ?? '') }}"
            required>
          @error('car_model')
          <small class="error invalid-feedback" role="alert">{{ $message }}</small>
          @enderror
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label for="car_color">{{ __('Car Color*') }}</label>
          <input type="text" name="car_color" class="form-control @error('car_color') is-invalid @enderror"
            id="car_color" placeholder="Enter car color" value="{{ old('car_color', $usedCarSale->car_color ?? '') }}"
            required>
          @error('car_color')
          <small class="error invalid-feedback" role="alert">{{ $message }}</small>
          @enderror
        </div>
      </div>
    </div>

    <!-- Sales Executive Name -->
    <div class="form-group">
      <label for="so_name">{{ __('So Name') }}</label>
      <input type="text" name="so_name" class="form-control @error('so_name') is-invalid @enderror" id="so_name"
        placeholder="Enter So Name" value="{{ old('so_name', $usedCarSale->so_name ?? '') }}" required>
      @error('so_name')
      <small class="error invalid-feedback" role="alert">{{ $message }}</small>
      @enderror
    </div>

    <!-- Total Amount -->
    <div class="form-group">
      <label for="total_amount">{{ __('Total Amount*') }}</label>
      <input type="number" step="0.01" name="total_amount"
        class="form-control @error('total_amount') is-invalid @enderror" id="total_amount"
        placeholder="Enter total amount" value="{{ old('total_amount', $usedCarSale->total_amount ?? '') }}" required>
      @error('total_amount')
      <small class="error invalid-feedback" role="alert">{{ $message }}</small>
      @enderror
    </div>

    <!-- Payment Method Entries -->
    <div id="paymentEntries">
      <h5>Payments</h5>
      <div id="paymentsContainer">
        <!-- Existing payments will be populated here dynamically for update -->
      </div>
      <button type="button" class="btn btn-secondary mb-2" onclick="addPaymentRow()">Add Payment</button>
    </div>

    <!-- Form Buttons -->
    <div class="form-group d-flex justify-content-between mt-4">
      <div>
        <button type="submit" class="btn btn-primary" name="action" value="save">
          {{ isset($usedCarSale) ? 'Update' : 'Save' }}
        </button>
        <button type="submit" class="btn btn-info" name="action" value="save_generate_receipt">
          {{ isset($usedCarSale) ? 'Update and Generate Receipt' : 'Save and Generate Receipt' }}
        </button>
      </div>
      <a href="{{ route('admin.used-car-sales.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </div>

  <!-- Payment Summary Column -->
  <div class="col-md-4">
    <h4>Complete Payment Details</h4>
    <div id="paymentDetails">
      <p>Total Amount: ₹<span id="totalAmountDisplay">0.00</span></p>
      <p>Total Amount Paid: ₹<span id="amountPaidDisplay">0.00</span></p>
      <p>Balance: ₹<span id="balanceDisplay">0.00</span></p>
      <h4>Individual Payments Details</h4>
      <div id="individualPayments">
        <!-- Individual payment methods and amounts will be displayed here dynamically -->
      </div>
    </div>
    <div class="text-left">
      @if(isset($usedCarSale) && $usedCarSale->id)
      <a href="{{ route('admin.used-car-sales.receipt', $usedCarSale) }}" class="btn btn-info" target="_blank">
        <i class="fas fa-print"></i> Generate Receipt
      </a>
      @endif
    </div>
  </div>
</div>

@include('admin.used-car-sales._scripts')
