<?php

namespace PP\NotificationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationMessage
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\NotificationBundle\Entity\NotificationMessageRepository")
 */
class NotificationMessage
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**    
    * @ORM\ManyToOne(targetEntity="PP\UserBundle\Entity\User", cascade={"persist"} )     
    * @Assert\Valid()
    */
    private $author;
    
    /**    
    * @ORM\ManyToOne(targetEntity="PP\MessageBundle\Entity\Message", cascade={"persist"} )     
    * @Assert\Valid()
    */
    private $message;
    
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
     * Set author
     *
     * @param \PP\UserBundle\Entity\User $author
     *
     * @return NotificationMessage
     */
    public function setAuthor(\PP\UserBundle\Entity\User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \PP\UserBundle\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set notificationBase
     *
     * @param \PP\NotificationBundle\Entity\Notification $notificationBase
     *
     * @return NotificationMessage
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

    /**
     * Set message
     *
     * @param \PP\MessageBundle\Entity\Message $message
     *
     * @return NotificationMessage
     */
    public function setMessage(\PP\MessageBundle\Entity\Message $message = null)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return \PP\MessageBundle\Entity\Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
