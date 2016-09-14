<?php

namespace Itctrust;

/**
 * This file is part of Itctrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Itctrust
 */

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory;
use Itctrust\ItctrustRegistersBladeDirectives;

class ItctrustServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        'Migration' => 'command.itctrust.migration',
        'MakeRole' => 'command.itctrust.make-role',
        'MakeMandate' => 'command.itctrust.make-mandate',
        'MakePermission' => 'command.itctrust.make-permission',
        'MakePermissionSet' => 'command.itctrust.make-permissionset',
        'AddItctrustUserTraitUse' => 'command.itctrust.add-trait',
        'Setup' => 'command.itctrust.setup',
        'MakeSeeder' => 'command.itctrust.seeder'
    ];

    /**
     * Bootstrap the application events.
     *
     * @param  Factory $view
     * @return void
     */
    public function boot(Factory $view)
    {
        // Register published configuration.
        $this->publishes([
            __DIR__.'/../config/config.php' => app()->basePath() . '/config/itctrust.php',
            __DIR__.'/../config/itctrust_seeder.php' => app()->basePath() . '/config/itctrust_seeder.php',
        ]);

        if (class_exists('\Blade')) {
            $this->registerBladeDirectives($view);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerItctrust();

        $this->registerCommands();

        $this->mergeConfig();
    }

    /**
     * Register the blade directives
     *
     * @param  Factory $view
     * @return void
     */
    private function registerBladeDirectives(Factory $view)
    {
        // Fetch Blade Compiler off of the View\Factory
        $bladeCompiler = $view->getEngineResolver()
                              ->resolve('blade')
                              ->getCompiler();

        $directivesRegistrator = new ItctrustRegistersBladeDirectives($bladeCompiler);
        $directivesRegistrator->handle($this->app->version());
    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    private function registerItctrust()
    {
        $this->app->bind('itctrust', function ($app) {
            return new Itctrust($app);
        });

        $this->app->alias('itctrust', 'Itctrust\Itctrust');
    }

    /**
     * Register the given commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        foreach (array_keys($this->commands) as $command) {
            $method = "register{$command}Command";

            call_user_func_array([$this, $method], []);
        }

        $this->commands(array_values($this->commands));
    }
    
    protected function registerMigrationCommand()
    {
        $this->app->singleton('command.itctrust.migration', function () {
            return new MigrationCommand();
        });
    }
    
    protected function registerMakeRoleCommand()
    {
        $this->app->singleton('command.itctrust.make-role', function ($app) {
            return new MakeRoleCommand($app['files']);
        });
    }

    protected function registerMakePermissionSetCommand()
    {
        $this->app->singleton('command.itctrust.make-permissionset', function ($app) {
            return new MakePermissionSetCommand($app['files']);
        });
    }

    protected function registerMakeMandateCommand()
    {
        $this->app->singleton('command.itctrust.make-mandate', function ($app) {
            return new MakeMandateCommand($app['files']);
        });
    }
    
    protected function registerMakePermissionCommand()
    {
        $this->app->singleton('command.itctrust.make-permission', function ($app) {
            return new MakePermissionCommand($app['files']);
        });
    }
    
    protected function registerAddItctrustUserTraitUseCommand()
    {
        $this->app->singleton('command.itctrust.add-trait', function () {
            return new AddItctrustUserTraitUseCommand();
        });
    }
    
    protected function registerSetupCommand()
    {
        $this->app->singleton('command.itctrust.setup', function () {
            return new SetupCommand();
        });
    }

    protected function registerMakeSeederCommand()
    {
        $this->app->singleton('command.itctrust.seeder', function () {
            return new MakeSeederCommand();
        });
    }

    /**
     * Merges user's and itctrust's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php',
            'itctrust'
        );
    }

    /**
     * Get the services provided.
     *
     * @return array
     */
    public function provides()
    {
        return array_values($this->commands);
    }
}
