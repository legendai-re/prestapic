<?php
// src/MyProject/MyBundle/Entity/Thread.php

namespace PP\ThreadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Entity\Thread as BaseThread;

/**
 * @ORM\Entity
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Thread extends BaseThread
{
    /**
     * @var string $id
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $id;
    
    public function setId($id){
        $this->id = $id;
        return $this;
    }
    
     public function getId(){       
        return $this->id;
    }
}
