@extends('layouts.admin')

@section('title', 'Edit Booking Advance')

@section('main')
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">Edit Booking Advance</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.booking-advances.update', $bookingAdvance) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.booking-advances._form')

            <div class="form-group">
                <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                <a href="{{ route('admin.booking-advances.index') }}" class="btn btn-default">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
