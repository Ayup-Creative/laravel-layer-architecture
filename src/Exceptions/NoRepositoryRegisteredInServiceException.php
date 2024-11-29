<?php

namespace Ayup\LaravelLayerArchitecture\Exceptions;

use Exception;
use Throwable;

class NoRepositoryRegisteredInServiceException extends Exception
{
    public function __construct(string $service, int $code = 0, ?Throwable $previous = null)
    {
        $message = sprintf("No repository has been registered in service '%s'. Check that a protected 'repository' property is declared.", $service);

        parent::__construct($message, $code, $previous);
    }
}
