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

use PP\NotificationBundle\Entity\Notification;
use PP\NotificationBundle\Entity\NotificationFollow;
use PP\NotificationBundle\Constant\NotificationType;
use PP\NotificationBundle\JsonNotificationModel\JsonNotification;
 
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
        
        $imageRequestList = array();
        $propositionsList = array();      

        foreach($imageRequestIds as $id){            
            array_push($imageRequestList, $imageRequestRepository->getOneImageRequest($id["id"]));
            $tempPropositions = $propositionRepository->getPropositions($id["id"], 3, 1);
            $propositionsList['imageRequest_'.$id['id']] =  $tempPropositions;            
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
                'loadRequestForm' => $loadRequestForm->createView()
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
    
    private function getViewHandler()
    {
        return $this->container->get('fos_rest.view_handler');
    }
}
