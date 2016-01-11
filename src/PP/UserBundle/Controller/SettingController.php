<?php

namespace PP\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\Common\Collections\ArrayCollection;
use PP\RequestBundle\Constant\Constants;
use PP\RequestBundle\Entity\ImageRequest;
use PP\UserBundle\Entity\User;
use PP\RequestBundle\Form\Type\ImageRequestType;
use PP\UserBundle\Form\Type\EditProfileFormType;

class SettingController extends Controller 
{
    public function indexAction(Request $request, $slug)
    {
        /* get session and currentUser*/
        $session = $this->getRequest()->getSession();
        $currentUser = $this->getUser();
        
        /* init repositories */
        $em = $this->getDoctrine()->getManager();        
        $userRepository = $em->getRepository('PPUserBundle:User');                                    
        $pageProfile = $userRepository->getUserBySlug($slug);
        
        if($currentUser == null || $pageProfile->getId() != $currentUser->getId()){
            throw new NotFoundHttpException("Nothing here");
        }
        
        /////////////////////
        /* change password */
        
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($currentUser, $request);
        $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }        

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.change_password.form.factory');

        $changePasswordForm = $formFactory->createForm();                
        
        $changePasswordForm->handleRequest($request);
               
        if ($changePasswordForm->isValid()) {
           
            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');
            
            $event = new FormEvent($changePasswordForm, $request);
            $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_SUCCESS, $event);
                        
            $currentUser->setPlainPassword($changePasswordForm->getData()->getPlainPassword());
            $userManager->updatePassword($currentUser);
            $userManager->updateUser($currentUser);                                                                

            $url = $this->generateUrl('pp_user_setting', array('slug'=>$currentUser->getSlug()));
            $response = new RedirectResponse($url);
        }        
        /////////////////////
                
        /* enable notification */
        $formNotifEnable = $this->get('form.factory')->createNamedBuilder('pp_user_api_settings_patch_notification_mode_form', 'form', array(), array())
                ->setAction($this->generateUrl('pp_user_api_settings_patch_notification_mode', array(), true))
                ->getForm();                        
        
        return $this->render('PPUserBundle:Settings:settings.html.twig', array(
            "user" => $currentUser,
            "changePasswordForm" => $changePasswordForm->createView(),
            "formNotifEnable" => $formNotifEnable->createView()
        ));
    }
    
  
}
