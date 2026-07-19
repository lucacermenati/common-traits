<?php

declare(strict_types=1);

namespace LucaCermenati\CommonTraits\Providers;

use Illuminate\Support\ServiceProvider;

class CommonTraitsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/common-traits.php',
            'common-traits',
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/common-traits.php'
                => config_path('common-traits.php'),
        ], 'common-traits-config');
    }
}