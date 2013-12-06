<?php namespace Packettide\Sire;

use Illuminate\Support\ServiceProvider;
use Packettide\Sire\Generators\MigrationGenerator;
use Packettide\Sire\Generators\ModelGenerator;
use Packettide\Sire\Generators\ControllerGenerator;
use Packettide\Sire\Generators\ViewGenerator;
use Packettide\Sire\Generators\SeedGenerator;
use Symfony\Component\Finder\Finder;
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
     * Register and inject dependencies into the Sire class
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
            $finder = new Finder();
            return new MigrationGenerator($finder, $app['migrator']);
        });

		$this->app['sire.generators.seed'] = $this->app->share(function($app)
		{
            $finder = new Finder();
			return new SeedGenerator();
		});

		$this->app['sire.generators.model'] = $this->app->share(function($app)
		{
			return new ModelGenerator();
		});

        $this->app['sire.generators.controller'] = $this->app->share(function($app)
        {
            return new ControllerGenerator();
        });


		$this->app['sire.generators.view'] = $this->app->share(function($app)
		{
			return new ViewGenerator();
		});

    	$this->app['sire'] = $this->app->share(function($app)
		{
			return new Sire($app['mustache'], $app['templater'],
				$app['sire.generators.migration'],
				$app['sire.generators.model'],
                $app['sire.generators.controller'],
                $app['sire.generators.view'],
				$app['sire.generators.seed']);
		});

        $this->app['sire.cmd'] = $this->app->share(function($app)
        {
            return new SireCmd($app['sire']);
        });

        $this->commands(
            'sire.cmd'
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