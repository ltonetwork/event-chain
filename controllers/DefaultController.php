<?php

use Psr\Container\ContainerInterface;
use Jasny\Config;

/**
 * Default controller
 */
class DefaultController extends Jasny\Controller
{
    use Jasny\Controller\RouteAction;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string  application environment
     */
    protected $env;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get('config');
        $this->env = $container->get('app.env');
    }

    /**
     * Show API info
     */
    public function infoAction()
    {
        $info = [
            'name' => $this->config->app->name ?? '',
            'version' => $this->config->app->version ?? '',
            'description' => $this->config->app->description ?? '',
            'env' => $this->env,
            'url' => defined('BASE_URL') ? BASE_URL : null
        ];

        $this->output($info, 'json');
    }
}
