@extends('layouts.admin')

@section('title', 'Edit Used Car Sale')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Edit Used Car Sale</h3>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.used-car-sales.update', $usedCarSale) }}" method="POST" id="sale-form">
      @csrf
      @method('PUT')
      @include('admin.used-car-sales._form')
    </form>
  </div>
</div>
@endsection
