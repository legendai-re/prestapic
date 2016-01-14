<?php

/**
 * This file is part of the FOSCommentBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PP\CommentBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use PP\CommentBundle\Entity\Comment;
use PP\CommentBundle\JsonModel\JsonCommentModel;
use PP\CommentBundle\JsonModel\JsonCommentThreadModel;

use PP\UserBundle\JsonModel\JsonUserModel;

use PP\NotificationBundle\Entity\Notification;
use PP\NotificationBundle\Entity\NotificationComment;
use PP\NotificationBundle\Constant\NotificationType;
use PP\NotificationBundle\JsonNotificationModel\JsonNotification;

class CommentApiController extends Controller
{

    public function getCommentsAction(Request $request){
        $response = new Response(); 
         
        $em = $this->getDoctrine()->getManager();        
        $imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest'); 
        $commentRepository = $em->getRepository('PPCommentBundle:Comment');
        
        $requestId = $request->get("requestId");
        $page = $request->get("page");
        if($requestId!=null && $page!=null){
            $imageRequest = $imageRequestRepository->find($requestId);
            $commentThread = $imageRequest->getCommentThread();
            $comments = $commentRepository->getComments($requestId, 4, $page);
            
            $jsonComments = array();
            
            foreach($comments as $comment){                
                array_push($jsonComments, new JsonCommentModel(
                                                    $comment->getId(),
                                                    $comment->getContent(),
                                                    new JsonUserModel(
                                                            $comment->getAuthor()->getId(),
                                                            $comment->getAuthor()->getName(),
                                                            $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'. $comment->getAuthor()->getProfilImage()->getWebPath("70x70"),
                                                            $this->generateUrl('pp_user_profile', array('slug' => $comment->getAuthor()->getSlug()), true)
                                                    ),
                                                    $comment->getCreatedDate()
                ));                
            }
            
            $jsonCurrentUser = array();
            if ($this->get('security.context')->isGranted('ROLE_USER')){
                $currentUser = $this->getUser();
                $jsonCurrentUser = new JsonUserModel(
                        $currentUser->getId(),
                        $currentUser->getName(),
                        $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'. $currentUser->getProfilImage()->getWebPath("70x70"),
                        $this->generateUrl('pp_user_profile', array('slug' => $currentUser->getSlug()), true)
                );
            }
            
            $jsonCommentThread = new JsonCommentThreadModel(
                    $commentThread->getId(),
                    $jsonComments,                     
                    $jsonCurrentUser,
                    $commentThread->getCommentNb(),
                    $commentThread->getCreatedDate()
            );
            
            echo json_encode($jsonCommentThread);
        }
        
        return $response;
        
    }
    
    public function postCommentAction(Request $request){
        
        $response = new JsonResponse();       
        
        $requestId = $request->get("requestId");
        $content = $request->get("content");
        
        if ($this->get('security.context')->isGranted('ROLE_USER') && $requestId != null && $content != null) { 
            
            $em = $this->getDoctrine()->getManager();
            $imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');            
            
            $imageRequest = $imageRequestRepository->find($requestId);         
            $currentUser = $this->getUser();
            
            if($imageRequest!=null && !in_array($currentUser, $imageRequest->getAuthor()->getBlockedUsers()->toArray())){
                $commentThread = $imageRequest->getCommentThread();
                $comment = new Comment();
                $comment->setAuthor($currentUser);
                $comment->setContent($content);
                $comment->setCommentThread($commentThread);
                $commentThread->addComment($comment);
                $em->persist($commentThread);
                $em->persist($comment);
                $em->flush();
                
                
                if($imageRequest->getAuthor()->getNotificationEnabled() && $imageRequest->getAuthor() != $currentUser){
                        /* create notification */
                        $pageProfileNotifThread = $imageRequest->getAuthor()->getNotificationThread();
                        $notification = new Notification(NotificationType::COMMENT);
                        $pageProfileNotifThread->addNotification($notification);
                        $imageRequest->getAuthor()->incrementNotificationsNb();
                        $em->persist($pageProfileNotifThread);
                        $em->persist($currentUser);
                        $em->flush();

                        $notificationComment = new NotificationComment($notification->getId());
                        $notificationComment->setNotificationBase($notification);
                        $notificationComment->setImageRequest($imageRequest);
                        $notificationComment->setComment($comment);
                        $em->persist($notificationComment);
                        $em->flush();

                        /* send notification */
                        $setClickedUrl = $this->generateUrl('pp_notification_api_patch_clicked', array("id"=>$notification->getId()));

                        $faye = $this->container->get('pp_notification.faye.client');                    
                        $channel = '/notification/'.$pageProfileNotifThread->getSlug();                    
                        $jsonNotication = new JsonNotification(
                                NotificationType::COMMENT,
                                false,
                                false,
                                $notification->getCreateDate(),
                                $this->container->get('pp_notification.ago')->ago($notification->getCreateDate()),
                                $this->generateUrl('pp_request_view', array('slug' => $imageRequest->getSlug())),
                                $setClickedUrl,
                                $currentUser->getId(),
                                $currentUser->getName(),
                                $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'. $currentUser->getProfilImage()->getWebPath("70x70"),
                                $imageRequest->getTitle()  
                        );
                        $data = array('notification' => $jsonNotication);                    
                        $faye->send($channel, $data);
                    }                
                $response->setStatusCode(Response::HTTP_OK);
            }
            else {$response->setStatusCode(Response::HTTP_FORBIDDEN);}
        }
        else {$response->setStatusCode(Response::HTTP_FORBIDDEN);}
        
        return $response;
    }    
    
  

    private function getViewHandler()
    {
        return $this->container->get('fos_rest.view_handler');
    }
}
