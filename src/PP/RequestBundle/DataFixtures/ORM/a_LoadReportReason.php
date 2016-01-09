<?php

namespace PP\RequestBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PP\ReportBundle\Entity\ReportReason;
/**
 * Description of LoadCategory
 *
 * @author Olivier
 */
class a_LoadReportReason implements FixtureInterface{
    
  public function load(ObjectManager $manager)
  {
    $lorem = "Donec lobortis mi sed diam sagittis mollis. Maecenas ligula nibh, ornare at vestibulum sed, ultricies non mauris. Nulla facilisi. Mauris pulvinar bibendum maximus.";
    $names = array(
        'Problem Content',
        'Misplaced Deviation',
        'Permission Issues',
        'My Intellectual Property',
        'Explicit Pornography',
        'Sexualized Minor',
        'Malware',
        '"Warez"'        
    );

    foreach ($names as $name) {
     
      $reportResaon = new ReportReason();
      $reportResaon->setName($name);
      $reportResaon->setDetails($lorem);
      // On la persiste
      $manager->persist($reportResaon);
    }
    
    $manager->flush();
  }
   
  
}
