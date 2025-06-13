<?php

declare(strict_types=1);

namespace Sam\DataObjects;

use Illuminate\Support\ServiceProvider;

class DtoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register Propertyextractor as Singleton
        $this->app->singleton(PropertyExtractor::class, function () {
            return new PropertyExtractor(true); // Turn on the cache
        });
    }
}
