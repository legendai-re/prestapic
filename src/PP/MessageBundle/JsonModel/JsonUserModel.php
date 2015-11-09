<?php

namespace PP\MessageBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonUserModel {
    
    public function __construct($id, $name, $image, $getThreadApiUrl){
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
        $this->getThreadApiUrl = $getThreadApiUrl;
    }
    
    public $id;
    public $name;
    public $image;
    public $getThreadApiUrl;
    
}
