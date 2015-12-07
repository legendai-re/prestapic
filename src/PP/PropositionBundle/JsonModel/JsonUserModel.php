<?php

namespace PP\PropositionBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonUserModel {
    
    public function __construct($id, $name, $image, $url, $isAuthor){
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
        $this->url = $url;
        $this->isAuthor = $isAuthor;
    }
    
    public $id;
    public $name;
    public $image;
    public $url;
    public $isAuthor;
}
