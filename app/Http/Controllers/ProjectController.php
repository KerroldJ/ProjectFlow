<?php

namespace App\Http\Controllers;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects with optional search, filtering and sorting.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'priority', 'sort', 'direction']);

        $sortableColumns = ['client_name', 'project_name', 'status', 'priority', 'start_date', 'due_date', 'created_at'];
        $sort = in_array($request->string('sort')->value(), $sortableColumns, true)
            ? $request->string('sort')->value()
            : 'created_at';
        $direction = $request->string('direction')->value() === 'asc' ? 'asc' : 'desc';

        $projects = Project::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->value();

                $query->where(function ($query) use ($search) {
                    $query->where('client_name', 'like', "%{$search}%")
                        ->orWhere('project_name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->value()))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->string('priority')->value()))
            ->orderBy($sort, $direction)
            ->get();

        return Inertia::render('projects/Index', [
            'projects' => $projects,
            'filters' => $filters,
            'statuses' => ProjectStatus::values(),
            'priorities' => ProjectPriority::values(),
        ]);
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): Response
    {
        return Inertia::render('projects/Create', [
            'statuses' => ProjectStatus::values(),
            'priorities' => ProjectPriority::values(),
        ]);
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        Project::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Project created.')]);

        return to_route('projects.index');
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project): Response
    {
        return Inertia::render('projects/Show', [
            'project' => $project,
        ]);
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project): Response
    {
        return Inertia::render('projects/Edit', [
            'project' => $project,
            'statuses' => ProjectStatus::values(),
            'priorities' => ProjectPriority::values(),
        ]);
    }

    /**
     * Update the specified project in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Project updated.')]);

        return to_route('projects.index');
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Project deleted.')]);

        return to_route('projects.index');
    }
}
