<?php

/**
 * @covers EmailFactory
 */
class EmailFactoryTest extends \PHPUnit_Framework_TestCase
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
     * Test creating factory
     */
    public function testCreate()
    {
        $factory = new EmailFactory($this->twig, (object)$this->options);
        
        $this->assertSame($this->twig, $factory->getTwig());
        $this->assertEquals($this->options, $factory->getOptions());        
    }

    /**
     * Test creating email
     */
    public function testCreateEmail()
    {
        $factory = new EmailFactory($this->twig, $this->options);
        $email = $factory->create('foo-name');
        
        $this->assertInstanceOf(Email::class, $email);
        $this->assertSame($this->twig, $email->getTwig());
        
        $this->assertAttributeEquals('foo-name.html.twig', 'template', $email);
        $this->assertAttributeEquals('info@example.com', 'From', $email);
        $this->assertAttributeEquals('Example', 'FromName', $email);

        $this->expectException(BadMethodCallException::class);
        $factory->getEmails();
    }

    /**
     * Test creating email with 'keep' option
     */
    public function testCreateEmailKeep()
    {
        $factory = new EmailFactory($this->twig, $this->options + ['keep' => true]);

        $email1 = $factory->create('foo-name');
        $this->assertCount(1, $factory->getEmails());

        $email2 = $factory->create('foo-name');
        $this->assertCount(2, $factory->getEmails());

        $email3 = $factory->create('bar-name');
        $this->assertCount(3, $factory->getEmails());

        $this->assertEquals($email1, $factory->getEmails()[0]);
        $this->assertEquals($email2, $factory->getEmails()[1]);
        $this->assertEquals($email3, $factory->getEmails()[2]);
    }

    /**
     * Test 'invoke' method
     */
    public function testInvoke()
    {
        $email = $this->createMock(Email::class);           
        $factory = $this->getMockBuilder(EmailFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();                 

        $factory->expects($this->once())->method('create')->with('foo-name')->willReturn($email);

        $result = $factory('foo-name');
        $this->assertEquals($email, $result);
    }
}
