<?php

namespace PP\MessageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MessageController extends Controller
{
    
    public function initMessageBoxAction()
    {                
        $getCurrentUserForm = null;
        if ($this->get('security.context')->isGranted('ROLE_USER')) {                        
             /* create loadPage form */        
            $getCurrentUserForm = $this->get('form.factory')->createNamedBuilder('pp_message_api_get_current_user_form', 'form', array(), array())         
               ->setAction($this->generateUrl('pp_message_api_get_current_user', array(), true))
               ->getForm()
               ->createView();
        }
        
        return $this->render('PPMessageBundle:Message:messageBox.html.twig', array(            
            'getCurrentUserForm' => $getCurrentUserForm
        ));
    }
    
}
