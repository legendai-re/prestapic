<?php

namespace PP\ReportBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonReportTicketModel {
    
    public function __construct($id, $type, $targetId, $reason, $details, $author, $createdDate){
        $this->id = $id;
        $this->type = $type;
        $this->targetId = $targetId;
        $this->reason = $reason;
        $this->details = $details;
        $this->author = $author;
        $this->createdDate = $createdDate;
    }
    
    public $id;
    public $type;
    public $targetId;
    public $reason;
    public $details;
    public $author;
    public $createdDate;    
}
