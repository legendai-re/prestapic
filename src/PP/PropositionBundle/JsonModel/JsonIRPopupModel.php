<?php

namespace PP\PropositionBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonIRPopupModel {
    
    public function __construct($id, $title, $author, $url, $createdDate){
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->url = $url;
        $this->createdDate = $createdDate;
    }
    
    public $id;
    public $title;    
    public $author;    
    public $url;
    public $createdDate;    
    
}
