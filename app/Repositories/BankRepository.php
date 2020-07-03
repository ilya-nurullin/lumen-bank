<?php

namespace App\Repositories;

use App\Bank\Models\Account;
use App\Bank\Models\Amount;
use App\Exceptions\BankAccountDoesNotExistException;
use App\Exceptions\NotEnoughMoneyException;

interface BankRepository
{
    /**
     * Returns current balance for an account
     *
     * @param Account $account
     *
     * @return string Decimal as string
     * @throws BankAccountDoesNotExistException
     */
    public function getBalance(Account $account): string;

    /**
     * Makes transaction between from and to account
     *
     * @param Account $from
     * @param Account $to
     * @param Amount $amount
     *
     * @return mixed
     *
     * @throws BankAccountDoesNotExistException|NotEnoughMoneyException|\Throwable
     */
    public function makeTransaction(Account $from, Account $to, Amount $amount);

    /**
     * Check account number existence
     * @param Account $account
     *
     * @return bool
     */
    public function accountExists(Account $account): bool;

    /**
     * @param Account $account
     * @param Amount  $initialBalance
     *
     * @return bool
     * @throws \Exception
     */
    public function createAccount(Account $account, Amount $initialBalance): bool;
}
