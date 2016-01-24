<?php

namespace PP\PropositionBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonIRPopupModel {
    
    public function __construct($id, $title, $author, $url, $category, $categoryUrl, $tagList, $createdDate){
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->url = $url;
        $this->category = $category;
        $this->categoryUrl = $categoryUrl;
        $this->tagList = $tagList;
        $this->createdDate = $createdDate;
    }
    
    public $id;
    public $title;    
    public $author;    
    public $url;
    public $category;
    public $categoryUrl;
    public $tagList;
    public $createdDate;    
    
}
