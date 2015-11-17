<?php

namespace PP\MessageBundle\JsonModel;

class JsonMessageThreadModel {
    
    public function __construct($threadId, $userName, $userImage, $userId, $message, $messageFromUs, $date, $dateLight, $haveNewMessage){
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
    public $haveNewMessage;
    
}