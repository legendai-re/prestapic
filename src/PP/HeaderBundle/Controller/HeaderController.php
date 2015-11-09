<?php

namespace PP\HeaderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HeaderController extends Controller
{
    public function showHeaderAction()
    {   
        $currentUser = $this->getUser();                 
         
        $threadForm = $this->get('form.factory')->createNamedBuilder('pp_notification_api_get_thread_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_notification_api_get_thread', array(), true))
            ->getForm();
         
         $setNotificationViewedForm = $this->get('form.factory')->createNamedBuilder('pp_notification_api_patch_viewed_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_notification_api_patch_viewed', array(), true))
            ->getForm();
        
        $notificationForm = $this->get('form.factory')->createNamedBuilder('pp_notification_api_get_notification_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_notification_api_get_notification', array('page'=>1), true))
            ->getForm();
        
        return $this->render('PPHeaderBundle:header:header.html.twig', array(
            'notificationForm' => $notificationForm->createView(),
            'threadForm' => $threadForm->createView(),
            'setNotificationViewedForm' => $setNotificationViewedForm->createView(),
            'currentUser' => $currentUser
        ));
    }
}
