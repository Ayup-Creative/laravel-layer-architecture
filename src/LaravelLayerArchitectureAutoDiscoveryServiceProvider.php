<?php

namespace Ayup\LaravelLayerArchitecture;

use Ayup\LaravelLayerArchitecture\Commands\MakeLayerCommand;
use Ayup\LaravelLayerArchitecture\Facades\Autodiscovery as AutodiscoveryFacade;
use Illuminate\Support\ServiceProvider;

class LaravelLayerArchitectureAutoDiscoveryServiceProvider extends ServiceProvider
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

        AutodiscoveryFacade::discoverAndRegister();
    }
}
