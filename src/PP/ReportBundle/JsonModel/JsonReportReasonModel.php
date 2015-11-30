<?php

namespace PP\ReportBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonReportReasonModel {
    
    public function __construct($id, $name){
        $this->id = $id;
        $this->name = $name;       
    }
    
    public $id;
    public $name;    
}
