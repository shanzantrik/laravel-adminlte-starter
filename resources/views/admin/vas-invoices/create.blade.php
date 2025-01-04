@extends('layouts.admin')

@section('title', 'Create VAS Invoice')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Add a VAS Invoice</h3>
    <div class="float-right">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.vas-invoices.index') }}" class="text-decoration-none">
            <i class="fas fa-list"></i> View All VAS Invoices
          </a>
        </li>
      </ol>
    </div>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.vas-invoices.store') }}" method="POST" id="sale-form">
      @csrf
      @include('admin.vas-invoices._form')
    </form>
  </div>
</div>
@endsection
