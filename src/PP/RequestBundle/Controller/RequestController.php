<?php

namespace PP\RequestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;

use PP\RequestBundle\Form\Type\ImageRequestType;
use PP\RequestBundle\Entity\ImageRequest;
use PP\PropositionBundle\Form\Type\PropositionType;
use PP\PropositionBundle\Entity\Proposition;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use PP\RequestBundle\Constant\Constants;

use PP\NotificationBundle\JsonNotificationModel\JsonNotification;
use PP\NotificationBundle\Entity\NotificationNewProposition;
use PP\NotificationBundle\Entity\Notification;
use PP\NotificationBundle\Constant\NotificationType;


class RequestController extends Controller
{
    
    
    public function indexAction(Request $request)
    {   
       
        
        /* get session and currentUser*/
        $session = $this->getRequest()->getSession();
        $currentUser = $this->getUser();
        
        /* will be true if search was done */
        $haveSearchParam = false;
        $searchParam = null;
        $getParameters = array();
        
         /* handle GET data */
        if ($request->isMethod('GET')) {            
            if($request->get('search_query') != null){
                $haveSearchParam = true;
                $searchParam = $request->get('search_query');
                $getParameters['search_query'] = $searchParam;                
            }            
        }               
        
        /* init repositories */
        $em = $this->getDoctrine()->getManager();
	$imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');
        $propositionRepository = $em->getRepository('PPPropositionBundle:Proposition');
        $userRepository = $em->getRepository('PPUserBundle:User');
        $tagRepository = $em->getRepository('PPRequestBundle:Tag');                                                
                
        /*copy(__DIR__.'/../../../../web/Resources/public/images/profile/test.jpeg', __DIR__.'/../../../../web/uploads/img/user/profile/original/new.jpeg');
        $profilImage = new \PP\ImageBundle\Entity\Image();
        $profilImage->setUploadDir('user/profile');
        $profilImage->setAlt('profilImg');
        $profilImage->setUrl('jpeg');  
        $imgsize = getimagesize(__DIR__.'/../../../../web/uploads/img/user/profile/original/new.jpeg');
        $mime = $imgsize['mime'];
        $file = new UploadedFile(__DIR__.'/../../../../web/uploads/img/user/profile/original/new.jpeg', "new", $mime, $imgsize, 0, true );
        $profilImage->setFile($file);        
        $em->persist($profilImage);
        $em->flush();*/     
        
        /* set displayMode (default ORDER_BY_DATE) */
        if($session->get('imageRequestOrder') != null){
            $displayMode = $session->get('imageRequestOrder');
        }else $displayMode = Constants::ORDER_BY_DATE;
        if($currentUser == null && $displayMode == Constants::ORDER_BY_INTEREST )$displayMode = Constants::ORDER_BY_DATE;
        
        /* create image request display mode form */                                   
        $displayModeForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('pp_request_homepage', $getParameters));
        if($displayMode == Constants::ORDER_BY_DATE){
            $displayModeForm = $displayModeForm->add('byupvote', 'submit');
            if($currentUser!=null)$displayModeForm = $displayModeForm->add('byinterest', 'submit'); 
        }
        else if($displayMode == Constants::ORDER_BY_UPVOTE){
            $displayModeForm = $displayModeForm->add('bydate', 'submit');
            if($currentUser!=null)$displayModeForm = $displayModeForm->add('byinterest', 'submit'); 
        }else if($currentUser!=null){
            $displayModeForm = $displayModeForm->add('bydate', 'submit');
            $displayModeForm = $displayModeForm->add('byupvote', 'submit');                        
        }
        $displayModeForm = $displayModeForm->getForm();                
                 
        /* handle image request POST data displayMode only */
        if ($request->isMethod('POST')) {               
            $displayModeForm->handleRequest($request);
            if ($displayModeForm->isValid()) {
                /* get action to do */
                $action = $displayModeForm->getClickedButton()->getName();                
                switch ($action){
                    case "bydate":
                        $session->set('imageRequestOrder', Constants::ORDER_BY_DATE);
                        break;                        
                    case "byupvote":
                        $session->set('imageRequestOrder', Constants::ORDER_BY_UPVOTE);
                        break;
                     case "byinterest":
                        if($currentUser!=null)$session->set('imageRequestOrder', Constants::ORDER_BY_INTEREST);
                        break;
                }
                return $this->redirect($this->generateUrl('pp_request_homepage', $getParameters));
            }
        }                
                        
        /////////////////////////////////
        ////////////// FORM /////////////
        
        /* create loadPage form */        
        $loadRequestForm = $this->get('form.factory')->createNamedBuilder('pp_request_api_get_request_form_1', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_request_api_get_request', array("page"=>1), true))
            ->getForm();
        
        /* create new image request form */
        $imageRequest = new ImageRequest();
        $form = $this->get('form.factory')->create(new ImageRequestType, $imageRequest, array(            
            'action' => $this->generateUrl('pp_request_add_request'),
            'method' => 'POST',
        ));                                                   
        
        /* handle image request POST data */
        if ($request->isMethod('POST')) {
            if ($this->get('security.context')->isGranted('ROLE_USER')) {    
                $form->handleRequest($request);
                               
                /* edit image request */
                $editImageRequestForm->handleRequest($request);
                if ($editImageRequestForm->isValid()) {
                    /* get image request to edit */
                    $tempImageRequest = $imageRequestRepository->find($editImageRequestForm->getData());
                    /* get action to do */
                    $action = $editImageRequestForm->getClickedButton()->getName();                
                    switch ($action){                                           
                        case "delete":
                            if ($this->get('security.context')->isGranted('ROLE_MODERATOR')) {
                                $em->remove($tempImageRequest);
                                $em->flush();
                            }
                            break;                
                    }
                    /* redirect */
                    return $this->redirect($this->generateUrl('pp_request_homepage', $getParameters));
                }
            }
        }
       
        /* render page */
        return $this->render('PPRequestBundle:Request:index.html.twig', array(            
            'form' => $form->createView(),                                    
            'displayMode' => $displayMode,
            'displayModeForm' => $displayModeForm->createView(),
            'loadRequestForm' => $loadRequestForm->createView(),
        ));
        
    }
    
