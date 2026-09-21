<?php

namespace App\Providers;

use App\Models\Admin\AccessUser;
use App\Models\Admin\Menu;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {



        Inertia::share([
            // Share the authenticated user
            'auth' => function () {
                return [
                    'user' => Auth::user()
                ];
            },



            // Share app settings
            'settings' => function () {
                return config('app.name'); // or your custom settings
            },

            // Example: Share sidebar menu
            'sidebar' => function () {

                $access = [];

                if(!empty( Auth::user())){
                    $access = AccessUser::where('user_id', Auth::user()->id)->pluck('menu_id');
                }
                return Menu::orderBy('sequance')->with('children', function($q){
                    $q->orderBy('sequance')
                    ->where('status', 1);
                })
                        ->whereNull('parent_id')
                        ->where('status', 1)
                        ->where(function($q) use($access){
                            if(!empty( Auth::user()) && Auth::user()->role_id != 1){
                                $q->whereIn("id", $access);
                            }
                        })
                    ->get();
            },
        ]);
    }
}
