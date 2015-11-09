<?php

namespace PP\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * NotificationFollow
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\NotificationBundle\Entity\NotificationFollowRepository")
 */
class NotificationFollow
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
    * @ORM\ManyToOne(targetEntity="PP\UserBundle\Entity\User")     
    * @Assert\Valid()          
    */
    private $followYou;        
    
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
     * Set followYou
     *
     * @param \PP\UserBundle\Entity\User $followYou
     *
     * @return notificationFollow
     */
    public function setFollowYou(\PP\UserBundle\Entity\User $followYou = null)
    {
        $this->followYou = $followYou;

        return $this;
    }

    /**
     * Get followYou
     *
     * @return \PP\UserBundle\Entity\User
     */
    public function getFollowYou()
    {
        return $this->followYou;
    }


    /**
     * Set id
     *
     * @param integer $id
     *
     * @return NotificationFollow
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
     * @return NotificationFollow
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
