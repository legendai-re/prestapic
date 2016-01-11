<?php

namespace PP\CommentBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonCommentModel {
    
    public function __construct($id, $content, $author, $createdDate){
        $this->id = $id;
        $this->content = $content;
        $this->author = $author;
        $this->createdDate = $createdDate;
    }
    
    public $id;
    public $content;
    public $author;
    public $createdDate;    
    
}
