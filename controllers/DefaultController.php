<?php

/**
 * Default controller
 */
class DefaultController extends Jasny\Controller
{
    use Jasny\Controller\RouteAction;

    /**
     * Show API info
     */
    public function infoAction()
    {
        $info = [
            'name' => App::name(),
            'version' => App::version(),
            'description' => App::description(),
            'env' => App::env(),
            'url' => defined('BASE_URL') ? BASE_URL : null
        ];

        $this->output($info, 'json');
    }
}
