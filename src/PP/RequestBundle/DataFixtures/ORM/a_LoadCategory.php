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
class a_LoadCategory implements FixtureInterface{
    
  public function load(ObjectManager $manager)
  {
    
    $names = array(
      'Black & White',
      'Landscape',
      'Portrait',
      'Nature',
      'Animals'
    );

    foreach ($names as $name) {
     
      $category = new Category();
      $category->setName($name);

      // On la persiste
      $manager->persist($category);
    }
    
    $manager->flush();
  }
   
  
}
