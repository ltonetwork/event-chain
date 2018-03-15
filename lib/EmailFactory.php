<?php

/**
 * Factory for creating an email
 */
class EmailFactory
{
    /**
     * @var Twig_Environment
     */
    protected $twig;
    
    /**
     * @var array
     */
    protected $options;
    
    /**
     * Created emails
     * @var Email[]
     */
    protected $emails = [];
    
    
    /**
     * Class constructor
     * 
     * @param Twig_Environment $twig
     * @param array|stdClass   $options
     */
    public function __construct(Twig_Environment $twig, $options)
    {
        $this->twig = $twig;
        $this->options = arrayify($options);
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
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
     * Create a new email
     * 
     * @param string $template
     * @return \Email
     */
    public function create($template)
    {
        $email = new Email($this->twig, $template, $this->options);
        
        if (!empty($this->options['keep'])) {
            $this->emails[] = $email;
        }
        
        return $email;
    }
    
    /**
     * Return the created emails.
     * 
     * @return Email[]
     * @throws BadMethodCallException if the `keep` option isn't enabled
     */
    public function getEmails()
    {
        if (!isset($this->options['keep'])) {
            throw new BadMethodCallException("This function only works with the `keep` option");
        }
        
        return $this->emails;
    }
    
    /**
     * Invoke the factory
     * 
     * @param string $template
     * @return \Email
     */
    public function __invoke($template)
    {
        return $this->create($template);
    }
}
