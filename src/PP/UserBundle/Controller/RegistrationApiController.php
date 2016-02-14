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

use FOS\RestBundle\View\View;


use PP\UserBundle\Constant\UserConstants;
use PP\UserBundle\Form\Type\RegistrationFormType;
/**
 * Controller managing the registration
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class RegistrationApiController extends Controller
{
    public function getRegisterFormAction(Request $request)
    {
        
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->createUser();
        $user->setEnabled(true);
        
        $form = $this->get('form.factory')->create(new  RegistrationFormType(\PP\UserBundle\Entity\User::class), $user, array(            
            'action' => $this->generateUrl('pp_user_register'),
            'method' => 'POST',
        ));

        /*return $this->render('FOSUserBundle:Registration:register.html.twig', array(
            'form' => $form->createView(),
        ));*/
        
        $view = View::create()
            ->setData(array( 
                'form' => $form->createView()                
            ))
            ->setTemplate('FOSUserBundle:Registration:register_content.html.twig');

        return $this->getViewHandler()->handle($view);
        
    }
    
    private function getViewHandler()
    {
        return $this->container->get('fos_rest.view_handler');
    }
    
}
