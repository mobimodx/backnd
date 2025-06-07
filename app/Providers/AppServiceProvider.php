<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BenBjurstrom\Replicate\Replicate;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(Replicate::class, function () {
            return new Replicate(
                apiToken: env('REPLICATE_API_TOKEN'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
