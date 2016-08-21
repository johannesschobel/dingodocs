<?php

namespace JohannesSchobel\DingoDocs;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use JohannesSchobel\DingoDocs\Commands\GenerateDocumentation;

class DingoDocsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $config = $this->app['config']['dingodocs'];

        $this->loadViewsFrom(__DIR__.'/../../resources/views/', 'dingodocs');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'dingodocs');

        $this->publishes([
            __DIR__.'/../../resources/lang'     => $this->resource_path('lang/vendor/dingodocs'),
            __DIR__.'/../../resources/assets'   => $this->resource_path('assets/vendor/dingodocs'),
            __DIR__.'/../../resources/views'    => $this->resource_path('views/vendor/dingodocs'),
        ], 'resources');

        $this->publishes([
            __DIR__.'/../../config/dingodocs.php'   => config_path('dingodocs.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->setupConfig();
        $this->setupStorage();

        // register the available commands
        $this->app['dingodocs.generate'] = $this->app->share(function () {
            return new GenerateDocumentation();
        });

        $this->commands([
            'dingodocs.generate',
        ]);
    }

    /**
     * Get the Configuration
     */
    private function setupConfig() {
        $this->mergeConfigFrom(realpath(__DIR__ . '/../../config/dingodocs.php'), 'dingodocs');
    }

    /**
     * Create the dingodocs folder in the storage in order to store the requests / responses
     */
    private function setupStorage() {
        Storage::disk(config('dingodocs.storage_disk'))->makeDirectory('dingodocs');
    }

    /**
     * Return a fully qualified path to a given file.
     *
     * @param string $path
     *
     * @return string
     */
    public function resource_path($path = '')
    {
        return app()->basePath().'/resources'.($path ? '/'.$path : $path);
    }
}
