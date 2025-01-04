@extends('layouts.admin')

@section('title', 'New Vehicle Sale')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Add a New Vehicle Sale</h3>
    <div class="float-right">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.new-vehicle-sales.index') }}" class="text-decoration-none">
            <i class="fas fa-list"></i> View All New Vehicle Sales
          </a>
        </li>
      </ol>
    </div>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.new-vehicle-sales.store') }}" method="POST" id="sale-form">
      @csrf
      @include('admin.new-vehicle-sales._form')
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Add initial payment row
    if (document.getElementById('paymentsContainer').children.length === 0) {
        addPaymentRow();
    }

    // Update payment summary
    updatePaymentSummary();
});
</script>
@endsection
