<?php

namespace Ayup\LaravelLayerArchitecture;

use Ayup\LaravelLayerArchitecture\Commands\MakeLayerCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class LaravelLayerArchitectureServiceProvider extends ServiceProvider
{
    /**
     * Register any application services (bind interfaces to implementations).
     */
    public function register(): void
    {
        $this->registerCommands();
        $this->registerConfig();
        $this->registerPublishes();

        $this->registerBindings(
            folders: config('layer-architecture.paths', []),
            appNamespace: $this->app->getNamespace()
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // No bootstrap logic is needed for this provider.
    }

    /**
     * Register the application's console commands.
     */
    protected function registerCommands(): void
    {
        $this->commands([MakeLayerCommand::class]);
    }

    /**
     * Register the publishable resources for the package.
     */
    protected function registerPublishes(): void
    {
        $this->publishes([__DIR__.'/../config/layer-architecture-auto-discovery.php'], 'layer-architecture-auto-discovery-config');
    }

    /**
     * Register the configuration for layer architecture auto-discovery.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/layer-architecture-auto-discovery.php', 'layer-architecture-auto-discovery');
    }

    /**
     * Register bindings by scanning the specified folders for interface files
     * and binding them to their corresponding implementations.
     *
     * @param  array  $folders  The folders to scan for PHP files.
     * @param  string  $appNamespace  The application namespace.
     */
    protected function registerBindings(array $folders, string $appNamespace): void
    {
        foreach ($folders as $folder) {
            $files = $this->getPhpFilesInFolder(app_path($folder));

            foreach ($files as $file) {
                if ($this->isInterfaceFile($file) && ! $this->isAbstractClassFile($file)) {
                    $this->bindInterfaceToImplementation($file, $appNamespace);
                }
            }
        }
    }

    /**
     * Fetch all PHP files in the given folder.
     *
     * @return \Symfony\Component\Finder\SplFileInfo[]
     */
    protected function getPhpFilesInFolder(string $path): array
    {
        if (! $this->app['files']->isDirectory($path)) {
            return [];
        }

        return $this->app['files']->allFiles($path);
    }

    /**
     * Check if the given file is an interface file.
     *
     * @param  \Symfony\Component\Finder\SplFileInfo  $file
     */
    protected function isInterfaceFile($file): bool
    {
        $nameWithoutExt = pathinfo($file->getFilename(), PATHINFO_FILENAME);

        return Str::endsWith($nameWithoutExt, 'Interface');
    }

    /**
     * Check if the associated concrete class is an abstract class.
     *
     * @param  \Symfony\Component\Finder\SplFileInfo  $file
     */
    protected function isAbstractClassFile($file): bool
    {
        $interfaceName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
        $concreteName = Str::replaceLast('Interface', '', $interfaceName);
        $concretePath = $file->getPath().DIRECTORY_SEPARATOR.$concreteName.'.php';

        if (! $this->app['files']->exists($concretePath)) {
            return true; // Consider if no concrete class exists as invalid for binding
        }

        // Check if concrete class is abstract
        $concreteFileContents = $this->app['files']->get($concretePath);
        $isAbstract = Str::contains($concreteFileContents, 'abstract class ');

        return $isAbstract;
    }

    /**
     * Bind an interface to its implementation if the concrete class exists and is not abstract.
     *
     * @param  \Symfony\Component\Finder\SplFileInfo  $file
     */
    protected function bindInterfaceToImplementation($file, string $appNamespace): void
    {
        // Extract essential names and paths
        $interfaceName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
        $concreteName = Str::replaceLast('Interface', '', $interfaceName);

        $concretePath = $file->getPath().DIRECTORY_SEPARATOR.$concreteName.'.php';

        // Build Fully Qualified Class Names (FQCN)
        $interfaceFqcn = $this->buildClassName($file->getRealPath(), $appNamespace);
        $concreteFqcn = $this->buildClassName($concretePath, $appNamespace);

        // Register the binding
        $this->app->bind($interfaceFqcn, $concreteFqcn);
    }

    /**
     * Build the FQCN for a given file path.
     */
    protected function buildClassName(string $filePath, string $appNamespace): string
    {
        $relativePath = str_replace(app_path(), '', $filePath);
        $relativePath = trim($relativePath, DIRECTORY_SEPARATOR);
        $relativePath = str_replace(['/', '\\'], '\\', $relativePath);

        // Strip the .php extension and return
        return $appNamespace.substr($relativePath, 0, -4);
    }
}
