<?php

use Jasny\ApplicationEnv;
use LTO\Account;

/**
 * Controller that provides information about the service.
 */
class DefaultController extends Jasny\Controller
{
    /**
     * @var object
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
     * @param object         $appConfig  "config:app"
     * @param ApplicationEnv $env
     * @param Account        $node       "node.account"
     */
    public function __construct($appConfig, ApplicationEnv $env, Account $node)
    {
        $this->app = $appConfig;
        $this->env = (string)$env;
        $this->node = $node;
    }

    /**
     * Show API info
     */
    public function run()
    {
        $info = [
            'name' => $this->app->name ?? '',
            'version' => $this->app->version ?? '',
            'description' => $this->app->description ?? '',
            'env' => $this->env,
            'url' => defined('BASE_URL') ? BASE_URL : null,
            'signkey' => $this->node->getPublicSignKey()
        ];

        $this->output($info, 'json');
    }
}
