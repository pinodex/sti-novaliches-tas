<?php

namespace App\Providers;

use Auth;
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

        $twig->addGlobal('nav', config('menu.top'));
        $twig->addGlobal('menu', config('menu.side'));

        view()->composer('*', function ($view) use ($twig, &$executed) {
            if ($executed) {
                return;
            }

            if (Auth::check()) {
                $twig->addGlobal('bulletins', Bulletin::orderBy('created_at', 'DESC')->with('author')->get());
                $executed = true;
            }

            $twig->addGlobal('current_route', request()->route()->getName());
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
