<?php

namespace Ayup\LaravelLayerArchitecture\Exceptions;

use Exception;
use Throwable;

class NoModelRegisteredInRepositoryException extends Exception
{
    public function __construct(string $repository, int $code = 0, ?Throwable $previous = null)
    {
        $message = sprintf("No model has been registered in repository '%s'. Check that a protected 'model' property is declared.", $repository);

        parent::__construct($message, $code, $previous);
    }
}
