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
        'Abstract',
        'Macro',
        'Animals',
        'Nature',
        'Black&White',
        'Celebrities',
        'People',
        'Architecture',        
        'Commercial',
        'Sport',
        'Concert',       
        'Family',
        'Street',
        'Fashion',
        'Transportation',
        'Film',
        'Travel',        
        'Underwater',
        'Food',
        'Urban',
        'Journalism',
        'Wedding',
        'Landscapes',
        'Uncategorize'
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
