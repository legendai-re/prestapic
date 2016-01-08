<?php

namespace PP\MessageBundle\JsonModel;

class JsonMessageThreadModel {
    
   /* public function __construct($threadId, $userName, $userImage, $userId, $message, $messageFromUs, $date, $dateLight, $haveNewMessage){
        $this->threadId = $threadId;
        $this->userName = $userName;
        $this->userImage = $userImage;
        $this->userId = $userId;
        $this->message = $message;
        $this->messageFromUs = $messageFromUs;
        $this->date = $date;
        $this->dateLight = $dateLight;    
        $this->haveNewMessage = $haveNewMessage;
    }
    
    public $threadId;
    public $userName;
    public $userImage;
    public $userId;
    public $message;
    public $messageFromUs;
    public $date;
    public $dateLight;
    public $haveNewMessage;*/
    
    public function __construct($id, $target, $lastMessage, $messageList, $haveNewMessage, $page, $createdDate){
        $this->id = $id;
        $this->target = $target;
        $this->lastMessage = $lastMessage;
        $this->messageList = $messageList;
        $this->haveNewMessage = $haveNewMessage;
        $this->page = $page;
        $this->createdDate = $createdDate;
    }
    
    public $id;    
    public $target;
    public $lastMessage;
    public $messageList;
    public $haveNewMessage;
    public $page;
    public $createdDate;    
    
}