<?php

namespace App\Exceptions;

class CreateAccountException extends \Exception
{
    /**
     * CreateAccountException constructor.
     *
     * @param string $errorMessage
     */
    public function __construct(string $errorMessage)
    {
        parent::__construct($errorMessage);
    }
}
