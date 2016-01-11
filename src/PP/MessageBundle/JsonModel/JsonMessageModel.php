<?php

namespace PP\MessageBundle\JsonModel;

class JsonMessageModel {
    
    public function __construct($threadId, $id, $content, $author, $messageFromUs, $date, $dateLight){
        $this->threadId = $threadId;
        $this->id = $id;
        $this->content = $content;
        $this->author = $author;
        $this->messageFromUs = $messageFromUs;
        $this->date = $date;
        $this->dateLight = $dateLight;                
    }
    
    public $threadId;
    public $id;
    public $content;
    public $author;
    public $messageFromUs;
    public $date;
    public $dateLight;        
    
}