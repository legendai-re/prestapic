<?php

namespace PP\RequestBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PP\RequestBundle\Entity\Category;
/**
 * Description of LoadCategory
 *
 * @author Olivier
 */
class b_LoadUserImage implements FixtureInterface{
    
    public function load(ObjectManager $manager)
    {

        for($i=0; $i<15; $i++){
            $profilImage = new \PP\ImageBundle\Entity\Image();
            $profilImage->setUploadDir('user/profile');
            $profilImage->setAlt('profilImg');
            $profilImage->setUrl('png');               
            $manager->persist($profilImage);

        }
        for($i=0; $i<15; $i++){       
            $coverImage = new \PP\ImageBundle\Entity\Image();
            $coverImage->setUploadDir('user/cover');
            $coverImage->setAlt('coverImg');
            $coverImage->setUrl('png');               
            $manager->persist($coverImage);
        }

        $manager->flush();
    }
  
    public function getOrder()
    {
       return 2; 
    }
  
}
