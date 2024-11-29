<?php

namespace Ayup\LaravelLayerArchitecture\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static static add(?string $repositoriesPath = null, ?string $servicesPath = null)
 * @method static static addServiceDirectory(string $path)
 * @method static static addRepositoryDirectory(string $path)
 * @method static array getServiceDirectories
 * @method static array getRepositoriesDirectories
 * @method static void discoverAgain
 * @method static void discoverAndRegister
 */
class Autodiscovery extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'autodiscovery';
    }
}
