<?php

namespace PP\DashboardBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonRequestModel {
    
    public function __construct($id, $title, $request, $author, $reportTicketList, $reportNb, $createdDate){
        $this->id = $id;
        $this->title = $title;
        $this->request = $request;
        $this->author = $author;
        $this->reportTicketList = $reportTicketList;
        $this->reportNb = $reportNb;
        $this->createdDate = $createdDate;
    }
    
    public $id;
    public $title;
    public $request;
    public $author;
    public $reportTicketList;
    public $reportNb;
    public $createdDate;
    
}
