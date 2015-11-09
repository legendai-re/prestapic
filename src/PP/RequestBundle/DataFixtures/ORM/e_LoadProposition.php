<?php

namespace PP\RequestBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PP\PropositionBundle\Entity\Proposition;
use PP\ImageBundle\Entity\Image;
use Symfony\Component\Validator\Constraints\DateTime;
/**
 * Description of LoadCategory
 *
 * @author Olivier
 */
class e_LoadProposition implements FixtureInterface{
    
  public function load(ObjectManager $manager)
  {
    
      
    for($x = 31; $x<88; $x++){
        $image = new Image();
        $image->setAlt('image'.$x);
        $image->setUrl('jpeg');
        $image->setUploadDir('proposition');
        $manager->persist($image);                
    }
    $manager->flush();
    
    $imageRepository = $manager->getRepository('PPImageBundle:Image');
    $userRepository = $manager->getRepository('PPUserBundle:User');
    $imageRequestRepository = $manager->getRepository('PPRequestBundle:ImageRequest');
    $imageRequests = $imageRequestRepository->findAll();
    $users = $userRepository->findAll();
    $maxIR = sizeof($imageRequests);
    $maxUserId = sizeof($users);
    $today = new \DateTime();
    $lastWeek = new \DateTime();        
    $lastWeek->sub(new \DateInterval('P14D'));
    
    for($i=31; $i<88; $i++){
        
        $maxDay = $today->format('d');
        $minDay = $lastWeek->format('d');
        $year = $today->format('Y');
        $month = $today->format('m');
        $hour = rand(1, 23);
        $min = rand(1, 59);
        $sec = rand(1, 59);
        $day = rand($minDay, $maxDay);                
        $date = date_create("$year-$month-$day $hour:$min:$sec.000000");
        
        $imageId = rand (9, 29);
        $irId = rand(1, $maxIR);
        $image = $imageRepository->find($i);
        $imageRequest = $imageRequestRepository->find($irId);
        $author = $userRepository->find(rand(1, $maxUserId)); 
        
        while($imageRequest->getAuthor()->getId() == $author->getId()){           
            $author = $userRepository->find(rand(1, $maxUserId)); 
        }
        
        $proposition = new Proposition();
        $proposition->setTitle("propsition $i");
        $proposition->setCreatedDate($date);
        
        $proposition->setAuthor($author);
        $proposition->setImage($image);
        $proposition->setImageRequest($imageRequest);
                
        $imageRequest->addProposition($proposition);
        
        $manager->persist($proposition);
    }   
    
    $manager->flush();
  }
   
  
}
