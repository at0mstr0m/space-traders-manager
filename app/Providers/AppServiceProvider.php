<?php

declare(strict_types=1);

namespace App\Providers;

use App\Helpers\SpaceTraders;
use App\Macros\ArrayMacros;
use App\Macros\CollectionMacros;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SpaceTraders::class, function (Application $app) {
            return new SpaceTraders(env('API_TOKEN'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Collection::mixin(new CollectionMacros());
        Arr::mixin(new ArrayMacros());
    }
}
