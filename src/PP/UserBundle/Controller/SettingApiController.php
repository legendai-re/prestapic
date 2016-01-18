<?php

namespace PP\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class SettingApiController extends Controller
{
    public function patchNotificationModeAction(Request $request){
        
        $response = new Response();        
        
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $currentUser= $this->getUser();
            if($currentUser != null){
                $em = $this->getDoctrine()->getManager();
                
                if($currentUser->getNotificationEnabled()){
                    $currentUser->setNotificationEnabled(false);
                }else{
                    $currentUser->setNotificationEnabled(true);
                }
                      
                $em->persist($currentUser);
                $em->flush();
                $response->setStatusCode(Response::HTTP_OK);
            }else $response->setStatusCode(Response::HTTP_FORBIDDEN);            
        }else $response->setStatusCode(Response::HTTP_FORBIDDEN);        
        return $response;;        
    }
    
    
    private function getViewHandler()
    {
        return $this->container->get('fos_rest.view_handler');
    }
}
