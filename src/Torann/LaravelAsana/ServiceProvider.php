<?php namespace Torann\LaravelAsana;

class ServiceProvider extends \Illuminate\Support\ServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
        $this->publishes([
            __DIR__.'/../../config/asana.php' => config_path('asana.php'),
        ]);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['torann.asana'] = $this->app->share(function($app)
		{
            $config = $app->config->get('asana', array());

			return new Asana($config);
		});
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
