<?php

namespace PP\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Message
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\MessageBundle\Entity\MessageRepository")
 */
class Message
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
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime")
     */
    private $createdDate;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isViewed", type="boolean")
     */
    private $isViewed;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isSent", type="boolean")
     */
    private $isSent;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isReceived", type="boolean")
     */
    private $isReceived;
        
    /**    
    * @ORM\ManyToOne(targetEntity="PP\UserBundle\Entity\User")
    * @Assert\Valid()          
    */
    private $author;
    
    /**
    * @ORM\ManyToOne(targetEntity="PP\UserBundle\Entity\User")
    * @Assert\Valid()          
    */
    private $target;
    
    /**    
    * @ORM\ManyToOne(targetEntity="PP\MessageBundle\Entity\MessageThread", inversedBy="messages")
    * @Assert\Valid()          
    */
    private $messageThread;
    
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
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return Message
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Message
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set isViewed
     *
     * @param boolean $isViewed
     *
     * @return Message
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
     * Set isSent
     *
     * @param boolean $isSent
     *
     * @return Message
     */
    public function setIsSent($isSent)
    {
        $this->isSent = $isSent;

        return $this;
    }

    /**
     * Get isSent
     *
     * @return boolean
     */
    public function getIsSent()
    {
        return $this->isSent;
    }

    /**
     * Set isReceived
     *
     * @param boolean $isReceived
     *
     * @return Message
     */
    public function setIsReceived($isReceived)
    {
        $this->isReceived = $isReceived;

        return $this;
    }

    /**
     * Get isReceived
     *
     * @return boolean
     */
    public function getIsReceived()
    {
        return $this->isReceived;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdDate = new \DateTime();
        $this->isViewed = false;
        $this->isReceived = false;
        $this->isSent = false;
    }

    /**
     * Set author
     *
     * @param \PP\UserBundle\Entity\User $author
     *
     * @return Message
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
     * Set target
     *
     * @param \PP\UserBundle\Entity\User $target
     *
     * @return Message
     */
    public function setTarget(\PP\UserBundle\Entity\User $target = null)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return \PP\UserBundle\Entity\User
     */
    public function getTarget()
    {
        return $this->target;
    }
      

    /**
     * Set messageThread
     *
     * @param \PP\MessageBundle\Entity\MessageThread $messageThread
     *
     * @return Message
     */
    public function setMessageThread(\PP\MessageBundle\Entity\MessageThread $messageThread = null)
    {
        $this->messageThread = $messageThread;

        return $this;
    }

    /**
     * Get messageThread
     *
     * @return \PP\MessageBundle\Entity\MessageThread
     */
    public function getMessageThread()
    {
        return $this->messageThread;
    }
}
