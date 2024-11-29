<?php

namespace Ayup\LaravelLayerArchitecture;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class Autodiscovery
{

    const string CONCRETES = 'concretes';
    const string CONTRACTS = 'contracts';
    const string REPOSITORIES = 'repositories';
    const string SERVICES = 'services';

    protected array $servicesDirectories = [];
    protected array $repositoriesDirectories = [];

    public function __construct(
        private Application $app
    )
    {
    }

    public function discoverAgain(): void
    {
        // Perform the discovery process and scan the directories
        // that have been configured.
        $this->discover();
    }

    /**
     * Discover and register aliases and singletons.
     *
     * @return void
     */
    public function discoverAndRegister(): void
    {
        $this->discover();
    }

    /**
     * Perform discovery.
     *
     * @return array
     */
    protected function discover()
    {
        // Perform the discovery process and scan the directories
        // that have been configured.
        $paths = array_merge(
            $this->getRepositoriesDirectories(),
            $this->getServiceDirectories()
        );

        $discovered = [];

        foreach ($paths as $path) {
            $discovered = array_merge_recursive($discovered, $this->discoverPath($path));
        }

        // Register discovered aliases within the application.
        $this->registerAliases(aliases: static::extractAliases($discovered));

        // Register discovered concrete objects within the application.
        $this->registerSingletons(singletons: static::extractConcretes($discovered));

        return $discovered;
    }

    /**
     * Extract items that are considered 'abstract'.
     *
     * @param array $discoveries
     * @return array
     */
    protected static function extractAliases(array $discoveries): array
    {
        // Pluck out all items stored within a contract key.
        return Arr::get($discoveries, static::CONTRACTS);
    }

    /**
     * Extract items that are considered 'concrete'.
     *
     * @param array $discoveries
     * @return array
     */
    protected static function extractConcretes(array $discoveries): array
    {
        // Pluck out all items stored within a concrete key.
        return Arr::get($discoveries, static::CONCRETES);
    }

    /**
     * Register discovered singletons.
     *
     * @param array $singletons
     * @return void
     */
    protected function registerSingletons(array $singletons): void
    {
        // Iterate over the objects passed and create a singleton
        // binding for each. We're expecting services and
        // repositories here.
        foreach ($singletons as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }
    }

    /**
     * Register discovered aliases.
     *
     * @param array $aliases
     * @return void
     */
    protected function registerAliases(array $aliases): void
    {
        // The aliases array is multidimensional, so we iterate
        // twice to create an alias for all intended objects.
        foreach ($aliases as $abstract => $array) {
            foreach ($array as $alias) {
                $this->app->alias($abstract, $alias);
            }
        }
    }

    /**
     * Scans a path and returns a formatted array of discovered files
     * and the classes/interfaces that are expected to be found
     * based on PSR-4 autoloading.
     *
     * @param string $path
     * @return array|array[]
     */
    protected function discoverPath(string $path): array
    {
        $discovered = [
            static::CONTRACTS => [],
            static::CONCRETES => [],
        ];

        // Use glob() to scan the directory for files we'd like to use, we
        // use the '**' wildcard to also include any subdirectories.
        foreach (glob($path . '/**/*') as $filename) {
            // Attempt to create a PHP namespace from the filename.
            $namespace = $this->filenameToNamespacedClass($filename);

            // Attempt to create an alias string from the filename
            // we've discovered.
            $alias = $this->namespaceToAlias($namespace);

            // If the PHP namespace ends with the expected interface suffix,
            // then we intend to create an alias, otherwise we will register
            // a singleton (for concrete objects).
            if (str_ends_with($namespace, $this->getInterfaceSuffix())) {
                $discovered[static::CONTRACTS][$alias][] = $namespace;
            } else {
                $discovered[static::CONCRETES][$alias] = $namespace;
            }
        }

        // Return the discoveries we've made.
        return $discovered;
    }

