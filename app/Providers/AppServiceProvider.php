<?php

namespace App\Providers;

use Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Models\Bulletin;

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

            if (Auth::check()) {
                $provider = explode(':', Auth::id());

                $twig->addGlobal('nav', config('menu.' . $provider[0] . '.top'));
                $twig->addGlobal('menu', config('menu.' . $provider[0] . '.side'));
                
                $twig->addGlobal('bulletins', Bulletin::orderBy('created_at', 'DESC')->with('author')->get());
                $executed = true;
            }

            $twig->addGlobal('current_route', request()->route()->getName());
        });
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
