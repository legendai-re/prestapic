<?php

namespace PP\RequestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PopularTag
 *
 * @ORM\Table(name="popular_tag")
 * @ORM\Entity(repositoryClass="PP\RequestBundle\Entity\PopularTagRepository")
 */
class PopularTag
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetimetz")
     */
    private $createdDate;

    
    /**
    * @ORM\ManyToOne(targetEntity="PP\RequestBundle\Entity\Tag", cascade={"persist"})
    * @Assert\Valid()
    */
    private $tag;
    
    /**
     * Get id
     *
     * @return int
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
     * @return PopularTag
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
     * Set tag
     *
     * @param \PP\RequestBundle\Entity\Tag $tag
     *
     * @return PopularTag
     */
    public function setTag(\PP\RequestBundle\Entity\Tag $tag = null)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return \PP\RequestBundle\Entity\Tag
     */
    public function getTag()
    {
        return $this->tag;
    }
}
