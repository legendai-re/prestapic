<?php

namespace PP\MessageBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonUserModel {
    
    public function __construct($id, $name, $image){
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;        
    }
    
    public $id;
    public $name;
    public $image;    
    
}
