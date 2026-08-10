<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Base repository contract shared by every domain repository.
 *
 * @template TModel of Model
 */
interface IRepository
{
    /**
     * Retrieve every record, optionally filtered/sorted.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, TModel>
     */
    public function all(array $filters = []): Collection;

    /**
     * Find a single record by its identifier.
     *
     * @return TModel
     */
    public function find(int $id): Model;

    /**
     * Persist a new record.
     *
     * @param  array<string, mixed>  $data
     * @return TModel
     */
    public function store(array $data): Model;

    /**
     * Update an existing record.
     *
     * @param  array<string, mixed>  $data
     * @return TModel
     */
    public function update(int $id, array $data): Model;

    /**
     * Delete a record.
     */
    public function delete(int $id): bool;
}
