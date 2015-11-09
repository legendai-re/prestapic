<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


namespace PP\NotificationBundle\JsonNotificationModel;

use PP\NotificationBundle\Constant\NotificationType;

/**
 * Description of JsonNotificationSelected
 *
 * @author Olivier
 */
class JsonNotification {
    
    public function __construct($type, $isViewed, $isClicked, $date, $dateLight, $redirectUrl, $setClickedUrl, $authorName, $authorImg, $targetTitle){
        $this->type = $type;
        $this->isViewed = $isViewed;
        $this->isClicked = $isClicked;
        $this->redirectUrl = $redirectUrl;
        $this->date = $date->format('Y-m-d H:i:s');
        $this->dateLight = $dateLight;
        $this->setClickedUrl =$setClickedUrl;
        $this->authorName = $authorName;
        $this->authorImg = $authorImg;
        $this->targetTitle = $targetTitle;
    }
    
    public $type;
    public $date;
    public $dateLight;    
    public $isViewed;
    public $isClicked;
    public $setClickedUrl;
    
    public $redirectUrl; 
    public $authorName;
    public $authorImg;
    public $targetTitle;               
}
