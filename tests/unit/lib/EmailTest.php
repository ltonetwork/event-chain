<?php

/**
 * @covers Email
 */
class EmailTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Twig_Environment
     */
    protected $twig;
    
    /**
     * @var array
     */
    protected $options = [
        'From' => 'info@example.com',
        'FromName' => 'Example'
    ];
    
    protected function setUp()
    {
        $this->twig = $this->createMock(Twig_Environment::class);
    }

    /**
     * Test creating email
     */
    public function testCreate()
    {
        $email = new Email($this->twig, 'foo-name', $this->options);

        $this->assertSame($this->twig, $email->getTwig());        
        $this->assertAttributeEquals('foo-name.html.twig', 'template', $email);
        $this->assertAttributeEquals('info@example.com', 'From', $email);
        $this->assertAttributeEquals('info@example.com', 'Sender', $email);
        $this->assertAttributeEquals('Example', 'FromName', $email);
    }

    /**
     * Test 'getTemplate' method
     */
    public function testGetTemplate()
    {
        $email = new Email($this->twig, 'foo-name', $this->options);
        $this->assertEquals('foo-name.html.twig', $email->getTemplate());
    }

    /**
     * Test 'setSubject' method
     */
    public function testSetSubject()
    {
        $email = new Email($this->twig, 'foo-name', $this->options);
        $email->setSubject('Foo subject');

        $this->assertEquals('Foo subject', $email->Subject);   
    }
    
    /**
     * Test 'with' method
     */
    public function testWith()
    {        
        $email = new Email($this->twig, 'foo-name', $this->options);
        $tmpl = $this->createMock(Twig_Template::class);
        $text = 'Some rendered email text';
        
        $this->twig->expects($this->once())->method('loadTemplate')->with('foo-name.html.twig')->willReturn($tmpl);
        $tmpl->expects($this->once())->method('render')
            ->with($this->identicalTo(['email' => $email, 'foo' => 'bar']))
            ->willReturn($text);
        
        $email->with(['foo' => 'bar']);

        $this->assertAttributeContains('text/html', 'ContentType', $email);
        $this->assertAttributeContains($text, 'Body', $email);
    }

    /**
     * Provide data for testing 'sendTo' method
     *
     * @return array
     */
    public function sendToProvider()
    {
        return [
            ['to-test@example.com', null],
            ['to-test@example.com', 'To-test name'],
        ];
    }

    /**
     * Test 'sendTo' method
     *
     * @dataProvider sendToProvider
     */
    public function testSendTo($adress, $name)
    {
        $email = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->setMethods(['addAddress', 'send', 'clearAddresses'])
            ->getMock();           

         $email->expects($this->once())->method('addAddress')->with($adress, $name);
         $email->expects($this->once())->method('send');
         $email->expects($this->once())->method('clearAddresses');

        $name ?
            $email->sendTo($adress, $name) :
            $email->sendTo($adress);            
    }

    /**
     * Test 'mockSend' method
     */
    public function testMockSend()
    {
        $email = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->setMethods(['sendTo', 'with', 'tmpfileSend'])
            ->getMock();           

        $email->expects($this->never())->method('sendTo');
        $email->expects($this->never())->method('with');
        $email->expects($this->never())->method('tmpfileSend');

        $email->mockSend();
    }
}
