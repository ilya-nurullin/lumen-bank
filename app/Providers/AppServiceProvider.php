<?php

namespace App\Providers;

use App\Repositories\BankRepository;
use App\Repositories\SQLBankRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(BankRepository::class, SQLBankRepository::class);
    }
}
