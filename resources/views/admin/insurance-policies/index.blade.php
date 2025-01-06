@extends('layouts.admin')

@section('title', 'Insurance Policies')

@section('main')
@if(session('success'))
<div class="alert alert-success">
  {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger">
  {{ session('error') }}
</div>
@endif
<div class="row">
  <div class="col-6"></div>
  <div class="col-6">
    <a href="{{ route('admin.insurance-policies.create') }}" class="mt-3 btn btn-primary float-right">
      <i class="fas fa-plus mr-1"></i> {{ __('Insurance Policy') }}
    </a>
  </div>
</div>

<div class="card mt-3">
  <div class="card-header">
    <h3 class="card-title">Insurance Policies</h3>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('admin.insurance-policies.index') }}" class="mb-3">
      <div class="input-group">
        <input type="text" name="search" class="form-control"
          placeholder="Search by Proposal/Policy Number, Amount, Customer, or Sales Executive"
          value="{{ request('search') }}">
        <div class="input-group-append">
          <button class="btn btn-primary" type="submit">Search</button>
          <a href="{{ route('admin.insurance-policies.index') }}" class="btn btn-secondary ml-2">Clear</a>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered table-sm">
        <thead>
          <tr>
            <th>Proposal/Policy Number</th>
            <th>Total Amount</th>
            <th>Amount Paid</th>
            <th>Balance</th>
            <th>Customer</th>
            <th>Sales Executive</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($insurancePolicies as $insurancePolicy)
          <tr>
            <td>{{ $insurancePolicy->proposal_policy_number }}</td>
            <td>₹{{ number_format($insurancePolicy->total_amount, 2) }}</td>
            <td>₹{{ number_format($insurancePolicy->amount_paid, 2) }}</td>
            <td>₹{{ number_format($insurancePolicy->balance, 2) }}</td>
            <td>{{ $insurancePolicy->customer->name ?? 'N/A' }}</td>
            <td>{{ $insurancePolicy->so_name ?? 'N/A' }}</td>
            <td>
              <a href="{{ route('admin.insurance-policies.edit', $insurancePolicy) }}"
                class="btn btn-sm btn-primary">Edit</a>
              <a href="{{ route('admin.insurance-policies.receipt', $insurancePolicy) }}" class="btn btn-sm btn-info"
                target="_blank">
                <i class="fas fa-print"></i> Receipt
              </a>
              <form action="{{ route('admin.insurance-policies.destroy', $insurancePolicy) }}" method="POST"
                style="display: inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger"
                  onclick="return confirm('Are you sure?')">Delete</button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center">No Insurance Policies Found</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <nav aria-label="Page navigation">
      <div class="d-flex justify-content-between align-items-center">
        <div>Showing {{ $insurancePolicies->firstItem() }} to {{ $insurancePolicies->lastItem() }} of {{
          $insurancePolicies->total() }} results</div>
        <ul class="pagination mb-0">
          {{ $insurancePolicies->appends(['search' => $search])->onEachSide(1)->links() }}
        </ul>
      </div>
    </nav>
  </div>
</div>
@endsection
