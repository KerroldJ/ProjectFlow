<?php

namespace App\Actions;

use App\Concerns\ProjectData;
use App\Domain\Project\ProjectDomain;

/**
 * Application service (business logic) for projects.
 *
 * Sits between the controller and the repository: it accepts/returns DTOs and
 * depends only on the {@see ProjectDomain} abstraction, never on Eloquent.
 */
class ProjectService
{
    public function __construct(private readonly ProjectDomain $projects) {}

    /**
     * List projects, applying optional search/filter/sort criteria.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function list(array $filters = []): array
    {
        return $this->projects->all($filters)
            ->map(fn ($project) => ProjectData::fromModel($project)->toArray())
            ->all();
    }

    /**
     * Paginate projects, applying optional search/filter/sort criteria.
     *
     * @param  array<string, mixed>  $filters
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, int|null>}
     */
    public function paginate(array $filters = [], int $perPage = 10): array
    {
        $paginator = $this->projects->paginate($filters, $perPage);

        return [
            'data' => collect($paginator->items())
                ->map(fn ($project) => ProjectData::fromModel($project)->toArray())
                ->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    /**
     * Fetch a single project as a DTO array.
     *
     * @return array<string, mixed>
     */
    public function find(int $id): array
    {
        return ProjectData::fromModel($this->projects->find($id))->toArray();
    }

    /**
     * Create a project from validated data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        $dto = ProjectData::fromArray($data);

        $project = $this->projects->store($dto->toAttributes());

        return ProjectData::fromModel($project)->toArray();
    }

    /**
     * Update a project from validated data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(int $id, array $data): array
    {
        $dto = ProjectData::fromArray($data);

        $project = $this->projects->update($id, $dto->toAttributes());

        return ProjectData::fromModel($project)->toArray();
    }

    /**
     * Delete a project.
     */
    public function delete(int $id): bool
    {
        return $this->projects->delete($id);
    }
}
