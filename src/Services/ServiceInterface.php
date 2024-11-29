<?php

namespace Ayup\LaravelLayerArchitecture\Services;

use Ayup\LaravelLayerArchitecture\Repositories\RepositoryInterface;

interface ServiceInterface
{
    /**
     * Returns the configured Repository object.
     *
     * @return RepositoryInterface
     */
    public function getRepository();
}
