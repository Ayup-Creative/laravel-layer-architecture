<?php

namespace Ayup\LaravelLayerArchitecture\Services;

use Ayup\LaravelLayerArchitecture\Exceptions\NoRepositoryRegisteredInServiceException;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class Service implements ServiceInterface
{
    /**
     * @inheritDoc
     * @throws NoRepositoryRegisteredInServiceException
     */
    public function getRepository()
    {
        if(!property_exists($this, 'repository')) {
            throw new NoRepositoryRegisteredInServiceException(static::class);
        }

        return $this->repository;
    }

    /**
     * Get the fillable entity attributes from the parameter bag.
     *
     * @param ParameterBag $parameterBag
     *
     * @return array
     * @throws NoRepositoryRegisteredInServiceException
     */
    protected function getFillableParameters(ParameterBag $parameterBag): array
    {
        return Arr::only(
            array: $parameterBag->all(),
            keys: $this->getRepository()->getModel()->getFillable()
        );
    }
}
