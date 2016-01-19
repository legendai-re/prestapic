<?php

namespace PP\DashboardBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonReportReasonModel {
    
    public function __construct($id, $name, $type){
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
    }
    
    public $id;
    public $name;
    public $type;
}
