<?php

namespace PP\DashboardBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonAllReportsModel {
    
    public function __construct($imageRequestList, $propositionList, $userList){
        $this->imageRequestList = $imageRequestList;
        $this->propositionList = $propositionList;
        $this->userList = $userList;        
    }
    
    public $imageRequestList;
    public $propositionList;
    public $userList;  
}
