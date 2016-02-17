<?php

namespace Recca0120\Terminal;

use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * namespace.
     *
     * @var string
     */
    protected $namespace = 'Recca0120\Terminal\Http\Controllers';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request, ConfigContract $config)
    {
        if ($this->app->runningInConsole() === true) {
            $this->handlePublishes();
        }

        if ($config->get('app.debug') === true  ||
            in_array(
                $request->getClientIp(),
                $config->get('terminal.whitelists', [])
            ) === true
        ) {
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'terminal');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/terminal.php', 'terminal');
        $this->app->call([$this, 'registerRoutes']);
    }

    /**
     * register routes.
     *
     * @param Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function registerRoutes(Router $router)
    {
        if ($this->app->routesAreCached() === false) {
            $prefix = 'terminal';
            $router->group([
                'as'         => 'terminal::',
                'namespace'  => $this->namespace,
                'prefix'     => $prefix,
            ], function () {
                require __DIR__.'/Http/routes.php';
            });
        }
    }

    /**
     * handle publishes.
     *
     * @return void
     */
    protected function handlePublishes()
    {
        $this->publishes([
            __DIR__.'/../config/terminal.php' => config_path('terminal.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/terminal'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/terminal'),
        ], 'public');
    }
}
