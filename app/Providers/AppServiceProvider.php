<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(\App\Support\HelpRegistry::class, fn() => new \App\Support\HelpRegistry());

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set locale from session or user profile
        \Illuminate\Support\Facades\App::setLocale(
            session('locale') ?? (auth()->check() ? auth()->user()->getLocale() : config('app.locale'))
        );

        \App\Models\Help::observe(\App\Observers\HelpObserver::class);

        // Register price visibility gate
        \Illuminate\Support\Facades\Gate::define('seePrices', function (\App\Models\User $user) {
            return $user->canSeePrices();
        });
    }
}
