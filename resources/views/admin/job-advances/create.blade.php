@extends('layouts.admin')

@section('title', 'Create Job Advance')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Add a Job Advance</h3>
    <div class="float-right">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.job-advances.index') }}" class="text-decoration-none">
            <i class="fas fa-list"></i> View All Job Advances
          </a>
        </li>
      </ol>
    </div>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.job-advances.store') }}" method="POST" id="sale-form">
      @csrf
      @include('admin.job-advances._form')
    </form>
  </div>
</div>
@endsection
