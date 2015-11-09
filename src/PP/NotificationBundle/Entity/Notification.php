<?php

namespace PP\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Notification
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\NotificationBundle\Entity\NotificationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Notification
{
    
    public function __construct($notificationType)
    {
        $this->notificationType = $notificationType;
        $this->createDate = new \DateTime();
        $this->isViewed = false;
        $this->isClicked = false;
        $this->notificationPatternId = 0;
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
     * @var \DateTime
     *
     * @ORM\Column(name="createDate", type="datetime")
     */
    private $createDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isViewed", type="boolean")
     */
    private $isViewed;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="isClicked", type="boolean")
     */
    private $isClicked;

    /**
     * @var integer
     *
     * @ORM\Column(name="notificationType", type="integer")
     */
    private $notificationType;
    
    
    /**    
     * @ORM\ManyToOne(targetEntity="PP\NotificationBundle\Entity\NotificationThread", inversedBy="notifications")
     * @Assert\Valid()          
     */
    private $notificationThread;
    
    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return Notification
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set isViewed
     *
     * @param boolean $isViewed
     *
     * @return Notification
     */
    public function setIsViewed($isViewed)
    {
        $this->isViewed = $isViewed;

        return $this;
    }

    /**
     * Get isViewed
     *
     * @return boolean
     */
    public function getIsViewed()
    {
        return $this->isViewed;
    }

    /**
     * Set notificationType
     *
     * @param integer $notificationType
     *
     * @return NotificationBase
     */
    public function setNotificationType($notificationType)
    {
        $this->notificationType = $notificationType;

        return $this;
    }

    /**
     * Get notificationType
     *
     * @return integer
     */
    public function getNotificationType()
    {
        return $this->notificationType;
    }

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
     * Set isClicked
     *
     * @param boolean $isClicked
     *
     * @return Notification
     */
    public function setIsClicked($isClicked)
    {
        $this->isClicked = $isClicked;

        return $this;
    }

    /**
     * Get isClicked
     *
     * @return boolean
     */
    public function getIsClicked()
    {
        return $this->isClicked;
    }

    

    /**
     * Set notificationThread
     *
     * @param \PP\NotificationBundle\Entity\NotificationThread $notificationThread
     *
     * @return Notification
     */
    public function setNotificationThread(\PP\NotificationBundle\Entity\NotificationThread $notificationThread = null)
    {
        $this->notificationThread = $notificationThread;

        return $this;
    }

    /**
     * Get notificationThread
     *
     * @return \PP\NotificationBundle\Entity\NotificationThread
     */
    public function getNotificationThread()
    {
        return $this->notificationThread;
    }
    
    
}
