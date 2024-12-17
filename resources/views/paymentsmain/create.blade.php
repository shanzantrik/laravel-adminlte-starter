@extends('layouts.admin')

@section('content')
<div class="container">
  <h2>{{ isset($payment) ? 'Edit Payment' : 'Add Payment' }}</h2>

  <form method="POST"
    action="{{ isset($payment) ? route('paymentsmain.update', $payment->id) : route('paymentsmain.store') }}">
    @csrf
    @if(isset($payment)) @method('PUT') @endif

    @include('paymentsmain._form')

    <button type="submit" class="btn btn-success mt-3">{{ isset($payment) ? 'Update' : 'Submit' }}</button>
  </form>
</div>
@endsection
