<?php

namespace PP\RequestBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PP\PropositionBundle\Entity\Proposition;
use PP\ImageBundle\Entity\Image;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\File\UploadedFile;
/**
 * Description of LoadCategory
 *
 * @author Olivier
 */
class e_LoadProposition implements FixtureInterface{
    
  public function load(ObjectManager $manager)
  {
              
    
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
    
    $reuseTime = 0;
    $y = 1;
    
    ini_set('memory_limit', '-1');
    for($i=1; $i<5000; $i++){
        
        if ($y > $reuseTime){
            $irId = rand(1, $maxIR);
            $reuseTime = rand(1, 25);
            $imageRequest = $imageRequestRepository->find($irId);
            $y=0;
        } else $y++;
        
        $imgName = rand(31, 97);        
        copy(__DIR__.'/../../../../../web/Resources/public/images/proposition/'.$imgName.'.jpeg', __DIR__.'/../../../../../web/uploads/img/proposition/original/new'.$i.'.jpeg');
        $profilImage = new \PP\ImageBundle\Entity\Image();
        $profilImage->setUploadDir('proposition');
        $profilImage->setAlt('profilImg');
        $profilImage->setUrl('jpeg');        
        $imgsize = getimagesize(__DIR__.'/../../../../../web/uploads/img/proposition/original/new'.$i.'.jpeg');
        $mime = $imgsize['mime'];
        $file = new UploadedFile(__DIR__.'/../../../../../web/uploads/img/proposition/original/new'.$i.'.jpeg', "new'.$i.'", $mime, $imgsize, 0, true );
        $profilImage->setFile($file);                                                 
        
        
        $maxDay = $today->format('d');
        $minDay = $lastWeek->format('d');
        $year = $today->format('Y');
        $month = $today->format('m');
        $hour = rand(1, 23);
        $min = rand(1, 59);
        $sec = rand(1, 59);
        $day = rand($minDay, $maxDay);                
        $date = date_create("$year-$month-$day $hour:$min:$sec.000000");
        
        //$imageId = rand (9, 29);
        
        //$image = $imageRepository->find($i);
        
        $author = $userRepository->find(rand(1, $maxUserId)); 
        
        while($imageRequest->getAuthor()->getId() == $author->getId()){           
            $author = $userRepository->find(rand(1, $maxUserId)); 
        }
        
        $proposition = new Proposition();
        $proposition->setTitle("proposition $i");
        $proposition->setCreatedDate($date);
        
        $proposition->setAuthor($author);
        $proposition->setImage($profilImage);
        $proposition->setImageRequest($imageRequest);
                
        $imageRequest->addProposition($proposition);
        
        $manager->persist($proposition);
        $manager->persist($profilImage);
        
    }   
    
    $manager->flush();
  }
   
  
}
