@extends('layouts.admin')

@section('title', 'Edit Counter Sale')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Edit Counter Sale</h3>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.counter-sales.update', $counterSale) }}" method="POST" id="sale-form">
      @csrf
      @method('PUT')
      @include('admin.counter-sales._form')
    </form>
  </div>
</div>
@endsection
