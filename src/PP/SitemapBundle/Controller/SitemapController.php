<?php

namespace PP\SitemapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use PP\SitemapBundle\Constant\Constants;

class SitemapController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository('PPUserBundle:User');
        $imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');
        
        $userPageNb = sizeof($userRepository->findAll())/Constants::USER_PER_PAGE;
        $irPageNb = sizeof($imageRequestRepository->findAll())/Constants::IMPAGE_REQUEST_PER_PAGE;
        
        return $this->render('SitemapBundle:Sitemap:sitemap.xml.twig', array(
            "userPageNb" => $userPageNb,
            "irPageNb" => $irPageNb
        ));
    }
    
    public function mainAction()                        
    {                
        return $this->render('SitemapBundle:Sitemap:main.xml.twig', array(            
        ));
    }
    
    public function userAction($page)
    {
        $em = $this->getDoctrine()->getManager();	        
        $userRepository = $em->getRepository('PPUserBundle:User');
        
        $users = $userRepository->searchUser(null, null,Constants::USER_PER_PAGE, $page);
        
        return $this->render('SitemapBundle:Sitemap:user.xml.twig', array(
            "users" => $users
        ));
    }
    
     public function ImageRequestAction($page)
    {
        $em = $this->getDoctrine()->getManager();	        
        $imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');
        
        $imagesRequests = $imageRequestRepository->getImageRequest(Constants::IMPAGE_REQUEST_PER_PAGE, $page);
        
        return $this->render('SitemapBundle:Sitemap:request.xml.twig', array(
            "imagesRequests" => $imagesRequests
        ));
    }
    
}
