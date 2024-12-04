<div class="form-group">
  <label for="name">{{ __('Name*') }}</label>
  <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name"
    placeholder="Enter customer name" value="{{ old('name', $customer->name ?? '') }}">
  @error('name')
  <small class="error invalid-feedback" role="alert">{{ $message }}</small>
  @enderror
</div>

<div class="form-group">
  <label for="phone_no">{{ __('Phone No*') }}</label>
  <input type="text" name="phone_no" class="form-control @error('phone_no') is-invalid @enderror" id="phone_no"
    placeholder="Enter customer phone number" value="{{ old('phone_no', $customer->phone_no ?? '') }}">
  @error('phone_no')
  <small class="error invalid-feedback" role="alert">{{ $message }}</small>
  @enderror
</div>

<div class="form-group">
  <label for="vehicle_registration_no">{{ __('Vehicle Registration No*') }}</label>
  <input type="text" name="vehicle_registration_no"
    class="form-control @error('vehicle_registration_no') is-invalid @enderror" id="vehicle_registration_no"
    placeholder="Enter vehicle registration number"
    value="{{ old('vehicle_registration_no', $customer->vehicle_registration_no ?? '') }}">
  @error('vehicle_registration_no')
  <small class="error invalid-feedback" role="alert">{{ $message }}</small>
  @enderror
</div>
