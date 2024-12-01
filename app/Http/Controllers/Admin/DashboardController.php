<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingAdvance;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get data for last 6 months
        $months = collect(range(5, 0))->map(function ($i) {
            return Carbon::now()->startOfMonth()->subMonths($i);
        });

        // Booking Advances data
        $bookingAdvancesData = $months->map(function ($month) {
            return [
                'month' => $month->format('M Y'),
                'count' => BookingAdvance::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
                'amount' => BookingAdvance::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('amount_paid')
            ];
        });

        // Customers data
        $customersData = $months->map(function ($month) {
            return [
                'month' => $month->format('M Y'),
                'count' => Customer::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count()
            ];
        });

        // Get totals
        $totalBookingAdvances = BookingAdvance::count();
        $totalCustomers = Customer::count();
        $totalAmountCollected = BookingAdvance::sum('amount_paid');

        return view('admin.dashboard.index', compact(
            'bookingAdvancesData',
            'customersData',
            'totalBookingAdvances',
            'totalCustomers',
            'totalAmountCollected'
        ));
    }
}
