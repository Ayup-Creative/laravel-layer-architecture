<?php

use Illuminate\Support\Facades\Artisan;

test('service provider registers make:layer command', function () {

    $commands = array_keys(Artisan::all());

    expect($commands)
        ->toContain('make:layer');

});

test('service provider merges configuration properly', function () {
    $config = config('layer-architecture-auto-discovery');

    expect($config)
        ->toHaveKey('paths.services')
        ->toHaveKey('paths.repositories');
});
