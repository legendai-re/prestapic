<?php

namespace PP\MessageBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonUserModel {
    
    public function __construct($id, $name, $image, $url){
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
        $this->url = $url;

    }
    
    public $id;
    public $name;
    public $image;
    public $url;
}
