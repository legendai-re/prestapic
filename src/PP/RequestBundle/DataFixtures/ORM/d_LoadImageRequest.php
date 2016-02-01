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
class d_LoadImageRequest implements FixtureInterface{
    
  public function load(ObjectManager $manager)
  {                   
                
    $loremArray = ["Nam","imperdiet","ipsum","in","venenatis","convallis","leo","ante","malesuada","nisi","vel","elementum","eros","metus","vel","leo","Nullam","accumsan","interdum","arcu,","eu","porta","felis","elementum","sed","Nam","pulvinar","imperdiet","augue","luctus","lacinia","eros","Aliquam","placerat","ut","nibh","in","bibendum","Nam","eget","sem","vitae","ex","tempus","lacinia","eget","non","augue","Curabitur","odio","eros","scelerisque","in","feugiat","at","dictum","nec","nulla","Nulla","molestie","velit","eu","sapien","blandit,","non","auctor","nisl","pellentesque","Cras","id","pulvinar","orci","Mauris","nisl","lorem,","semper","sed","venenatis","ut","mollis","quis","orci","In","erat","arcu","cursus","sed","molestie","vitae,","finibus","a","quam","Praesent","ut","aliquet","tellus","Integer","interdum","dui","vitae","blandit","euismod,","quam","ex","dictum","tellus","et","eleifend","arcu","purus","eget","felis","Donec","euismod","dui","sodales","pretium","metus","vitae","porta","nulla","Donec","ullamcorper","tortor","vitae","quam","semper","semper"];
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
    
    $y = 1;
    $reuseUpvote = 0;
    for($i=0; $i<250; $i++){
        if($i%100 == 0)echo " IR-->  ".$i;
        $nameLenght = rand(3, 8);
        $name = "";
        for($x = 0; $x<$nameLenght; $x++){
            if($x == 0)$name .= ucfirst($loremArray[rand(0, sizeof($loremArray)-1)]);                
            else $name .= ' '.strtolower($loremArray[rand(0, sizeof($loremArray)-1)]);
        }
        
        if ($y > $reuseUpvote){
            $reuseUpvote = rand(1, 5);
            $upvote = rand(0, 30);           
            $y=0;
        } else $y++;
        
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
        $imageRequest->setTitle($name);
        $imageRequest->setRequest('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus at ligula eu magna auctor fermentum et vulputate libero. Maecenas cursus ligula sit amet mauris iaculis, quis lobortis purus ultricies. Nam vehicula orci velit, in placerat diam vulputate ut. Nunc porta eget nisl vel tincidunt. Mauris suscipit aliquet eros, in ultricies neque. Nulla facilisi. Quisque vestibulum iaculis urna, et porttitor mi rutrum eu. Phasellus porta lorem eu tortor malesuada viverra. Integer ut luctus velit. Donec metus mauris, ullamcorper elementum augue faucibus, rutrum tristique sapien. Integer pulvinar nisl sed euismod feugiat. Cras rutrum eu tellus nec varius. Donec congue vel quam ut gravida.
Aliquam finibus fringilla erat, et bibendum tortor iaculis et. Praesent id arcu interdum, ultrices enim nec, iaculis enim. Nulla feugiat purus sit amet tristique rutrum. Ut a tellus commodo nisi euismod facilisis. Donec posuere mauris et ante egestas, eu convallis nulla eleifend. Donec eget tortor eu lorem tincidunt ultrices sit amet non velit. Morbi malesuada turpis quis est sagittis porttitor.');        
        $imageRequest->setCreatedDate($date);
        $imageRequest->setCategory($categoryRepository->find(rand($minId, $maxId)));
        $imageRequest->setUpvote($upvote);
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
       
        
        /*$commentThread = new CommentThread($imageRequest->getId());
        $imageRequest->setCommentThread($commentThread);
        $manager->persist($commentThread);
        $manager->flush();*/
    }  
    
    $manager->flush();
  }
  
}
