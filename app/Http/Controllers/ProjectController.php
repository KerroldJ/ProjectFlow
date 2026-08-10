<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Render the projects page shell.
     *
     * The project data itself is loaded client-side from the JSON REST API
     * (see App\Http\Controllers\Api\ProjectController). The status/priority
     * options come from the form request — the enums are never referenced in
     * the controller directly.
     */
    public function index(): Response
    {
        return Inertia::render('projects/Index', [
            'statuses' => StoreProjectRequest::statusOptions(),
            'priorities' => StoreProjectRequest::priorityOptions(),
        ]);
    }
}
