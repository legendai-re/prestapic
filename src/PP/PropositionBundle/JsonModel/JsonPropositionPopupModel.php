<?php

namespace PP\PropositionBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonPropositionPopupModel {
    
    public function __construct($connected, $id, $title, $image, $author, $upvoteNb, $selected, $imageRequest, $canUpvote, $createdDate, $timestamp){
        $this->connected = $connected;
        $this->id = $id;
        $this->title = $title;
        $this->image = $image;
        $this->author = $author;
        $this->upvoteNb = $upvoteNb;
        $this->selected = $selected;
        $this->imageRequest = $imageRequest;
        $this->canUpvote = $canUpvote;
        $this->createdDate = $createdDate;
        $this->timestamp = $timestamp;
    }
    
    public $connected;
    public $id;
    public $title;
    public $image;
    public $author;
    public $upvoteNb;
    public $selected;
    public $imageRequest;
    public $canUpvote;
    public $createdDate;
    public $timestamp;
    
}
