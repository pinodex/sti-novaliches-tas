<?php

namespace App\Providers;

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

            $twig->addGlobal('bulletins', Bulletin::orderBy('created_at', 'DESC')->with('author')->get());
            $executed = true;
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
