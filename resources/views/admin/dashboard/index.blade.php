@extends('layouts.admin')

@section('title', 'Dashboard')

@section('main')
<div class="row">
  <!-- Summary Cards -->
  <div class="col-lg-4 col-6">
    <div class="small-box bg-info">
      <div class="inner">
        <h3>{{ $totalBookingAdvances }}</h3>
        <p>Total Booking Advances</p>
      </div>
      <div class="icon">
        <i class="fas fa-receipt"></i>
      </div>
    </div>
  </div>
  <div class="col-lg-4 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3>₹{{ number_format($totalAmountCollected, 2) }}</h3>
        <p>Total Amount Collected</p>
      </div>
      <div class="icon">
        <i class="fas fa-money-bill-wave"></i>
      </div>
    </div>
  </div>
  <div class="col-lg-4 col-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3>{{ $totalCustomers }}</h3>
        <p>Total Customers</p>
      </div>
      <div class="icon">
        <i class="fas fa-users"></i>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Booking Advances Graph -->
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Booking Advances (Last 6 Months)</h3>
      </div>
      <div class="card-body">
        <canvas id="bookingAdvancesChart" height="200"></canvas>
      </div>
    </div>
  </div>

  <!-- Customers Graph -->
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">New Customers (Last 6 Months)</h3>
      </div>
      <div class="card-body">
        <canvas id="customersChart" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Booking Advances Chart
    new Chart(document.getElementById('bookingAdvancesChart'), {
        type: 'bar',
        data: {
            labels: @json($bookingAdvancesData->pluck('month')),
            datasets: [{
                label: 'Number of Bookings',
                data: @json($bookingAdvancesData->pluck('count')),
                backgroundColor: 'rgba(60,141,188,0.9)',
                borderColor: 'rgba(60,141,188,0.8)',
                borderWidth: 1
            }, {
                label: 'Amount Collected (₹)',
                data: @json($bookingAdvancesData->pluck('amount')),
                type: 'line',
                borderColor: '#00a65a',
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 2,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Customers Chart
    new Chart(document.getElementById('customersChart'), {
        type: 'line',
        data: {
            labels: @json($customersData->pluck('month')),
            datasets: [{
                label: 'New Customers',
                data: @json($customersData->pluck('count')),
                borderColor: '#f39c12',
                backgroundColor: 'rgba(243, 156, 18, 0.2)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 2,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush
