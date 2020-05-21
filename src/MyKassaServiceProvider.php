<?php

namespace GoWebPro\MyKassa;

use Illuminate\Support\ServiceProvider;

class MyKassaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/mykassa.php' => config_path('mykassa.php'),
        ], 'config');

        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mykassa.php', 'mykassa');

        $this->app->singleton('mykassa', function () {
            return $this->app->make(MyKassa::class);
        });

        $this->app->alias('mykassa', 'MyKassa');

        //
    }
}
