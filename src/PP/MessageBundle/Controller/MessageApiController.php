<?php

namespace PP\MessageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

use PP\NotificationBundle\Entity\Notification;
use PP\NotificationBundle\Constant\NotificationType;
use PP\NotificationBundle\JsonNotificationModel\JsonNotification;
use PP\NotificationBundle\Entity\NotificationMessage;

use PP\MessageBundle\JsonModel\JsonMessageModel;
use PP\MessageBundle\JsonModel\JsonUserModel;
use PP\MessageBundle\JsonModel\JsonMessageThreadModel;
use PP\MessageBundle\Entity\MessageThread;
use PP\MessageBundle\Entity\Message;

class MessageApiController extends Controller
{
    public function getSearchUserAction(Request $request)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/x-javascript');
        
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            
            $currentUser = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $userRepository = $em->getRepository('PPUserBundle:User');
            
            if($currentUser!=null){
                
                $jsonUsers = array();
                $jsonUsers['users'] = array();         
                
                $userList = $userRepository->searchUser($currentUser->getId(), $request->get('search'), 5);
                foreach ($userList as $user){
                    $getThreadApiUrl = $this->generateUrl('pp_message_api_get_thread', array('targetId'=>$user->getId()), true);
                    array_push($jsonUsers['users'], new JsonUserModel(
                                                                $user->getId(),
                                                                $user->getName(),
                                                                $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$user->getProfilImage()->getWebPath('70x70'),
                                                                $getThreadApiUrl)
                                                                );
                }
                
                echo json_encode($jsonUsers);
                
            }else $response->setStatusCode(Response::HTTP_FORBIDDEN);            
        }else $response->setStatusCode(Response::HTTP_FORBIDDEN);        
        return $response;
    }
    
    public function getThreadAction(Request $request){
        
        $response = new Response();
        $response->headers->set('Content-Type', 'application/x-javascript');
        $data = array();
        
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
                        
            $targetId = $request->get('targetId');
            $currentUser = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $messageRepository = $em->getRepository('PPMessageBundle:Message');
            $messageThreadRepository = $em->getRepository('PPMessageBundle:MessageThread');
            $userRepositoy = $em->getRepository('PPUserBundle:User');
            $targetUser = $userRepositoy->find($targetId);
            
            if($currentUser!=null && $targetUser!=null){
                $messageThread = null;
                $messageThreadId = $messageRepository->getCommonMessageThread($currentUser->getId(), $targetId);
                if($messageThreadId!=null)$messageThread = $messageThreadRepository->find($messageThreadId);
                if($messageThread != null){
                    $data['messageThreadFounded'] = true;
                    $data['messageThread'] = new JsonMessageThreadModel(
                                        $messageThread->getId(),
                                        $targetUser->getName(),
                                        $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$targetUser->getProfilImage()->getWebPath('70x70'),
                                        $currentUser->getId(),
                                        $messageThread->getLastMessage()->getContent(),                                        
                                        false,
                                        $messageThread->getLastMessage()->getCreatedDate(),
                                        $this->container->get('pp_notification.ago')->ago($messageThread->getLastMessage()->getCreatedDate()),                                     
                                        false
                    );
                }else{
                    $data['messageThreadFounded'] = false;
                }
                
                $data['targetUser'] = new JsonUserModel(
                                                            $targetUser->getId(),
                                                            $targetUser->getname(),
                                                            $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$targetUser->getProfilImage()->getWebPath('70x70'),
                                                            null                                                        
                );
                
                $data['postMessageThreadUrl'] = $this->generateUrl('pp_message_api_post_message', array(), true);
                $data['getConversationUrl'] = $this->generateUrl('pp_message_api_get_conversation', array(), true);
                echo json_encode($data);
                
            }else $response->setStatusCode(Response::HTTP_FORBIDDEN);            
        }else $response->setStatusCode(Response::HTTP_FORBIDDEN);        
        return $response;
    }
    
    public function postMessageAction(Request $request){
        
        $response = new Response();
        $response->headers->set('Content-Type', 'application/x-javascript');
        $data = array();
        
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            
            $currentUser = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $messageRepository = $em->getRepository('PPMessageBundle:Message');
            $messageThreadRepository = $em->getRepository('PPMessageBundle:MessageThread');
            $userRepositoy = $em->getRepository('PPUserBundle:User');
            $action = 'newMessage';
            
            if($currentUser!=null){
                                                
                $postData = $request->getContent();
                $postData = json_decode($postData);
                
                $targetUser = $userRepositoy->find($postData->targetUserId);
                
                if(!$postData->haveMessageThread){
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
                    
                    $jsonMessage = new JsonMessageThreadModel(
                                        $messageThread->getId(),
                                        $currentUser->getName(),
                                        $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$currentUser->getProfilImage()->getWebPath('70x70'),
                                        $currentUser->getId(),
                                        $postData->messageContent,
                                        false,
                                        new \DateTime(),
                                        $this->container->get('pp_notification.ago')->ago(new \DateTime()),                                     
                                        true
                    );
                    
                    $newThread = new JsonMessageThreadModel(
                                        $messageThread->getId(),
                                        $targetUser->getName(),
                                        $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$targetUser->getProfilImage()->getWebPath('70x70'),
                                        $targetUser->getId(),
                                        $postData->messageContent,
                                        true,
                                        new \DateTime(),
                                        $this->container->get('pp_notification.ago')->ago(new \DateTime()),
                                        false
                    );
                    $data['newThread'] = $newThread;
                    
                }else{
                    $messageThread = $messageThreadRepository->find($postData->currentMessageThreadId);
                }
                
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
                if($postData->haveMessageThread){                    
                    $jsonMessage = new JsonMessageModel(
                            $messageThread->getId(),
                            $message->getId(),
                            $message->getContent(),
                            $currentUser->getName(),
                            $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$currentUser->getProfilImage()->getWebPath('70x70'),
                            false,
                            $message->getCreatedDate(),
                            $this->container->get('pp_notification.ago')->ago($message->getCreatedDate())
                    );
                }

                $channel = '/messages/'.$targetUser->getId();               
                $jsonMessageData = array('message' => $jsonMessage, 'action'=>$action);
                $faye->send($channel, $jsonMessageData);
                
                /* else if target user not in message send notification */
                if(!$targetUser->getIsInMessage()){
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
                
                
                echo json_encode($data);
                
            }else $response->setStatusCode(Response::HTTP_FORBIDDEN);            
        }else $response->setStatusCode(Response::HTTP_FORBIDDEN);        
        return $response;
    }
    
    public function getInboxAction(Request $request, $userId){
                       
        $response = new Response();
        $response->headers->set('Content-Type', 'application/x-javascript');
        $data = array();
        
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            
            $currentUser = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $messageRepository = $em->getRepository('PPMessageBundle:Message');            
            $userRepository = $em->getRepository('PPUserBundle:User');            
            $jsonMessages['threads'] = array();
            $jsonMessages['currentuser'] = null;
                    
            if($currentUser!=null){
                                                  
                $messageThreads = $userRepository->getInboxMessage($currentUser, 5)->getMessageThreads();
                
                foreach ($messageThreads as $messageThread){
                    
                    $lastMessage = $messageThread->getLastMessage();
                    if($lastMessage!=null){
                        $threadUser =  $lastMessage->getAuthor();
                        $fromUs = false;
                        if($lastMessage->getAuthor()->getId() == $currentUser->getId()){
                            $fromUs = true;
                            $threadUser = $lastMessage->getTarget();
                        }

                        $tempMessage = new JsonMessageThreadModel(
                                            $messageThread->getId(),
                                            $threadUser->getName(),
                                            $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$threadUser->getProfilImage()->getWebPath('70x70'),
                                            $threadUser->getId(),
                                            $lastMessage->getContent(),
                                            $fromUs,
                                            $lastMessage->getCreatedDate(),
                                            $this->container->get('pp_notification.ago')->ago($lastMessage->getCreatedDate()),
                                            false

                        );                    
                        array_push($jsonMessages['threads'],$tempMessage);
                    }                                        
                    
                }
                $jsonMessages['currentUser'] = new JsonUserModel(
                                                            $currentUser->getId(),
                                                            $currentUser->getname(),
                                                            $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$currentUser->getProfilImage()->getWebPath('70x70'),
                                                            null                                                        
                );
                
                $jsonMessages['conversationUrl'] = $this->generateUrl('pp_message_api_get_conversation', array(), true);
                $jsonMessages['postMessageUrl'] = $this->generateUrl('pp_message_api_post_message', array(), true);                                        
                $jsonMessages['getThreadUrl'] = $this->generateUrl('pp_message_api_get_thread', array(), true);
                echo json_encode($jsonMessages);
                
            }else $response->setStatusCode(Response::HTTP_FORBIDDEN);            
        }else $response->setStatusCode(Response::HTTP_FORBIDDEN);        
        return $response;        
    }       
    
    public function getConversationAction(Request $request){
        
        $response = new Response();
        $response->headers->set('Content-Type', 'application/x-javascript');
        $data = array();
        
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            
            $threadId = $request->get('threadId');
            $currentUser = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $messageRepository = $em->getRepository('PPMessageBundle:Message');
            $messageThreadRepository = $em->getRepository('PPMessageBundle:MessageThread');            
            $userRepository = $em->getRepository('PPUserBundle:User');            
            $jsonMessages['messages'] = array();
                    
            if($currentUser!=null){
                
                $conversation = $messageThreadRepository->getConversation($threadId);
                $messages = $conversation->getMessages();
                
                foreach ($messages as $message){
                    $fromUs = false;
                    if($message->getAuthor()->getId() == $currentUser->getId())$fromUs = true;
                    
                    $tempMessage = new JsonMessageModel(
                                    $threadId,
                                    $message->getId(),
                                    $message->getContent(),
                                    $message->getAuthor()->getName(),
                                    $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$message->getAuthor()->getProfilImage()->getWebPath('70x70'),
                                    $fromUs,
                                    $message->getCreatedDate(),
                                    $this->container->get('pp_notification.ago')->ago($message->getCreatedDate())
                    );
                    
                    array_push($jsonMessages['messages'], $tempMessage);
                }                
                
                echo json_encode($jsonMessages);
                
            }else $response->setStatusCode(Response::HTTP_FORBIDDEN);            
        }else $response->setStatusCode(Response::HTTP_FORBIDDEN);        
        return $response;
        
    }
    
    private function getViewHandler()
    {
        return $this->container->get('fos_rest.view_handler');
    }
}
