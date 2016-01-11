<?php

namespace PP\CommentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Comment
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\CommentBundle\Entity\CommentRepository")
 */
class Comment
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
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetimetz")
     */
    private $createdDate;

    /**    
    * @ORM\ManyToOne(targetEntity="PP\CommentBundle\Entity\CommentThread", inversedBy="comments")
    * @Assert\Valid()
    */
    private $commentThread;
    
    /**    
     * @ORM\ManyToOne(targetEntity="PP\UserBundle\Entity\User", inversedBy="comments")
     * @Assert\Valid()          
     */
    private $author;
    
    public function __construct()
    {
        $this->createdDate = new \Datetime();
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
     * Set content
     *
     * @param string $content
     *
     * @return Comment
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
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return Comment
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
     * Set commentThread
     *
     * @param \PP\CommentBundle\Entity\CommentThread $commentThread
     *
     * @return Comment
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

    /**
     * Set author
     *
     * @param \PP\UserBundle\Entity\User $author
     *
     * @return Comment
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
}
