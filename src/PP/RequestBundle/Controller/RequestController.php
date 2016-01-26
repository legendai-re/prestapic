<?php

namespace PP\RequestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use PP\RequestBundle\Form\Type\ImageRequestType;
use PP\RequestBundle\Entity\ImageRequest;
use PP\PropositionBundle\Form\Type\PropositionType;
use PP\PropositionBundle\Entity\Proposition;

use PP\RequestBundle\Constant\Constants;

use PP\NotificationBundle\JsonNotificationModel\JsonNotification;
use PP\NotificationBundle\Entity\NotificationNewProposition;
use PP\NotificationBundle\Entity\Notification;
use PP\NotificationBundle\Constant\NotificationType;

use PP\CommentBundle\Entity\CommentThread;

class RequestController extends Controller
{
    
    
    public function indexAction(Request $request)
    {                  
        /* get session and currentUser*/
        $session = $request->getSession();
        $currentUser = $this->getUser();
        
        /* will be true if search was done */
        $haveSearchParam = false;
        $searchParam = null;
        $getParameters = array();
        
         /* handle GET data */
        if ($request->isMethod('GET')) {            
            if($request->get('search_query') != null || $request->get('categories') != null || $request->get('tags') != null || $request->get('me') != null){
                $haveSearchParam = true;             
            }            
        }                                                                              
        
        /* init repositories */
        $em = $this->getDoctrine()->getManager();
	$imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');                                                                              
        
        /* set displayMode (default ORDER_BY_DATE) */
        if($session->get('imageRequestOrder') != null){
            $displayMode = $session->get('imageRequestOrder');
        }else {$displayMode = Constants::ORDER_BY_DATE;}
        if($currentUser == null && $displayMode == Constants::ORDER_BY_INTEREST ){$displayMode = Constants::ORDER_BY_DATE;}
        
        $contentToDisplay = Constants::DISPLAY_REQUEST_PENDING;
        if($session->get('contentToDisplay') != null){
            $contentToDisplay = $session->get('contentToDisplay');
        }else {$contentToDisplay = Constants::DISPLAY_REQUEST_PENDING;}                                                                      
                        
        /////////////////////////////////
        ////////////// FORM /////////////
        
        /* create loadPage form */        
        $loadRequestForm = $this->get('form.factory')->createNamedBuilder('pp_request_api_get_request', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_request_api_get_request', array(), true))
            ->getForm();                
        
        /* create upote request form */
        $upvoteRequestForm = $this->get('form.factory')->createNamedBuilder('pp_request_api_patch_request_vote', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_request_api_patch_request_vote', array(), true))
            ->getForm();
        
        /* create upvote proposition form */
        $upvotePropositionForm = $this->get('form.factory')->createNamedBuilder('pp_proposition_api_patch_proposition_vote_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_proposition_api_patch_proposition_vote', array(), true))
            ->getForm();            
        
        /* render page */
        return $this->render('PPRequestBundle:Request:index.html.twig', array(                                                         
            'displayMode' => $displayMode,
            'contentToDisplay' => $contentToDisplay,
            'loadRequestForm' => $loadRequestForm->createView(),
            'haveSearchParam' => $haveSearchParam,            
            'upvoteRequestForm' => $upvoteRequestForm->createView(),
            'upvotePropositionForm' => $upvotePropositionForm->createView()
        ));
        
    }
    
    public function editRequestAction(Request $request){
        
        /* init repositories */
        $em = $this->getDoctrine()->getManager();
        $tagRepository = $em->getRepository('PPRequestBundle:Tag');   
        $requestRepository = $em->getRepository("PPRequestBundle:ImageRequest");
        
        $currentUser = $this->getUser();        
        
        $imageRequest = new ImageRequest();
        $form = $this->get('form.factory')->create(new ImageRequestType, $imageRequest);
        
        if ($request->isMethod('POST')) {
            if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {    
                $form->handleRequest($request);
                if ($form->isValid()) {
                    
                    $oldRequest = $requestRepository->find($imageRequest->getId());
                    if($currentUser->getId() == $oldRequest->getAuthor()->getId()){
                        $oldRequest->setTitle($imageRequest->getTitle());
                        $oldRequest->setRequest($imageRequest->getRequest());
                        $oldRequest->setCategory($imageRequest->getCategory());                        
                        
                        foreach ($oldRequest->getTags() as $tag){
                            $oldRequest->removeTag($tag);
                        }
                        
                        $tagString = strtolower($imageRequest->getTagsStr().',');
                        $tagString = str_replace(' ', '', $tagString);
                        $tagList = array();	
                        $actualTag = "";
                        $tagCharArray = str_split($tagString);
                        foreach($tagCharArray as $char){
                            if($char != ","){
                                    $actualTag.=$char;
                            }
                            else {
                                if(!empty($actualTag)){
                                    if(strlen($actualTag)<26){
                                        array_push($tagList, $actualTag);
                                    }
                                }
                                $actualTag = "";
                            }
                        }                                
                        $tagList = array_unique($tagList);
                        foreach ($tagList as $tag){
                            $existedTag = $tagRepository->geTagByName($tag);
                            if($existedTag == null){
                                $tempTag = new \PP\RequestBundle\Entity\Tag();
                                $tempTag->setName($tag);
                                $em->persist($tempTag);
                                $oldRequest->addTag($tempTag);
                            }else{
                                $oldRequest->addTag($existedTag);
                            }
                        }
                        $em->persist($oldRequest);
                        $em->flush();
                        
                        /* redirect */
                        return $this->redirect($this->generateUrl('pp_request_view', array(
                            'slug' => $oldRequest->getSlug()
                        )));
                    }
                    
                }
            }
        }
        return $this->redirect($this->generateUrl('pp_request_homepage', array(                            
         )));                
    }
    
    public function addRequestAction(Request $request){
        /* init repositories */
        $em = $this->getDoctrine()->getManager();
        $tagRepository = $em->getRepository('PPRequestBundle:Tag');   
        
        $currentUser = $this->getUser();        
        
        $imageRequest = new ImageRequest();
        $form = $this->get('form.factory')->create(new ImageRequestType, $imageRequest);
        
        if ($request->isMethod('POST')) {
            if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {    
                $form->handleRequest($request);
                if ($form->isValid()) {
                    /* SAVE NEW REQUEST */
                    /* check if tags alredy exist, if not add them else make resaltion with the one thal already exist*/
                    $tagString = strtolower($imageRequest->getTagsStr().',');
                    $tagString = str_replace(' ', '', $tagString);
                    $tagList = array();	
                    $actualTag = "";
                    $tagCharArray = str_split($tagString);
                    foreach($tagCharArray as $char){
                        if($char != ","){
                                $actualTag.=$char;
                        }
                        else {
                            if(!empty($actualTag)){
                                if(strlen($actualTag)<26){
                                    array_push($tagList, $actualTag);
                                }
                            }
                            $actualTag = "";
                        }
                    }                                
                    $tagList = array_unique($tagList);
                    foreach ($tagList as $tag){
                        $existedTag = $tagRepository->geTagByName($tag);
                        if($existedTag == null){
                            $tempTag = new \PP\RequestBundle\Entity\Tag();
                            $tempTag->setName($tag);
                            $em->persist($tempTag);
                            $imageRequest->addTag($tempTag);
                        }else{
                            $imageRequest->addTag($existedTag);
                        }
                    }
                    /* end of checking tags, persist the new image request */                    
                    $imageRequest->setAuthor($currentUser);
                    $em->persist($imageRequest);
                    $em->flush();
                    
                    $commentThread = new CommentThread($imageRequest->getId());
                    $imageRequest->setCommentThread($commentThread);
                    $em->persist($commentThread);
                    $em->persist($imageRequest);
                    $em->flush();
                    
                    $request->getSession()->getFlashBag()->add('notice', 'Demande bien enregistrée.');
                    $request->getSession()->getFlashBag()->add('createCommentThread', 'Demande bien enregistrée.');
                    
                    /* redirect */
                    return $this->redirect($this->generateUrl('pp_request_view', array(
                        'slug' => $imageRequest->getSlug()
                    )));
                }
            }
        }
        
        return $this->redirect($this->generateUrl('pp_request_homepage', array()));
    }
    
    public function viewAction($slug, Request $request)
    {   
       
       /* init repositories */
        $em = $this->getDoctrine()->getManager();
        $propositionRepository = $em->getRepository('PPPropositionBundle:Proposition');
        $imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');
        $userRepository = $em->getRepository('PPUserBundle:User');               
                         
        /* get current user */
        $currentUser = $this->getUser();
        
        /* get image request id with slug */
        $id = $imageRequestRepository->getIdBySlug($slug);
        
        /* get image request by id */	       
	$imageRequest = $imageRequestRepository->getOneImageRequest($id);       
        
        /* get selected propositon if exist */
        $accepetedProposition = new Proposition();
        $canUpvotePropositionSelected = null;       
        if($imageRequest->getClosed()){
            $accepetedProposition = $imageRequest->getAcceptedProposition();
            if($currentUser!=null && $currentUser->getId() != $accepetedProposition->getAuthor()->getId() && !$userRepository->haveLikedProposition($currentUser->getId(), $accepetedProposition->getId())){
                $canUpvotePropositionSelected = true;
            }            
        }
        
        /////////////////////////////////
        ////////////// FORM /////////////
        
        /* create report ticket form */
        $disableTicketForm = null;
        if($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {                       
            if($currentUser!=null && $imageRequest->getAuthor()!=null && $this->get('security.authorization_checker')->isGranted('ROLE_MODERATOR') || $imageRequest->getAuthor()->getId() == $currentUser->getId()) {              
            $disableTicketForm = $this->get('form.factory')->createNamedBuilder('pp_report_api_post_disable_ticket_form', 'form', array(), array())         
                ->setAction($this->generateUrl('pp_report_api_post_disable_ticket', array(), true))
                ->getForm()
                ->createView();
            }            
        }
        
        $isAuthor = false;
        if($currentUser!=null && $currentUser->getId() == $imageRequest->getAuthor()->getId()){
            $isAuthor = true;
        }
        
        /* create new proposition form */       
        $proposition = new Proposition();
        $propositionForm = $this->get('form.factory')->create(new PropositionType, $proposition);        
        
        /* create upote request form */
        $getEditForm = null;
        if($isAuthor){
            $getEditForm = $this->get('form.factory')->createNamedBuilder('pp_request_api_get_edit_request_form', 'form', array(), array())         
                ->setAction($this->generateUrl('pp_request_api_get_edit_request', array(), true))
                ->getForm()
                ->createView();
        }
        
        /* create upote request form */
        $upvoteRequestForm = $this->get('form.factory')->createNamedBuilder('pp_request_api_patch_request_vote', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_request_api_patch_request_vote', array(), true))
            ->getForm();
        
        /* create image request upvote form */
        $loadPropositionForm = $this->get('form.factory')->createNamedBuilder('pp_request_api_get_request_proposition_form_1', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_request_api_get_request_proposition', array("imageRequestId"=>$id , "page"=>1), true))
            ->getForm();
        
        /* create upvote proposition form */
        $upvotePropositionForm = $this->get('form.factory')->createNamedBuilder('pp_proposition_api_patch_proposition_vote_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_proposition_api_patch_proposition_vote', array(), true))
            ->getForm()
            ->createView();
        
        /* create postcomment form */
        $postCommentForm = $this->get('form.factory')->createNamedBuilder('pp_request_api_post_comment_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_request_api_post_comment', array(), true))
            ->getForm()
            ->createView();
        
        /* create get comments form */
        $getCommentForm = $this->get('form.factory')->createNamedBuilder('pp_request_api_get_comments_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_request_api_get_comments', array(), true))
            ->getForm()
            ->createView();
        
        $canProposeImage = false;
        $canUpvoteImageRequest = false;
        if($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            if(!in_array($currentUser, $imageRequest->getAuthor()->getBlockedUsers()->toArray()))$canProposeImage = true;
            if($currentUser->getId() != $imageRequest->getAuthor()->getId() && !$userRepository->haveLikedRequest($currentUser->getId(), $imageRequest->getId())){
                $canUpvoteImageRequest = true;
            }
        }
        
        /* handle POST data */
        if ($request->isMethod('POST')) {
            /* add proposition */
            $propositionForm->handleRequest($request);                
            if ($propositionForm->isValid()) {
                if($imageRequest->getAuthor()->getId() != $this->getUser()->getId() && !in_array($currentUser, $imageRequest->getAuthor()->getBlockedUsers()->toArray())){
                    
                    $imageRequest->addProposition($proposition)  ;
                    $proposition->setAuthor($currentUser);
                    $em->persist($proposition);
                    $em->flush();
                    
                    if($imageRequest->getAuthor()->getNotificationEnabled()){
                        /* create notification */  
                        $imageRequestAuthorThread = $imageRequest->getAuthor()->getNotificationThread();
                        $notification = new Notification(NotificationType::NEW_PROPOSITION);
                        $imageRequestAuthorThread->addNotification($notification);
                        $imageRequest->getAuthor()->incrementNotificationsNb();
                        $em->persist($imageRequestAuthorThread);
                        $em->persist($currentUser);
                        $em->flush();

                        $notificationNewProposition = new NotificationNewProposition($notification->getId());
                        $notificationNewProposition->setProposition($proposition);                   
                        $notificationNewProposition->setNotificationBase($notification);
                        $em->persist($notificationNewProposition);
                        $em->flush();

                        $request->getSession()->getFlashBag()->add('propositon', 'Proposition bien enregistrée.');

                        /* send notification */
                        $setClickedUrl = $this->generateUrl('pp_notification_api_patch_clicked', array("id"=>$notification->getId()));

                        $faye = $this->container->get('pp_notification.faye.client');                    
                        $channel = '/notification/'.$imageRequestAuthorThread->getId();
                        $jsonNotication = new JsonNotification(
                                NotificationType::NEW_PROPOSITION,
                                false,
                                false,
                                $notification->getCreateDate(),
                                $this->container->get('pp_notification.ago')->ago($notification->getCreateDate()),
                                $this->generateUrl('pp_request_view', array('slug' => $imageRequest->getSlug())), 
                                $setClickedUrl,
                                $currentUser->getName(),
                                $currentUser->getId(),
                                $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'. $currentUser->getProfilImage()->getWebPath("70x70"),
                                $imageRequest->getTitle()  
                        );
                        $data = array('notification' => $jsonNotication);                    
                        $faye->send($channel, $data); 
                    }
                }
                /* redirect */
                return $this->redirect($this->generateUrl('pp_request_view', array(
                    'slug' => $slug                                    
                )));                                                                                                                               
            }

            /* edit proposition */
            if(isset($editPropositionForm)){
                $editPropositionForm->handleRequest($request);
                if ($editPropositionForm->isValid()) {
                    /* get proposition to edit */
                    $tempProposition = $propositionRepository->find($editPropositionForm->getData());
                    /* get action to do */
                    $action = $editPropositionForm->getClickedButton()->getName();                
                    switch ($action){                                               
                        case "delete":
                            if($this->get('security.authorization_checker')->isGranted('ROLE_MODERATEUR')) {
                                if($tempProposition == $accepetedProposition){
                                    $imageRequest->setAcceptedProposition(null);
                                    $imageRequest->setClosed(false);
                                }
                                $imageRequest->removeProposition($tempProposition);
                                $em->remove($tempProposition);
                                $em->flush();
                            }
                            break;                
                    }
                }
                /* redirect */
                return $this->redirect($this->generateUrl('pp_request_view', array(
                    'slug' => $slug 
                )));
            }
            
        }                

        /* render page */
        return $this->render('PPRequestBundle:Request:view.html.twig', array(
            'imageRequest' => $imageRequest,
            'propositionForm' => $propositionForm->createView(),            
            'acceptedProposition' => $accepetedProposition,
            'canUpvotePropositionSelected' => $canUpvotePropositionSelected,            
            'canUpvoteImageRequest' => $canUpvoteImageRequest,
            'canProposeImage' => $canProposeImage,
            'loadPropositionForm' => $loadPropositionForm->createView(),
            'upvoteRequestForm' => $upvoteRequestForm->createView(),
            'upvotePropositionForm' => $upvotePropositionForm,
            'disableTicketForm' => $disableTicketForm,
            'currentUser' => $currentUser,
            'getEditForm' => $getEditForm,
            'isAuthor' =>$isAuthor,
            'getCommentForm' =>$getCommentForm,
            'postCommentForm' => $postCommentForm
        ));
    }
    
    public function sideInfoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();                
        $activeUserRepository = $em->getRepository('PPUserBundle:ActiveUser');
        $popularRequestRepository = $em->getRepository('PPRequestBundle:PopularRequest');
        $tagRepository = $em->getRepository('PPRequestBundle:Tag');
        
        $popularTags = $tagRepository->getPopularTags(25);
        $activeUsers = $activeUserRepository->getActiveUsers(5);        
        $imageRequests = $popularRequestRepository->getPopularImageRequests(5);       
        
        return $this->render('PPRequestBundle:Request:sideInfo.html.twig', array(
            'popularTags' => $popularTags,
            'activeUsers' => $activeUsers,
            'imageRequests' => $imageRequests
        ));
    }
      
}

