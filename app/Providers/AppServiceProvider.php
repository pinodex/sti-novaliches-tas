<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Observers\UserObserver;
use App\Models\Bulletin;
use App\Models\User;

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

        $twig = app('twig');
        $executed = false;

        view()->composer('*', function ($view) use ($twig, &$executed) {
            if ($executed) {
                return;
            }

            $twig->addGlobal('bulletins', Bulletin::orderBy('created_at', 'DESC')->with('author')->get());
            $executed = true;
        });

        User::observe(UserObserver::class);
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
