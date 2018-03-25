<?php

/**
 * @covers Comment
 */
class CommentTest extends \Codeception\Test\Unit
{
    public function testCast()
    {
        $content = [
            'uri' => 'http://example.com/storage/image.png',
            'hash' => '4q1YgkX8217mGpsHqfCZ2CtAy51yn54wgxebb554Vgs5'
        ];
        
        $comment = new Comment();
        
        $comment->setValues([
            'content' => $content,
            'content_type' => 'image/png'
        ]);
        
        $this->assertEquals((object)$content, $comment->content);
    }
    
    /**
     * @covers ResourceBase::setIdentity
     */
    public function testSetIdentity()
    {
        $identity = $this->createMock(Identity::class);
        
        $comment = new Comment();
        
        $ret = $comment->setIdentity($identity);
        
        $this->assertSame($ret, $comment);
        $this->assertAttributeSame($identity, 'identity', $comment);
    }
}
