<?php

namespace PP\CommentBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonCommentThreadModel {
    
    public function __construct($id, $comments, $currentUser, $commentNb, $createdDate){
        $this->id = $id;
        $this->comments = $comments;
        $this->currentUser = $currentUser;
        $this->commentNb = $commentNb;
        $this->createdDate = $createdDate;
    }
    
    public $id;
    public $comments;
    public $currentUser;
    public $commentNb;
    public $createdDate;
    
}
