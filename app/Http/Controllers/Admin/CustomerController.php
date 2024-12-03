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
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $draw = $request->get('draw');

            $search = $request->get('search');
            if (!empty($search['value'])) {
                $searchValue = $search['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('phone_no', 'like', "%{$searchValue}%")
                        ->orWhere('vehicle_registration_no', 'like', "%{$searchValue}%");
                });
            }
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
            // Handle column sorting
            $order = $request->get('order', []);
            if (!empty($order)) {
                $columns = ['name', 'phone_no', 'vehicle_registration_no', 'created_at'];
                $orderColumn = $order[0]['column'] ?? 0;
                $orderDirection = $order[0]['dir'] ?? 'asc';

                if (isset($columns[$orderColumn])) {
                    $query->orderBy($columns[$orderColumn], $orderDirection);
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }
            // Count total and filtered records
            $totalRecords = Customer::count();
            $filteredRecords = $query->count();

            // Fetch paginated results
            $results = $query->skip($start)
                ->take($length)
                ->get();
            $data = [];
            foreach ($results as $row) {
                $data[] = [
                    'id' => $row->id,
                    'name' => $row->name,
                    'phone_no' => $row->phone_no,
                    'vehicle_registration_no' => $row->vehicle_registration_no,
                    'created_at' => $row->created_at->format('M d, Y'),
                    'actions' => Blade::render('
                    <div class="btn-group">
                        @permission(\'customers.create\')
                            <a href="' . route('admin.customers.edit', $row) . '" class="btn btn-default">Update</a>
                        @endpermission
                        @permission(\'customers.delete\')
                            <button type="button" class="btn btn-danger delete-btn" data-destroy="' . route('admin.customers.destroy', $row) . '">Delete</button>
                        @endpermission
                    </div>
                ', ['row' => $row])
                ];
            }
            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
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

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        validate_permission('customers.create');

        try {
            DB::beginTransaction();
            $request->validate([
                'name' => 'required|string|max:255',
                'phone_no' => 'required|string|size:10|regex:/^[0-9]{10}$/|unique:customers,phone_no',
                'vehicle_registration_no' => 'nullable|string|max:20|unique:customers'
            ]);
            DB::commit();
            $customer = Customer::create($request->only('name', 'phone_no', 'vehicle_registration_no'));

            return redirect()
                ->route('admin.customers.index')
                ->with('success', 'Customer created successfully!');  // Ensure 200 status for success
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in customer store: " . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the customer. Please try again.'); // Explicitly return a 500 status
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

    public function update($request, Customer $customer): RedirectResponse
    {
        validate_permission('customers.update');

        $request->validate([
            'name' => 'required|string|max:255',
            'phone_no' => 'required|string|size:10|regex:/^[0-9]{10}$/|unique:customers,phone_no',
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
