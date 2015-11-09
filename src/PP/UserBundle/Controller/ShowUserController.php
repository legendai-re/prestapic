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
    public function indexAction(Request $request, $slug, $page)
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
        
        
       
        
        
        /////////////////////////////////
        ////////////// FORM /////////////
        
        /* create loadPage form */        
        $loadRequestForm = $this->get('form.factory')->createNamedBuilder('pp_user_api_get_user_request_form_1', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_user_api_get_user_request', array("userId"=>$pageProfile->getId(), "page"=>1), true))
            ->getForm();
        
        /*create following form */
        $followForm = $this->get('form.factory')->createNamedBuilder('pp_user_api_patch_user_follow_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_user_api_patch_user_follow', array("userId"=>$pageProfile->getId()), true))
            ->getForm();                                
        
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
                        'page' => 1
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
            'isFollowing' => $isFollowing
        ));
    }
    
    public function editAction(Request $request, $slug)
    {
        /* get session and currentUser*/
        $session = $this->getRequest()->getSession();
        $currentUser = $this->getUser();
        
        /* init repositories */
        $em = $this->getDoctrine()->getManager();	
        $userRepository = $em->getRepository('PPUserBundle:User');       
        
        $currentUser = $this->getUser();
        $pageProfile = $userRepository->getUserBySlug($slug);                
        
        if($this->get('security.context')->isGranted('ROLE_USER') && $pageProfile != null && $currentUser != null && $currentUser->getId() == $pageProfile->getId()) {
                                  
            $editUserForm = $this->get('form.factory')->create(new EditProfileFormType($currentUser), $currentUser, array(                            
            ));                          
            
            
            if ($request->isMethod('POST')) {            
                $editUserForm->handleRequest($request);
                if ($editUserForm->isValid()) {
                    $em->persist($currentUser);
                    $em->flush();
                    
                    $currentUser->createThumbnail();
                    
                    return $this->redirect($this->generateUrl('pp_user_profile', array(
                        'slug' => $currentUser->getSlug(),
                        'page' => 1
                    )));
                }
            }
            
            return $this->render('PPUserBundle:Profile:edit_profile.html.twig', array(
                'currentUser' =>$currentUser,
                'editUserForm' => $editUserForm->createView()
            ));
        }else{
            return $this->redirect($this->generateUrl('pp_request_homepage', array(
                'page' => 1 
            )));
        }
    }
    
   

}
