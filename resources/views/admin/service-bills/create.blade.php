@extends('layouts.admin')

@section('title', 'Create Service Bill')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Add a Service Bill</h3>
    <div class="float-right">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.service-bills.index') }}" class="text-decoration-none">
            <i class="fas fa-list"></i> View All Service Bills
          </a>
        </li>
      </ol>
    </div>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.service-bills.store') }}" method="POST" id="sale-form">
      @csrf
      @include('admin.service-bills._form')
    </form>
  </div>
</div>
@endsection
