<?php

namespace PP\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use PP\RequestBundle\Entity\Category;

use PP\ReportBundle\Entity\ReportReason;

use PP\DashboardBundle\JsonModel\JsonAllContentModel;
use PP\DashboardBundle\JsonModel\JsonCategoryModel;
use PP\DashboardBundle\JsonModel\JsonTagModel;
use PP\DashboardBundle\JsonModel\JsonReportReasonModel;

class ContentManageApiController extends Controller
{
    /**
    * @Security("has_role('ROLE_ADMIN')")
    */
    public function getContentAction()
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/x-javascript');
        
        $em = $this->getDoctrine()->getManager();
        $categoryRepository = $em->getRepository('PPRequestBundle:Category');
        $tagRepository = $em->getRepository('PPRequestBundle:Tag');
        $reportReasonRepository = $em->getRepository('PPReportBundle:ReportReason');
        
        $categories = $categoryRepository->findAll();
        $tags = $tagRepository->findAll();
        $reportReasons = $reportReasonRepository->findByEnabled(true);
        
        $jsonCategories = array();
        foreach ($categories as $category){
            $jsonCategories[$category->getId()] = new JsonCategoryModel($category->getId(), $category->getName());
        }
        
        $jsonTags = array();
        foreach ($tags as $tag){
            $jsonTags[$tag->getId()] = new JsonTagModel($tag->getId(), $tag->getName());
        }
        
        $jsonReportReasons = array();
        foreach($reportReasons as $reportReason){
            $jsonReportReasons[$reportReason->getId()] = new JsonReportReasonModel($reportReason->getId(), $reportReason->getName(), $reportReason->getType());
        }
        
        echo json_encode(new JsonAllContentModel(
                $jsonCategories,
                $jsonTags,
                $jsonReportReasons,
                $this->generateUrl("pp_dashboard_content_api_post_category", array(), true),
                $this->generateUrl("pp_dashboard_content_api_post_delete_category", array(), true),
                $this->generateUrl("pp_dashboard_content_api_patch_category", array(), true),
                $this->generateUrl("pp_dashboard_content_api_post_delete_tag", array(), true),
                $this->generateUrl("pp_dashboard_content_api_post_report_reason", array(), true),
                $this->generateUrl("pp_dashboard_content_api_post_delete_report_reason", array(), true)
        ));
        
        return $response;
    }
    
    /**
    * @Security("has_role('ROLE_ADMIN')")
    */
    public function postCategoryAction(Request $request){
        $response = new Response();          
        $newCat = $request->get("name");
        if($newCat != null){
            
            $em = $this->getDoctrine()->getManager();
            $categoryRepository = $em->getRepository('PPRequestBundle:Category');
            
            if($categoryRepository->findOneBy(array("name" => $newCat)) == null){
                $category = new Category();
                $category->setName($newCat);
                $em->persist($category);
                $em->flush();
                echo json_encode(array("id"=>$category->getId()));
            }else {$response->setStatusCode(Response::HTTP_CONFLICT);}            
        }
        
        return $response;
    }
    
     /**
    * @Security("has_role('ROLE_ADMIN')")
    */
    public function patchCategoryAction(Request $request){
        $response = new Response();          
        $catId = $request->get("id");
        $catName = $request->get("name");
        
        if($catId != null && $catName != "Uncategorize"){
                        
            $em = $this->getDoctrine()->getManager();
            $categoryRepository = $em->getRepository('PPRequestBundle:Category');            
            
            if($categoryRepository->findOneBy(array("name" => $catName)) == null){
            
                $catToPatch = $categoryRepository->find($catId);
                if($catToPatch != null){
                    $catToPatch->setName($catName);
                    $em->persist($catToPatch);
                    $em->flush();                    
                }else {$response->setStatusCode(Response::HTTP_NO_CONTENT);}
                
            }else {$response->setStatusCode(Response::HTTP_CONFLICT);}            
            
        }else {$response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);}
        
        return $response;
    }
    
    /**
    * @Security("has_role('ROLE_ADMIN')")
    */
    public function postDeleteCategoryAction(Request $request){
        $response = new Response();          
        $catId = $request->get("catId");

        if($catId != null){
            
            $em = $this->getDoctrine()->getManager();
            $categoryRepository = $em->getRepository('PPRequestBundle:Category');
            $imageRequestRepository = $em->getRepository("PPRequestBundle:ImageRequest");
                               
            $catToDelete = $categoryRepository->find($catId);
            $uncategorize = $categoryRepository->findOneBy(array("name" => "Uncategorize"));
            if($catToDelete != null && $catToDelete != $uncategorize){
                
                $imageRequests = $imageRequestRepository->findBy(array("category" => $catToDelete));
                foreach ($imageRequests as $imageRequest){
                    $uncategorize = $categoryRepository->findOneBy(array("name" => "Uncategorize"));
                    $imageRequest->setCategory($uncategorize);
                    $em->persist($imageRequest);
                }                
                $em->remove($catToDelete);
                $em->flush();
            }else {$response->setStatusCode(Response::HTTP_FORBIDDEN);}   
                             
        }
        
        return $response;
    }
    
    /**
    * @Security("has_role('ROLE_ADMIN')")
    */
    public function postDeleteTagAction(Request $request){
        $response = new Response();          
        $tagId = $request->get("id");

        if($tagId != null){
            
            $em = $this->getDoctrine()->getManager();
            $tagRepository = $em->getRepository('PPRequestBundle:Tag');
            $imageRequestRepository = $em->getRepository("PPRequestBundle:ImageRequest");
                               
            $tagToDelete = $tagRepository->find($tagId);
            
            if($tagToDelete){                
                $imageRequests = $imageRequestRepository->getImageRequestByTag($tagId);
                foreach ($imageRequests as $imageRequest){                    
                    $imageRequest->removeTag($tagToDelete);
                    $em->persist($imageRequest);
                }                
                $em->remove($tagToDelete);
                $em->flush();
            }                             
        }        
        return $response;
    }
    
    /**
    * @Security("has_role('ROLE_ADMIN')")
    */
    public function postReportReasonAction(Request $request){
        $response = new Response();          
        $type = $request->get("type");
        $name = $request->get("name");

        if($type != null && $name != null){
            
            $em = $this->getDoctrine()->getManager();
            
            $reportReason = new ReportReason();
            $reportReason->setDetails("empry");
            $reportReason->setName($name);
            $reportReason->setType($type);
                       
            $em->persist($reportReason);
            $em->flush();
            echo json_encode(array("id"=>$reportReason->getId()));
        }        
        return $response;
    }
    
    /**
    * @Security("has_role('ROLE_ADMIN')")
    */
    public function postDeleteReportReasonAction(Request $request){
        $response = new Response();          
        $reportReasonId = $request->get("id");

        if($reportReasonId != null){
            
            $em = $this->getDoctrine()->getManager();
            $reportReasonRepository = $em->getRepository('PPReportBundle:ReportReason');            
                               
            $reportReasonToDelete = $reportReasonRepository->find($reportReasonId);
            
            if($reportReasonToDelete){                
                $reportReasonToDelete->setEnabled(false);
                $em->persist($reportReasonToDelete);
                $em->flush();
            }                             
        }        
        return $response;
    }
    
    
    private function getViewHandler()
    {
        return $this->container->get('fos_rest.view_handler');
    }
}
