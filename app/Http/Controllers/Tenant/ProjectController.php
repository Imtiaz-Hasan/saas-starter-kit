<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Demo CRUD for the tenant-scoped Project model. Every query here transparently
 * targets the current tenant's database — there is no tenant_id to filter on.
 */
class ProjectController extends Controller
{
    public function index(): View
    {
        return view('tenant.projects.index', [
            'projects' => Project::latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        Project::create($validated);

        return back()->with('status', 'Project created.');
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,archived'],
        ]);

        $project->update($validated);

        return back()->with('status', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return back()->with('status', 'Project deleted.');
    }
}