    /**
     * Convert a FQN to an alias.
     *
     * @param string $namespace
     * @return string
     */
    protected function namespaceToAlias(string $namespace): string
    {
        // Get the string that we would expect to see at the end
        // of an interface/contract definition.
        $suffix = $this->getInterfaceSuffix();

        // If the expected suffix is not blank, then we can use
        // preg_replace to remove it from the end of the
        // namespace string.
        if (!blank($suffix)) {
            $namespace = preg_replace('/' . $suffix . '$/i', '', $namespace);
        }

        // Finally, we can replace the namespace separator (backslash)
        // with a period, just to keep things identifiable.
        return str_replace('\\', '.', $namespace);
    }

    /**
     * Convert a filename to a namespaced class name.
     *
     * @param string $filename
     * @return string|null
     */
    protected function filenameToNamespacedClass(string $filename): ?string
    {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException("The file '{$filename}' does not exist.");
        }

        $namespace = null;
        $name = null;

        // Read the file line by line to extract namespace and declaration.
        $file = fopen($filename, 'r');
        while (($line = fgets($file)) !== false) {
            $trimmedLine = trim($line);

            // Match namespace declaration.
            if (preg_match('/^namespace\s+([^;]+);$/i', $trimmedLine, $matches)) {
                $namespace = $matches[1];
            }

            // Match class, interface, or trait declaration.
            if (preg_match('/^(class|interface|trait)\s+([a-zA-Z0-9_]+)\s*(?:extends|implements|\{|\s)*/i', $trimmedLine, $matches)) {
                $name = $matches[2];
                break; // Stop reading further; we've found the declaration.
            }
        }

        fclose($file);

        // Combine namespace and name to form the FQN.
        if ($namespace && $name) {
            return $namespace . '\\' . $name;
        }

        return null; // Return null if namespace or name was not found.
    }

    /**
     * Returns the path to the directory storing services.
     * Defaults to <app>/Services
     *
     * @return string
     */
    protected function getServicesPath(): string
    {
        $path = config('layer-architecture-auto-discovery.services_path');

        if (is_null($path)) {
            return app_path('Services');
        }

        return $path;
    }

    /**
     * Returns the path to the directory storing repositories.
     * Defaults to <app>/Repositories
     *
     * @return string
     */
    protected function getRepositoriesPath(): string
    {
        $path = config('layer-architecture-auto-discovery.repositories_path');

        if (is_null($path)) {
            return app_path('Repositories');
        }

        return $path;
    }

    /**
     * Returns the suffix of classes that are designated to be contracts (interfaces).
     * Defaults to ""
     *
     * @return string
     */
    protected function getInterfaceSuffix(): string
    {
        return config(
            key: 'layer-architecture-auto-discovery.interface_suffix',
            default: ''
        );
    }

    /**
     * Add services and repositories path.
     *
     * @param string|null $repositoriesPath
     * @param string|null $servicesPath
     * @return Autodiscovery
     */
    public function add(?string $repositoriesPath = null, ?string $servicesPath = null): static
    {
        if ($repositoriesPath !== null) {
            $this->addRepositoryDirectory($repositoriesPath);
        }

        if ($servicesPath !== null) {
            $this->addServiceDirectory($servicesPath);
        }

        return $this;
    }

    /**
     * Store additional repositories directory.
     *
     * @param string $path
     * @return Autodiscovery
     */
    public function addServiceDirectory(string $path): static
    {
        $this->servicesDirectories[] = $path;
        return $this;
    }

    /**
     * Store additional repository directories.
     *
     * @param string $path
     * @return Autodiscovery
     */
    public function addRepositoryDirectory(string $path): static
    {
        $this->repositoriesDirectories[] = $path;
        return $this;
    }

    /**
     * Get the services directories.
     *
     * @return array
     */
    public function getServiceDirectories(): array
    {
        return array_merge([$this->getServicesPath()], $this->servicesDirectories);
    }

    /**
     * Get the repositories directories.
     *
     * @return array
     */
    public function getRepositoriesDirectories(): array
    {
        return array_merge([$this->getRepositoriesPath()], $this->repositoriesDirectories);
    }
}
