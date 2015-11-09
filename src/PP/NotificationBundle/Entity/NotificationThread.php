<?php

namespace PP\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

use PP\NotificationBundle\Constant\NotificationType;
/**
 * NotificationThread
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\NotificationBundle\Entity\NotificationThreadRepository")
 */
class NotificationThread
{
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lastNotificationDate = new \Datetime();
    }
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;
    
    /**
    * @Gedmo\Slug(fields={"name"})
    * @ORM\Column(length=128, unique=true)
    */
     private $slug;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastNotificationDate", type="date")
     */
    private $lastNotificationDate;
    
    /**    
    * @ORM\OneToOne(targetEntity="PP\UserBundle\Entity\User")
    * @Assert\Valid()          
    */
    private $user;
        
    /**    
     * @ORM\OneToMany(targetEntity="PP\NotificationBundle\Entity\Notification", mappedBy="notificationThread", cascade={"persist", "remove"})     
     * @Assert\Valid()
     */
    private $notifications;
    
    
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
     * Set name
     *
     * @param string $name
     *
     * @return NotificationThread
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set lastNotificationDate
     *
     * @param \DateTime $lastNotificationDate
     *
     * @return NotificationThread
     */
    public function setLastNotificationDate($lastNotificationDate)
    {
        $this->lastNotificationDate = $lastNotificationDate;

        return $this;
    }

    /**
     * Get lastNotificationDate
     *
     * @return \DateTime
     */
    public function getLastNotificationDate()
    {
        return $this->lastNotificationDate;
    }
    
    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return NotificationThread
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }
  



    /**
     * Add notifications
     *
     * @param \PP\NotificationBundle\Entity\Notification $notification
     *
     * @return NotificationThread
     */
    public function addNotification(\PP\NotificationBundle\Entity\Notification $notification)
    {        
        
        $notification->setNotificationThread($this);
        $this->notifications[] = $notification;

        return $this;
    }

    /**
     * Remove notification
     *
     * @param \PP\NotificationBundle\Entity\Notification $notification
     */
    public function removeNotification(\PP\NotificationBundle\Entity\Notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Set user
     *
     * @param \PP\UserBundle\Entity\User $user
     *
     * @return NotificationThread
     */
    public function setUser(\PP\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \PP\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
    
}
