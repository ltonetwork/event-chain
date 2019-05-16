<?php /** @noinspection PhpVariableVariableInspection */ declare(strict_types=1);

use Jasny\Config;
use Jasny\DotKey;
use Jasny\ApplicationEnv;

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
     * @param mixed $source    Application environment
     * @param array $options
     */
    public function load($source, array $options = []): Config
    {
        if (!$source instanceof ApplicationEnv) {
            return parent::load($source, $options);
        }

        $this->loadFromComposerJson();
        $this->loadSettings($source);
        $this->loadSettings($source, '.local');
        $this->loadSettings($source, '', 'config/local');

        $this->addEnvironmentVariables();
        $this->addAppVersion();

        $this->addDBPrefix();

        return $this;
    }

    /**
     * Load app settings from composer.json
     */
    protected function loadFromComposerJson(): void
    {
        if (!file_exists('composer.json')) {
            return;
        }
        
        $this->app = (object)[];
        $app = (new Config)->load('composer.json');

        foreach (['name', 'version', 'description'] as $prop) {
            if (isset($app->$prop)) {
                $this->app->$prop = $app->$prop;
            }
        }
    }
    
    /**
     * Load configuration settings.
     *
     * @param ApplicationEnv $env
     * @param string         $suffix
     */
    protected function loadSettings($env, $suffix = '', $path = 'config'): void
    {
        if (file_exists("$path/settings{$suffix}.yml")) {
            parent::load("$path/settings{$suffix}.yml");
        }
        
        foreach ($env->getLevels() as $level) {
            $level = trim($level);
            $file = "$path/settings.$level{$suffix}.yml";
            
            if (file_exists($file)) {
                parent::load($file);
            }
        }
    }

    /**
     * Add settings from environment variables
     */
    protected function addEnvironmentVariables(): void
    {
        if (!isset($this->environment_variables)) {
            return;
        }
        
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
    protected function addAppVersion(): void
    {
        if (!isset($this->app)) {
            $this->app = (object)[];
        }
        
        if (isset($this->app->version)) {
            // nothing
        } elseif (is_dir('.git')) {
            $this->app->version = trim(`git rev-parse HEAD`);
        } else {
            $ctime = filectime(getcwd() ?: __DIR__);
            $this->app->version = is_int($ctime) ? date('YmdHis', $ctime) : null;
        }
    }
    
    /**
     * Add prefix in database name
     */
    protected function addDBPrefix(): void
    {
        if (!isset($this->db->default)) {
            return;
        }

        /** @var stdClass $settings */
        $settings = $this->db->default;

        if (isset($settings->prefix)) {
            $settings->database = $settings->prefix . $settings->database;
            unset($settings->prefix);
        }
    }
}
