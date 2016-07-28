<?php

namespace GSharedContacts\Providers;

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
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('GSharedContacts\Google\SharedContactsInterface', 'GSharedContacts\Google\SharedContactsGAEOAuth2');

    }
}
