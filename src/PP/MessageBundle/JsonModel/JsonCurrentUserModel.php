<?php

namespace PP\MessageBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonCurrentUserModel {
    
    public function __construct($id, $name, $image, $threadList, $postMessageApiUrl, $getConversationApiUrl){
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
        $this->threadList = $threadList;        
        $this->postMessageApiUrl = $postMessageApiUrl;
        $this->getConversationApiUrl = $getConversationApiUrl;
    }
    
    public $id;
    public $name;
    public $image;
    public $threadList;    
    public $postMessageApiUrl;
    public $getConversationApiUrl;
    
}
