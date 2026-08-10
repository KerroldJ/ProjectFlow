<?php

namespace App\Http\Controllers\Api;

use App\Actions\ProjectService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $projects) {}

    /**
     * GET /api/projects — paginated list of projects (search / filter / sort).
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'priority', 'sort', 'direction']);
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        return response()->json($this->projects->paginate($filters, $perPage));
    }

    /**
     * POST /api/projects — create a project.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->projects->create($request->validated());

        return response()->json(['data' => $project], 201);
    }

    /**
     * GET /api/projects/{project} — show a single project.
     */
    public function show(int $project): JsonResponse
    {
        return response()->json(['data' => $this->projects->find($project)]);
    }

    /**
     * PUT /api/projects/{project} — update a project.
     */
    public function update(UpdateProjectRequest $request, int $project): JsonResponse
    {
        $updated = $this->projects->update($project, $request->validated());

        return response()->json(['data' => $updated]);
    }

    /**
     * DELETE /api/projects/{project} — delete a project.
     */
    public function destroy(int $project): JsonResponse
    {
        $this->projects->delete($project);

        return response()->json(status: 204);
    }
}
