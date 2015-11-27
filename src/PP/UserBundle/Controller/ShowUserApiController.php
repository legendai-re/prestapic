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

class ShowUserApiController extends Controller
{
    
    /**
     * Get user requests view
     *
     * @param int $userId
     * @param int $page
     *
     * @return View
     */
    public function getUserRequestAction($userId, $page){                           
        
        /* init repositories */
        $em = $this->getDoctrine()->getManager();
	$imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');
        $propositionRepository = $em->getRepository('PPPropositionBundle:Proposition');
        $userRepository = $em->getRepository('PPUserBundle:User');
        
        $pageProfile = $userRepository->find($userId);
                
        $imageRequestIds = $imageRequestRepository->getUserImageRequestContributionIds($pageProfile->getId(), Constants::REQUEST_PER_PAGE, $page);
        
        $currentUser = $this->getUser();
        $imageRequestList = array();
        $propositionsList = array();      
        $canUpvoteImageRequest = array();
        
        foreach($imageRequestIds as $id){
            $tempImageRequest = $imageRequestRepository->getOneImageRequest($id["id"]);
            array_push($imageRequestList, $tempImageRequest);
            $tempPropositions = $propositionRepository->getPropositions($id["id"], 3, 1);
            $propositionsList['imageRequest_'.$id['id']] =  $tempPropositions;
            
            if($currentUser!=null && $currentUser->getId() != $tempImageRequest->getAuthor()->getId() && !$userRepository->haveLikedRequest($currentUser->getId(), $tempImageRequest->getId())){
                $canUpvoteImageRequest[$tempImageRequest->getId()] = true;
            }else{ $canUpvoteImageRequest[$tempImageRequest->getId()] = false;}
        }
        
        $nextPage = $page+1;
        $haveNextPage = true;
        if(sizeof($imageRequestList) < Constants::REQUEST_PER_PAGE)$haveNextPage = false;
        
        /////////////////////////
        ////////// FORM /////////
        
        /* create loadPage form */
        $loadRequestForm = $this->get('form.factory')->createNamedBuilder('pp_user_api_get_user_request_form_'.$nextPage, 'form', array(), array())         
            ->setAction($this->generateUrl('pp_user_api_get_user_request', array("userId"=>$pageProfile->getId(), "page"=>$nextPage), true))
            ->getForm();
        
        $view = View::create()
            ->setData(array(                      
                'page'=>$page,
                'haveNextPage' => $haveNextPage,
                'nextPage' => $nextPage,
                'imageRequestList' => $imageRequestList,                
                'propositionsList' => $propositionsList,
                'loadRequestForm' => $loadRequestForm->createView(),
                'canUpvoteImageRequest' => $canUpvoteImageRequest
            ))
            ->setTemplate(new TemplateReference('PPRequestBundle', 'Request', 'requestList'));

        return $this->getViewHandler()->handle($view);
        
    }
    
    public function patchUserFollowAction(Request $request, $userId){
        
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            /* init repositories */
            $em = $this->getDoctrine()->getManager();            
            $userRepository = $em->getRepository('PPUserBundle:User');
            
            $pageProfile = $userRepository->find($userId);
            $currentUser = $this->getUser();
            
            if($pageProfile!=null && $currentUser!=null && $pageProfile->getId() != $currentUser->getId()){
                if(!in_array($pageProfile, $currentUser->getFollowing()->toArray())){
                    /* create follow */
                    $currentUser->addFollowing($pageProfile);
                    
                    /* create notification */
                    $pageProfileNotifThread = $pageProfile->getNotificationThread();
                    $notification = new Notification(NotificationType::FOLLOW);
                    $pageProfileNotifThread->addNotification($notification);
                    $pageProfile->incrementNotificationsNb();
                    $em->persist($pageProfileNotifThread);
                    $em->persist($currentUser);
                    $em->flush();
                    
                    
                    $notificationFollow = new NotificationFollow($notification->getId());
                    $notificationFollow->setFollowYou($currentUser);
                    $notificationFollow->setNotificationBase($notification);
                    $em->persist($notificationFollow);
                    $em->flush();
                    
                    /* send notification */
                    $setClickedUrl = $this->generateUrl('pp_notification_api_patch_clicked', array("id"=>$notification->getId()));
                    
                    $faye = $this->container->get('pp_notification.faye.client');                    
                    $channel = '/notification/'.$pageProfileNotifThread->getSlug();                    
                    $jsonNotication = new JsonNotification(
                            NotificationType::FOLLOW,
                            false,
                            false,
                            $notification->getCreateDate(),
                            $this->container->get('pp_notification.ago')->ago($notification->getCreateDate()),
                            $this->generateUrl('pp_user_profile', array('slug' => $currentUser->getSlug())),
                            $setClickedUrl,
                            $currentUser->getName(),
                            $currentUser->getId(),
                            $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'. $currentUser->getProfilImage()->getWebPath("70x70"),
                            null  
                    );
                    $data = array('notification' => $jsonNotication);                    
                    $faye->send($channel, $data);                    

                    $response->setData(json_encode(array('succes'=>true, 'newValue'=>'unfollow')));
                }else{
                    $currentUser->removeFollowing($pageProfile);                    
                    $em->persist($currentUser);
                    $em->flush();
                    $response->setData(json_encode(array('succes'=>true, 'newValue'=>'follow')));
                }                
            }
        }
        else $response->setData(json_encode(array('succes'=>false)));
        
