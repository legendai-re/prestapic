<?php

namespace PP\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Collections\ArrayCollection;
use PP\RequestBundle\Constant\Constants;
use PP\RequestBundle\Entity\ImageRequest;
use PP\UserBundle\Entity\User;
use PP\RequestBundle\Form\Type\ImageRequestType;
use PP\UserBundle\Form\Type\EditProfileFormType;

class ShowUserController extends Controller 
{
    public function indexAction(Request $request, $slug)
    {
        /* get session and currentUser*/
        $session = $this->getRequest()->getSession();
        $currentUser = $this->getUser();
        
        /* init repositories */
        $em = $this->getDoctrine()->getManager();
	$imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');
        $propositionRepository = $em->getRepository('PPPropositionBundle:Proposition');
        $userRepository = $em->getRepository('PPUserBundle:User');
        $tagRepository = $em->getRepository('PPRequestBundle:Tag');
        
        $pageProfile = $userRepository->getUserBySlug($slug);
        if($pageProfile == null){
            throw new NotFoundHttpException("L'utilisateur \"".$slug."\" n'existe pas.");			
        }
        
        $isHownProfile = false;
        $isFollowing = false;
        if($currentUser!=null){
            if(in_array($pageProfile, $currentUser->getFollowing()->toArray())){
                $isFollowing = true;
            }else $isFollowing = false;
            
            if($pageProfile->getId() == $currentUser->getId())$isHownProfile = true;
        }
        
        $setModeratorForm = null;
        $isModerator = false;
        if($this->get('security.context')->isGranted('ROLE_ADMIN') && $currentUser != null) {
            if($this->get('pp_user.service.role')->isGranted('ROLE_MODERATOR', $pageProfile))$isModerator = true;
            
            /* create set moderator form */        
            $setModeratorForm = $this->get('form.factory')->createNamedBuilder('pp_user_api_patch_moderator_form', 'form', array(), array())         
                ->setAction($this->generateUrl('pp_user_api_patch_moderator', array("userId"=>$pageProfile->getId(), "page"=>1), true))
                ->getForm()
                ->createView();
        }               
        
        /////////////////////////////////
        ////////////// FORM /////////////
        
        /* create report ticket form */
        $reportTicketForm = null;
        $disableTicketForm = null;
        $reportReasonList = array();
        if($this->get('security.context')->isGranted('ROLE_USER')) {
            $reportReasonList = $em->getRepository("PPReportBundle:ReportReason")->findAll();
            $reportTicketForm = $this->get('form.factory')->createNamedBuilder('pp_report_api_post_report_ticket_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_report_api_post_report_ticket', array(), true))
            ->getForm()
            ->createView();
            
            if($this->get('security.context')->isGranted('ROLE_MODERATOR')) {              
            $disableTicketForm = $this->get('form.factory')->createNamedBuilder('pp_report_api_post_disable_ticket_form', 'form', array(), array())         
                ->setAction($this->generateUrl('pp_report_api_post_disable_ticket', array(), true))
                ->getForm()
                ->createView();
            }
            
        }
        
        /* create loadPage form */        
        $loadRequestForm = $this->get('form.factory')->createNamedBuilder('pp_user_api_get_user_request_form_1', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_user_api_get_user_request', array("userId"=>$pageProfile->getId(), "page"=>1), true))
            ->getForm();
        
        /*create following form */
        $followForm = $this->get('form.factory')->createNamedBuilder('pp_user_api_patch_user_follow_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_user_api_patch_user_follow', array("userId"=>$pageProfile->getId()), true))
            ->getForm();
        
        /* create upote request form */
        $upvoteRequestForm = $this->get('form.factory')->createNamedBuilder('pp_request_api_patch_request_vote', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_request_api_patch_request_vote', array(), true))
            ->getForm();
        
        $editProfileForm = null;
        if($isHownProfile){
            /*create following form */
            $editProfileForm = $this->get('form.factory')->createNamedBuilder('pp_user_api_get_edit_profile_form', 'form', array(), array())         
                ->setAction($this->generateUrl('pp_user_api_get_edit_profile_form', array(), true))
                ->getForm()
                ->createView();
        }
        
        /* create new image request form */         
        $imageRequest = new ImageRequest();
        $form = $this->get('form.factory')->create(new ImageRequestType, $imageRequest, array(            
            'action' => $this->generateUrl('pp_request_add_request'),
            'method' => 'POST',
        ));                
       
        
        /* handle POST data */         
        if ($request->isMethod('POST')) {
            if ($this->get('security.context')->isGranted('ROLE_USER')) {    
                $followForm->handleRequest($request);
                                               
                if ($followForm->isValid()) {
                    if(!in_array($pageProfile, $currentUser->getFollowing()->toArray())){
                        $currentUser->addFollowing($pageProfile);
                        $em->persist($currentUser);                        
                    }else{
                        $currentUser->removeFollowing($pageProfile);
                        $em->persist($currentUser);
                    }
                    $em->flush();
                     return $this->redirect($this->generateUrl('pp_user_profile', array(
                        'slug' => $slug,                        
                    )));
                }
            }
        }
        
        $galleryImgages = $propositionRepository->getOneUserPropositions($pageProfile->getId(), 4);
        $galleryImgagesNb = $propositionRepository->countOneUserPropositions($pageProfile->getId());
             
        
        return $this->render('PPUserBundle:Profile:show_profile.html.twig', array(
            'isHownProfile' => $isHownProfile,
            'pageProfile' => $pageProfile,
            'galleryImages' => $galleryImgages,
            'galleryImagesNb' => $galleryImgagesNb,
            'followForm' => $followForm->createView(),
            'form' => $form->createView(),
            'loadRequestForm' => $loadRequestForm->createView(),
            'editProfileForm' => $editProfileForm,
            'isFollowing' => $isFollowing,
            'upvoteRequestForm' => $upvoteRequestForm->createView(),
            'setModeratorForm' => $setModeratorForm,
            'isModerator' => $isModerator,
            "reportTicketForm" => $reportTicketForm,
            "disableTicketForm" => $disableTicketForm,
            "reportReasonList" => $reportReasonList
        ));
    }
    
    public function editAction(Request $request)
    {
        /* init repositories */
        $em = $this->getDoctrine()->getManager();       
        
        $currentUser = $this->getUser();            
        
        if($this->get('security.context')->isGranted('ROLE_USER') && $currentUser != null) {
                                  
            $editUserForm = $this->get('form.factory')->create(new EditProfileFormType($currentUser), $currentUser, array(                            
            ));                          
                        
            if ($request->isMethod('POST')) {            
                $editUserForm->handleRequest($request);
                if ($editUserForm->isValid()) {
                    $em->persist($currentUser);
                    $em->flush();
                    
                    $currentUser->createThumbnail();
                    
                    return $this->redirect($this->generateUrl('pp_user_profile', array(
                        'slug' => $currentUser->getSlug()                        
                    )));
                }
            }
            
            return $this->redirect($this->generateUrl('pp_user_profile', array(
                        'slug' => $currentUser->getSlug()
            )));
        }else{
            return $this->redirect($this->generateUrl('pp_request_homepage', array(                
            )));
        }
    }
    
   public function searchResultAction(){
       
       $loadUsersForm = $this->get('form.factory')->createNamedBuilder('pp_user_api_get_search_user_view_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_user_api_get_search_user_view', array(), true))
            ->getForm()
            ->createView();    
       
       return $this->render('PPUserBundle:Search:search_result.html.twig', array(
           "loadUsersForm" => $loadUsersForm
       ));
       
   }

}
