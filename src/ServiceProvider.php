<?php

namespace Torann\LaravelAsana;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/config/asana.php', 'asana'
        );

        $this->commands([
            \Torann\LaravelAsana\Commands\CustomFields::class,
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerAsanaService();

        if ($this->app->runningInConsole()) {
            $this->registerResources();
        }
    }

    /**
     * Register the Asana service.
     *
     * @return void
     */
    public function registerAsanaService()
    {
        $this->app->singleton('torann.asana', function ($app) {
            $config = $app->config->get('asana', []);

            return new Asana($config);
        });
    }

    /**
     * Register Asana resources.
     *
     * @return void
     */
    public function registerResources()
    {
        if ($this->isLumen() === false) {
            $this->publishes([
                __DIR__ . '/../config/asana.php' => config_path('asana.php'),
            ], 'config');
        }
    }

    /**
     * Check if package is running under Lumen app
     *
     * @return bool
     */
    protected function isLumen()
    {
        return str_contains($this->app->version(), 'Lumen') === true;
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
