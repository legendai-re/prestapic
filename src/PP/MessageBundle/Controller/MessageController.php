<?php

namespace PP\MessageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MessageController extends Controller
{
    
    public function initMessageBoxAction()
    {        
        $getInboxForm = null;
        
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $currentUser = $this->getUser();

            /* create loadPage form */        
            $getInboxForm = $this->get('form.factory')->createNamedBuilder('pp_message_api_get_inbox_form', 'form', array(), array())         
               ->setAction($this->generateUrl('pp_message_api_get_inbox', array("userId"=>$currentUser->getId()), true))
               ->getForm()
               ->createView();
        }
        
        return $this->render('PPMessageBundle:Message:messageBox.html.twig', array(
            'getInboxForm' => $getInboxForm
        ));
    }
    
}
