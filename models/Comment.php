<?php

/**
 * A comment
 */
class Comment extends MongoSubDocument implements Resource
{
    use ResourceBase;
    
    /**
     * The person that created the comment
     * @var Idenity
     */
    public $identity;
    
    /**
     * Date/time the comment was created
     * @var DateTime
     */
    public $timestamp;
    
    /**
     * The media type of the content
     * @var string
     */
    public $content_media_type = 'text/plain';
    
    /**
     * Method that was used to encode the content
     * @var string
     */
    public $content_encoding;
    
    /**
     * The full content or a link to the content
     * @var string|object
     */
    public $content;
    
    
    /**
     * @inheritDoc
     */
    public function cast()
    {
        if (is_array($this->content)) {
            $this->content = (object)$this->content;
        }
        
        return parent::cast();
    }
}
