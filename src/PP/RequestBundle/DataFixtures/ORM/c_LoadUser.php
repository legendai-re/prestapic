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
        
        $namesList = ['Admin', 'Olivier', 'Paul', 'Alexandre', 'Reid', 'Briggs', 'Ibarra', 'Hunter', 'Daniels', 'Barry', 'Norman', 'Hurley', 'Leblanc', 'Anthony', 'Blackwell', 'Palmer', 'Guzman', 'Johnston', 'Hanson', 'Chase', 'Nielsen', 'Bray', 'Holden', 'Evans', 'Drake', 'Espinoza', 'Curry', 'Hodge', 'Stanton', 'Peterson', 'Gilmore', 'Keith', 'Clements'];
        $emailList = array();
        foreach ($namesList as $name){
            array_push($emailList, strtolower($name).'@gmail.com');
        }

        for($i=0; $i<15; $i++){
             
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
            $user->setName($namesList[$i]);
            $user->setProfilImage($profilImage);
            if(strcmp($namesList[$i], 'Admin') == 0 || strcmp($namesList[$i], 'Olivier') == 0){
                $user->addRole('ROLE_ADMIN');
            }
            else $user->setRoles(array('ROLE_USER'));
            $user->setPlainPassword(strtolower($namesList[$i]));
            $user->setEnabled(true);
            $user->setEmail($emailList[$i]);
            $user->setUsername($emailList[$i]);
            $em->persist($user);
            
        
        }
        
        
        $em->flush();
    }
      
  
}
