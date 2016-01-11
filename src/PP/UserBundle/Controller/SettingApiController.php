<?php

namespace PP\UserBundle\Controller;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

use PP\PropositionBundle\Form\Type\PropositionType;
use PP\PropositionBundle\Entity\Proposition;
use PP\RequestBundle\Constant\Constants;

use PP\UserBundle\Form\Type\EditProfileFormType;

use PP\NotificationBundle\Entity\Notification;
use PP\NotificationBundle\Entity\NotificationFollow;
use PP\NotificationBundle\Constant\NotificationType;
use PP\NotificationBundle\JsonNotificationModel\JsonNotification;

use PP\MessageBundle\JsonModel\JsonUserModel;

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
