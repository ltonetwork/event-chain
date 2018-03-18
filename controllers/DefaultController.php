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
            'env' => App::env()
        ];

        $this->output($info, 'json');
    }
}
