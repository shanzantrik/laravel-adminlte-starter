@extends('layouts.admin')

@section('title', 'Edit Used Car Advance')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Edit Used Car Advance</h3>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.used-car-advances.update', $usedCarAdvance) }}" method="POST" id="sale-form">
      @csrf
      @method('PUT')
      @include('admin.used-car-advances._form')
    </form>
  </div>
</div>
@endsection
