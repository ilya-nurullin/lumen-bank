<?php

namespace App\Repositories;

use App\Bank\Models\Account;
use App\Bank\Models\Amount;
use App\Exceptions\BankAccountDoesNotExist;
use App\Exceptions\NotEnoughMoney;

class SQLBankRepository implements BankRepository
{
    /**
     * @var \Illuminate\Database\Connection
     */
    private $db;

    /**
     * SQLBankRepository constructor.
     */
    public function __construct()
    {
        $this->db = app('db');
    }

    /**
     * @param Account $from
     * @param Account $to
     * @param Amount  $amount
     *
     * @return bool
     * @throws \Throwable
     */
    public function makeTransaction(Account $from, Account $to, Amount $amount): bool
    {
        return $this->db->transaction(function ($transaction) use ($to, $from, $amount) {
            $fromAccountNumber = $from->getAccountNumber();
            $toAccountNumber = $to->getAccountNumber();

            if (Amount::fromString($this->getBalance($from))->isLessThan($amount)) {
                throw new NotEnoughMoney($fromAccountNumber);
            }

            $fromResult = $this->db->update("UPDATE accounts SET balance = balance - ? WHERE account_number = ?", [
                    $amount,
                    $fromAccountNumber,
                ]);

            if ($fromResult !== 1)
                throw new BankAccountDoesNotExist($fromAccountNumber);

            $toResult = $this->db->update("UPDATE accounts SET balance = balance + ? WHERE account_number = ?", [
                    $amount,
                    $toAccountNumber,
                ]);

            if ($toResult !== 1)
                throw new BankAccountDoesNotExist($toAccountNumber);

            return true;
        });
    }

    /**
     * @param Account $account
     *
     * @return string
     * @throws BankAccountDoesNotExist
     */
    public function getBalance(Account $account): string
    {
        $accountNumber = $account->getAccountNumber();

        $accountBalanceResult = $this->db->selectOne('SELECT balance from accounts where account_number = ?',
            [$accountNumber]);

        if (is_null($accountBalanceResult))
            throw new BankAccountDoesNotExist($accountNumber);
        else
            return $accountBalanceResult->balance;
    }

    /**
     * @param Account $account
     * @param Amount  $initialBalance
     *
     * @return bool
     * @throws \Exception
     */
    public function createAccount(Account $account, Amount $initialBalance): bool
    {
        if ($this->accountExists($account)) {
            $accountNumber = $account->getAccountNumber();
            throw new \Exception("Account #$accountNumber is already taken");
        }

        if (! $initialBalance->isPositive())
            throw new \Exception("Initial balance should be positive");

        $accountNumber = $account->getAccountNumber();

        $this->db->insert('INSERT INTO accounts(balance, account_number) VALUES (?,?)', [
                $initialBalance,
                $accountNumber,
            ]);

        return true;
    }

    public function accountExists(Account $account): bool
    {
        $accountNumber = $account->getAccountNumber();

        $countResult = $this->db->selectOne('SELECT COUNT(*) as count FROM accounts where account_number = ?',
            [$accountNumber]);

        return intval($countResult->count) === 1;
    }
}
