<?php

namespace Bizzle\Plugin;

use Illuminate\Support\ServiceProvider;

/**
* This is the Plugin Service Provider
* @package Bizzle/Plugin
* @author jayson_julian@commude.ph
*/
class PluginServiceProvider extends ServiceProvider
{
     /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
    *
    * List of Commands Included in this Package
    *
    */
    protected $commands = [
        'Bizzle\Plugin\PluginCreateCommand',
    ];

    /**
    *
    * Gets the Service Provided by the Provider
    *
    */
    public function provides()
    {
        return ['bizzle'];
    }

    /**
    *
    * 
    *
    */
    public function boot()
    {
        // Nothing to Boot
    }

    /**
    *
    * Registers the commands
    *
    */
    public function register()
    {
        $this->commands($this->commands);
    }
}
