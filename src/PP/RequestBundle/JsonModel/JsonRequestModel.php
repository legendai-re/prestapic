<?php

namespace PP\RequestBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonRequestModel {
    
    public function __construct($id, $title, $request, $author, $createdDate){
        $this->id = $id;
        $this->title = $title;
        $this->request = $request;
        $this->author = $author;
        $this->createdDate = $createdDate;
    }
    
    public $id;
    public $title;
    public $request;
    public $author;
    public $createdDate;
    
}
