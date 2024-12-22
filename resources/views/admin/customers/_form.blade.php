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
  <label for="pan_number">{{ __('PAN Number*') }}</label>
  <input type="text" name="pan_number" class="form-control @error('pan_number') is-invalid @enderror" id="pan_number"
    placeholder="Enter PAN number" value="{{ old('pan_number', $customer->pan_number ?? '') }}">
  @error('pan_number')
  <small class="error invalid-feedback" role="alert">{{ $message }}</small>
  @enderror
</div>
