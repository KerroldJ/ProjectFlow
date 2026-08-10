<?php

namespace App\Domain\User;

use App\Repository\IRepository;
use App\Repository\Models\User;

/**
 * User domain repository contract.
 *
 * @extends IRepository<User>
 */
interface UserDomain extends IRepository
{
    /**
     * Find a user by their email address.
     */
    public function findByEmail(string $email): ?User;
}
