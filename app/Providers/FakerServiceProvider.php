<?php

namespace App\Providers;

use App\Macros\FakerMacros;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\ServiceProvider;

class FakerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        /**
         * Attach providers here to make custom Providers available via helper
         * function fake().
         *
         * https://github.com/laravel/framework/issues/42988#issuecomment-1169450207
         */
        $locale = $this->app->bound('config')
            ? $this->app->make('config')->get('app.faker_locale')
            : 'en_US';

        $abstract = Generator::class . ':' . $locale;

        $this->app->afterResolving($abstract, function (Generator $instance) {
            $instance->addProvider(new FakerMacros($instance));
        });

        /*
         * Attach providers here, to make custom Providers available via Faker
         * in tests.
         *
         * https://hofmannsven.com/2021/faker-provider-in-laravel#ref-extending-the-service-provider
         */

        $this->app->singleton(Generator::class, function () use ($locale) {
            $faker = Factory::create($locale);
            $faker->addProvider(new FakerMacros($faker));

            return $faker;
        });
    }
}
