@extends('layouts.admin')

@section('title', 'Edit Extended Warranty')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Edit Extended Warranty</h3>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.extended-warranties.update', $extendedWarranty) }}" method="POST" id="sale-form">
      @csrf
      @method('PUT')
      @include('admin.extended-warranties._form')
    </form>
  </div>
</div>
@endsection
