<?php

namespace PP\DashboardBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonUserReportedModel {
    
    public function __construct($id, $name, $image, $coverImage, $reportNb){
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
        $this->coverImage = $coverImage;
        $this->reportNb = $reportNb;
    }
    
    public $id;
    public $name;
    public $image;
    public $coverImage;
    public $reportNb;
    
}
