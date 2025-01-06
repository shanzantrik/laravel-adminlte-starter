@extends('layouts.admin')

@section('title', 'Create Used Car Advance')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Add a Used Car Advance</h3>
    <div class="float-right">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.used-car-advances.index') }}" class="text-decoration-none">
            <i class="fas fa-list"></i> View All Used Car Advances
          </a>
        </li>
      </ol>
    </div>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.used-car-advances.store') }}" method="POST" id="sale-form">
      @csrf
      @include('admin.used-car-advances._form')
    </form>
  </div>
</div>
@endsection
