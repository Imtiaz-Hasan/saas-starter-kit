<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Project;
use Illuminate\View\View;

class TenantDashboardController extends Controller
{
    public function index(): View
    {
        // These queries run against the current tenant's database automatically.
        return view('tenant.dashboard', [
            'projectCount' => Project::count(),
            'activeCount' => Project::where('status', 'active')->count(),
            'recentProjects' => Project::latest()->take(5)->get(),
        ]);
    }
}
