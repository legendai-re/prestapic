<?php

namespace PP\PropositionBundle\Controller;

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

use PP\NotificationBundle\JsonNotificationModel\JsonNotification;
use PP\NotificationBundle\Entity\NotificationSelected;
use PP\NotificationBundle\Entity\Notification;
use PP\NotificationBundle\Constant\NotificationType;

class PropositionApiController extends Controller
{
    
    /**
     * Upvote a proposition
     *
     * @param int $propositionId
     *
     * @return JsonResponse
     */
    public function patchPropositionVoteAction($propositionId){
        
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
       
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            
            /* init repositories */
            $em = $this->getDoctrine()->getManager();
            $propositionRepository = $em->getRepository('PPPropositionBundle:Proposition');            
            $userRepository = $em->getRepository('PPUserBundle:User');            
            
            $currentUser = $this->getUser();
            $proposition = $propositionRepository->find($propositionId);
            
            if($currentUser->getId() != $proposition->getAuthor()->getId() && !$userRepository->haveLikedProposition($currentUser->getId(), $proposition->getId())){
               $proposition->addUpvote();
               $proposition->addUpvotedBy($currentUser);
               $em->persist($proposition);
               $em->flush();
               $response->setData(json_encode(array('succes'=>true, 'upvote'=>$proposition->getUpvote())));
            }else $response->setData(json_encode(array('succes'=>false)));
            
        }else $response->setData(json_encode(array('succes'=>false)));
               
        return $response;            
        
    }
    
    /**
     * Select a proposition
     *
     * @param int $imageRequestId
     * @param int $propositionId
     *
     * @return JsonResponse
     */
    public function patchRequestSelectAction(Request $request, $imageRequestId, $propositionId){
        
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            
            $currentUser = $this->getUser();
            
            /* init repositories */
            $em = $this->getDoctrine()->getManager();
            $propositionRepository = $em->getRepository('PPPropositionBundle:Proposition');            
            $userRepository = $em->getRepository('PPUserBundle:User');
            $imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');
            
            $proposition = $propositionRepository->find($propositionId);
            $imageRequest = $imageRequestRepository->find($imageRequestId);
            
            if($proposition!=null && $imageRequest!=null && $currentUser!=null && $imageRequest->getAuthor()->getId() == $currentUser->getId()){
                $proposition->setAccepted(true);                    
                $imageRequest->setClosed(true);
                $imageRequest->setAcceptedProposition($proposition); 
                
                /* create notification */  
                $propositionAuthorThread = $proposition->getAuthor()->getNotificationThread();
                $notification = new Notification(NotificationType::PROPOSITION_SELECTED);
                $propositionAuthorThread->addNotification($notification);
                $proposition->getAuthor()->incrementNotificationsNb();
                $em->persist($propositionAuthorThread);
                $em->persist($currentUser);
                $em->flush();

                $notificationSelected = new NotificationSelected($notification->getId());
                $notificationSelected->setImageRequest($imageRequest);                   
                $notificationSelected->setNotificationBase($notification);
                $em->persist($notificationSelected);
                $em->flush();
                
                 /* send notification */
                $setClickedUrl = $this->generateUrl('pp_notification_api_patch_clicked', array("id"=>$notification->getId()));
                $faye = $this->container->get('pp_notification.faye.client');                    
                $channel = '/notification/'.$propositionAuthorThread->getSlug();                
                $jsonNotication = new JsonNotification(
                            NotificationType::PROPOSITION_SELECTED,
                            false,
                            false,
                            $notification->getCreateDate(),
                            $this->container->get('pp_notification.ago')->ago($notification->getCreateDate()),
                            $this->generateUrl('pp_request_view', array('slug' => $notificationSelected->getImageRequest()->getSlug())),
                            $setClickedUrl,
                            $notificationSelected->getImageRequest()->getAuthor()->getName(),
                            $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'. $notificationSelected->getImageRequest()->getAuthor()->getProfilImage()->getWebPath("70x70"),
                            $notificationSelected->getImageRequest()->getTitle()  
                );
                $data = array('notification' => $jsonNotication);                    
                $faye->send($channel, $data);
                
                
                $response->setData(json_encode(array('succes'=>true, 'redirect'=>$this->generateUrl('pp_request_view', array('slug' => $imageRequest->getSlug())))));
            }else $response->setData(json_encode(array('succes'=>false)));
            
        }else $response->setData(json_encode(array('succes'=>false, 'message'=>'unauthorized')));
        
        return $response;
    }
    
    private function getViewHandler()
    {
        return $this->container->get('fos_rest.view_handler');
    }
}
