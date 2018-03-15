<?php
namespace Helper;

use Symfony\Component\DomCrawler\Crawler;
use Codeception\Exception\ElementNotFound;
use Codeception\Exception\Fail;
        
class Functional extends \Codeception\Module
{
    /**
     * Get the container
     * 
     * @return \Psr\Container\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getModule('\Jasny\Codeception\Module')->container;
    }
    
    /**
     * Get email by template name
     * 
     * @param type $template
     * @return \Email
     * @throws Fail
     */
    protected function getEmailByTemplate($template)
    {
        $emailFactory = $this->getContainer()->get('email');
        
        foreach ($emailFactory->getEmails() as $email) {
            if ($email->getTemplate() === $template) {
                return $email;
            }
        }
        
        throw new Fail("Email '$template' has not been send");
    }

    /**
     * Check that an email is send
     * 
     * @param string       $template
     * @param string|array $contains   Assert that the email contains these strings
     */
    public function seeEmailIsSend($template, $contains = [])
    {
        $email = $this->getEmailByTemplate($template);
        $this->assertEmailContains($email, $contains);
    }
    
    /**
     * Assert that the email contains these strings
     * 
     * @param \Email       $email
     * @param string|array $contains
     */
    public function assertEmailContains(\Email $email, $contains)
    {
        foreach ((array)$contains as $needle) {
            $this->assertContains($needle, $email->Body);
        }
    }

    /**
     * Get a node from the email body
     * 
     * @param string $template
     * @param string $css       CSS selector
     * @throws ElementNotFound
     */
    protected function getNodeFromEmail($template, $css)
    {
        $email = $this->getEmailByTemplate($template);
        
        $crawler = new Crawler($email->Body);
        $node = $crawler->filter($css);
        
        if ($node->count() === 0) {
            throw new ElementNotFound($css, 'Element in email that matches CSS');
        }
        
        return $node;
    }
    
    /**
     * Grab text from an email
     * 
     * @param string $template
     * @param string $css       CSS selector
     * @return string
     * @throws ElementNotFound
     */
    public function grabTextFromEmail($template, $css)
    {
        $node = $this->getNodeFromEmail($template, $css);
        return $node->first()->text();
    }
    
    /**
     * Grab an attribute from an email
     * 
     * @param string $template
     * @param string $css        CSS selector
     * @param string $attribute
     * @return string
     * @throws ElementNotFound
     */
    public function grabAttributeFromEmail($template, $css, $attribute)
    {
        $node = $this->getNodeFromEmail($template, $css);
        return $node->attr($attribute);
    }
    
    /**
     * Login as user with given email
     *
     * @param string $email
     */
    public function amLoggedInAs($email)
    {
        $auth = $this->getContainer()->get('auth');
        $user = \User::fetch(['email' => $email]);

        if (!$user) {
            $this->fail("Unable to login as '$email': User not found");
        }
        
        $auth->setUser($user);
    }

    /**
     * Logout
     */
    public function amLoggedOut()
    {
        $this->getContainer()->get('auth')->logout();
    }
}
