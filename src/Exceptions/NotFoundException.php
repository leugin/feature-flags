<?php

namespace Leugin\FeatureFlags\Exceptions;

use Leugin\FeatureFlags\Data\Constants\ExceptionCode;

class NotFoundException extends \DomainException
{

    public function __construct($message = "n", $code = ExceptionCode::NOT_FOUND, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}