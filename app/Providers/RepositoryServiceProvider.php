<?php

namespace App\Providers;

use App\Domain\Project\ProjectDomain;
use App\Domain\User\UserDomain;
use App\Repository\Eloquent\ProjectEloquentRepository;
use App\Repository\Eloquent\UserEloquentRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bind each domain repository contract to its Eloquent implementation.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        ProjectDomain::class => ProjectEloquentRepository::class,
        UserDomain::class => UserEloquentRepository::class,
    ];
}
