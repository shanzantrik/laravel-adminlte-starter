@extends('layouts.admin')

@section('title', 'Add Booking Advance')

@section('main')
<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Add a New Booking Advance</h3>
    <div class="float-right">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.booking-advances.index') }}" class="text-decoration-none">
            <i class="fas fa-list"></i> View All Booking Advances
          </a>
        </li>
      </ol>
    </div>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.booking-advances.store') }}" method="POST">
      @csrf
      @include('admin.booking-advances._form')
      <div style="visibility: hidden">
        <button type="submit" class="btn btn-info" id="saveGenerateButton" name="action" value="save_generate_receipt"
          disabled>
          {{ isset($bookingAdvance) ? 'Update and Generate Receipt' : '' }}
        </button>
      </div>
    </form>
  </div>
</div>
<script>
  document.getElementById('booking-form').addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent default form submission
    const form = event.target;

    fetch(form.action, {
        method: form.method,
        body: new FormData(form),
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.open(data.receipt_url, '_blank');
            window.location.href = data.index_url;
        } else {
            alert('There was an error processing your request.');
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>
@endsection
