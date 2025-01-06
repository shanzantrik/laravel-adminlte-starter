@extends('layouts.admin')

@section('title', 'Edit Insurance Advance')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Edit Insurance Advance</h3>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.insurance-advances.update', $insuranceAdvance) }}" method="POST" id="sale-form">
      @csrf
      @method('PUT')
      @include('admin.insurance-advances._form')
    </form>
  </div>
</div>
@endsection
