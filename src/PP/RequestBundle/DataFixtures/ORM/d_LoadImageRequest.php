<?php

namespace PP\RequestBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PP\RequestBundle\Entity\ImageRequest;


use Symfony\Component\Validator\Constraints\DateTime;
/**
 * Description of LoadCategory
 *
 * @author Olivier
 */
class d_LoadImageRequest implements FixtureInterface{
    
  public function load(ObjectManager $manager)
  {                   
    
    $categoryRepository = $manager->getRepository('PPRequestBundle:Category');
    $userRepository = $manager->getRepository('PPUserBundle:User');
    $tagRepository = $manager->getRepository('PPRequestBundle:Tag');
    $categories = $categoryRepository->findAll();
    $users = $userRepository->findAll();
    $categoriesId = array();
    foreach ($categories as $id){
        array_push($categoriesId, $id->getId());
    }
    $minId = min($categoriesId);
    $maxId = max($categoriesId);
    $maxUserId = sizeof($users);
    $today = new \DateTime();
    $lastWeek = new \DateTime();        
    $lastWeek->sub(new \DateInterval('P14D'));  
    
    
    for($i=0; $i<1000; $i++){
        $nbTag = rand(2, 6);
        $currentTags = array();
          
        $maxDay = $today->format('d');
        $minDay = $lastWeek->format('d');
        $year = $today->format('Y');
        $month = $today->format('m');
        $hour = rand(1, 23);
        $min = rand(1, 59);
        $sec = rand(1, 59);
        $day = rand(1, 5);
                
        $date = date_create("$year-$month-$day $hour:$min:$sec.000000");
       
        $imageRequest = new ImageRequest();
        $imageRequest->setTitle("image request ".$i);
        $imageRequest->setRequest('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus at ligula eu magna auctor fermentum et vulputate libero. Maecenas cursus ligula sit amet mauris iaculis, quis lobortis purus ultricies. Nam vehicula orci velit, in placerat diam vulputate ut. Nunc porta eget nisl vel tincidunt. Mauris suscipit aliquet eros, in ultricies neque. Nulla facilisi. Quisque vestibulum iaculis urna, et porttitor mi rutrum eu. Phasellus porta lorem eu tortor malesuada viverra. Integer ut luctus velit. Donec metus mauris, ullamcorper elementum augue faucibus, rutrum tristique sapien. Integer pulvinar nisl sed euismod feugiat. Cras rutrum eu tellus nec varius. Donec congue vel quam ut gravida.
Aliquam finibus fringilla erat, et bibendum tortor iaculis et. Praesent id arcu interdum, ultrices enim nec, iaculis enim. Nulla feugiat purus sit amet tristique rutrum. Ut a tellus commodo nisi euismod facilisis. Donec posuere mauris et ante egestas, eu convallis nulla eleifend. Donec eget tortor eu lorem tincidunt ultrices sit amet non velit. Morbi malesuada turpis quis est sagittis porttitor.');        
        $imageRequest->setCreatedDate($date);
        $imageRequest->setCategory($categoryRepository->find(rand($minId, $maxId)));
        $imageRequest->setUpvote(rand(0, 15));
        $author = $userRepository->find(rand(1, $maxUserId)); 
        $imageRequest->setAuthor($author);
        
        for($x=0; $x<$nbTag; $x++){
            $tempTagId = rand(1, 100);
            if(!in_array($tempTagId, $currentTags)){
                $tempTag = $tagRepository->find($tempTagId);
                $imageRequest->addTag($tempTag);
            }            
            array_push($currentTags, $tempTagId);
        }
        
        $manager->persist($imageRequest);                   
    }  
    
    $manager->flush();
  }
  
}
