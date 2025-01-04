@extends('layouts.admin')

@section('title', 'Edit Job Advance')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Edit Job Advance</h3>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.job-advances.update', $jobAdvance) }}" method="POST" id="sale-form">
      @csrf
      @method('PUT')
      @include('admin.job-advances._form')
    </form>
  </div>
</div>
@endsection
