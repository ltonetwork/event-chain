<?php

use Jasny\Config;
use Jasny\ApplicationEnv;
use LTO\Account;

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
     * @var Account
     */
    protected $node;


    /**
     * @param Config         $config  "config"
     * @param ApplicationEnv $env
     * @param Account        $node    "node.account"
     */
    public function __construct(Config $config, ApplicationEnv $env, Account $node)
    {
        $this->config = $config;
        $this->env = (string)$env;
        $this->node = $node;
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
            'url' => defined('BASE_URL') ? BASE_URL : null,
            'signkey' => $this->node->getPublicSignKey()
        ];

        $this->output($info, 'json');
    }
}
