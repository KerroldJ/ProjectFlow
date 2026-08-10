<?php

namespace App\Repository\Eloquent;

use App\Domain\User\UserDomain;
use Illuminate\Database\Eloquent\Collection;

class UserEloquentRepository implements UserDomain
{
    /**
     * Retrieve every user, optionally searched.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, User>
     */
    public function all(array $filters = []): Collection
    {
        return User::query()
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $search = $filters['search'];

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Find a single user by their identifier.
     */
    public function find(int $id): User
    {
        return User::query()->findOrFail($id);
    }

    /**
     * Find a user by their email address.
     */
    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    /**
     * Persist a new user.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): User
    {
        return User::query()->create($data);
    }

    /**
     * Update an existing user.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): User
    {
        $user = $this->find($id);
        $user->update($data);

        return $user;
    }

    /**
     * Delete a user.
     */
    public function delete(int $id): bool
    {
        return (bool) $this->find($id)->delete();
    }
}
