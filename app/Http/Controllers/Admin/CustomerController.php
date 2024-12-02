<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\DataTablesColumnsBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\Role;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function index(Request $request): View | JsonResponse
    {
        validate_permission('customers.read');

        if ($request->ajax()) {
            // Base query for customers
            $query = Customer::query();

            // Apply phone number filter if provided
            if ($request->filled('phone_no')) {
                $query->where('phone_no', 'like', '%' . $request->phone_no . '%');
            }

            // Apply general search filter from DataTables search box
            if ($request->has('search') && $request->search['value']) {
                $searchValue = $request->search['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('phone_no', 'like', '%' . $searchValue . '%')
                        ->orWhere('vehicle_registration_no', 'like', '%' . $searchValue . '%');
                });
            }

            // Count total and filtered records
            $totalRecords = Customer::count();
            $filteredRecords = $query->count();

            // Fetch paginated results
            $rows = $query->offset($request->start)->limit($request->length)->get();

            return DataTables::of($rows)
                ->setTotalRecords($totalRecords)
                ->setFilteredRecords($filteredRecords)
                ->addColumn('actions', function ($row) {
                    return Blade::render('
                    <div class="btn-group">
                        @permission(\'customers.create\')
                            <a href="{{ route(\'admin.customers.edit\', $row) }}" class="btn btn-default">Update</a>
                        @endpermission
                        @permission(\'customers.delete\')
                            <button type="button" class="btn btn-danger delete-btn" data-destroy="{{ route(\'admin.customers.destroy\', $row) }}">Delete</button>
                        @endpermission
                    </div>
                ', ['row' => $row]);
                })
                ->addColumn('created_at', function ($row) {
                    return Blade::render('
                    {{ $row->created_at->format(\'M d, Y\') }}
                ', ['row' => $row]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        $tableConfigs = (new DataTablesColumnsBuilder(Customer::class))
            ->setSearchable('name')
            ->setOrderable('name')
            ->setSearchable('phone_no')
            ->setOrderable('phone_no')
            ->setSearchable('vehicle_registration_no')
            ->setOrderable('vehicle_registration_no')
            ->setName('created_at', 'Created at')
            ->removeColumns(['updated_at'])
            ->withActions()
            ->make();

        return view('admin.customers.index', compact('tableConfigs'));
    }

    public function create(): View
    {
        validate_permission('customers.create');

        $customer = new Customer();
        $roles = Role::all();
        $customerRoles = [];
        return view('admin.customers.create', compact('customer', 'roles', 'customerRoles'));
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        validate_permission('customers.create');

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone_no' => 'required|string|max:10|unique:customers',
                'vehicle_registration_no' => 'nullable|string|max:20|unique:customers'
            ]);

            $customer = Customer::create($request->only('name', 'phone_no', 'vehicle_registration_no'));

            return response()->json([
                'success' => true,
                'customer' => $customer
            ], 200);  // Ensure 200 status for success
        } catch (\Exception $e) {
            // Log the exception and return an error response
            Log::error("Error in customer store: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the customer.',
            ], 500);  // Explicitly return a 500 status
        }
    }

    public function show(Customer $customer): View
    {
        validate_permission('customers.read');

        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer): View
    {
        validate_permission('customers.update');

        return view('admin.customers.edit', compact('customer'));
    }

    public function update($request, Customer $customer)
    {
        validate_permission('customers.update');

        $request->validate([
            'name' => 'required|string|max:255',
            'phone_no' => 'required|string|max:10|unique:customers',
            'vehicle_registration_no' => 'nullable|string|max:20|unique:customers'
        ]);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer updated successfully!');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        validate_permission('customers.delete');

        $customer->delete();
        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully!');
    }

    public function search(Request $request)
    {
        try {
            $query = $request->get('query');
            Log::info('Customer search request received', ['query' => $query]);

            if (empty($query)) {
                return response()->json([
                    'error' => 'Search query is required'
                ], 400);
            }

            $customers = Customer::where(function ($q) use ($query) {
                $q->where('phone_no', 'LIKE', "%{$query}%")
                    ->orWhere('vehicle_registration_no', 'LIKE', "%{$query}%");
            })
                ->select(['id', 'name', 'phone_no', 'vehicle_registration_no'])
                ->limit(10)
                ->get();

            Log::info('Customer search results', [
                'query' => $query,
                'count' => $customers->count()
            ]);

            return response()->json($customers);
        } catch (\Exception $e) {
            Log::error('Error in customer search', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'An error occurred while searching for customers'
            ], 500);
        }
    }
}
