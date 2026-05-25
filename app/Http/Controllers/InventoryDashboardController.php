<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryDashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboardService): View
    {
        $dashboardData = $dashboardService->buildDashboard(
            $request->boolean('show_outside_suppliers')
        );

        return view('inventory-dashboard', $dashboardData);
    }
}
