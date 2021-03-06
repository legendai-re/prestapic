<?php

namespace PP\ReportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DisableTicket
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\ReportBundle\Entity\DisableTicketRepository")
 */
class DisableTicket
{
    public function __construct()
    {
            $this->createdDate = new \DateTime;
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
     * @var integer
     *
     * @ORM\Column(name="reportTicketType", type="integer")
     */
    private $disableTicketType;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="targetId", type="integer")
     */
    private $targetId;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime")
     */
    private $createdDate;
    
    /**
     * @var string
     *
     * @ORM\Column(name="details", type="text", nullable=true)
     */
    private $details;
    
    /**    
    * @ORM\ManyToOne(targetEntity="PP\UserBundle\Entity\User", inversedBy="disabletTickets")
    * @Assert\Valid()
    */
    private $author;
    
    /**    
    * @ORM\ManyToOne(targetEntity="PP\ReportBundle\Entity\ReportReason", inversedBy="disabledTickets")
    * @Assert\Valid()
    */
    private $reason;
    
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
     * @return DisableTicket
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
     * Set details
     *
     * @param string $details
     *
     * @return DisableTicket
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get details
     *
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set author
     *
     * @param \PP\UserBundle\Entity\User $author
     *
     * @return DisableTicket
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
     * Set reason
     *
     * @param \PP\ReportBundle\Entity\ReportReason $reason
     *
     * @return DisableTicket
     */
    public function setReason(\PP\ReportBundle\Entity\ReportReason $reason = null)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return \PP\ReportBundle\Entity\ReportReason
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set disableTicketType
     *
     * @param integer $disableTicketType
     *
     * @return DisableTicket
     */
    public function setDisableTicketType($disableTicketType)
    {
        $this->disableTicketType = $disableTicketType;

        return $this;
    }

    /**
     * Get disableTicketType
     *
     * @return integer
     */
    public function getDisableTicketType()
    {
        return $this->disableTicketType;
    }

    /**
     * Set targetId
     *
     * @param integer $targetId
     *
     * @return DisableTicket
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;

        return $this;
    }

    /**
     * Get targetId
     *
     * @return integer
     */
    public function getTargetId()
    {
        return $this->targetId;
    }
}
