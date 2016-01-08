<?php

namespace PP\DashboardBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonCategoryModel {
    
    public function __construct($id, $name){
        $this->id = $id;
        $this->name = $name;        
    }
    
    public $id;
    public $name;    
}
