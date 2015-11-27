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

use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use PP\RequestBundle\Constant\Constants;

class RequestApiController extends Controller
{
    /*
     * get Image request
     * 
     * @params integer page
     * 
     * @return View
     */
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
        $concerningMeParam = false;
        $getParameters = array('page'=>1);
        
         /* handle GET data */
        if ($request->isMethod('GET')) {
            if($request->get('display_mode') != null){
                $session->set('imageRequestOrder', $request->get('display_mode'));
            }
            if($request->get('content_to_display') != null){
                $session->set('contentToDisplay', $request->get('content_to_display'));
            }
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
            
            if($currentUser!=null && $request->get('me') != null && $request->get('me') == "true"){
                $concerningMeParam = true;
            }
        }                
        
        /* init repositories */
        $em = $this->getDoctrine()->getManager();
	$imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');
        $propositionRepository = $em->getRepository('PPPropositionBundle:Proposition');
        $userRepository = $em->getRepository('PPUserBundle:User');        
        
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
        }else{
            $displayMode = Constants::ORDER_BY_DATE;
        }
        if($currentUser == null && $displayMode == Constants::ORDER_BY_INTEREST ){
            $displayMode = Constants::ORDER_BY_DATE;
        }
        
        if($session->get('contentToDisplay') != null){
            $contentToDisplay = $session->get('contentToDisplay');
        }else {$contentToDisplay = Constants::DISPLAY_REQUEST;}
                                                                            
        /* get image requests  and create edit image request form  */
        if($currentUser != null){
            $userId = $currentUser->getId();
        }
        else{
            $userId = 0;
        }

        $followingIds = $userRepository->getFollonwingIds($userId);                    
        
        $imageRequestList = array();
        $propositionsList = array();
        
        $contentToDisplay = $session->get("contentToDisplay");
        $haveNextPage = true;       
        
        if($contentToDisplay == Constants::DISPLAY_REQUEST){
            $canUpvoteImageRequest = array();
            $imageRequestsId = $imageRequestRepository->getImageRequestsId($em, $searchParam, Constants::REQUEST_PER_PAGE, $page, $displayMode, $userId, $followingIds, $tagsParam, $categoriesParam, $concerningMeParam);            
            foreach($imageRequestsId as $id){
                $tempImageRequest = $imageRequestRepository->getOneImageRequest($id["id"]);
                $tempImageRequest->setDateAgo($this->container->get('pp_notification.ago')->ago($tempImageRequest->getCreatedDate()));
                array_push($imageRequestList, $tempImageRequest);
                $tempPropositions = $propositionRepository->getPropositions($id["id"], 3, 1);
                $propositionsList['imageRequest_'.$id['id']] =  $tempPropositions;
                if($currentUser!=null && $currentUser->getId() != $tempImageRequest->getAuthor()->getId() && !$userRepository->haveLikedRequest($currentUser->getId(), $tempImageRequest->getId())){
                    $canUpvoteImageRequest[$tempImageRequest->getId()] = true;
                }else{ $canUpvoteImageRequest[$tempImageRequest->getId()] = false;}
            }
            if(sizeof($imageRequestList) < Constants::REQUEST_PER_PAGE){
                $haveNextPage = false;
            }
                     
        }else if($contentToDisplay == Constants::DISPLAY_PROPOSITION){
            $canUpvoteProposition = array();            
            $propositionsList =  $propositionRepository->getPropositions(null, Constants::PROPOSITION_PER_HOME_PAGE, $page, $displayMode, $userId, $followingIds, $searchParam, $tagsParam, $categoriesParam, $concerningMeParam);
            
            if($currentUser!=null){
                foreach($propositionsList as $proposition){
                    $canUpvoteProposition[$proposition->getId()] = false;
                    if($currentUser!=null && $currentUser->getId() != $proposition->getAuthor()->getId() && !$userRepository->haveLikedProposition($currentUser->getId(), $proposition->getId())){
                        $canUpvoteProposition[$proposition->getId()] = true;
                    } 
                }                        
            }
            
            if(sizeof($propositionsList) < Constants::PROPOSITION_PER_HOME_PAGE){
                $haveNextPage = false;
            }            
        }                        
        
        if($contentToDisplay == Constants::DISPLAY_REQUEST){
            $view = View::create()
                ->setData(array(                                      
                    'page'=>$page,
                    'nextPage' => $nextPage,
                    'haveNextPage' => $haveNextPage,
                    'imageRequestList' => $imageRequestList,                
                    'displayMode' => $displayMode,
                    'propositionsList' => $propositionsList,
                    'loadRequestForm' => $loadRequestForm->createView(),
                    'canUpvoteImageRequest' => $canUpvoteImageRequest
                ))
                ->setTemplate(new TemplateReference('PPRequestBundle', 'Request', 'requestList'));
        }else if($contentToDisplay == Constants::DISPLAY_PROPOSITION){
            $view = View::create()
                ->setData(array(                                      
                    'page'=>$page,
                    'nextPage' => $nextPage,
                    'haveNextPage' => $haveNextPage,                    
                    'propositionList' => $propositionsList,
                    'loadRequestForm' => $loadRequestForm->createView(),
                    'canUpvoteProposition' => $canUpvoteProposition
                ))
                ->setTemplate(new TemplateReference('PPRequestBundle', 'Request', 'propositionList'));
        }else{
            return new \Symfony\Component\HttpFoundation\Response();
        }

        return $this->getViewHandler()->handle($view);
        
    }
    
    /*
     * Upvote an image request
     * 
     * @params integer id (imageRequest id)
     * 
     * @return JsonResponse
     */
    public function patchRequestVoteAction(Request $request){
        
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');
        
        $id = $request->get("id");
        
        if ($this->get('security.context')->isGranted('ROLE_USER') && $id != null) { 
            
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
            else {$response->setData(json_encode(array('succes'=>false)));}
        }
        else {$response->setData(json_encode(array('succes'=>false)));}
        
        return $response;
    }
    
    /*
     * Get propositions of one image request
     * 
     * @params  integer imageRequestId
     *          integer page
     * 
     * @return  View
     */
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
        
        if ($currentUser != null && $imageRequest->getAuthor()->getId() == $currentUser->getId() && !$imageRequest->getClosed()) {
            $canSelectProposition = true;
        }

        foreach ($propositionList as $proposition){
            $canUpvoteProposition[$proposition->getId()] = false;
            
            if($currentUser!=null && $currentUser->getId() != $proposition->getAuthor()->getId() && !$userRepository->haveLikedProposition($currentUser->getId(), $proposition->getId())){
                $canUpvoteProposition[$proposition->getId()] = true;
            }            
                        
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