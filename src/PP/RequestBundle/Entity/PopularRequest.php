<?php

namespace PP\RequestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PopularRequest
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\RequestBundle\Entity\PopularRequestRepository")
 */
class PopularRequest
{
    public function __construct()
    {    
        $this->createdDate = new \Datetime();
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
    * @ORM\ManyToOne(targetEntity="PP\RequestBundle\Entity\ImageRequest", cascade={"persist"})
    * @Assert\Valid()
    */
    private $imageRequest;
    
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
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return PopularRequest
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
     * Set imageRequest
     *
     * @param \PP\RequestBundle\Entity\ImageRequest $imageRequest
     *
     * @return PopularRequest
     */
    public function setImageRequest(\PP\RequestBundle\Entity\ImageRequest $imageRequest = null)
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
}
