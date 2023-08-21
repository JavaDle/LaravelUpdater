<?php
/*
* @author: Husein JavaDLE
* https://github.com/JavaDle
*/

namespace javadle\updater;

use Illuminate\Support\ServiceProvider;
use javadle\updater\Commands\CommandCheck;
use javadle\updater\Commands\CommandUpdate;
use javadle\updater\Commands\CommandCurrentVersion;

class UpdaterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/updater.php' => config_path('updater.php'),], 'updater');
        $this->publishes([__DIR__ . '/../lang' => lang_path()], 'updater');

        $this->publishes([
            __DIR__.'/../public/javadle/updater/sweetalert2.js' => public_path('javadle/updater/sweetalert2.js')
        ], 'javadle-updater');

        $this->publishes([
            __DIR__.'/../public/javadle/updater/jquery-3.7.0.min.js' => public_path('javadle/updater/jquery-3.7.0.min.js')
        ], 'javadle-updater');

        $this->publishes([__DIR__ . '/../views' => resource_path('views/vendor/updater')], 'updater');

        $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');

        $this->loadTranslationsFrom(__DIR__ . '/lang', 'updater');


        $this->commands(
            [
                CommandUpdate::class,
                CommandCheck::class,
                CommandCurrentVersion::class
            ]
        );
    }

    public function register()
    {
        //
    }
}
