<?php

namespace PP\RequestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Post
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\RequestBundle\Entity\ImageRequestRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ImageRequest
{   
    public function __construct()
    {
            $this->enabled = true;
            $this->createdDate = new \Datetime();
            $this->propositions = new ArrayCollection();
            $this->tags = new ArrayCollection();
            $this->propositionsNb = 0;
            $this->updateDate = new \Datetime();            
            $this->closed = false;
            $this->upvote=0;
    }
    
    /**
    * @ORM\OneToMany(targetEntity="PP\PropositionBundle\Entity\Proposition", mappedBy="imageRequest", cascade={"remove"})
    */
    private $propositions;
    
    /**    
     * @ORM\OneToOne(targetEntity="PP\PropositionBundle\Entity\Proposition", cascade={"remove"})
     * @ORM\JoinColumn(name="accepted_proposition_id", referencedColumnName="id",  unique=false)     
     * @Assert\Valid()          
     */
    private $acceptedProposition;
    
    /**    
     * @ORM\ManyToOne(targetEntity="PP\UserBundle\Entity\User", inversedBy="imageRequests")
     * @Assert\Valid()          
     */
    private $author;
    
    /**     
     * @ORM\ManyToOne(targetEntity="PP\RequestBundle\Entity\Category")    
     * @Assert\Valid()          
     */
    private $category;
    
    /**     
     * @ORM\ManyToMany(targetEntity="PP\RequestBundle\Entity\Tag", inversedBy="imageRequests")    
     * @Assert\Valid()          
     */
    private $tags;
    
    /**     
     * @ORM\ManyToMany(targetEntity="PP\UserBundle\Entity\User", inversedBy="imageRequestsUpvoted")    
     * @Assert\Valid()          
     */
    private $upvotedBy;
    
    /**    
     *     
     */
    private $tagsStr;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
    * @Gedmo\Slug(fields={"title"})
    * @ORM\Column(length=128, unique=true)
    */
     private $slug;
  
    /**
     * @var integer
     *
     * @ORM\Column(name="propositions_nb", type="integer")
     */
    private $propositionsNb;
    
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\Length(
     *      min = 3,
     *      max = 90,
     *      minMessage = "Your request title must be at least {{ limit }} characters long",
     *      maxMessage = "Your request title cannot be longer than {{ limit }} characters"
     * )
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="request", type="text")
     * @Assert\Length(
     *      min = 10,
     *      max = 30000,
     *      minMessage = "Your request description must be at least {{ limit }} characters long",
     *      maxMessage = "Your request description cannot be longer than {{ limit }} characters"
     * )
     */
    private $request;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime")
     */
    private $createdDate;
    
    private $dateAgo;
    
     /**
     * @var boolean
     *
     * @ORM\Column(name="closed", type="boolean")
     */
    private $closed;        
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updateDate", type="datetime", nullable=true)
     */
    private $updateDate;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="upvote", type="integer")
     */
    private $upvote;
    
     /**
     * @var integer
     *
     * @ORM\Column(name="reportNb", type="integer", nullable=true)
     */
    private $reportNb;
    
    /**    
    * @ORM\OneToOne(targetEntity="PP\CommentBundle\Entity\CommentThread")
    * @Assert\Valid()          
    */
    private $commentThread;
    
    public function setDateAgo($dateAgo){
        $this->dateAgo = $dateAgo;
        return $this;
    }
    
    public function getDateAgo(){
        return $this->dateAgo;
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
     * Get id
     *
     * @return integer
     */
    public function setId($id)
    {
         $this->id = $id;
         return $this;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return ImageRequest
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
     * Set request
     *
     * @param string $request
     *
     * @return ImageRequest
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get request
     *
     * @return string
     */
    public function getRequest()
    {
        return $this->request;
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
     * Set updateDate
     *
     * @param \DateTime $updateDate
     *
     * @return Request
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * Get updateDate
     *
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * Add proposition
     *
     * @param \PP\PropositionBundle\Entity\Proposition $proposition
     *
     * @return ImageRequest
     */
    public function addProposition(\PP\PropositionBundle\Entity\Proposition $proposition)
    {
        $this->propositions[] = $proposition;
        $proposition->setImageRequest($this);
        $this->propositionsNb++;
        return $this;
    }

    /**
     * Remove proposition
     *
     * @param \PP\PropositionBundle\Entity\Proposition $proposition
     */
    public function removeProposition(\PP\PropositionBundle\Entity\Proposition $proposition)
    {
        $this->propositions->removeElement($proposition);
        $this->propositionsNb--;
        return $this;
    }

    /**
     * Get propositions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPropositions()
    {
        return $this->propositions;
    }

    /**
     * Set closed
     *
     * @param boolean $closed
     *
     * @return ImageRequest
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;

        return $this;
    }

    /**
     * Get closed
     *
     * @return boolean
     */
    public function getClosed()
    {
        return $this->closed;
    }


    /**
     * Set acceptedProposition
     *
     * @param \PP\PropositionBundle\Entity\Proposition $acceptedProposition
     *
     * @return ImageRequest
     */
    public function setAcceptedProposition(\PP\PropositionBundle\Entity\Proposition $acceptedProposition = null)
    {
        $this->acceptedProposition = $acceptedProposition;

        return $this;
    }

    /**
     * Get acceptedProposition
     *
     * @return \PP\PropositionBundle\Entity\Proposition
     */
    public function getAcceptedProposition()
    {
        return $this->acceptedProposition;
    }

    /**
     * Set propositionsNb
     *
     * @param integer $propositionsNb
     *
     * @return ImageRequest
     */
    public function setPropositionsNb($propositionsNb)
    {
        $this->propositionsNb = $propositionsNb;

        return $this;
    }

    /**
     * Get propositionsNb
     *
     * @return integer
     */
    public function getPropositionsNb()
    {
        return $this->propositionsNb;
    }

    /**
     * Set category
     *
     * @param \PP\RequestBundle\Entity\Category $category
     *
     * @return ImageRequest
     */
    public function setCategory(\PP\RequestBundle\Entity\Category $category = null)
    {
        $this->category = $category;
        if($this->category!=null){
            $x = $this->category->getImageRequestsNb();
            $this->category->setImageRequestsNb($x+1);
        }
        return $this;
    }

    /**
     * Get category
     *
     * @return \PP\PropositionBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }
    
    /**
    * @ORM\PreRemove()    
    */
    public function decrementCategoty(){               
        $x = $this->category->getImageRequestsNb();
        $this->category->setImageRequestsNb($x-1);        
    }

    /**
     * Set upvote
     *
     * @param integer $upvote
     *
     * @return ImageRequest
     */
    public function setUpvote($upvote)
    {
        $this->upvote = $upvote;

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
     * Set upvote     
     *
     * @return ImageRequest
     */
    public function addUpvote()
    {
        $this->upvote++;

        return $this;
    }

    /**
     * Add tag
     *
     * @param \PP\RequestBundle\Entity\Tag $tag
     *
     * @return ImageRequest
     */
    public function addTag(\PP\RequestBundle\Entity\Tag $tag)
    {        
        $this->tags[] = $tag;        
        return $this;
    }

    /**
     * Remove tag
     *
     * @param \PP\RequestBundle\Entity\Tag $tag
     */
    public function removeTag(\PP\RequestBundle\Entity\Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }
    
    public function getTagsStr()
    {
        return $this->tagsStr;
    }
    
    public function setTagsStr($tagStr){
        $this->tagsStr = $tagStr;                               
        
        return $this;
    }
    
    /**
    * @ORM\PrePersist()    
    */
    public function addTagUserNb(){               
        foreach ($this->tags as $tag){
            $tag->addUsedNb();
        }        
    }
    
    /**
     * @ORM\PreRemove()
     */
    public function removeTagsRelation(){
        foreach ($this->tags as $tag){
            $tag->removeUsedNb();
            $tag->getImageRequests()->removeElement($this);
        }   
    }
    

    /**
     * Set author
     *
     * @param \PP\UserBundle\Entity\User $author
     *
     * @return ImageRequest
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
     * Set slug
     *
     * @param string $slug
     *
     * @return ImageRequest
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
     * Add upvotedBy
     *
     * @param \PP\UserBundle\Entity\User $upvotedBy
     *
     * @return ImageRequest
     */
    public function addUpvotedBy(\PP\UserBundle\Entity\User $upvotedBy)
    {
        $upvotedBy->addImageRequestsUpvoted($this);
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
     * Add author
     *
     * @param \PP\UserBundle\Entity\User $author
     *
     * @return ImageRequest
     */
    public function addAuthor(\PP\UserBundle\Entity\User $author)
    {
        $this->author[] = $author;

        return $this;
    }

    /**
     * Remove author
     *
     * @param \PP\UserBundle\Entity\User $author
     */
    public function removeAuthor(\PP\UserBundle\Entity\User $author)
    {
        $this->author->removeElement($author);
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return ImageRequest
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

    /**
     * Set disableTicket
     *
     * @param \PP\ReportBundle\Entity\DisableTicket $disableTicket
     *
     * @return ImageRequest
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
     * @return ImageRequest
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

    /**
     * Set commentThread
     *
     * @param \PP\CommentBundle\Entity\CommentThread $commentThread
     *
     * @return ImageRequest
     */
    public function setCommentThread(\PP\CommentBundle\Entity\CommentThread $commentThread = null)
    {
        $this->commentThread = $commentThread;

        return $this;
    }

    /**
     * Get commentThread
     *
     * @return \PP\CommentBundle\Entity\CommentThread
     */
    public function getCommentThread()
    {
        return $this->commentThread;
    }
    
}
