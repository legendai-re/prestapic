<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PP\UserBundle\Controller;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Symfony\Component\Form\FormError;

use PP\UserBundle\Constant\UserConstants;
use PP\UserBundle\Form\Type\RegistrationFormType;
/**
 * Controller managing the registration
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class RegistrationController extends Controller
{
    public function myRegisterAction(Request $request)
    {
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }
        
        
        $form = $this->get('form.factory')->create(new  RegistrationFormType(\PP\UserBundle\Entity\User::class), $user, array(            
            'action' => $this->generateUrl('pp_user_register'),
            'method' => 'POST',
        ));
        
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $userRepository = $em->getRepository('PPUserBundle:User');
            
            $haveError = false;
            if($userRepository->findOneBy(array("email"=>$form->getData()->getEmail())) != null){
                $form->get('email')->addError(new FormError('email already used'));
                $haveError = true;
            }
            
            if($userRepository->findOneBy(array("username"=>$form->getData()->getUsername())) != null){
                $form->get('username')->addError(new FormError('username already used'));
                $haveError = true;
            }
            
            $username = $form->getData()->getUsername();
            $arrayUserName = str_split($username);
            $allowedChar = UserConstants::getAllowedChar();
            foreach ($arrayUserName as $character){
                if(!in_array($character, $allowedChar)){
                    $form->get('username')->addError(new FormError('invalid username'));
                    $haveError = true;
                    break;
                }
            }           
            
            if(!$haveError){
                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                ////////////////
                /* my setting */            
                $imgName = rand(1, 7);        
                copy(__DIR__.'/../../../../web/Resources/public/images/profile/avatar_'.$imgName.'.jpg',  __DIR__.'/../../../../web/uploads/img/user/profile/original/new.jpg');
                $profilImage = new \PP\ImageBundle\Entity\Image();
                $profilImage->setUploadDir('user/profile');
                $profilImage->setAlt('profilImg');
                $profilImage->setUrl('png');        
                $imgsize = getimagesize(__DIR__.'/../../../../web/uploads/img/user/profile/original/new.jpg');
                $mime = $imgsize['mime'];
                $file = new UploadedFile(__DIR__.'/../../../../web/uploads/img/user/profile/original/new.jpg', "new", $mime, $imgsize, 0, true );
                $profilImage->setFile($file);           

                $user->setProfilImage($profilImage);            
                $user->setRoles(array('ROLE_USER'));
                $user->setName($form->getData()->getUsername());
                $user->setSlug($form->getData()->getUsername());
                $user->setEnabled(true);
                $user->setEmailConfirmed(false);
                ////////////////                                  
            
                $userManager->updateUser($user);

                if(in_array($user->getSlug(), array("users", "_profiler"))){                
                    $user = $userRepository->find($user->getId());
                    $user->setSlug($user->getSlug()."-nope");
                    $em->persist($user);
                    $em->flush();
                }                    

                $url = $this->generateUrl('pp_user_profile', array('slug'=>$user->getSlug()));
                $response = new RedirectResponse($url);                                       
                
                $newEvent = $event->getResponse();
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $newEvent));
                
                return $response;
            }
        }

        return $this->render('FOSUserBundle:Registration:register.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Tell the user to check his email provider
     */
    public function checkEmailAction()
    {
        $email = $this->get('session')->get('fos_user_send_confirmation_email/email');
        $this->get('session')->remove('fos_user_send_confirmation_email/email');
        $user = $this->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->render('FOSUserBundle:Registration:checkEmail.html.twig', array(
            'user' => $user,
        ));
    }

    /**
     * Receive the confirmation token from user email provider, login the user
     */
    public function confirmAction(Request $request, $token)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $user->setEmailConfirmed(true);
        
        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

        $userManager->updateUser($user);

        if (null === $response = $event->getResponse()) {
            $url = $this->generateUrl('pp_user_profile', array('slug'=>$user->getSlug()));
            $response = new RedirectResponse($url);
        }

        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));

        return $response;
    }

    /**
     * Tell the user his account is now confirmed
     */
    public function confirmedAction()
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render('FOSUserBundle:Registration:confirmed.html.twig', array(
            'user' => $user,
            'targetUrl' => $this->getTargetUrlFromSession(),
        ));
    }

    private function getTargetUrlFromSession()
    {
        // Set the SecurityContext for Symfony <2.6
        if (interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')) {
            $tokenStorage = $this->get('security.token_storage');
        } else {
            $tokenStorage = $this->get('security.authorization_checker');
        }

        $key = sprintf('_security.%s.target_path', $tokenStorage->getToken()->getProviderKey());

        if ($this->get('session')->has($key)) {
            return $this->get('session')->get($key);
        }
    }
}
