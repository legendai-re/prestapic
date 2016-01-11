<?php

namespace PP\NotificationBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationNewProposition
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\NotificationBundle\Entity\NotificationNewPropositionRepository")
 */
class NotificationNewProposition
{
    public function __construct($id)
    {
        $this->id = $id;
    }
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id    
     */
    private $id;

    
    /**    
    * @ORM\ManyToOne(targetEntity="PP\PropositionBundle\Entity\Proposition")     
    * @Assert\Valid()
    */
    private $proposition;
    
    /**    
    * @ORM\OneToOne(targetEntity="PP\NotificationBundle\Entity\Notification", cascade={"persist", "remove"} )     
    * @Assert\Valid()          
    */
    private $notificationBase;
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set proposition
     *
     * @param \PP\PropositionBundle\Entity\Proposition $proposition
     *
     * @return NotificationNewProposition
     */
    public function setProposition(\PP\PropositionBundle\Entity\Proposition $proposition = null)
    {
        $this->proposition = $proposition;

        return $this;
    }

    /**
     * Get proposition
     *
     * @return \PP\PropositionBundle\Entity\Proposition
     */
    public function getProposition()
    {
        return $this->proposition;
    }


    /**
     * Set id
     *
     * @param integer $id
     *
     * @return NotificationNewProposition
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set notificationBase
     *
     * @param \PP\NotificationBundle\Entity\Notification $notificationBase
     *
     * @return NotificationNewProposition
     */
    public function setNotificationBase(\PP\NotificationBundle\Entity\Notification $notificationBase = null)
    {        
        $this->notificationBase = $notificationBase;

        return $this;
    }

    /**
     * Get notificationBase
     *
     * @return \PP\NotificationBundle\Entity\Notification
     */
    public function getNotificationBase()
    {
        return $this->notificationBase;
    }
}
