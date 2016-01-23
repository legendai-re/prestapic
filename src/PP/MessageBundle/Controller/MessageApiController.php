<?php

namespace PP\MessageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use PP\NotificationBundle\Entity\Notification;
use PP\NotificationBundle\Constant\NotificationType;
use PP\NotificationBundle\JsonNotificationModel\JsonNotification;
use PP\NotificationBundle\Entity\NotificationMessage;

use PP\MessageBundle\JsonModel\JsonMessageModel;
use PP\MessageBundle\JsonModel\JsonUserModel;
use PP\MessageBundle\JsonModel\JsonCurrentUserModel;
use PP\MessageBundle\JsonModel\JsonMessageThreadModel;
use PP\MessageBundle\Entity\MessageThread;
use PP\MessageBundle\Entity\Message;
use PP\MessageBundle\Constant\Constant;

class MessageApiController extends Controller
{       
    
    /*
     * Get current user info
     * 
     * @params -
     * 
     * @return JsonResponse
     */
    public function getCurrentUserAction(Request $request){
        $response = new Response();
        $response->headers->set('Content-Type', 'application/x-javascript');        
        
        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            
            $currentUser = $this->getUser();
            
            if($currentUser!=null){                       
                
                $threadList = $currentUser->getMessageThreads();
                $formatedThreadList = array();
                $target=null;
                
                foreach ($threadList as $thread){                    
                    foreach ($thread->getUsers() as $user){
                        if($user->getId() != $currentUser->getId()){
                            $target = new JsonUserModel(
                                                $user->getId(),
                                                $user->getName(),
                                                $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$user->getProfilImage()->getWebPath('70x70')
                            );
                            break;
                        }                        
                    }
                    
                    $tempLastMessage = $thread->getLastMessage();
                    $messageFromUs = false;
                    $lastMessageAuthor = $tempLastMessage->getAuthor();
                    
                    if($lastMessageAuthor->getId() == $currentUser->getId()){
                        $messageFromUs = true;
                    }
                    
                    $formatedLastMessageAuthor = new JsonUserModel(
                                                $lastMessageAuthor->getId(),
                                                $lastMessageAuthor->getName(),
                                                $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$lastMessageAuthor->getProfilImage()->getWebPath('70x70')
                    );
                    
                    $lastMessage = new JsonMessageModel(
                                                $thread->getId(),
                                                $tempLastMessage->getId(),
                                                $tempLastMessage->getContent(),
                                                $formatedLastMessageAuthor,
                                                $messageFromUs,
                                                $tempLastMessage->getCreatedDate(),                                                
                                                $this->container->get('pp_notification.ago')->ago($tempLastMessage->getCreatedDate())                                     
                    );
                    
                    $formatedThreadList[$thread->getId()] = new JsonMessageThreadModel(
                                                                $thread->getId(),
                                                                $target,
                                                                $lastMessage, 
                                                                array(),
                                                                false,
                                                                1,
                                                                $thread->getCreatedDate()
                    );
                }
                                
                $formatedCurrentUser = new JsonCurrentUserModel(
                                                        $currentUser->getId(),
                                                        $currentUser->getName(),
                                                        $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$currentUser->getProfilImage()->getWebPath('70x70'),
                                                        $formatedThreadList,                                                        
                                                        $this->generateUrl('pp_message_api_post_message', array(), true),
                                                        $this->generateUrl('pp_message_api_get_conversation', array(), true)
                                                        
                );
                echo json_encode($formatedCurrentUser);
                
            }else {$response->setStatusCode(Response::HTTP_FORBIDDEN);}
            
        }else {$response->setStatusCode(Response::HTTP_FORBIDDEN);}
        return $response;        
    }       
    
    /*
     * Post new message
     *       
     ** @params integer     threadId
     *          integer     targetId
     *          String      messageContent
     */
    public function postMessageAction(Request $request){
        
        $response = new Response();
        $response->headers->set('Content-Type', 'application/x-javascript');
        $data = array();
        
        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            
            $currentUser = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $messageRepository = $em->getRepository('PPMessageBundle:Message');
            $messageThreadRepository = $em->getRepository('PPMessageBundle:MessageThread');
            $userRepositoy = $em->getRepository('PPUserBundle:User');
            $action = 'newMessage';
            $haveBlockedSender = false;
           
            if($currentUser!=null){
                                                
                $postData = $request->getContent();
                $postData = json_decode($postData);                
                $targetId = $postData->targetId;
                if(isset($postData->threadId)){
                    $threadId = $postData->threadId;
                }
                else {
                    $threadId = null;
                }
                
                $targetUser = $userRepositoy->find($targetId);
                /* if not blocked by target */
                if(!in_array($currentUser, $targetUser->getBlockedUsers()->toArray())){
                    if($threadId == null){
                        $threadId = $messageRepository->getCommonMessageThread($currentUser->getId(), $targetId);
                        if($threadId == null){
                            /* don't have common thread so create one */                    
                            $messageThread = new MessageThread();
                            $messageThread->addUser($currentUser);
                            $messageThread->addUser($targetUser);
                            $currentUser->addMessageThread($messageThread);
                            $targetUser->addMessageThread($messageThread);
                            $em->persist($currentUser);
                            $em->persist($targetUser);
                            $em->persist($messageThread);
                            $em->flush();
                            $action = 'newThread';

                            $jsonMessage = new JsonMessageModel(
                                                $messageThread->getId(),
                                                999,
                                                $postData->messageContent,
                                                new JsonUserModel($currentUser->getId(), $currentUser->getName(), $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$currentUser->getProfilImage()->getWebPath('70x70')),                            
                                                true,
                                                new \DateTime(),
                                                $this->container->get('pp_notification.ago')->ago(new \DateTime())
                            );

                            $newThread = new JsonMessageThreadModel(
                                                $messageThread->getId(),
                                                new JsonUserModel($targetUser->getId(), $targetUser->getName(), $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$targetUser->getProfilImage()->getWebPath('70x70')),                            
                                                $jsonMessage,
                                                array($jsonMessage),
                                                true,
                                                1,
                                                new \DateTime()                                            
                            );
                            $newThreadTosend = $newThread;
                            $data['newThread'] = $newThread;
                        }else {$messageThread = $messageThreadRepository->find($threadId);}
                    }else if($threadId != null){
                        $messageThread = $messageThreadRepository->find($threadId);
                    }

                    echo json_encode($data);

                    $message = new Message();
                    $message->setAuthor($currentUser);
                    $message->setTarget($targetUser);
                    $message->setContent($postData->messageContent);
                    $message->setMessageThread($messageThread);

                    $messageThread->addMessage($message);
                    $em->persist($messageThread);
                    $em->flush();                

                    $faye = $this->container->get('pp_notification.faye.client');

                    /* if the target user is in message send message */                
                    $jsonMessage = new JsonMessageModel(
                            $messageThread->getId(),
                            $message->getId(),
                            $message->getContent(),
                            new JsonUserModel($currentUser->getId(), $currentUser->getName(), $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$currentUser->getProfilImage()->getWebPath('70x70')),                            
                            false,
                            $message->getCreatedDate(),
                            $this->container->get('pp_notification.ago')->ago($message->getCreatedDate())
                    );                
                    if(isset($newThreadTosend)){
                        $newThreadTosend->lastMessage->messageFromUs = false;
                        $newThreadTosend->target = new JsonUserModel($currentUser->getId(), $currentUser->getName(), $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$currentUser->getProfilImage()->getWebPath('70x70'));
                    }
                    else {
                        $newThreadTosend = array();
                    }
                    $channel = '/messages/'.$targetUser->getId();               
                    $jsonMessageData = array('message' => $jsonMessage, 'action'=>$action, 'newThread'=>$newThreadTosend);
                    $faye->send($channel, $jsonMessageData);

                    /* else if target user not in message send notification */
                    if($targetUser->getNotificationEnabled() && !$targetUser->getIsInMessage()){
                        $targetUserNotifThread = $targetUser->getNotificationThread();
                        $notification = new Notification(NotificationType::MESSAGE);
                        $targetUserNotifThread->addNotification($notification);
                        $targetUser->incrementNotificationsNb();
                        $em->persist($targetUserNotifThread);
                        $em->persist($targetUser);
                        $em->flush();

                        $notificationMessage = new NotificationMessage($notification->getId());
                        $notificationMessage->setAuthor($currentUser);
                        $notificationMessage->setMessage($message);
                        $notificationMessage->setNotificationBase($notification);
                        $em->persist($notificationMessage);
                        $em->flush();

                        $setClickedUrl = $this->generateUrl('pp_notification_api_patch_clicked', array("id"=>$notification->getId()));
                        $channel = '/notification/'.$targetUser->getSlug();                    
                        $jsonNotication = new JsonNotification(
                                NotificationType::MESSAGE,
                                false,
                                false,
                                $notification->getCreateDate(),
                                $this->container->get('pp_notification.ago')->ago($notification->getCreateDate()),
                                $this->generateUrl('pp_user_profile', array('slug' => $currentUser->getSlug())),
                                $setClickedUrl,
                                $currentUser->getId(),
                                $currentUser->getName(),                            
                                $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'. $currentUser->getProfilImage()->getWebPath("70x70"),
                                null,
                                $messageThread->getId()
                        );

                        $notifData = array('notification' => $jsonNotication);                    
                        $faye->send($channel, $notifData);
                    }
                }else {$response->setStatusCode(Response::HTTP_FORBIDDEN);}                                              
            }else {$response->setStatusCode(Response::HTTP_FORBIDDEN);}
        }else {$response->setStatusCode(Response::HTTP_FORBIDDEN);}
        return $response;
    }        
    
    /*
     * Get messages of one thread
     * 
     * @params  integer     page
     *          integer     threadId
     * 
     * @return JsonResponse
     */
    public function getConversationAction(Request $request){
        
        $response = new Response();
        $response->headers->set('Content-Type', 'application/x-javascript');        
        
        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            
            $page = $request->get("page");
            $threadId = $request->get('threadId');
            $currentUser = $this->getUser();
            $em = $this->getDoctrine()->getManager();            
            $messageThreadRepository = $em->getRepository('PPMessageBundle:MessageThread');            
            $jsonMessages['messages'] = array();
                    
            if($currentUser!=null){
                
                $conversation = $messageThreadRepository->getConversation($threadId, Constant::MESSAGE_LIMIT, $page);
                if($conversation!=null){
                    $messages = $conversation->getMessages();

                    foreach ($messages as $message){
                        $fromUs = false;
                        if($message->getAuthor()->getId() == $currentUser->getId()){$fromUs = true;}

                        $tempMessage = new JsonMessageModel(
                                        $threadId,
                                        $message->getId(),
                                        $message->getContent(),
                                        new JsonUserModel($message->getAuthor()->getId(), $message->getAuthor()->getName(), $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$message->getAuthor()->getProfilImage()->getWebPath('70x70')),
                                        $fromUs,
                                        $message->getCreatedDate(),
                                        $this->container->get('pp_notification.ago')->ago($message->getCreatedDate())
                        );

                        array_unshift($jsonMessages['messages'], $tempMessage);
                    }     
                }
                
                echo json_encode($jsonMessages);
                
            }else {$response->setStatusCode(Response::HTTP_FORBIDDEN);}
        }else {$response->setStatusCode(Response::HTTP_FORBIDDEN);}
        return $response;
    }
    
    private function getViewHandler()
    {
        return $this->container->get('fos_rest.view_handler');
    }
}
