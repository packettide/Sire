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
                $this->commands(
                        '\Packettide\Sire\SireCmd'
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