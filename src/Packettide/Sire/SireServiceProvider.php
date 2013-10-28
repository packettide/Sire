<?php namespace Packettide\Sire;

use Illuminate\Support\ServiceProvider;
use Packettide\Sire\Generators\MigrationGenerator;
use Mustache_Engine as Mustache;

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
			return new Mustache();
		});

		$this->app['templater'] = $this->app->share(function($app)
		{
			return new Templater($app['mustache']);
		});

		$this->app['sire.generators.migration'] = $this->app->share(function($app)
		{
			return new MigrationGenerator();
		});

    	$this->app['sire'] = $this->app->share(function($app)
		{
			return new Sire($app['mustache'], $app['templater'], 
				$app['sire.generators.migration']);
		});

        $this->commands(
            'Packettide\Sire\SireCmd'
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