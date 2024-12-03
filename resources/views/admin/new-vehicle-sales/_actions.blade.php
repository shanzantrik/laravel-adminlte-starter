<div class="btn-group">
  @permission('new_vehicle_sales.update')
  <a href="{{ route('admin.new-vehicle-sales.edit', $sale) }}" class="btn btn-sm btn-primary">
    <i class="fas fa-edit"></i>
  </a>
  @endpermission

  @permission('new_vehicle_sales.delete')
  <form action="{{ route('admin.new-vehicle-sales.destroy', $sale) }}" method="POST" style="display: inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
      <i class="fas fa-trash"></i>
    </button>
  </form>
  @endpermission
</div>
