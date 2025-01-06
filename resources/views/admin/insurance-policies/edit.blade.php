@extends('layouts.admin')

@section('title', 'Edit Insurance Policy')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Edit Insurance Policy</h3>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.insurance-policies.update', $insurancePolicy) }}" method="POST" id="sale-form">
      @csrf
      @method('PUT')
      @include('admin.insurance-policies._form')
    </form>
  </div>
</div>
@endsection
