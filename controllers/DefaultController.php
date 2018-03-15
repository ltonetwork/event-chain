<?php

/**
 * The default controller
 */
class DefaultController extends BaseController
{
    /**
     * Default action for loading base site url
     */
    public function indexAction()
    {
        $this->view('index');
    }
    
    /**
     * Show static page
     * 
     * @param string $page
     */
    public function pageAction($page)
    {
        $this->view("pages/" . basename($page));
    }
}
