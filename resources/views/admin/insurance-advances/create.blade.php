@extends('layouts.admin')

@section('title', 'Create Insurance Advance')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Add an Insurance Advance</h3>
    <div class="float-right">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.insurance-advances.index') }}" class="text-decoration-none">
            <i class="fas fa-list"></i> View All Insurance Advances
          </a>
        </li>
      </ol>
    </div>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.insurance-advances.store') }}" method="POST" id="sale-form">
      @csrf
      @include('admin.insurance-advances._form')
    </form>
  </div>
</div>
@endsection
