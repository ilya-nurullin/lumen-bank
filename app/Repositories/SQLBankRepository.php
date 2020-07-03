<?php

namespace App\Repositories;

use App\Bank\Models\Account;
use App\Bank\Models\Amount;
use App\Exceptions\BankAccountDoesNotExistException;
use App\Exceptions\CreateAccountException;
use App\Exceptions\NotEnoughMoneyException;

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
                throw new NotEnoughMoneyException($fromAccountNumber);
            }

            if ($from->getAccountNumber() > $to->getAccountNumber()) {
                $firstQueryResult = $this->subMoney($amount, $fromAccountNumber);
                $this->checkQueryResult($firstQueryResult, $transaction, $fromAccountNumber);

                $secondQueryResult = $this->addMoney($amount, $toAccountNumber);
                $this->checkQueryResult($secondQueryResult, $transaction, $toAccountNumber);
            }
            else {
                $firstQueryResult = $this->addMoney($amount, $toAccountNumber);
                $this->checkQueryResult($firstQueryResult, $transaction, $toAccountNumber);

                $secondQueryResult = $this->subMoney($amount, $fromAccountNumber);
                $this->checkQueryResult($secondQueryResult, $transaction, $fromAccountNumber);
            }

            return true;
        }, 3);
    }

    /**
     * @param Account $account
     *
     * @return string
     * @throws BankAccountDoesNotExistException
     */
    public function getBalance(Account $account): string
    {
        $accountNumber = $account->getAccountNumber();

        $accountBalanceResult = $this->db->selectOne('SELECT balance from accounts where account_number = ?',
            [$accountNumber]);

        if (is_null($accountBalanceResult))
            throw new BankAccountDoesNotExistException($accountNumber);
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
            throw new CreateAccountException("Account #$accountNumber is already taken");
        }

        if (! $initialBalance->isPositive())
            throw new CreateAccountException("Initial balance should be positive");

        $accountNumber = $account->getAccountNumber();

        $this->db->insert('INSERT INTO accounts(balance, account_number) VALUES (?,?)', [
                $initialBalance,
                $accountNumber,
            ]);

        return true;
    }

    /**
     * @param Account $account
     *
     * @return bool
     */
    public function accountExists(Account $account): bool
    {
        $accountNumber = $account->getAccountNumber();

        $countResult = $this->db->selectOne('SELECT COUNT(*) as count FROM accounts where account_number = ?',
            [$accountNumber]);

        return intval($countResult->count) === 1;
    }

    /**
     * @param        $amount
     * @param string $accountNumber
     *
     * @return int
     */
    private function addMoney($amount, string $accountNumber) {
        return $this->db->update("UPDATE accounts SET balance = balance + ? WHERE account_number = ?", [
            $amount,
            $accountNumber,
            ]);
    }

    /**
     * @param        $amount
     * @param string $accountNumber
     *
     * @return int
     */
    private function subMoney($amount, string $accountNumber) {
        return $this->db->update("UPDATE accounts SET balance = balance - ? WHERE account_number = ?", [
            $amount,
            $accountNumber,
            ]);
    }

    /**
     * @param $res
     * @param $transaction
     * @param $account
     *
     * @throws BankAccountDoesNotExistException
     */
    private function checkQueryResult($res, $transaction, $account)
    {
        if ($res !== 1) {
            $transaction->rollback();
            throw new BankAccountDoesNotExistException($account);
        }
    }
}
