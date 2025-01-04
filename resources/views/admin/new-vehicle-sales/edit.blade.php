@extends('layouts.admin')

@section('title', 'Edit New Vehicle Sale')

@section('main')
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">Edit New Vehicle Sale</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.new-vehicle-sales.update', $newVehicleSale) }}" method="POST" id="sale-form">
            @csrf
            @method('PUT')
            @include('admin.new-vehicle-sales._form')
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Initialize payment rows for existing payments
    @if(isset($newVehicleSale) && $newVehicleSale->payments)
        @foreach($newVehicleSale->payments as $payment)
            addPaymentRow({
                payment_by: @json($payment->payment_by),
                payment_date: @json($payment->payment_date),
                amount: @json($payment->amount),
                reference_number: @json($payment->reference_number),
                bank_name: @json($payment->bank_name),
                approved_by: @json($payment->approved_by),
                discount_note_no: @json($payment->discount_note_no),
                approved_note_no: @json($payment->approved_note_no),
                institution_name: @json($payment->institution_name),
                credit_instrument: @json($payment->credit_instrument)
            });
        @endforeach
    @endif

    // Update payment summary
    updatePaymentSummary();
});
</script>
@endsection
