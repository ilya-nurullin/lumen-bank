<?php

namespace Repositories;

use App\Bank\Models\Account;
use App\Repositories\SQLBankRepository;
use TestCase;

class SQLBankRepositoryTest extends TestCase
{
    public function testAccountExists()
    {
        $bankRepository = app(SQLBankRepository::class);

        $this->assertTrue($bankRepository->accountExists(new Account("1")));

        $this->assertFalse($bankRepository->accountExists(new Account("98765431")));
    }
}
