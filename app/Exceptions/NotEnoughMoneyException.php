<?php

namespace App\Exceptions;

class NotEnoughMoneyException extends \Exception
{
    /**
     * BankAccountDoesNotExists constructor.
     *
     * @param string $bankAccount
     */
    public function __construct(string $bankAccount)
    {
        parent::__construct("There is not enough money in the Bank account #$bankAccount");
    }
}
