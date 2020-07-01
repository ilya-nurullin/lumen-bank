<?php

use Illuminate\Database\Seeder;

class AccountsSeeder extends Seeder
{
    public function run()
    {
        $values = [];

        for ($i = 1; $i < 15001; ++$i) {
            $values[] = "('$i', 15000)";
        }

        app('db')->insert('INSERT INTO accounts(account_number, balance) VALUES '.join(',', $values));
    }
}
