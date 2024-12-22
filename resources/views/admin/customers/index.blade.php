@extends('layouts.admin')

@section('title', 'Customers')

@section('main')
<div class="row">
  <div class="col-6"></div>
  <div class="col-6">
    @permission('customers.create')
    <a href="{{ route('admin.customers.create') }}" class="mt-3 btn btn-primary float-right">
      <i class="fas fa-plus mr-1"></i>
      {{ __('New Customer') }}
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
        <h3 class="card-title">Customers</h3>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="datatables" data-route="{{ route('admin.customers.index') }}"
            data-configs="{{ json_encode($tableConfigs) }}" class="table table-bordered table-sm">
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

<div class="modal" tabindex="-1" id="delete-modal">
  <div class="modal-dialog">
    <div class="modal-content" id="delete-form">
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  $(document).ready(function() {
    $('#customers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.customers.index') }}",
            type: 'GET',
            error: function(xhr, error, thrown) {
                console.log('DataTables error:', error);
            }
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'phone_no', name: 'phone_no' },
            { data: 'pan_number', name: 'pan_number' },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[3, 'desc']], // Order by created_at by default
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        stateSave: true,
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
            lengthMenu: '_MENU_ records per page',
            zeroRecords: 'No matching records found',
            info: 'Showing _START_ to _END_ of _TOTAL_ records',
            infoEmpty: 'No records available',
            infoFiltered: '(filtered from _MAX_ total records)',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
    });
});
</script>
@endsection
