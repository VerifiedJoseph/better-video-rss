<?php

namespace App\Exception;

use Throwable;

class ConfigurationException extends \Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        $message = 'Config Error: ' . $message;

        parent::__construct($message, $code, $previous);
    }
}
