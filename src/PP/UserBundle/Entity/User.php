<?php

namespace PP\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use PP\NotificationBundle\Entity\NotificationThread;

/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\UserBundle\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks
 */
class User extends BaseUser
{            
    
    
    /**
    * @ORM\Column(name="id", type="integer")
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
    * @var boolean $emailConfirmed
    *
    * @ORM\Column(name="emailConfirmed", type="boolean")   
    */
    private $emailConfirmed;
    
    /**   
    * @ORM\Column(name="slug", type="string", length=128, unique=true)
    */
    private $slug;

    /**
    * @var string $name
    *
    * @ORM\Column(name="name", type="string", length=40)
    * @Assert\Length(
    *      min = 1,
    *      max = 25,
    *      minMessage = "Your name must be at least {{ limit }} characters long",
    *      maxMessage = "Your name cannot be longer than {{ limit }} characters"
    * )
    */
    protected $name;
    
    
    /**
    * @var string $description
    *
    * @ORM\Column(name="description", type="string", length=255, nullable=true)
    * @Assert\Length(    
    *      max = 175,
    *      maxMessage = "Your description cannot be longer than {{ limit }} characters"
    * )
    */
    private $description;
    
    /**
    * @var string $contact
    *
    * @ORM\Column(name="contact", type="string", length=100, nullable=true)
    * @Assert\Length(
    *      min = 1,    
    *      max = 50,
    *      minMessage = "Your contact must be at least {{ limit }} characters long",  
    *      maxMessage = "Your contact cannot be longer than {{ limit }} characters"
    * )
    */
    private $contact;
    
    /**
    * @ORM\OneToOne(targetEntity="PP\ImageBundle\Entity\Image", cascade={"persist", "remove"})
    * @Assert\Valid()
    */
    private $profilImage;

    /**
    * @ORM\OneToOne(targetEntity="PP\ImageBundle\Entity\Image", cascade={"persist", "remove"})
    * @ORM\JoinColumn(name="profile_image_id", referencedColumnName="id", nullable=true)
    * @Assert\Valid()
    */
    private $coverImage;

    /**    
    * @ORM\OneToMany(targetEntity="PP\RequestBundle\Entity\ImageRequest", mappedBy="author")
    * @Assert\Valid()          
    */
    private $imageRequests;
    
    /**    
    * @ORM\OneToMany(targetEntity="PP\CommentBundle\Entity\Comment", mappedBy="author")
    * @Assert\Valid()          
    */
    private $comments;
    
    /**    
    * @ORM\OneToOne(targetEntity="PP\NotificationBundle\Entity\NotificationThread", cascade={"persist"} )
    * @Assert\Valid()          
    */
    private $notificationThread;
    
    /**
     * @var integer $notificationsNb
     * 
     * @ORM\Column(name="notificationsNb", type="integer", nullable=true)
     */
    private $notificationsNb;
        
    /**    
    * @ORM\ManyToMany(targetEntity="PP\MessageBundle\Entity\MessageThread", inversedBy="users")
    * @Assert\Valid()          
    */
    private $messageThreads;
    
    /**    
    * @ORM\ManyToMany(targetEntity="PP\UserBundle\Entity\User")
    * @Assert\Valid() 
    * @ORM\JoinTable(name="user_followers") 
    */
    private $followers;

    /**
    * @var integer
    *
    * @ORM\Column(name="followers_nb", type="integer" , nullable=true, options={"default":0})        
    */
    private $followers_nb;

    /**    
    * @ORM\ManyToMany(targetEntity="PP\UserBundle\Entity\User", cascade={"persist", "remove"})
    * @Assert\Valid()      
    * @ORM\JoinTable(name="user_following") 
    */
    private $following;
    
    /**    
    * @ORM\ManyToMany(targetEntity="PP\UserBundle\Entity\User", cascade={"persist", "remove"})
    * @Assert\Valid()      
    * @ORM\JoinTable(name="blocked_users") 
    */
    private $blockedUsers;

    /**
    * @var integer
    *
    * @ORM\Column(name="following_nb", type="integer", nullable=true,  options={"default":0})
    */
    private $following_nb;

    /**    
    * @ORM\OneToMany(targetEntity="PP\PropositionBundle\Entity\Proposition", mappedBy="author")
    * @Assert\Valid()          
    */
    private $propositions;

    /**    
    * @ORM\ManyToMany(targetEntity="PP\RequestBundle\Entity\ImageRequest", mappedBy="upvotedBy", cascade={"remove"})
    * @Assert\Valid()          
    */
    private $imageRequestsUpvoted;

