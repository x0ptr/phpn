<?php

namespace PHPN\Laravel;

use Illuminate\Support\ServiceProvider;

class PHPNServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            Commands\DesktopServeCommand::class,
            Commands\DesktopBundleCommand::class,
        ]);
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/phpn.php' => config_path('phpn.php'),
            ], 'phpn-config');
        }
    }
}
