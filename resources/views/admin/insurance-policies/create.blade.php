@extends('layouts.admin')

@section('title', 'Create Insurance Policy')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Add an Insurance Policy</h3>
    <div class="float-right">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.insurance-policies.index') }}" class="text-decoration-none">
            <i class="fas fa-list"></i> View All Insurance Policies
          </a>
        </li>
      </ol>
    </div>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.insurance-policies.store') }}" method="POST" id="sale-form">
      @csrf
      @include('admin.insurance-policies._form')
    </form>
  </div>
</div>
@endsection
