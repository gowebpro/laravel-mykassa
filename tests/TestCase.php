<?php

namespace GoWebPro\MyKassa\Test;

use GoWebPro\MyKassa\MyKassa;
use GoWebPro\MyKassa\MyKassaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * @var MyKassa
     */
    protected $mykassa;

    public function setUp(): void
    {
        parent::setUp();

        $this->mykassa = $this->app['mykassa'];

        $this->app['config']->set('mykassa.project_id', '12345');
        $this->app['config']->set('mykassa.secret_key', 'secret_key');
        $this->app['config']->set('mykassa.secret_key_second', 'secret_key_second');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            MyKassaServiceProvider::class,
        ];
    }

    /**
     * @param array $config
     */
    protected function withConfig(array $config)
    {
        $this->app['config']->set($config);
        $this->app->forgetInstance(MyKassa::class);
        $this->mykassa = $this->app->make(MyKassa::class);
    }
}
