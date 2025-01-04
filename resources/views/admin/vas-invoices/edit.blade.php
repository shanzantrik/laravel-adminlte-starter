@extends('layouts.admin')

@section('title', 'Edit VAS Invoice')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Edit VAS Invoice</h3>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.vas-invoices.update', $vasInvoice) }}" method="POST" id="sale-form">
      @csrf
      @method('PUT')
      @include('admin.vas-invoices._form')
    </form>
  </div>
</div>
@endsection
