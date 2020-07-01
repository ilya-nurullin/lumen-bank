<?php

namespace App\Bank\Models;

class Account
{
    /**
     * @var string
     */
    private $accountNumber;

    /**
     * Account constructor.
     *
     * @param string $accountNumber
     */
    public function __construct(string $accountNumber)
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return string
     */
    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }
}
