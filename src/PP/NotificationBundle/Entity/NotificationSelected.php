<?php

namespace PP\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * NotificationSelected
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\NotificationBundle\Entity\NotificationSelectedRepository")
 */
class NotificationSelected
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
    * @ORM\ManyToOne(targetEntity="PP\RequestBundle\Entity\ImageRequest")     
    * @Assert\Valid()          
    */
    private $imageRequest;        
    
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
     * Set id
     *
     * @param integer $id
     *
     * @return NotificationSelected
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set imageRequest
     *
     * @param \PP\RequestBundle\Entity\ImageRequest $imageRequest
     *
     * @return NotificationSelected
     */
    public function setImageRequest(\PP\RequestBundle\Entity\ImageRequest $imageRequest = null)
    {
        $this->imageRequest = $imageRequest;

        return $this;
    }

    /**
     * Get imageRequest
     *
     * @return \PP\RequestBundle\Entity\ImageRequest
     */
    public function getImageRequest()
    {
        return $this->imageRequest;
    }

    /**
     * Set notificationBase
     *
     * @param \PP\NotificationBundle\Entity\Notification $notificationBase
     *
     * @return NotificationSelected
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
