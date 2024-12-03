<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewVehicleSale;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Helpers\DataTablesColumnsBuilder;
use Yajra\DataTables\DataTables;

class NewVehicleSaleController extends Controller
{
  public function index(Request $request): View | JsonResponse
  {
    try {
      validate_permission('new_vehicle_sales.read');

      if ($request->ajax()) {
        $query = NewVehicleSale::with('customer');

        return DataTables::of($query)
          ->addColumn('customer_name', function ($sale) {
            return $sale->customer->name ?? 'N/A';
          })
          ->addColumn('actions', function ($sale) {
            return view('admin.new-vehicle-sales._actions', compact('sale'))->render();
          })
          ->editColumn('amount', function ($sale) {
            return '₹' . number_format($sale->amount, 2);
          })
          ->rawColumns(['actions'])
          ->make(true);
      }

      $tableConfigs = (new DataTablesColumnsBuilder(NewVehicleSale::class))
        ->setSearchable('invoice_number')
        ->setOrderable('invoice_number')
        ->setName('customer_name', 'Customer')
        ->setSearchable('vehicle_model')
        ->setOrderable('vehicle_model')
        ->setSearchable('chassis_number')
        ->setOrderable('chassis_number')
        ->setSearchable('engine_number')
        ->setOrderable('engine_number')
        ->setSearchable('color')
        ->setOrderable('color')
        ->setSearchable('amount')
        ->setOrderable('amount')
        ->setSearchable('payment_method')
        ->setOrderable('payment_method')
        ->setSearchable('payment_status')
        ->setOrderable('payment_status')
        ->removeColumns(['created_at', 'updated_at', 'customer_id'])
        ->withActions()
        ->make();

      return view('admin.new-vehicle-sales.index', compact('tableConfigs'));
    } catch (\Exception $e) {
      \Log::error('Error in new vehicle sales index:', ['error' => $e->getMessage()]);
      abort(403, 'Unauthorized action.');
    }
  }

  public function create(): View
  {
    validate_permission('new_vehicle_sales.create');

    $customers = Customer::all();
    return view('admin.new-vehicle-sales.create', compact('customers'));
  }

  public function store(Request $request): RedirectResponse
  {
    validate_permission('new_vehicle_sales.create');

    try {
      \Log::info('Form data received:', $request->all());

      $validated = $request->validate([
        'invoice_number' => 'required|unique:new_vehicle_sales',
        'customer_id' => 'required|exists:customers,id',
        'vehicle_model' => 'required|string',
        'chassis_number' => 'required|unique:new_vehicle_sales',
        'engine_number' => 'required|unique:new_vehicle_sales',
        'color' => 'required|string',
        'amount' => 'required|numeric',
        'payment_method' => 'required|string',
        'remarks' => 'nullable|string'
      ]);

      \Log::info('Validation passed, creating sale');

      $sale = NewVehicleSale::create($validated);

      \Log::info('Sale created:', ['id' => $sale->id]);

      session()->flash('success', 'Vehicle sale created successfully!');

      return redirect()->route('admin.new-vehicle-sales.index');
    } catch (\Exception $e) {
      \Log::error('Store failed: ' . $e->getMessage());
      return redirect()
        ->back()
        ->withInput()
        ->withErrors(['error' => $e->getMessage()]);
    }
  }

  public function show(NewVehicleSale $new_vehicle_sale): View
  {
    validate_permission('new_vehicle_sales.read');
    return view('admin.new-vehicle-sales.show', ['sale' => $new_vehicle_sale]);
  }

  public function edit(NewVehicleSale $new_vehicle_sale): View
  {
    validate_permission('new_vehicle_sales.update');
    $customers = Customer::all();
    return view('admin.new-vehicle-sales.edit', ['sale' => $new_vehicle_sale, 'customers' => $customers]);
  }

  public function update(Request $request, NewVehicleSale $new_vehicle_sale): RedirectResponse
  {
    validate_permission('new_vehicle_sales.update');

    try {
      $validated = $request->validate([
        'invoice_number' => 'required|unique:new_vehicle_sales,invoice_number,' . $new_vehicle_sale->id,
        'customer_id' => 'required|exists:customers,id',
        'vehicle_model' => 'required|string',
        'chassis_number' => 'required|unique:new_vehicle_sales,chassis_number,' . $new_vehicle_sale->id,
        'engine_number' => 'required|unique:new_vehicle_sales,engine_number,' . $new_vehicle_sale->id,
        'color' => 'required|string',
        'amount' => 'required|numeric',
        'payment_method' => 'required|string',
        'remarks' => 'nullable|string'
      ]);

      $new_vehicle_sale->update($validated);

      return redirect()
        ->route('admin.new-vehicle-sales.index')
        ->with('success', 'Sale updated successfully!');
    } catch (\Exception $e) {
      Log::error("Error in new vehicle sale update: " . $e->getMessage());
      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'An error occurred while updating the sale.');
    }
  }

  public function destroy(NewVehicleSale $new_vehicle_sale): RedirectResponse
  {
    validate_permission('new_vehicle_sales.delete');
    $new_vehicle_sale->delete();
    return redirect()
      ->route('admin.new-vehicle-sales.index')
      ->with('success', 'Sale deleted successfully!');
  }
}
