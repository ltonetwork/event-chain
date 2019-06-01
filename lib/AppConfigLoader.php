<?php

declare(strict_types=1);

use Improved\IteratorPipeline\Pipeline;
use Jasny\Config;
use Jasny\Config\LoaderInterface;
use Jasny\Config\Loader\DelegateLoader;
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
class AppConfigLoader implements LoaderInterface
{
    /**
     * @var DelegateLoader
     */
    protected $loader;

    /**
     * AppConfigLoader constructor.
     *
     * @param DelegateLoader $loader
     */
    public function __construct(DelegateLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Load the application settings.
     * 
     * @param ApplicationEnv|string $env      Application environment
     * @param array                 $options
     */
    public function load($env, array $options = []): Config
    {
        $config = new Config();

        $config->merge(
            $this->loadFromComposerJson(),
            ...$this->loadSettings($env),
            ...$this->loadSettings($env, '.local'),
            ...$this->loadSettings($env, '', 'config/local')
        );

        $this->addFallbackAppVersion($config);
        $this->addDBPrefix($config);

        return $config;
    }

    /**
     * Load app settings from composer.json
     */
    protected function loadFromComposerJson(): stdClass
    {
        if (!file_exists('composer.json')) {
            return (object)[];
        }
        
        $composer = json_decode(file_get_contents('composer.json'), true);
        $app = (object)array_only($composer, ['name', 'version', 'description']);

        return (object)['app' => $app];
    }
    
    /**
     * Load configuration settings.
     * 
     * @param ApplicationEnv $env
     * @param string         $suffix
     * @param string         $path
     * @return Config[]
     */
    protected function loadSettings(ApplicationEnv $env, $suffix = '', $path = 'config'): array
    {
        $files = $env->getLevels(0, null, function($level) use ($suffix, $path) {
            return "$path/settings" . ($level === '' ? '' : '.' . $level) . "{$suffix}.yml";
        });

        return Pipeline::with($files)
            ->filter(function($file) {
                return file_exists($file);
            })
            ->map(function(string $file) {
                return $this->loader->load($file);
            })
            ->toArray();
    }

    /**
     * Add app version based on project dir ctime
     * @codeCoverageIgnore
     *
     * @param Config $config
     */
    protected function addFallbackAppVersion(Config $config): void
    {
        if (!isset($config->app)) {
            $config->app = (object)[];
        }
        
        if (!isset($config->app->version)) {
            $config->app->version = date('YmdHis', filectime(getcwd()));
        }
    }
    
    /**
     * Add prefix in database name
     *
     * @param Config $config
     */
    protected function addDBPrefix(Config $config): void
    {
        if (!isset($config->db)) {
            return;
        }

        foreach ($config->db as $db) {
            if (!isset($db->prefix)) {
                continue;
            }

            $db->database = $db->prefix . $db->database;
            unset($db->prefix);
        }
    }
}
