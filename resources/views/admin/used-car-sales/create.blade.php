@extends('layouts.admin')

@section('title', 'Create Used Car Sale')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Add a Used Car Sale</h3>
    <div class="float-right">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.used-car-sales.index') }}" class="text-decoration-none">
            <i class="fas fa-list"></i> View All Used Car Sales
          </a>
        </li>
      </ol>
    </div>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.used-car-sales.store') }}" method="POST" id="sale-form">
      @csrf
      @include('admin.used-car-sales._form')
    </form>
  </div>
</div>
@endsection
