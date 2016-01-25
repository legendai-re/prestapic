<?php

namespace PP\RequestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Tag
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PP\RequestBundle\Entity\TagRepository")
 */
class Tag
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
    * @ORM\ManyToMany(targetEntity="PP\RequestBundle\Entity\ImageRequest", mappedBy="tags")
    */
    private $imageRequests;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\Length(
     *      min = 1,
     *      max = 25,
     *      minMessage = "Your tag must be at least {{ limit }} characters long",
     *      maxMessage = "Your tag title cannot be longer than {{ limit }} characters"
     * )
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="usedNb", type="integer")
     */
    private $usedNb;


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
     * Set name
     *
     * @param string $name
     *
     * @return Tag
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
     * Set usedNb
     *
     * @param integer $usedNb
     *
     * @return Tag
     */
    public function setUsedNb($usedNb)
    {
        $this->usedNb = $usedNb;

        return $this;
    }

    /**
     * Get usedNb
     *
     * @return integer
     */
    public function getUsedNb()
    {
        return $this->usedNb;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->imageRequests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->usedNb = 0;
    }

    /**
     * Add imageRequest
     *
     * @param \PP\RequestBundle\Entity\ImageRequest $imageRequest
     *
     * @return Tag
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
    
    public function addUsedNb(){
        $x = $this->usedNb;
        $this->usedNb = $x+1;
    }
    
    public function removeUsedNb(){
        $x = $this->usedNb;
        $this->usedNb = $x-1;
    }
}
