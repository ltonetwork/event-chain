<?php

use Jasny\View\Twig as TwigView;

/**
 * Render and send e-mail
 */
class Email extends PHPMailer
{
    /**
     * @var Twig_Environment
     */
    protected $twig;
    
    /**
     * @var string
     */
    protected $template;
    
    /**
     * @var string
     */
    protected $templateNs;
    
    
    /**
     * Class constructor
     * 
     * @param Twig_Environment $twig
     * @param string           $template
     * @param array            $options
     */
    public function __construct(Twig_Environment $twig, $template, array $options)
    {
        $this->twig = $twig;
        $this->template = $template . (pathinfo($template, PATHINFO_EXTENSION) === '' ? '.html.twig' : '');
        
        $this->setOptions($options);
        
        parent::__construct(true); // Enable exceptions
    }
    
    /**
     * Set the properties through options array
     * 
     * @param array $options
     */
    protected function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        
        if (empty($this->Sender)) {
            $this->Sender = $this->From;
        }
    }
    
    /**
     * Get Twig environment
     *
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }
    
    /**
     * Get the template name
     * 
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
    
    
    /**
     * Set mail subject
     * 
     * @param string $subject
     */
    public function setSubject($subject)
    {
       $this->Subject = $subject; 
    }
    
    /**
     * Render the e-mail message
     * 
     * @param array $context
     * @return $this
     */
    public function with(array $context)
    {
        $template = (isset($this->templateNs) ? "@{$this->templateNs}/" : '') . $this->template;
        $tmpl = $this->getTwig()->loadTemplate($template);
        
        $message = $tmpl->render(['email' => $this] + $context);
        $this->msgHTML($message);
        
        return $this;
    }
    
    /**
     * Send the email
     * 
     * @param string $emailAddress
     * @param string $name
     */
    public function sendTo($emailAddress = null, $name = null)
    {
        if (isset($emailAddress)) {
            $this->addAddress($emailAddress, $name);
        }

        try {
            $this->send();
        } finally {
            $this->clearAddresses();
        }
    }    
    
    /**
     * Send the e-mail, but not really.
     * 
     * @return true
     */
    public function mockSend()
    {
        return true;
    }
    
    /**
     * Save the email to the filesytem.
     * 
     * @return true
     */
    public function tmpfileSend()
    {
        $filename = 'mail-' . date('YmdHis') . '-' . pathinfo($this->getTemplate(), PATHINFO_FILENAME);
        
        return file_put_contents(sys_get_temp_dir() . "/$filename", $this->Body) > 0;
    }
}
