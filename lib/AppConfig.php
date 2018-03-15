<?php

use Jasny\Config;
use Jasny\DotKey;

/**
 * Application config
 *
 * Get's app's name, version and description from composer.json.
 * 
 * Uses the application environment to load environment specific configuration files.
 * If the configuration exists in DynamoDB, it will override the configuration from file.
 * Eg when `APPLICATION_ENV='dev.foo.bar'`, loads
 *  - settings.yml
 *  - settings.dev.yml
 *  - settings.dev.foo.yml
 *  - settings.dev.foo.bar.yml
 *  - settings.local.yml
 *  - settings.dev.local.yml
 *  - settings.dev.foo.local.yml
 *  - settings.dev.foo.bar.local.yml
 *
 * @codeCoverageIgnore
 */
class AppConfig extends Config
{
    /**
     * Load the application settings.
     * 
     * @param string $env      Application environment
     * @param array  $options
     */
    public function load($env, $options = [])
    {
        $this->loadFromComposerJson();
        $this->loadSettings($env);
        $this->loadSettings($env, '.local');
        
        $this->addEnvironmentVariables();
        $this->addAppVersion();
    }

    /**
     * Load app settings from composer.json
     */
    protected function loadFromComposerJson()
    {
        if (!file_exists('composer.json')) {
            return;
        }
        
        $this->app = (object)[];
        $app = new Config('composer.json');

        foreach (['name', 'version', 'description'] as $prop) {
            if (isset($app->$prop)) {
                $this->app->$prop = $app->$prop;
            }
        }
    }
    
    /**
     * Load configuration settings.
     * 
     * @param string $env
     * @param string $suffix
     */
    protected function loadSettings($env, $suffix = null)
    {
        if (file_exists("config/settings{$suffix}.yml")) {
            parent::load("config/settings{$suffix}.yml");
        }
        
        $parts = explode('.', $env);
        
        for ($i = 1, $m = count($parts); $i <= $m; $i++) {
            $file = "config/settings." . join('.', array_slice($parts, 0, $i)) . "{$suffix}.yml";
            
            if (file_exists($file)) {
                parent::load($file);
            }
        }
    }

    /**
     * Add settings from environment variables
     */
    protected function addEnvironmentVariables()
    {
        if (!isset($this->environment_variables)) return;
        
        foreach ($this->environment_variables as $var => $key) {
            if (getenv($var) !== false) {
                DotKey::on($this)->put($key, getenv($var));
            }
        }
    }
    
    /**
     * Add app version based on environment (git commit or ctime)
     * @codeCoverageIgnore
     */
    protected function addAppVersion()
    {
        if (!isset($this->app)) {
            $this->app = (object)[];
        }
        
        if (!isset($this->app->version)) {
            $this->app->version = is_dir('.git') ? trim(`git rev-parse HEAD`) : date('YmdHis', filectime(getcwd()));
        }
    }
}
