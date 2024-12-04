<?php

namespace Ayup\LaravelLayerArchitecture;

use Ayup\LaravelLayerArchitecture\Commands\MakeLayerCommand;
use Ayup\LaravelLayerArchitecture\Facades\Autodiscovery as AutodiscoveryFacade;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class LaravelLayerArchitectureAutoDiscoveryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('autodiscovery', fn () => app(Autodiscovery::class));

        $this->commands([
            MakeLayerCommand::class,
        ]);

        $this->publishes([__DIR__ . '/../config/layer-architecture-auto-discovery.php'], 'layer-architecture-auto-discovery-config');
        $this->mergeConfigFrom(__DIR__ . '/../config/layer-architecture-auto-discovery.php', 'layer-architecture-auto-discovery');
    }

    public function boot()
    {
        AutodiscoveryFacade::discoverAndRegister();
    }

    public function provides()
    {
        return [
            'autodiscovery',
            Autodiscovery::class,
        ];
    }
}
