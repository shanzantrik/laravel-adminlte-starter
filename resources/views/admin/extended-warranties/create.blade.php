@extends('layouts.admin')

@section('title', 'Create Extended Warranty')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Add an Extended Warranty</h3>
    <div class="float-right">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.extended-warranties.index') }}" class="text-decoration-none">
            <i class="fas fa-list"></i> View All Extended Warranties
          </a>
        </li>
      </ol>
    </div>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.extended-warranties.store') }}" method="POST" id="sale-form">
      @csrf
      @include('admin.extended-warranties._form')
    </form>
  </div>
</div>
@endsection
