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
          <!-- Phone number search input -->
          <input type="text" id="phoneSearch" class="form-control mb-3" placeholder="Search by Phone No.">
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
      // Initialize DataTable with custom search functionality
      const table = $('#datatables').DataTable({
          processing: true,
          serverSide: true,
          ajax: {
              url: $('#datatables').data('route'),
              data: function (d) {
                  // Include custom phone number search in AJAX request
                  d.phone_no = $('#phoneSearch').val();
              },
              type: 'GET'
          },
          columns: [
              @foreach ($tableConfigs as $config)
                  { data: '{{ $config["data"] }}', name: '{{ $config["data"] }}' },
              @endforeach
          ],
          order: [[0, 'asc']]
      });

      // Redraw the table whenever the phone search input changes
      $('#phoneSearch').on('keyup', function() {
          table.draw();
      });
  });
</script>
@endsection
