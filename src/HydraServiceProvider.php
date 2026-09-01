<?php

namespace SocialiteProviders\Teleserv;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class HydraServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/hydra.php', 'hydra');

        $this->publishes([
            __DIR__.'/config/hydra.php' => config_path('hydra.php'),
        ], 'hydra-config');

        $this->loadRoutesFrom(__DIR__.'/routes/hydra.php');

        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('teleserv', Provider::class);
        });
    }
}
