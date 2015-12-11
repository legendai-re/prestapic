<?php

namespace PP\PropositionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use PP\NotificationBundle\JsonNotificationModel\JsonNotification;
use PP\NotificationBundle\Entity\NotificationSelected;
use PP\NotificationBundle\Entity\Notification;
use PP\NotificationBundle\Constant\NotificationType;

use PP\PropositionBundle\JsonModel\JsonPropositionPopupModel;
use PP\PropositionBundle\JsonModel\JsonUserModel;
use PP\PropositionBundle\JsonModel\JsonIRPopupModel;

class PropositionApiController extends Controller
{
    
    
    public function getPropositionAction(Request $request){
        $propositionId = $request->get("id");
        
        $response = new Response();
               
        /* init repositories */
        $em = $this->getDoctrine()->getManager();
        $propositionRepository = $em->getRepository('PPPropositionBundle:Proposition');                        
        $imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');
        $userRepository = $em->getRepository('PPUserBundle:User');
                
        $proposition = $propositionRepository->find($propositionId);
        $imageRequest = $proposition->getImageRequest();        
        
        $irAuthor = $imageRequest->getAuthor();
        $propAuthor = $proposition->getAuthor();
        $isProAuthor = false;
        $isIrAuthor = false;
        
        if ($this->get('security.context')->isGranted('ROLE_USER')) {            
            $currentUser = $this->getUser();
            if($currentUser != null && $currentUser->getId() == $propAuthor->getId()){
                $isProAuthor = true;
            }
            if($currentUser != null && $currentUser->getId() == $irAuthor->getId()){
                $isIrAuthor = true;
            }
        }
        
        $canUpvoteProposition = false;
            
        if($currentUser!=null && $currentUser->getId() != $proposition->getAuthor()->getId() && !$userRepository->haveLikedProposition($currentUser->getId(), $proposition->getId())){
            $canUpvoteProposition = true;
        }
        
        $jsonProposition = new JsonPropositionPopupModel(
                                        $proposition->getId(),
                                        $proposition->getTitle(),
                                        $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$proposition->getImage()->getWebPath('original'),
                                        new JsonUserModel(
                                                $propAuthor->getID(), 
                                                $propAuthor->getName(),
                                                $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$propAuthor->getProfilImage()->getWebPath('70x70'),
                                                $this->generateUrl("pp_user_profile", array("slug"=>$propAuthor->getSlug()), true),
                                                $isProAuthor
                                        ),
                                        $proposition->getUpvote(),
                                        $proposition->getAccepted(),
                                        new JsonIRPopupModel(
                                                $imageRequest->getId(),
                                                $imageRequest->getTitle(),
                                                new JsonUserModel(
                                                    $irAuthor->getID(), 
                                                    $irAuthor->getName(),
                                                    $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$irAuthor->getProfilImage()->getWebPath('70x70'),
                                                    $this->generateUrl("pp_user_profile", array("slug"=>$irAuthor->getSlug()), true),
                                                    $isIrAuthor
                                                ),
                                                $this->generateUrl("pp_request_view", array("slug"=>$imageRequest->getSlug()), true),
                                                $imageRequest->getCreatedDate()
                                        ),
                                        $canUpvoteProposition,
                                        $proposition->getCreatedDate()
        );
        
        echo json_encode($jsonProposition);
        
        return $response;    
    }
    
    /**
     * Upvote a proposition
     *
     * @param int $propositionId
     *
     * @return JsonResponse
     */
    public function patchPropositionVoteAction(Request $request){
        
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        
        $propositionId = $request->get("id");
        
        if ($this->get('security.context')->isGranted('ROLE_USER') && $propositionId!=null) {
            
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
            $imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');
            
            $proposition = $propositionRepository->find($propositionId);
            $imageRequest = $imageRequestRepository->find($imageRequestId);
            
            if($proposition!=null && $imageRequest!=null && $currentUser!=null && $imageRequest->getAuthor()->getId() == $currentUser->getId() && !$imageRequest->getClosed()){
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
                            $notificationSelected->getImageRequest()->getAuthor()->getId(),
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
    
    public function patchDisableAction(){
        
        $response = new Response();
        
        $propositionId = $request->get("id");
        /* init repositories */
        $em = $this->getDoctrine()->getManager();
        $propositionRepository = $em->getRepository('PPPropositionBundle:Proposition');        
        $currentUser = $this->getUser();
        $proposition = $propositionRepository->find($propositionId);
            
        if (($this->get('security.context')->isGranted('ROLE_USER') && $proposition->getAuthor()->getId() == $currentUser->getId()) || $this->get('security.context')->isGranted('ROLE_MODERATOR')) {
            
           $proposition->disable();                                    
        
        }else{ $response->setStatusCode(Response::HTTP_FORBIDDEN);}
        
        return $response;
    }
    
    private function getViewHandler()
    {
        return $this->container->get('fos_rest.view_handler');
    }
}
