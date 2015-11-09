<?php

namespace PP\MessageBundle\JsonModel;

class JsonMessageModel {
    
    public function __construct($threadId, $id, $content, $authorName, $authorImage, $messageFromUs, $date, $dateLight){
        $this->threadId = $threadId;
        $this->id = $id;
        $this->content = $content;
        $this->authorName = $authorName;
        $this->authorImage = $authorImage;
        $this->messageFromUs = $messageFromUs;
        $this->date = $date;
        $this->dateLight = $dateLight;                
    }
    
    public $threadId;
    public $id;
    public $content;
    public $authorName;
    public $authorImage;
    public $messageFromUs;
    public $date;
    public $dateLight;        
    
}