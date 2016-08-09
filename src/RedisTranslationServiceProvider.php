<?php

namespace brunojk\LaravelSBRedisTranslation;

use Illuminate\Support\ServiceProvider;

class RedisTranslationServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton('translator', function ($app) {

            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $app['config']['app.locale'];
            $fblocale = $app['config']['app.fallback_locale'];

            $trans = new RedisTranslator($locale);
            $trans->setFallback($fblocale);

            return $trans;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return ['translator'];
    }
}