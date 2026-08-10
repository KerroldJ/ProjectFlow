<?php

namespace App\Domain\Project;

use App\Repository\IRepository;
use App\Repository\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Project domain repository contract.
 *
 * @extends IRepository<Project>
 */
interface ProjectDomain extends IRepository
{
    /**
     * Retrieve a paginated listing of projects.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Project>
     */
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator;
}
