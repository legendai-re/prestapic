<?php

namespace PP\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ActiveUser
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\UserBundle\Entity\ActiveUserRepository")
 */
class ActiveUser
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
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime", nullable=true)
     */
    private $createdDate;
    
    /**
    * @ORM\ManyToOne(targetEntity="PP\UserBundle\Entity\User", cascade={"persist"})
    * @Assert\Valid()
    */
    private $user;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="contributionNb", type="integer")
     */
    private $contributionNb;
    
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
     * Set user
     *
     * @param \PP\UserBundle\Entity\User $user
     *
     * @return ActiveUser
     */
    public function setUser(\PP\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \PP\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return ActiveUser
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
     * Set contributionNb
     *
     * @param integer $contributionNb
     *
     * @return ActiveUser
     */
    public function setContributionNb($contributionNb)
    {
        $this->contributionNb = $contributionNb;

        return $this;
    }

    /**
     * Get contributionNb
     *
     * @return integer
     */
    public function getContributionNb()
    {
        return $this->contributionNb;
    }
}
