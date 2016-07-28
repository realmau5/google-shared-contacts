<?php
namespace GSharedContacts\Google;

use Illuminate\Support\ServiceProvider;

/**
 * Class SharedContactsServiceProvider
 *
 * @package GSharedContacts\Google
 */
class SharedContactsServiceProvider extends ServiceProvider
{
    // Triggered automatically by Laravel
    public function register()
    {
        if ($this->app->environment() == 'app-engine') {
            $this->app->bind(
                'GSharedContacts\Google\SharedContactsInterface', // interface
                'GSharedContacts\Google\SharedContactsGAEOAuth2' // class
            );
        } else {
            $this->app->bind(
                'GSharedContacts\Google\SharedContactsInterface', // interface
                'GSharedContacts\Google\SharedContactsOAuth2' // class
            );
        }

    }

}