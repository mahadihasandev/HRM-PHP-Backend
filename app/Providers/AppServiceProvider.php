<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\FactoryRepositoryInterface;
use App\Repositories\Contracts\PayrollRepositoryInterface;
use App\Repositories\Contracts\PeopleRepositoryInterface;
use App\Repositories\Eloquent\FactoryRepository;
use App\Repositories\Eloquent\PayrollRepository;
use App\Repositories\Eloquent\PeopleRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PayrollRepositoryInterface::class, PayrollRepository::class);
        $this->app->bind(FactoryRepositoryInterface::class, FactoryRepository::class);
        $this->app->bind(PeopleRepositoryInterface::class, PeopleRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
