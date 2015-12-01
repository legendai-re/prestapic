<?php

namespace PP\DashboardBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonTagModel {
    
    public function __construct($id, $name){
        $this->id = $id;
        $this->name = $name;        
    }
    
    public $id;
    public $name;    
}
