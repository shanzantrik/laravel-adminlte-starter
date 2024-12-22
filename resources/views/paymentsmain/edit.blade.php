@extends('layouts.admin')

@section('main')
<div class="container">
  <h2>{{ isset($payment) ? 'Edit Payment' : 'Add Payment' }}</h2>

  <form action="{{ route('paymentsmain.update', $payment) }}" method="POST">
    @csrf
    @method('PUT')

    @include('paymentsmain._form')

    <button type="submit" class="btn btn-success mt-3">{{ isset($payment) ? 'Update' : 'Submit' }}</button>
  </form>
</div>
@endsection
