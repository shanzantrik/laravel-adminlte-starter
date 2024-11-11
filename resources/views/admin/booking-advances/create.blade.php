@extends('layouts.admin')

@section('title', 'Add Booking Advance')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Add a New Booking Advance</h3>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.booking-advances.store') }}" method="POST">
      @csrf

      @include('admin.booking-advances._form')

      <div class="form-group">
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        <a href="{{ route('admin.booking-advances.index') }}" class="btn btn-default">{{ __('Cancel') }}</a>
      </div>
    </form>
  </div>
</div>
@endsection