    /**    
    * @ORM\ManyToMany(targetEntity="PP\PropositionBundle\Entity\Proposition", mappedBy="upvotedBy", cascade={"remove"} )
    * @Assert\Valid()          
    */
    private $propositionsUpvoted;
    
    /**
    * @var boolean $isInMessage
    *
    * @ORM\Column(name="isInMessage", type="boolean", nullable=true)
    */
    private $isInMessage;
    
    /**    
    * @ORM\OneToMany(targetEntity="PP\ReportBundle\Entity\ReportTicket", mappedBy="author")
    * @Assert\Valid()
    */
    private $reportTickets;
    
    /**    
    * @ORM\OneToMany(targetEntity="PP\ReportBundle\Entity\DisableTicket", mappedBy="author")
    * @Assert\Valid()
    */
    private $disabletTickets;
    
     /**    
     * @ORM\OneToOne(targetEntity="PP\ReportBundle\Entity\DisableTicket", cascade={"persist", "remove"})     
     * @Assert\Valid()          
     */
    private $disableTicket;
    
    /**
    * @var boolean $notificationEnabled
    *
    * @ORM\Column(name="notificationEnabled", type="boolean", nullable=true)
    */
    private $notificationEnabled = true;
    
    /**
     * @var integer $reportNb
     * 
     * @ORM\Column(name="reportNb", type="integer", nullable=true)
     */
    private $reportNb;
    
    public function addReportNb(){
        $this->reportNb++;
        return $this;
    }
    
    /**
    * Set name
    *
    * @param string $name
    *
    * @return User
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
     * Add imageRequest
     *
     * @param \PP\RequestBundle\Entity\ImageRequest $imageRequest
     *
     * @return User
     */
    public function addImageRequest(\PP\RequestBundle\Entity\ImageRequest $imageRequest)
    {
        $this->imageRequests[] = $imageRequest;

        return $this;
    }

    /**
     * Remove imageRequest
     *
     * @param \PP\RequestBundle\Entity\ImageRequest $imageRequest
     */
    public function removeImageRequest(\PP\RequestBundle\Entity\ImageRequest $imageRequest)
    {
        $this->imageRequests->removeElement($imageRequest);
    }

    /**
     * Get imageRequests
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImageRequests()
    {
        return $this->imageRequests;
    }

    /**
     * Add proposition
     *
     * @param \PP\RequestBundle\Entity\ImageRequest $proposition
     *
     * @return User
     */
    public function addProposition(\PP\RequestBundle\Entity\ImageRequest $proposition)
    {
        $this->propositions[] = $proposition;

        return $this;
    }

