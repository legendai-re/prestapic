<?php

namespace PP\PropositionBundle\JsonModel;

/**
 * Description of jsonUserModel
 *
 * @author Olivier
 */
class JsonTagModel {
    
    public function __construct($id, $name, $url){
        $this->id = $id;
        $this->name = $name;
        $this->url = $url;

    }
    
    public $id;
    public $name;
    public $url;
}
