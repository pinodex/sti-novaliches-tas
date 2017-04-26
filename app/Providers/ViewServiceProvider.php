<?php

namespace App\Providers;

use Auth;
use App\Components\Menu;
use App\Models\Bulletin;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $twig = app('twig');
        $executed = false;

        view()->composer('*', function ($view) use ($twig, &$executed) {
            if ($executed) {
                return;
            }

            if (Auth::check()) {
                $menu = Menu::process(config('menu'));

                $twig->addGlobal('menu', $menu);
                $twig->addGlobal('bulletins', Bulletin::orderBy('created_at', 'DESC')->with('author')->get());
                
                $executed = true;
            }

            $twig->addGlobal('current_route', request()->route()->getName());
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
