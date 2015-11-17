<?php

/**
 * This file is part of the FOSCommentBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PP\RequestBundle\Controller;


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

use PP\RequestBundle\Form\Type\ImageRequestType;
use PP\RequestBundle\Entity\ImageRequest;
use PP\PropositionBundle\Form\Type\PropositionType;
use PP\PropositionBundle\Entity\Proposition;
use Symfony\Component\Validator\Constraints\DateTime;
use PP\RequestBundle\Constant\Constants;

class RequestApiController extends Controller
{
           
    public function getRequestAction(Request $request, $page)
    {
                
        /* get session and currentUser*/
        $session = $this->getRequest()->getSession();
        $currentUser = $this->getUser();
        
        /* will be true if search was done */
        $haveSearchParam = false;
        $searchParam = null;
        $tagsParam = array();
        $categoriesParam = array();
        $getParameters = array('page'=>1);
        
         /* handle GET data */
        if ($request->isMethod('GET')) {            
            if($request->get('search_query') != null){
                $haveSearchParam = true;
                $searchParam = $request->get('search_query');
                $getParameters['search_query'] = $searchParam;                
            }
            if($request->get('tags') != null){                
                $tagsParam = explode(" ",  $request->get('tags'));
            }
            if($request->get('categories') != null){
                $categoriesParam = explode(" ",  $request->get('categories'));
            }
        }                
        
        /* init repositories */
        $em = $this->getDoctrine()->getManager();
	$imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');
        $propositionRepository = $em->getRepository('PPPropositionBundle:Proposition');
        $userRepository = $em->getRepository('PPUserBundle:User');
        $tagRepository = $em->getRepository('PPRequestBundle:Tag');                                
        
        /* set page to GET parameters */
        $getParameters['page'] = $page;
                
        $nextPage = $page+1;
        
        /* create loadPage form */
        $loadRequestForm = $this->get('form.factory')->createNamedBuilder('pp_request_api_get_request_form_'.$nextPage, 'form', array(), array())         
            ->setAction($this->generateUrl('pp_request_api_get_request', array("page"=>$nextPage), true))
            ->getForm();
        
        /* set displayMode (default ORDER_BY_DATE) */
        if($session->get('imageRequestOrder') != null){
            $displayMode = $session->get('imageRequestOrder');
        }else $displayMode = Constants::ORDER_BY_DATE;
        if($currentUser == null && $displayMode == Constants::ORDER_BY_INTEREST )$displayMode = Constants::ORDER_BY_DATE;               
                                                                            
        /* get image requests  and create edit image request form  */
        if($currentUser != null)$userId = $currentUser->getId();
        else $userId = 0;
        $followingIds = $userRepository->getFollonwingIds($userId);            
        $imageRequestsId = $imageRequestRepository->getImageRequestsId($em, $searchParam, Constants::REQUEST_PER_PAGE, $page, $displayMode, $userId, $followingIds, $tagsParam, $categoriesParam);
        
        $imageRequestList = array();
        $propositionsList = array();
        
        foreach($imageRequestsId as $id){
            $tempImageRequest = $imageRequestRepository->getOneImageRequest($id["id"]);
            $tempImageRequest->setDateAgo($this->container->get('pp_notification.ago')->ago($tempImageRequest->getCreatedDate()));
            array_push($imageRequestList, $tempImageRequest);
            $tempPropositions = $propositionRepository->getPropositions($id["id"], 3, 1);
            $propositionsList['imageRequest_'.$id['id']] =  $tempPropositions;           
        }                                          
        
        $haveNextPage = true;       
        if(sizeof($imageRequestList) < Constants::REQUEST_PER_PAGE)$haveNextPage = false;        
        
        $view = View::create()
            ->setData(array(                                      
                'page'=>$page,
                'nextPage' => $nextPage,
                'haveNextPage' => $haveNextPage,
                'imageRequestList' => $imageRequestList,                
                'displayMode' => $displayMode,
                'propositionsList' => $propositionsList,
                'loadRequestForm' => $loadRequestForm->createView(),
            ))
            ->setTemplate(new TemplateReference('PPRequestBundle', 'Request', 'requestList'));

        return $this->getViewHandler()->handle($view);
        
    }
    
    public function patchRequestVoteAction($id){
        
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            
            $em = $this->getDoctrine()->getManager();
            $imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');
            $userRepository = $em->getRepository('PPUserBundle:User');
            
            $imageRequest = $imageRequestRepository->find($id);         
            $currentUser = $this->getUser();
            
            if($currentUser->getId() != $imageRequest->getAuthor()->getId() && !$userRepository->haveLikedRequest($currentUser->getId(), $imageRequest->getId())){
                $imageRequest->addUpvote();
                $imageRequest->addUpvotedBy($currentUser);
                $em->persist($imageRequest);
                $em->flush();
                $response->setData(json_encode(array('succes'=>true, 'upvote'=>$imageRequest->getUpvote())));
            }
            else $response->setData(json_encode(array('succes'=>false)));
        }
        else $response->setData(json_encode(array('succes'=>false)));
        
        return $response;
    }
    
    public function getRequestPropositionAction($imageRequestId, $page){
        
        /* init repositories */
        $em = $this->getDoctrine()->getManager();
        $propositionRepository = $em->getRepository('PPPropositionBundle:Proposition');
        $imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');
        $userRepository = $em->getRepository('PPUserBundle:User');        
        
        /* get current user */
        $currentUser = $this->getUser();
        
        $nextPage = $page+1;                
        /* create loadPage form */
        $loadPropositionForm = $this->get('form.factory')->createNamedBuilder('pp_request_api_get_request_proposition_form_'.$nextPage, 'form', array(), array())         
            ->setAction($this->generateUrl('pp_request_api_get_request_proposition', array("imageRequestId"=>$imageRequestId , "page"=>$nextPage), true))
            ->getForm();
        
        
        /* get image request by id */	
        $nbOfTotalProposition = $propositionRepository->countPropositions($imageRequestId);
	$imageRequest = $imageRequestRepository->getOneImageRequest($imageRequestId);
        $propositionList = $propositionRepository->getPropositions($imageRequestId, Constants::PROPOSITION_PER_PAGE, $page);
        $nbPages = ceil($nbOfTotalProposition/Constants::PROPOSITION_PER_PAGE);
        $canUpvoteProposition = array();
        $upvotePropositionForms = array();
        $selectPropositionForms = array();
        $canSelectProposition = false;
        
        if($currentUser!=null && $imageRequest->getAuthor()->getId() == $currentUser->getId() && !$imageRequest->getClosed())$canSelectProposition = true;                
        
        foreach ($propositionList as $proposition){
            $canUpvoteProposition[$proposition->getId()] = false;
            
            if($currentUser!=null && $currentUser->getId() != $proposition->getAuthor()->getId() && !$userRepository->haveLikedProposition($currentUser->getId(), $proposition->getId())){
                $canUpvoteProposition[$proposition->getId()] = true;
            }
            /* create upvote proposition form */
             $upvotePropositionForms[$proposition->getId()] = $this->get('form.factory')->createNamedBuilder('pp_proposition_api_patch_proposition_vote_form_'.$proposition->getId(), 'form', array(), array())         
            ->setAction($this->generateUrl('pp_proposition_api_patch_proposition_vote', array('propositionId'=>$proposition->getId()), true))
            ->getForm()
            ->createView();
                        
            if($canSelectProposition){                
                /* create select proposition form */
                $selectPropositionForms[$proposition->getId()] = $this->get('form.factory')->createNamedBuilder('pp_proposition_api_patch_request_select_form_'.$proposition->getId(), 'form', array(), array())         
               ->setAction($this->generateUrl('pp_proposition_api_patch_request_select', array('imageRequestId'=>$imageRequestId,'propositionId'=>$proposition->getId()), true))
               ->getForm()
               ->createView();
            }
        }
        
        
        
        $view = View::create()
            ->setData(array(                      
                'nbPages'=>$nbPages,
                'page'=>$page,
                'nextPage' => $nextPage,             
                'propositionList' => $propositionList,
                'canUpvoteProposition' => $canUpvoteProposition,
                'canSelectProposition' => $canSelectProposition,
                'loadPropositionForm' => $loadPropositionForm->createView(),
                'upvotePropositionForms' => $upvotePropositionForms,
                'selectPropositionForms' => $selectPropositionForms
            ))
            ->setTemplate(new TemplateReference('PPPropositionBundle', 'proposition', 'propositionList'));

        return $this->getViewHandler()->handle($view);
    }

    private function getViewHandler()
    {
        return $this->container->get('fos_rest.view_handler');
    }
}
