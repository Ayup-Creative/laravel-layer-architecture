<?php

namespace Ayup\LaravelLayerArchitecture\Commands;

use Ayup\LaravelStubMaker\Argument;
use Ayup\LaravelStubMaker\Concerns\MakesStubs;
use Ayup\LaravelStubMaker\Constructor;
use Ayup\LaravelStubMaker\Stub;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class MakeLayerCommand extends Command
{
    use MakesStubs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:layer {name} {--base=} {--namespace=} {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create corresponding service and repository files in configured directories';

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle()
    {
        $name = $this->divideIntoNamespace(
            $this->argument('name')
        );
        $model = $this->option('model') ?? Model::class;
        $base = $this->option('base');
        $namespace = $this->option('namespace');

        [$repoInterface, $repoClass] = $this->makeRepository($name, $model, $base, $namespace);
        [$serviceInterface, $serviceClass] = $this->makeService($name, $repoInterface, $base, $namespace);

        $stubs = [
            $repoInterface, $repoClass,
            $serviceInterface, $serviceClass,
        ];

        /** @var Stub[] $stubs */
        foreach ($stubs as $stub) {
            try {
                if ($stub->writeOut()) {
                    $this->info(sprintf("Created '%s'", $stub->getFqn()));
                }
            } catch (Exception $e) {
                $this->error(sprintf("Failed to create '%s'", $stub->getFqn()));
                $this->error($e->getMessage());
            }
        }

        exit;
    }

    private function divideIntoNamespace(string $name): string
    {
        $pieces = explode(DIRECTORY_SEPARATOR, $name);

        $path = Arr::map($pieces, fn ($piece) => Str::plural($piece));
        $path[] = implode('', Arr::map($pieces, fn ($piece) => Str::singular($piece)));

        return implode(DIRECTORY_SEPARATOR, $path);
    }

    /**
     * @throws Exception
     */
    private function makeRepository(string $class, string $model, ?string $base = null, ?string $namespace = null): array
    {
        $name = 'Repositories'.DIRECTORY_SEPARATOR.$class.'Repository';

        $path = is_null($base)
            ? $this->getRepositoriesPath($class)
            : $this->getPathFromBase($base.DIRECTORY_SEPARATOR.'Repositories', $class);

        $interface = $this->stub($name.$this->getInterfaceSuffix(), $namespace)
            ->interface()
            ->extends(\Ayup\LaravelLayerArchitecture\Repositories\RepositoryInterface::class)
            ->outputPath($path);

        $class = $this->stub($name, $namespace)
            ->extends(\Ayup\LaravelLayerArchitecture\Repositories\Repository::class)
            ->implements($interface->getFqn())
            ->outputPath($path)
            ->constructor(
                Constructor::make([
                    Argument::make('model')->protected()->hint($model),
                ])
            );

        return [$interface, $class];
    }

    /**
     * @throws Exception
     */
    private function makeService(string $class, string $repositoryInterface, ?string $base = null, ?string $namespace = null): array
    {
        $path = is_null($base)
            ? $this->getServicesPath($class)
            : $this->getPathFromBase($base.DIRECTORY_SEPARATOR.'Services', $class);

        $name = 'Services'.DIRECTORY_SEPARATOR.$class.'Service';

        $interface = $this->stub($name.$this->getInterfaceSuffix(), $namespace)
            ->interface()
            ->extends(\Ayup\LaravelLayerArchitecture\Services\ServiceInterface::class)
            ->outputPath($path);

        $class = $this->stub($name, $namespace)
            ->extends(\Ayup\LaravelLayerArchitecture\Services\Service::class)
            ->implements($interface->getFqn())
            ->outputPath($path)
            ->constructor(
                Constructor::make([
                    Argument::make('repository')->protected()->hint($repositoryInterface),
                ])
            );

        return [$interface, $class];
    }

    /**
     * Get a path with a specified base path.
     */
    private function getPathFromBase(string $path, string $class): string
    {
        return base_path($path)
            .DIRECTORY_SEPARATOR.(str_contains($class, DIRECTORY_SEPARATOR)
                ? Str::beforeLast($class, DIRECTORY_SEPARATOR)
                : null
            );
    }

    /**
     * Return the configured service path.
     */
    private function getServicesPath(string $path): string
    {
        return config('layer-architecture-auto-discovery.services_path')
            .DIRECTORY_SEPARATOR.(str_contains($path, DIRECTORY_SEPARATOR)
                ? Str::beforeLast($path, DIRECTORY_SEPARATOR)
                : null
            );
    }

    /**
     * Return the configured repository path.
     */
    private function getRepositoriesPath(string $path): string
    {
        return config('layer-architecture-auto-discovery.repositories_path')
            .DIRECTORY_SEPARATOR.(str_contains($path, DIRECTORY_SEPARATOR)
                ? Str::beforeLast($path, DIRECTORY_SEPARATOR)
                : null
            );
    }

    /**
     * Return the interface suffix string.
     */
    private function getInterfaceSuffix(): string
    {
        return config('layer-architecture-auto-discovery.interface_suffix', 'Interface');
    }
}