    /**
     * Remove proposition
     *
     * @param \PP\RequestBundle\Entity\ImageRequest $proposition
     */
    public function removeProposition(\PP\RequestBundle\Entity\ImageRequest $proposition)
    {
        $this->propositions->removeElement($proposition);
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
     * Set slug
     *
     * @param string $slug
     *
     * @return User
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
     * Add imageRequestsUpvoted
     *
     * @param \PP\RequestBundle\Entity\ImageRequest $imageRequestsUpvoted
     *
     * @return User
     */
    public function addImageRequestsUpvoted(\PP\RequestBundle\Entity\ImageRequest $imageRequestsUpvoted)
    {
        $this->imageRequestsUpvoted[] = $imageRequestsUpvoted;

        return $this;
    }

    /**
     * Remove imageRequestsUpvoted
     *
     * @param \PP\RequestBundle\Entity\ImageRequest $imageRequestsUpvoted
     */
    public function removeImageRequestsUpvoted(\PP\RequestBundle\Entity\ImageRequest $imageRequestsUpvoted)
    {
        $this->imageRequestsUpvoted->removeElement($imageRequestsUpvoted);
    }

    /**
     * Get imageRequestsUpvoted
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImageRequestsUpvoted()
    {
        return $this->imageRequestsUpvoted;
    }

    /**
     * Add propositionsUpvoted
     *
     * @param \PP\PropositionBundle\Entity\Proposition $propositionsUpvoted
     *
     * @return User
     */
    public function addPropositionsUpvoted(\PP\PropositionBundle\Entity\Proposition $propositionsUpvoted)
    {
        $this->propositionsUpvoted[] = $propositionsUpvoted;

        return $this;
    }

    /**
     * Remove propositionsUpvoted
     *
     * @param \PP\PropositionBundle\Entity\Proposition $propositionsUpvoted
     */
    public function removePropositionsUpvoted(\PP\PropositionBundle\Entity\Proposition $propositionsUpvoted)
    {
        $this->propositionsUpvoted->removeElement($propositionsUpvoted);
    }

    /**
     * Get propositionsUpvoted
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPropositionsUpvoted()
    {
        return $this->propositionsUpvoted;
    }

    /**
     * Set profilImage
     *
     * @param \PP\ImageBundle\Entity\Image $profilImage
     *
     * @return User
     */
    public function setProfilImage(\PP\ImageBundle\Entity\Image $profilImage = null)
    {
        $this->profilImage = $profilImage;
        $this->profilImage->addSizeList("70x70");
        return $this;
    }

    /**
     * Get profilImage
     *
     * @return \PP\ImageBundle\Entity\Image
     */
    public function getProfilImage()
    {
        return $this->profilImage;
    }

    /**
     * Set coverImage
     *
     * @param \PP\ImageBundle\Entity\Image $coverImage
     *
     * @return User
     */
    public function setCoverImage(\PP\ImageBundle\Entity\Image $coverImage = null)
    {
        $this->coverImage = $coverImage;
        $this->coverImage->addSizeList("1500x500");
        return $this;
    }

    /**
     * Get coverImage
     *
     * @return \PP\ImageBundle\Entity\Image
     */
    public function getCoverImage()
    {
        return $this->coverImage;
    }
    
     /////////////////////////////////
    //////// create thumbnail ///////       
    
    /**
    * @ORM\PostPersist()
    * @ORM\PostUpdate()     
    */
    public function createThumbnail(){
        $this->profilImage->resize("70x70",120, 120);
        if($this->coverImage!=null)$this->coverImage->resizeWidth("1500x500", 2880);
    }

    /**
     * Set followersNb
     *
     * @param integer $followersNb
     *
     * @return User
     */
    public function setFollowersNb($followersNb)
    {
        $this->followers_nb = $followersNb;

        return $this;
    }

    /**
     * Get followersNb
     *
     * @return integer
     */
    public function getFollowersNb()
    {
        return $this->followers_nb;
    }

    /**
     * Set followingNb
     *
     * @param integer $followingNb
     *
     * @return User
     */
    public function setFollowingNb($followingNb)
    {
        $this->following_nb = $followingNb;

        return $this;
    }

    /**
     * Get followingNb
     *
     * @return integer
     */
    public function getFollowingNb()
    {
        return $this->following_nb;
    }

  

    /**
     * Add follower
     *
     * @param \PP\UserBundle\Entity\User $follower
     *
     * @return User
     */
    public function addFollower(\PP\UserBundle\Entity\User $follower)
    {
        $this->followers[] = $follower;
        $this->followers_nb++;
        return $this;
    }

    /**
     * Remove follower
     *
     * @param \PP\UserBundle\Entity\User $follower
     */
    public function removeFollower(\PP\UserBundle\Entity\User $follower)
    {
        $this->followers->removeElement($follower);
        $this->followers_nb--;
    }

    /**
     * Get followers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * Add following
     *
     * @param \PP\UserBundle\Entity\User $following
     *
     * @return User
     */
    public function addFollowing(\PP\UserBundle\Entity\User $following)
    {
        $this->following[] = $following;
        $following->addFollower($this);
        $this->following_nb++;
        return $this;
    }

    /**
     * Remove following
     *
     * @param \PP\UserBundle\Entity\User $following
     */
    public function removeFollowing(\PP\UserBundle\Entity\User $following)
    {
        $this->following->removeElement($following);
        $following->removeFollower($this);
        $this->following_nb--;
    }

    /**
     * Get following
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFollowing()
    {
        return $this->following;
    }

    /**
     * @ORM\PrePersist()
     */
    public function createNotificationThread(){        
        $thread = new NotificationThread();
        $thread->setUser($this);
        $thread->setName($this->name);
        $this->setNotificationThread($thread);
    }

    /**
     * Set notificationThread
     *
     * @param \PP\NotificationBundle\Entity\NotificationThread $notificationThread
     *
     * @return User
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

    /**
     * Set notificationsNb
     *
     * @param integer $notificationsNb
     *
     * @return User
     */
    public function setNotificationsNb($notificationsNb)
    {
        $this->notificationsNb = $notificationsNb;

        return $this;
    }

    /**
     * Get notificationsNb
     *
     * @return integer
     */
    public function getNotificationsNb()
    {
        return $this->notificationsNb;
    }
    
    public function incrementNotificationsNb(){
        $this->notificationsNb++;        
        return $this;
    }
    
    public function decrementNotificationsNb(){
        $this->notificationsNb--;        
        return $this;
    }
    

    /**
     * Add messageThread
     *
     * @param \PP\MessageBundle\Entity\MessageThread $messageThread
     *
     * @return User
     */
    public function addMessageThread(\PP\MessageBundle\Entity\MessageThread $messageThread)
    {
        $this->messageThreads[] = $messageThread;

        return $this;
    }

    /**
     * Remove messageThread
     *
     * @param \PP\MessageBundle\Entity\MessageThread $messageThread
     */
    public function removeMessageThread(\PP\MessageBundle\Entity\MessageThread $messageThread)
    {
        $this->messageThreads->removeElement($messageThread);
    }

    /**
     * Get messageThreads
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMessageThreads()
    {
        return $this->messageThreads;
    }

    /**
     * Set isInMessage
     *
     * @param boolean $isInMessage
     *
     * @return User
     */
    public function setIsInMessage($isInMessage)
    {
        $this->isInMessage = $isInMessage;

        return $this;
    }

    /**
     * Get isInMessage
     *
     * @return boolean
     */
    public function getIsInMessage()
    {
        return $this->isInMessage;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return User
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set contact
     *
     * @param string $contact
     *
     * @return User
     */
    public function setContact($contact)
    {        
        $this->contact = preg_replace('#^https?://#', '', $contact);;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Add reportTicket
     *
     * @param \PP\ReportBundle\Entity\ReportTicket $reportTicket
     *
     * @return User
     */
    public function addReportTicket(\PP\ReportBundle\Entity\ReportTicket $reportTicket)
    {
        $this->reportTickets[] = $reportTicket;

        return $this;
    }

    /**
     * Remove reportTicket
     *
     * @param \PP\ReportBundle\Entity\ReportTicket $reportTicket
     */
    public function removeReportTicket(\PP\ReportBundle\Entity\ReportTicket $reportTicket)
    {
        $this->reportTickets->removeElement($reportTicket);
    }

    /**
     * Get reportTickets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReportTickets()
    {
        return $this->reportTickets;
    }

    /**
     * Add disabletTicket
     *
     * @param \PP\ReportBundle\Entity\disableTicket $disabletTicket
     *
     * @return User
     */
    public function addDisabletTicket(\PP\ReportBundle\Entity\disableTicket $disabletTicket)
    {
        $this->disabletTickets[] = $disabletTicket;

        return $this;
    }

    /**
     * Remove disabletTicket
     *
     * @param \PP\ReportBundle\Entity\disableTicket $disabletTicket
     */
    public function removeDisabletTicket(\PP\ReportBundle\Entity\disableTicket $disabletTicket)
    {
        $this->disabletTickets->removeElement($disabletTicket);
    }

    /**
     * Get disabletTickets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDisabletTickets()
    {
        return $this->disabletTickets;
    }

    /**
     * Set reportNb
     *
     * @param integer $reportNb
     *
     * @return User
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

    /**
     * Set disableTicket
     *
     * @param \PP\ReportBundle\Entity\DisableTicket $disableTicket
     *
     * @return User
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
     * Set notificationEnabled
     *
     * @param boolean $notificationEnabled
     *
     * @return User
     */
    public function setNotificationEnabled($notificationEnabled)
    {
        $this->notificationEnabled = $notificationEnabled;

        return $this;
    }

    /**
     * Get notificationEnabled
     *
     * @return boolean
     */
    public function getNotificationEnabled()
    {
        return $this->notificationEnabled;
    }

    /**
     * Add blockedUser
     *
     * @param \PP\UserBundle\Entity\User $blockedUser
     *
     * @return User
     */
    public function addBlockedUser(\PP\UserBundle\Entity\User $blockedUser)
    {
        $this->blockedUsers[] = $blockedUser;

        return $this;
    }

    /**
     * Remove blockedUser
     *
     * @param \PP\UserBundle\Entity\User $blockedUser
     */
    public function removeBlockedUser(\PP\UserBundle\Entity\User $blockedUser)
    {
        $this->blockedUsers->removeElement($blockedUser);
    }

    /**
     * Get blockedUsers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBlockedUsers()
    {
        return $this->blockedUsers;
    }

    /**
     * Add comment
     *
     * @param \PP\CommentBundle\Entity\Comment $comment
     *
     * @return User
     */
    public function addComment(\PP\CommentBundle\Entity\Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \PP\CommentBundle\Entity\Comment $comment
     */
    public function removeComment(\PP\CommentBundle\Entity\Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set emailConfirmed
     *
     * @param boolean $emailConfirmed
     *
     * @return User
     */
    public function setEmailConfirmed($emailConfirmed)
    {
        $this->emailConfirmed = $emailConfirmed;

        return $this;
    }

    /**
     * Get emailConfirmed
     *
     * @return boolean
     */
    public function getEmailConfirmed()
    {
        return $this->emailConfirmed;
    }
}
