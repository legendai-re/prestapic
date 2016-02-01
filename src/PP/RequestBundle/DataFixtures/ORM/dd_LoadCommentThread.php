<?php

namespace PP\RequestBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PP\RequestBundle\Entity\ImageRequest;
use PP\CommentBundle\Entity\CommentThread;

use Symfony\Component\Validator\Constraints\DateTime;
/**
 * Description of LoadCategory
 *
 * @author Olivier
 */
class dd_LoadCommentThread implements FixtureInterface{
    
  public function load(ObjectManager $manager)
  {                   
                       
    $imageRequestRepository = $manager->getRepository('PPRequestBundle:ImageRequest');   
    $imageRequests = $imageRequestRepository->findAll();
    $i = 0;
    foreach($imageRequests as $imageRequest){
        if($i%100 == 0)echo " CT-->  ".$i;
        $i++;
        $commentThread = new CommentThread($imageRequest->getId());
        $imageRequest->setCommentThread($commentThread);
        $manager->persist($commentThread);
    }  
    
    $manager->flush();
  }
  
}
