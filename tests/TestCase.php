<?php

namespace Ayup\LaravelLayerArchitecture\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            \Ayup\LaravelLayerArchitecture\LaravelLayerArchitectureServiceProvider::class,
        ];
    }
}
