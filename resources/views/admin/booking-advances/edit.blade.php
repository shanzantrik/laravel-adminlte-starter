@extends('layouts.admin')

@section('title', 'Edit Booking Advance')

@section('main')
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">Edit Booking Advance</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.booking-advances.update', $bookingAdvance) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.booking-advances._form')

            <button type="submit" class="btn btn-info" id="saveGenerateButton" name="action"
                value="save_generate_receipt" disabled>
                {{ isset($bookingAdvance) ? 'Update and Generate Receipt' : 'Save and Generate Receipt' }}
            </button>
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
