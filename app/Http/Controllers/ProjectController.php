<?php

namespace App\Http\Controllers;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Render the projects page shell.
     *
     * The project data itself is loaded client-side from the JSON REST API
     * (see App\Http\Controllers\Api\ProjectController); this action only
     * provides the enum options used by the filters and the create/edit dialog.
     */
    public function index(): Response
    {
        return Inertia::render('projects/Index', [
            'statuses' => ProjectStatus::values(),
            'priorities' => ProjectPriority::values(),
        ]);
    }
}
