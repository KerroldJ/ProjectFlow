<?php

namespace App\Repository\Eloquent;

use App\Domain\Project\ProjectDomain;
use App\Repository\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class ProjectEloquentRepository implements ProjectDomain
{
    /**
     * Columns that may be used to sort the listing.
     *
     * @var array<int, string>
     */
    private array $sortableColumns = [
        'client_name', 'project_name', 'status', 'priority', 'start_date', 'due_date', 'created_at',
    ];

    /**
     * Retrieve every project, optionally searched, filtered and sorted.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Project>
     */
    public function all(array $filters = []): Collection
    {
        $sort = in_array($filters['sort'] ?? null, $this->sortableColumns, true)
            ? $filters['sort']
            : 'created_at';
        $direction = ($filters['direction'] ?? null) === 'asc' ? 'asc' : 'desc';

        return Project::query()
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $search = $filters['search'];

                $query->where(function ($query) use ($search) {
                    $query->where('client_name', 'like', "%{$search}%")
                        ->orWhere('project_name', 'like', "%{$search}%");
                });
            })
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['priority']), fn ($query) => $query->where('priority', $filters['priority']))
            ->orderBy($sort, $direction)
            ->get();
    }

    /**
     * Find a single project by its identifier.
     */
    public function find(int $id): Project
    {
        return Project::query()->findOrFail($id);
    }

    /**
     * Persist a new project.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): Project
    {
        return Project::query()->create($data);
    }

    /**
     * Update an existing project.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): Project
    {
        $project = $this->find($id);
        $project->update($data);

        return $project;
    }

    /**
     * Delete a project.
     */
    public function delete(int $id): bool
    {
        return (bool) $this->find($id)->delete();
    }
}
