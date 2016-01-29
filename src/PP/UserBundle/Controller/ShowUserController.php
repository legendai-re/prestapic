<?php

namespace PP\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

use PP\RequestBundle\Entity\ImageRequest;
use PP\RequestBundle\Form\Type\ImageRequestType;

use PP\UserBundle\Form\Type\EditProfileFormType;
use PP\UserBundle\Constant\UserConstants;
class ShowUserController extends Controller 
{
    public function indexAction(Request $request, $slug)
    {
        /* get session and currentUser*/
        $session = $this->getRequest()->getSession();
        $currentUser = $this->getUser();
        
        $contentToDisplay = UserConstants::DISPLAY_REQUEST;
        if($session->get('contentToDisplayProfile') != null){
            $contentToDisplay = $session->get('contentToDisplayProfile');
        }else {$contentToDisplay = UserConstants::DISPLAY_REQUEST;}
        
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
        $isBlocked = false;
        if($currentUser!=null){
            if(in_array($pageProfile, $currentUser->getFollowing()->toArray())){
                $isFollowing = true;
            }else $isFollowing = false;
            if(in_array($pageProfile, $currentUser->getBlockedUsers()->toArray())){
                $isBlocked = true;
            }else $isBlocked = false;
            if($pageProfile->getId() == $currentUser->getId())$isHownProfile = true;
        }
        
        $setModeratorForm = null;
        $setAdminForm = null;
        $isModerator = false;
        if($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && $currentUser != null) {
            if($this->get('security.authorization_checker')->isGranted('ROLE_MODERATOR'))$isModerator = true;
            
            /* create set moderator form */        
            $setModeratorForm = $this->get('form.factory')->createNamedBuilder('pp_user_api_patch_moderator_form', 'form', array(), array())         
                ->setAction($this->generateUrl('pp_user_api_patch_moderator', array("userId"=>$pageProfile->getId(), "page"=>1), true))
                ->getForm()
                ->createView();
            
            /* create set admin form */
            $data = array();
            $setAdminForm = $this->createFormBuilder($data);
            
            if(!$pageProfile->hasRole("ROLE_ADMIN")){
                $setAdminForm->add('set admin', 'submit');               
            }
            else{
                $setAdminForm->add('unset admin', 'submit');
            }                        
            $setAdminForm = $setAdminForm->setAction($this->generateUrl('pp_user_profile', array('slug' => $pageProfile->getSlug())))->getForm();                       
            if ($request->isMethod('POST')) {
                $setAdminForm->handleRequest($request);
                if ($setAdminForm->isValid()){
                    if(!$pageProfile->hasRole("ROLE_ADMIN")){$pageProfile->addRole("ROLE_ADMIN");}
                    else {$pageProfile->removeRole("ROLE_ADMIN");}
                    $em->flush();
                    return $this->redirect($this->generateUrl('pp_user_profile', array(
                        'slug' => $pageProfile->getSlug()
                    )));
                }        
            }
            
            $setAdminForm = $setAdminForm->createView();
        }               
        
        /////////////////////////////////
        ////////////// FORM /////////////       
        
        
        /* create loadPage form */        
        $loadRequestForm = $this->get('form.factory')->createNamedBuilder('pp_user_api_get_user_request', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_user_api_get_user_request', array("userId"=>$pageProfile->getId()), true))
            ->getForm();
        
        /*create following form */
        $followForm = $this->get('form.factory')->createNamedBuilder('pp_user_api_patch_user_follow_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_user_api_patch_user_follow', array("userId"=>$pageProfile->getId()), true))
            ->getForm();
        
        /*create following form */
        $blockForm = $this->get('form.factory')->createNamedBuilder('pp_user_api_patch_blocked_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_user_api_patch_blocked', array(), true))
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
        
        $galleryImgages = $propositionRepository->getOneUserPropositions($pageProfile->getId(), 6);
        $galleryImgagesNb = $propositionRepository->countOneUserPropositions($pageProfile->getId());
             
        
        return $this->render('PPUserBundle:Profile:show_profile.html.twig', array(
            'isHownProfile' => $isHownProfile,
            'pageProfile' => $pageProfile,
            'galleryImages' => $galleryImgages,
            'galleryImagesNb' => $galleryImgagesNb,
            'followForm' => $followForm->createView(),
            'blockForm' => $blockForm->createView(),
            'form' => $form->createView(),
            'loadRequestForm' => $loadRequestForm->createView(),
            'editProfileForm' => $editProfileForm,
            'isFollowing' => $isFollowing,
            'isBlocked' => $isBlocked,
            'upvoteRequestForm' => $upvoteRequestForm->createView(),
            'setModeratorForm' => $setModeratorForm,
            'isModerator' => $isModerator,
            'contentToDisplay' => $contentToDisplay,
            'setAdminForm' => $setAdminForm
        ));
    }        
    
    public function editAction(Request $request)
    {
        /* init repositories */
        $em = $this->getDoctrine()->getManager();       
        
        $currentUser = $this->getUser();            
        
        if($this->get('security.authorization_checker')->isGranted('ROLE_USER') && $currentUser != null) {
            
            $formUser = new \PP\UserBundle\Entity\User();
            $editUserForm = $this->get('form.factory')->create(new EditProfileFormType($currentUser), $formUser);                          
            $editUserForm->setData($currentUser);   
            
            if ($request->isMethod('POST')) {            
                $editUserForm->handleRequest($request);
                if ($editUserForm->isValid()) {
                    
                    if($formUser->getName() != null)$currentUser->setName($formUser->getName());
                    if($formUser->getProfilImage() != null)$currentUser->setProfilImage($formUser->getProfilImage());
                    if($formUser->getCoverImage() != null)$currentUser->setCoverImage($formUser->getCoverImage());
                    if($formUser->getDescription() != null)$currentUser->setDescription($formUser->getDescription());
                    if($formUser->getContact() != null)$currentUser->setContact($formUser->getContact());
                    
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
