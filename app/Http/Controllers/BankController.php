<?php

namespace App\Http\Controllers;

use App\Bank\Models\Account;
use App\Bank\Models\Amount;
use App\Exceptions\BankAccountDoesNotExistException;
use App\Exceptions\CreateAccountException;
use App\Exceptions\NotEnoughMoneyException;
use App\Repositories\BankRepository;
use App\Rules\Decimal;
use Illuminate\Http\Request;

class BankController extends Controller
{
    /**
     * @var BankRepository
     */
    private $bankRepository;

    /**
     * Create a new controller instance.
     *
     * @param BankRepository $bankRepository
     */
    public function __construct(BankRepository $bankRepository)
    {
        $this->bankRepository = $bankRepository;
    }

    public function getBalance($accountNumber)
    {
        try {
            $balance = $this->bankRepository->getBalance(new Account($accountNumber));

            return ['balance' => $balance];
        } catch (BankAccountDoesNotExistException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function transaction(Request $request)
    {
        $this->validate($request, [
            'from'   => 'required',
            'to'     => ['required', 'different:from'],
            'amount' => [
                'required',
                new Decimal(),
            ],
        ]);

        try {
            $amount = Amount::fromString(\request('amount'));
            $accountNumberFrom = \request('from');
            $accountNumberTo = \request('to');

            $this->bankRepository->makeTransaction(new Account($accountNumberFrom), new Account($accountNumberTo),
                $amount);

            return ['status' => 'ok'];
        } catch (BankAccountDoesNotExistException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (NotEnoughMoneyException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            app('log')->error($e->getMessage(), $e->getTrace());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function createAccount(Request $request)
    {
        $this->validate($request, [
            'accountNumber'  => 'required',
            'initialBalance' => [
                'required',
                new Decimal(),
            ],
        ]);
        $accountNumber = $request->input('accountNumber');
        $initialBalance = Amount::fromString($request->input('initialBalance'));


        try {
            $res = $this->bankRepository->createAccount(new Account($accountNumber), $initialBalance);
            if ($res)
                return ['status' => 'ok'];
            else
                return response()->json(['status' => 'failed'], 500);
        } catch (CreateAccountException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            app('log')->error($e->getMessage(), $e->getTrace());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
