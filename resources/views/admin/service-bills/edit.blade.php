@extends('layouts.admin')

@section('title', 'Edit Service Bill')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Edit Service Bill</h3>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.service-bills.update', $serviceBill) }}" method="POST" id="sale-form">
      @csrf
      @method('PUT')
      @include('admin.service-bills._form')
    </form>
  </div>
</div>
@endsection
