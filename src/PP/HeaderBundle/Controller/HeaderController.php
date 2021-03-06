<?php

namespace PP\HeaderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HeaderController extends Controller
{
    public function showHeaderAction()
    {   
        $currentUser = $this->getUser();
        
        /* create get new request form form */
        $getRequestForm = $this->get('form.factory')->createNamedBuilder('pp_request_api_get_request_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_request_api_get_request_form', array(), true))
            ->getForm();
        
        $threadForm = $this->get('form.factory')->createNamedBuilder('pp_notification_api_get_thread_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_notification_api_get_thread', array(), true))
            ->getForm();
         
        $setNotificationViewedForm = $this->get('form.factory')->createNamedBuilder('pp_notification_api_patch_viewed_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_notification_api_patch_viewed', array(), true))
            ->getForm();
        
        $notificationForm = $this->get('form.factory')->createNamedBuilder('pp_notification_api_get_notification_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_notification_api_get_notification', array('page'=>1), true))
            ->getForm();
        
        $patchInMessageForm = $this->get('form.factory')->createNamedBuilder('pp_user_api_patch_is_in_message_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_user_api_patch_is_in_message', array(), true))
            ->getForm();
        
        return $this->render('PPHeaderBundle:header:header.html.twig', array(
            'getRequestForm' => $getRequestForm->createView(),
            'notificationForm' => $notificationForm->createView(),
            'threadForm' => $threadForm->createView(),
            'setNotificationViewedForm' => $setNotificationViewedForm->createView(),
            'patchInMessageForm' => $patchInMessageForm->createView(),
            'currentUser' => $currentUser
        ));
    }
    
    public function showFiltersAction()
    {
        $em = $this->getDoctrine()->getManager();                        
        $categotyRepository = $em->getRepository('PPRequestBundle:Category');
        
        $categories = $categotyRepository->findAll();        
                
        return $this->render('PPHeaderBundle:header:filters.html.twig', array(
            'categories' => $categories,            
        ));
    }
}
