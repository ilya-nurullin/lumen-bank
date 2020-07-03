<?php

namespace App\Exceptions;

class BankAccountDoesNotExistException extends \Exception
{
    /**
     * BankAccountDoesNotExists constructor.
     *
     * @param string $bankAccount
     */
    public function __construct(string $bankAccount)
    {
        parent::__construct("Bank account #$bankAccount does not exist");
    }
}
