<?php

namespace PP\RequestBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PP\UserBundle\Entity\User;
/**
 * Description of LoadCategory
 *
 * @author Olivier
 */
class c_LoadUser implements FixtureInterface{
    
    public function load(ObjectManager $em)
    {
        
        $namesList = ['Admin', 'Olivier', 'Paul', 'Pham', 'Reid', 'Briggs', 'Ibarra', 'Hunter', 'Daniels', 'Barry', 'Norman', 'Hurley', 'Leblanc', 'Anthony', 'Blackwell', 'Palmer', 'Guzman', 'Johnston', 'Hanson', 'Chase', 'Nielsen', 'Bray', 'Holden', 'Evans', 'Drake', 'Espinoza', 'Curry', 'Hodge', 'Stanton', 'Peterson', 'Gilmore', 'Keith', 'Clements'];
        $emailList = array();
        foreach ($namesList as $name){
            array_push($emailList, strtolower($name).'@gmail.com');
        }

        for($i=0; $i<15; $i++){
                                
            $imageRepository = $em->getRepository('PPImageBundle:Image');
            $profilId = rand (1, 4);
            $profileImg = $imageRepository->find($i+1);
            $coverImg = $imageRepository->find($i+15+1);
            if($profileImg !=null && $coverImg!=null){
                $user = new User();
                $user->setName($namesList[$i]);
                $user->setProfilImage($profileImg);
                $user->setCoverImage($coverImg);
                if(strcmp($namesList[$i], 'Admin') == 0){
                    $user->setRoles(array('ROLE_MODERATOR'));
                }
                else $user->setRoles(array('ROLE_USER'));
                $user->setPlainPassword(strtolower($namesList[$i]));
                $user->setEnabled(true);
                $user->setEmail($emailList[$i]);
                $user->setUsername($emailList[$i]);         
                $em->persist($user);
            }
        
        }
        
        
        $em->flush();
    }
      
  
}
