<?php

namespace PP\PropositionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Proposition
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\PropositionBundle\Entity\PropositionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Proposition
{
    public function __construct()
    {
        $this->enabled = true;
        $this->accepted = false;
        $this->createdDate = new \Datetime();
        $this->upvote = 0;        
    }
    
    /**
    * @ORM\ManyToOne(targetEntity="PP\RequestBundle\Entity\ImageRequest", inversedBy="propositions")
    * @ORM\JoinColumn(nullable=false)
    */
    private $imageRequest;
    
    /**
    * @ORM\OneToOne(targetEntity="PP\ImageBundle\Entity\Image", cascade={"persist", "remove"})
    * @Assert\Valid()
    */
    private $image;    
    
     /**    
     * @ORM\ManyToOne(targetEntity="PP\UserBundle\Entity\User", inversedBy="propositions")
     * @Assert\Valid()          
     */
    private $author;
    
    /**     
     * @ORM\ManyToMany(targetEntity="PP\UserBundle\Entity\User", inversedBy="propositionsUpvoted")    
     * @Assert\Valid()          
     */
    private $upvotedBy;
    
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
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\Length(
     *      min = 1,    
     *      max = 255,
     *      minMessage = "Your proposition title must be at least {{ limit }} characters long",
     *      maxMessage = "Your proposition title be longer than {{ limit }} characters"
     * )
     */
    private $title;

    /**
     * @var boolean
     *
     * @ORM\Column(name="accepted", type="boolean")
     */
    private $accepted;       
    
    /**
     * @var integer
     *
     * @ORM\Column(name="upvote", type="integer")
     */
    private $upvote;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime")
     */
    private $createdDate;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;
    
    /**    
     * @ORM\OneToOne(targetEntity="PP\ReportBundle\Entity\DisableTicket", cascade={"persist", "remove"})     
     * @Assert\Valid()          
     */
    private $disableTicket;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="reportNb", type="integer", nullable=true)
     */
    private $reportNb;    
    
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
     * Set title
     *
     * @param string $title
     *
     * @return Proposition
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set accepted
     *
     * @param boolean $accepted
     *
     * @return Proposition
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
     * Set upvote
     *
     * @param integer $upvote
     *
     * @return Proposition
     */
    public function setUpvote($upvote)
    {
        $this->upvote = $upvote;

        return $this;
    }
    
    /**
     * Set upvote     
     *
     * @return Proposition
     */
    public function addUpvote()
    {
        $this->upvote++;

        return $this;
    }

    /**
     * Get upvote
     *
     * @return integer
     */
    public function getUpvote()
    {
        return $this->upvote;
    }
    
    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return ImageRequest
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
     * Set image
     *
     * @param \PP\ImageBundle\Entity\Image $image
     *
     * @return Proposition
     */
    public function setImage(\PP\ImageBundle\Entity\Image $image = null)
    {
        $this->image = $image;
        $this->image->addSizeList("home");
        $this->image->addSizeList("single");
        $this->image->addSizeList("selected");
        return $this;
    }

    /**
     * Get image
     *
     * @return \PP\ImageBundle\Entity\Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set imageRequest
     *
     * @param \PP\RequestBundle\Entity\ImageRequest $imageRequest
     *
     * @return Proposition
     */
    public function setImageRequest(\PP\RequestBundle\Entity\ImageRequest $imageRequest)
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
    
    /////////////////////////////////
    //////// create thumbnail ///////
    
    /**
    * @ORM\PostPersist()    
    */
    public function createThumbnail(){               
        $this->image->resize("home",240, 150);
        $this->image->resize("single",380, 237);
        $this->image->resize("selected",770, 480);
    }

    /**
     * Set author
     *
     * @param \PP\UserBundle\Entity\User $author
     *
     * @return Proposition
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
     * Add upvotedBy
     *
     * @param \PP\UserBundle\Entity\User $upvotedBy
     *
     * @return Proposition
     */
    public function addUpvotedBy(\PP\UserBundle\Entity\User $upvotedBy)
    {
        $this->upvotedBy[] = $upvotedBy;

        return $this;
    }

    /**
     * Remove upvotedBy
     *
     * @param \PP\UserBundle\Entity\User $upvotedBy
     */
    public function removeUpvotedBy(\PP\UserBundle\Entity\User $upvotedBy)
    {
        $this->upvotedBy->removeElement($upvotedBy);
    }

    /**
     * Get upvotedBy
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUpvotedBy()
    {
        return $this->upvotedBy;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return Proposition
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }
    
    public function disable(){
        $this->enabled = false;
        $this->imageRequest->setPropositionsNb($this->imageRequest->getPropositionsNb()-1);
    }
    
    /**
     * Set disableTicket
     *
     * @param \PP\ReportBundle\Entity\DisableTicket $disableTicket
     *
     * @return Proposition
     */
    public function setDisableTicket(\PP\ReportBundle\Entity\DisableTicket $disableTicket = null)
    {
        $this->disableTicket = $disableTicket;

        return $this;
    }

    /**
     * Get disableTicket
     *
     * @return \PP\ReportBundle\Entity\DisableTicket
     */
    public function getDisableTicket()
    {
        return $this->disableTicket;
    }

    /**
     * Set reportNb
     *
     * @param integer $reportNb
     *
     * @return Proposition
     */
    public function setReportNb($reportNb)
    {
        $this->reportNb = $reportNb;

        return $this;
    }

    /**
     * Get reportNb
     *
     * @return integer
     */
    public function getReportNb()
    {
        return $this->reportNb;
    }
    
     public function addReportNb(){
        $this->reportNb++;
        return $this;
    }
}
