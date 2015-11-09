<?php

namespace PP\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NotificationController extends Controller
{
    public function showNotificationAction()
    {
        $notificationList = array();
        
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            
            $em = $this->getDoctrine()->getManager();                                                
            $currentUser = $this->getUser();
            
            if($currentUser != null){
                $notificationList = $currentUser->getNotificationThread()->getNotificationsFollow();                
            }
            
        }
        
        return $this->render('PPNotificationBundle:notification:notificationList.html.twig', array(
            'notificationList' => $notificationList
        ));
    }
}
