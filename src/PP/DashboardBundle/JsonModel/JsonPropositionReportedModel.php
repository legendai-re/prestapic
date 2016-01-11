<?php

namespace PP\DashboardBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonPropositionReportedModel {
    
    public function __construct($id, $title, $image, $author, $reportNb){
        $this->id = $id;
        $this->title = $title;
        $this->image = $image;
        $this->author = $author;
        $this->reportNb = $reportNb;
    }
    
    public $id;
    public $title;
    public $image;
    public $author;
    public $reportNb;
    
}
