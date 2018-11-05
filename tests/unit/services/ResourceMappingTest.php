<?php

/**
 * @covers ResourceMapping
 */
class ResourceMappingTest extends \Codeception\Test\Unit
{
    use Jasny\TestHelper;
    
    public $mapping = [
        'lt:/colors/*' => 'http://main.example.com/colors/',
        'lt:/foos/*' => 'http://foos.example.com/things/',
        'lt:/bars/*' => 'http://example.com/bars/',
        'lt:/bars/*/done' => 'http://example.com/bars/$2/done'
    ];
    
    public function testHasUrlTrue()
    {
        $storage = new ResourceMapping($this->mapping);
        
        $this->assertTrue($storage->hasUrl('lt:/foos/123?v=4ZL83zt5'));
    }

    public function testHasUrlFalse()
    {
        $storage = new ResourceMapping($this->mapping);
        
        $this->assertFalse($storage->hasUrl('lt:/paws/777'));
    }
    
    public function testGetUrl()
    {
        $storage = new ResourceMapping($this->mapping);
        
        $url = $storage->getUrl('lt:/foos/123?v=4ZL83zt5');
        
        $this->assertEquals('http://foos.example.com/things/', $url);
    }
    
    public function testGetUrlParameter()
    {
        $storage = new ResourceMapping($this->mapping);
        
        $url = $storage->getUrl('lt:/bars/333/done');
        
        $this->assertEquals('http://example.com/bars/333/done', $url);
    }
    
    /**
     * @expectedException OutOfRangeException
     */
    public function testGetUrlNotFound()
    {
        $storage = new ResourceMapping($this->mapping);
        
        $storage->getUrl('lt:/paws/777');
    }
}
