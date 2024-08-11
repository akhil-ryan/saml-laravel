<?php

namespace Oktalogin\SamlOktaLogin;

use Illuminate\Support\ServiceProvider;

class OktaLoginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/saml.php', 'saml'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('saml.enable_route')) {
            $this->loadRoutesFrom(__DIR__ . '/Http/routes/web.php');
        }

        $this->publishes([
            __DIR__ . '/../config/saml.php' => config_path('saml.php'),
        ], 'config');
    }
}
