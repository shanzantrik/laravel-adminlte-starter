@extends('layouts.admin')

@section('title', 'New Vehicle Sales')

@section('main')
<div class="row">
  <div class="col-6"></div>
  <div class="col-6">
    @permission('new_vehicle_sales.create')
    <a href="{{ route('admin.new-vehicle-sales.create') }}" class="mt-3 btn btn-primary float-right">
      <i class="fas fa-plus mr-1"></i>
      {{ __('New Vehicle Sale') }}
    </a>
    @endpermission
  </div>
</div>

<div class="row">
  <div class="col-12 mt-2">
    @include('layouts.shared.alert')
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card mt-3">
      <div class="card-header">
        <h3 class="card-title">New Vehicle Sales</h3>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="datatables" class="table table-bordered table-sm"
            data-route="{{ route('admin.new-vehicle-sales.index') }}" data-configs="{{ json_encode($tableConfigs) }}">
            <thead>
              <tr>
                @foreach ($tableConfigs as $config)
                <th>{{ $config['name'] }}</th>
                @endforeach
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    $('#datatables').DataTable({
        processing: true,
        serverSide: true,
        ajax: $('#datatables').data('route'),
        columns: @json($tableConfigs),
        order: [[0, 'desc']]
    });
});
</script>
@endpush
