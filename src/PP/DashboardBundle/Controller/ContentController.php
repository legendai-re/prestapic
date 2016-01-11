<?php

namespace PP\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ContentController extends Controller
{
   /**
   * @Security("has_role('ROLE_MODERATOR')")
   */
    public function indexAction()
    {        
        $em = $this->getDoctrine()->getManager();                
        
        $getContentForm = $this->get('form.factory')->createNamedBuilder('pp_dashboard_content_api_get_content_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_dashboard_content_api_get_content', array(), true))
            ->getForm()
            ->createView();
        
        return $this->render('PPDashboardBundle:Content:manage_content.html.twig', array(
            "getContentForm" => $getContentForm
        ));
    }
}
