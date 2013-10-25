<?php namespace Packettide\Sire;

use Illuminate\Support\ServiceProvider;

class SireServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('packettide/sire');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
		$this->app['mustache'] = $this->app->share(function($app)
		{
			return new Mustache_Engine();
		});

		$this->app['templater'] = $this->app->share(function($app)
		{
			return new Templater($app['mustache']);
		});

    	$this->app['sire'] = $this->app->share(function($app)
		{
			return new Sire($app['mustache'], $app['templater']);
		});

        $this->commands(
            $this->app['sire']
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('sire');
    }

}