<?php

namespace Database\Seeders;

use App\Repository\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProjectSeeder extends Seeder
{
    /**
     * Seed the projects table from the bundled JSON fixture.
     */
    public function run(): void
    {
        $path = database_path('data/projects.json');

        /** @var array<int, array{clientName: string, projectName: string, description: string, status: string, priority: string, startDate: string, dueDate: string}> $projects */
        $projects = File::json($path);

        foreach ($projects as $project) {
            Project::create([
                'client_name' => $project['clientName'],
                'project_name' => $project['projectName'],
                'description' => $project['description'],
                'status' => $project['status'],
                'priority' => $project['priority'],
                'start_date' => $project['startDate'],
                'due_date' => $project['dueDate'],
            ]);
        }
    }
}
