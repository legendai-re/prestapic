<?php

namespace PP\CommentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * CommentThread
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\CommentBundle\Entity\CommentThreadRepository")
 */
class CommentThread
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id     
     */
    private $id;

    /**    
    * @ORM\OneToMany(targetEntity="PP\CommentBundle\Entity\Comment", mappedBy="commentThread")
    * @Assert\Valid()          
    */
    private $comments;        
    
    /**
     * @var integer
     *
     * @ORM\Column(name="commentNb", type="integer")
     */
    private $commentNb;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetimetz")
     */
    private $createdDate;
    
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
     * Constructor
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->createdDate = new \DateTime();
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return CommentThread
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Add comment
     *
     * @param \PP\CommentBundle\Entity\Comment $comment
     *
     * @return CommentThread
     */
    public function addComment(\PP\CommentBundle\Entity\Comment $comment)
    {
        $this->comments[] = $comment;
        $this->commentNb++;
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
        $this->commentNb--;
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
     * Set commentNb
     *
     * @param integer $commentNb
     *
     * @return CommentThread
     */
    public function setCommentNb($commentNb)
    {
        $this->commentNb = $commentNb;

        return $this;
    }

    /**
     * Get commentNb
     *
     * @return integer
     */
    public function getCommentNb()
    {
        return $this->commentNb;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return CommentThread
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
}