        return $response;
        
    }
    
    public function patchIsInMessageAction(Request $request){
        
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $currentUser= $this->getUser();
            if($currentUser != null){
                $em = $this->getDoctrine()->getManager();
                
                $currentUser->setIsInMessage($request->get('mode'));
                
                $em->persist($currentUser);
                $em->flush();
                $response->setStatusCode(Response::HTTP_OK);
            }else $response->setStatusCode(Response::HTTP_FORBIDDEN);            
        }else $response->setStatusCode(Response::HTTP_FORBIDDEN);        
        return $response;;        
    }
    
    /*
     * Get users from string
     * 
     * @params String   search
     * 
     * @return JsonResponse
     */
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
                
                $userList = $userRepository->searchUser($currentUser->getId(), $request->get('search'), Constants::USER_PER_PAGE, 1);
                foreach ($userList as $user){                    
                    array_push($jsonUsers['users'], new JsonUserModel(
                                                                $user->getId(),
                                                                $user->getName(),
                                                                $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$user->getProfilImage()->getWebPath('70x70')
                    ));                       
                }
                
                echo json_encode($jsonUsers);
                
            }else {$response->setStatusCode(Response::HTTP_FORBIDDEN);}
        }else {$response->setStatusCode(Response::HTTP_FORBIDDEN);}
        return $response;
    }
    
    /*
     * Get users from string
     * 
     * @params String   search
     * 
     * @return JsonResponse
     */
    public function getSearchUserViewAction(Request $request)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/x-javascript');
        
        $page = $request->get("page");
        $name = $request->get("name");
       
            
        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository('PPUserBundle:User');
                                                                            
        $userList = $userRepository->searchUser(null, $name, Constants::USER_PER_PAGE, $page);                               
        
        $haveNextPage = true;       
        if(sizeof($userList) < Constants::USER_PER_PAGE){
            $haveNextPage = false;
        }
        
        $nextPage = $page+1;
        $view = View::create()
            ->setData(array(                                      
                'page'=>$page,
                'nextPage' => $nextPage,
                'haveNextPage' => $haveNextPage,
                'userList' => $userList
            ))
            ->setTemplate(new TemplateReference('PPUserBundle', 'Search', 'userList'));

        return $this->getViewHandler()->handle($view);
    }
    
    public function getEditProfileFormAction(){
        
        $currentUser = $this->getUser();
        
        if ($this->get('security.context')->isGranted('ROLE_USER') && $currentUser!=null) {
            
            $editUserForm = $this->get('form.factory')->create(new EditProfileFormType($currentUser), $currentUser, array(                            
            ));
            
            $view = View::create()
                ->setData(array( 
                    'currentUser' => $currentUser,
                    'editUserForm' => $editUserForm->createView()
                ))
                ->setTemplate(new TemplateReference('PPUserBundle', 'Profile', 'edit_profile_form'));
        
            return $this->getViewHandler()->handle($view);
        }
        
        else return new Response();
    }

    public function patchModeratorAction(Request $request){
        $response = new Response();
        $response->headers->set('Content-Type', 'application/x-javascript');
        
        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository('PPUserBundle:User');
        
        $currentUser = $this->getUser();
        $pageProfile = null;
        if($request->get("id")!=null){
            $pageProfile = $userRepository->find($request->get("id"));
        }        
        if($this->get('security.context')->isGranted('ROLE_ADMIN') && $currentUser != null && $pageProfile != null) {
            if(!$pageProfile->hasRole("ROLE_MODERATOR")){$pageProfile->addRole("ROLE_MODERATOR");}
            else {$pageProfile->removeRole("ROLE_MODERATOR");}
            $em->flush();
        }else $response->setStatusCode (Response::HTTP_FORBIDDEN);        
        
        return $response;
    }
    
    private function getViewHandler()
    {
        return $this->container->get('fos_rest.view_handler');
    }
}