<?php

namespace PP\RequestBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PP\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
/**
 * Description of LoadCategory
 *
 * @author Olivier
 */
class c_LoadUser implements FixtureInterface{
    
    public function load(ObjectManager $em)
    {
        
        $namesList = ['OlivierCoue', 'AlexandreJolly', 'Paul', 'Reid', 'Briggs', 'Ibarra', 'Hunter', 'Daniels', 'Barry', 'Norman', 'Hurley', 'Leblanc', 'Anthony', 'Blackwell', 'Palmer', 'Guzman', 'Johnston', 'Hanson', 'Chase', 'Nielsen', 'Bray', 'Holden', 'Evans', 'Drake', 'Espinoza', 'Curry', 'Hodge', 'Stanton', 'Peterson', 'Gilmore', 'Keith', 'Clements'];
        $emailList = array();
        foreach ($namesList as $name){
            array_push($emailList, strtolower($name).'@gmail.com');
        }

        for($i=0; $i<1000; $i++){
            if($i%100 == 0)echo " -->  ".$i; 
            $imgName = rand(1, 7);        
            copy(__DIR__.'/../../../../../web/Resources/public/images/profile/avatar_'.$imgName.'.jpg',  __DIR__.'/../../../../../web/uploads/img/user/profile/original/new'.$i.'.jpg');
            $profilImage = new \PP\ImageBundle\Entity\Image();
            $profilImage->setUploadDir('user/profile');
            $profilImage->setAlt('profilImg');
            $profilImage->setUrl('png');        
            $imgsize = getimagesize(__DIR__.'/../../../../../web/uploads/img/user/profile/original/new'.$i.'.jpg');
            $mime = $imgsize['mime'];
            $file = new UploadedFile(__DIR__.'/../../../../../web/uploads/img/user/profile/original/new'.$i.'.jpg', "new".$i, $mime, $imgsize, 0, true );
            $profilImage->setFile($file);

            $user = new User();
            //$user->setName($namesList[$i]);
            $user->setName("user_".$i);
            $user->setProfilImage($profilImage);
            $user->setRoles(array('ROLE_USER'));
            //$user->setPlainPassword(strtolower($namesList[$i]));
            $user->setPlainPassword("user_".$i);
            $user->setEnabled(true);         
            //$user->setEmail($emailList[$i]);
            $user->setEmail("user_".$i."@gmail.com");
            //$user->setUsername($namesList[$i]);
            $user->setUsername("user_".$i);
            $user->setEmailConfirmed(true);
            //$user->setSlug($namesList[$i]);
            $user->setSlug("user_".$i);
            
            /*if(strcmp($namesList[$i], 'AlexandreJolly') == 0 || strcmp($namesList[$i], 'OlivierCoue') == 0){
                $user->addRole('ROLE_ADMIN');
                if(strcmp($namesList[$i], 'AlexandreJolly') == 0){
                    $user->setEmail("accounts@alexandrejolly.com");
                    $user->setUsername("AlexandreJolly");
                    $user->setDescription("Student at MMI Bordeaux and passionate, everyday I learn how to design better experiences for people.");
                    $user->setContact("www.alexandrejolly.com");
                    $user->setPlainPassword("alexandre");
                }
                if(strcmp($namesList[$i], 'OlivierCoue') == 0){
                    $user->setEmail("olivier28.coue@gmail.com");
                    $user->setUsername("OlivierCoue");
                    $user->setDescription("Hi, my name is Olivier. I am passionate about development of web, mobile or software applications.");
                    $user->setContact("oliviercoue.com");
                    $user->setPlainPassword("olivier");
                } 
            }     */       
            $em->persist($user);                    
        }                
        $em->flush();
    }
      
  
}
