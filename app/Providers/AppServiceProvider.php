<?php

namespace App\Providers;

use App\Http\Model\AdminUser;
use App\Http\Model\Agent;
use App\Http\Model\Customer;
use App\Http\Model\User;
use App\Observer\AdminUserObserver;
use App\Observer\AgentObserver;
use App\Observer\CustomerObserver;
use App\Observer\UserObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        //开启 sql 语句打印
        if (config('app.debug')) {
            \DB::enableQueryLog();
        }
        AdminUser::observe(AdminUserObserver::class);
        Agent::observe(AgentObserver::class);
        Customer::observe(CustomerObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
