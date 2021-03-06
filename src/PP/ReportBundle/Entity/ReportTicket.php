<?php

namespace PP\ReportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ReportTicket
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\ReportBundle\Entity\ReportTicketRepository")
 */
class ReportTicket
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
    private $reportTicketType;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="targetId", type="integer")
     */
    private $targetId;
    
    /**
     * @var string
     *
     * @ORM\Column(name="details", type="text", nullable=true)
     */
    private $details;

    /**
     * @var boolean
     *
     * @ORM\Column(name="finished", type="boolean")
     */
    private $finished;

    /**
     * @var boolean
     *
     * @ORM\Column(name="accepted", type="boolean")
     */
    private $accepted;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime")
     */
    private $createdDate;

    /**    
    * @ORM\ManyToOne(targetEntity="PP\UserBundle\Entity\User", inversedBy="reportTickets")
    * @Assert\Valid()
    */
    private $author;
    
    /**    
    * @ORM\ManyToOne(targetEntity="PP\ReportBundle\Entity\ReportReason", inversedBy="reportTickets")
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
     * Set details
     *
     * @param string $details
     *
     * @return ReportTicket
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
     * Set finished
     *
     * @param boolean $finished
     *
     * @return ReportTicket
     */
    public function setFinished($finished)
    {
        $this->finished = $finished;

        return $this;
    }

    /**
     * Get finished
     *
     * @return boolean
     */
    public function getFinished()
    {
        return $this->finished;
    }

    /**
     * Set accepted
     *
     * @param boolean $accepted
     *
     * @return ReportTicket
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;

        return $this;
    }

    /**
     * Get accepted
     *
     * @return boolean
     */
    public function getAccepted()
    {
        return $this->accepted;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return ReportTicket
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
     * Set reportTicketType
     *
     * @param integer $reportTicketType
     *
     * @return ReportTicket
     */
    public function setReportTicketType($reportTicketType)
    {
        $this->reportTicketType = $reportTicketType;

        return $this;
    }

    /**
     * Get reportTicketType
     *
     * @return integer
     */
    public function getReportTicketType()
    {
        return $this->reportTicketType;
    }

    /**
     * Set author
     *
     * @param \PP\UserBundle\Entity\User $author
     *
     * @return ReportTicket
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
     * @return ReportTicket
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
     * Set targetId
     *
     * @param integer $targetId
     *
     * @return ReportTicket
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
