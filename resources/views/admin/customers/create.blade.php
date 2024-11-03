@extends('layouts.admin')

@section('title', 'Add Customer')

@section('main')
<div class="row">
  <div class="col-lg-6 col-md-6 col-sm-12">
    <div class="card mt-3">
      <div class="card-header">
        <h3 class="card-title">Add a new Customer</h3>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.customers.store') }}" method="POST">
          @csrf

          {{-- Include the form fields for customer --}}
          @include('admin.customers._form')

          <div class="form-group">
            @permission('customers.create')
            <button type="submit" class="btn btn-primary mr-2">{{ __('Save') }}</button>
            @endpermission
            <a href="{{ route('admin.customers.index') }}" class="btn btn-default" role="button">{{ __('Cancel') }}</a>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
@endsection
