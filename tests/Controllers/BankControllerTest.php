<?php

namespace Controllers;

use App\Bank\Models\Account;
use App\Bank\Models\Amount;
use App\Exceptions\BankAccountDoesNotExist;
use App\Repositories\BankRepository;
use App\Repositories\SQLBankRepository;
use TestCase;

class BankControllerTest extends TestCase
{
    /**
     * @var BankRepository
     */
    private $bankRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bankRepository = app(BankRepository::class);
    }

    /**
     * @param string $amount
     * @testWith ["1.00"]
     *            ["0.10"]
     *            ["0.01"]
     *            ["10.01"]
     *            ["10.11"]
     */
    public function testMakeTransaction(string $amount)
    {
        $from = '1';
        $to = '2';

        $fromQuery = "SELECT balance from accounts WHERE account_number = '$from'";
        $toQuery = "SELECT balance from accounts WHERE account_number = '$to'";

        $beforeFrom = Amount::fromString(app('db')->selectOne($fromQuery)->balance);
        $beforeTo = Amount::fromString(app('db')->selectOne($toQuery)->balance);

        $amount = Amount::fromString($amount);
        $response = $this->post(route('bank.transaction'), [
            'from'   => $from,
            'to'     => $to,
            'amount' => $amount->getDecimal()
        ]);

        $response->assertResponseOk();

        $afterFrom = app('db')->selectOne($fromQuery)->balance;
        $afterTo = app('db')->selectOne($toQuery)->balance;

        $this->assertEquals($afterFrom, $beforeFrom->sub($amount)->getDecimal());
        $this->assertEquals($afterTo, $beforeTo->add($amount)->getDecimal());
    }

    public function testMakeTransactionFailed()
    {
        $response = $this->post(route('bank.transaction'), []);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure(['from', 'to', 'amount']);

        $response = $this->post(route('bank.transaction'), [
            'from' => '1',
            'to' => '2',
            'amount' => '3'
        ]);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure(['amount']);
    }

    public function testMakeTransactionToSelf()
    {
        $response = $this->post(route('bank.transaction'), [
            'from' => '2',
            'to' => '2',
            'amount' => '3'
        ]);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure(['to', 'amount']);
    }

    public function testMakeTransactionNotEnoughMoney()
    {
        $response = $this->post(route('bank.transaction'), [
            'from' => '1',
            'to' => '2',
            'amount' => '300000.00'
        ]);
        $response->assertResponseStatus(400);
        $response->seeJsonStructure(['error']);
    }

    public function testGetBalance()
    {
        $response = $this->get(route('bank.balance', ['accountNumber' => 1]));
        $response->assertResponseOk();
        $response->seeJsonStructure(['balance']);
    }

    /**
     * @param $initialBalance
     * @testWith ["0.00"]
     *           ["10.00"]
     *           ["10.17"]
     * @throws BankAccountDoesNotExist
     */
    public function testCreateAccount($initialBalance)
    {
        $newAccountNumber = rand(25000, PHP_INT_MAX);

        try {
            $this->bankRepository->getBalance(new Account($newAccountNumber));
            $this->fail('Account exists');
        } catch (BankAccountDoesNotExist $e) {

        }
        $response = $this->post(route('bank.createAccount', [
            'accountNumber' => $newAccountNumber,
            'initialBalance' => $initialBalance
        ]));

        $response->assertResponseOk();
        $response->seeJsonStructure(['accountNumber', 'initialBalance']);
        $this->assertEquals($initialBalance, $this->bankRepository->getBalance(new Account($newAccountNumber)));
    }

    public function testCreateAccountNegativeInitialValue()
    {
        $response = $this->post(route('bank.createAccount', [
            'accountNumber' => rand(25000, PHP_INT_MAX),
            'initialBalance' => '-10.00'
        ]));

        $response->assertResponseStatus(422);
    }

    public function testCreateAccountExistingAccount()
    {
        $response = $this->post(route('bank.createAccount', [
            'accountNumber' => '3',
            'initialBalance' => '10.00'
        ]));

        $response->assertResponseStatus(400);
        $response->seeJsonStructure(['error']);
    }
}
