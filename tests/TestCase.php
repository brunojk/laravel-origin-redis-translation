<?php

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $redis;

    protected function out($mix){
        fwrite(STDOUT, print_r($mix, true));
    }

    /**
     * Get package providers.
     *
     * @param Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            'brunojk\LaravelSBRedisTranslation\RedisTranslationServiceProvider',
        ];
    }

    /**
     * Define environment setup.
     *
     * @param Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // load custom config
        $dbconfig = require 'config/database.php';
        $appconfig = require 'config/app.php';

        // set rethinkdb as default connection
        $app['config']->set('database.redis', $dbconfig['redis']);
        $app['config']->set('app.locale', $appconfig['locale']);
        $app['config']->set('app.fallback_locale', $appconfig['fallback_locale']);

        $this->redis = app()->make('redis');
    }
}
