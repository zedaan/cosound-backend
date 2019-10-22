<?php

namespace App\Providers;

use Stripe\Stripe;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Stripe::setApiKey(
            env('STRIPE_SECRET')
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * App Binding Containers
         */
        $classes = array(
            'Auth',
            'Feed',
            'Marketplace\Service',
            'Notification',
            'Post'
        );

        foreach ($classes as $class) {
            $this->app->bind(
                "App\Contracts\\${class}Contract",
                "App\Repositories\\${class}Repository"
            );
        }
    }
}