    public function addRequestAction(Request $request){
        /* init repositories */
        $em = $this->getDoctrine()->getManager();
        $tagRepository = $em->getRepository('PPRequestBundle:Tag');   
        
        $currentUser = $this->getUser();        
        
        $imageRequest = new ImageRequest();
        $form = $this->get('form.factory')->create(new ImageRequestType, $imageRequest);
        
        if ($request->isMethod('POST')) {
            if ($this->get('security.context')->isGranted('ROLE_USER')) {    
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
                                array_push($tagList, $actualTag);                                            
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
                    
                    $request->getSession()->getFlashBag()->add('notice', 'Demande bien enregistrée.');
                    $request->getSession()->getFlashBag()->add('createCommentThread', 'Demande bien enregistrée.');
                    
                    /* redirect */
                    return $this->redirect($this->generateUrl('pp_request_view', array(
                        'slug' => $imageRequest->getSlug()
                    )));
                }
            }
        }
        
        return $this->redirect($this->generateUrl('pp_request_homepage', array(
                        'page' => 1                        
        )));
    }
    
    public function viewAction($slug,$page, Request $request)
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
        $upvotePropositionSelectedForm = null;
        if($imageRequest->getClosed()){
            $accepetedProposition = $imageRequest->getAcceptedProposition();
            if($currentUser!=null && $currentUser->getId() != $accepetedProposition->getAuthor()->getId() && !$userRepository->haveLikedProposition($currentUser->getId(), $accepetedProposition->getId())){
                $canUpvotePropositionSelected = true;
            }
            /* create upvote proposition form */
             $upvotePropositionSelectedForm = $this->get('form.factory')->createNamedBuilder('pp_proposition_api_patch_proposition_vote_form_'.$accepetedProposition->getId(), 'form', array(), array())         
            ->setAction($this->generateUrl('pp_proposition_api_patch_proposition_vote', array('propositionId'=>$accepetedProposition->getId()), true))
            ->getForm()
            ->createView();
        }
        
        /////////////////////////////////
        ////////////// FORM /////////////
        
        /* create new proposition form */       
        $proposition = new Proposition();
        $propositionForm = $this->get('form.factory')->create(new PropositionType, $proposition);        
        
        /* create upote request form */
        $upvoteRequestForm = $this->get('form.factory')->createNamedBuilder('pp_request_api_patch_request_vote', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_request_api_patch_request_vote', array("id"=>$id), true))
            ->getForm();
        
        /* create image request upvote form */
        $loadPropositionForm = $this->get('form.factory')->createNamedBuilder('pp_request_api_get_request_proposition_form_1', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_request_api_get_request_proposition', array("imageRequestId"=>$id , "page"=>1), true))
            ->getForm();
        
        $canUpvoteImageRequest = false;
        if($this->get('security.context')->isGranted('ROLE_USER')) {
            if($currentUser->getId() != $imageRequest->getAuthor()->getId() && !$userRepository->haveLikedRequest($currentUser->getId(), $imageRequest->getId())){
                $canUpvoteImageRequest = true;
            }
        }
        
        /* handle POST data */
        if ($request->isMethod('POST')) {
            /* add proposition */
            $propositionForm->handleRequest($request);                
            if ($propositionForm->isValid()) {
                if($imageRequest->getAuthor()->getId() != $this->getUser()->getId()){
                    
                    $imageRequest->addProposition($proposition)  ;
                    $proposition->setAuthor($currentUser);
                    $em->persist($proposition);
                    
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
                    $channel = '/notification/'.$imageRequestAuthorThread->getSlug();
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
                            if($this->get('security.context')->isGranted('ROLE_MODERATEUR')) {
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
            'upvotePropositionSelectedForm' => $upvotePropositionSelectedForm,
            'canUpvoteImageRequest' => $canUpvoteImageRequest,            
            'loadPropositionForm' => $loadPropositionForm->createView(),
            'upvoteRequestForm' => $upvoteRequestForm->createView()
        ));
    }
    
    public function sideInfoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $propositionRepository = $em->getRepository('PPPropositionBundle:Proposition');
        $imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');
        $userRepository = $em->getRepository('PPUserBundle:User');
        $tagRepository = $em->getRepository('PPRequestBundle:Tag');
        
        $popularTags = $tagRepository->getPopularTags(5);
        $activeUsers = $userRepository->getActiveUsers(3);
        $imageRequests = $imageRequestRepository->getPopularImageRequests(3);
                              
        return $this->render('PPRequestBundle:Request:sideInfo.html.twig', array(
            'popularTags' => $popularTags,
            'activeUsers' => $activeUsers,
            'imageRequests' => $imageRequests
        ));
    }
      
}

