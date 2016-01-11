<?php

namespace PP\ReportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;

use PP\PropositionBundle\Form\Type\PropositionType;
use PP\PropositionBundle\Entity\Proposition;



class ReportController extends Controller
{
   
    public function reportPopupAction(){        
        
        $getReportForm = $this->get('form.factory')->createNamedBuilder('pp_report_api_get_report_form', 'form', array(), array())         
              ->setAction($this->generateUrl('pp_report_api_get_report_form', array(), true))
              ->getForm()
              ->createView();      
         
        return $this->render('PPReportBundle:Popup:formAction.html.twig', array(
            'getReportForm' => $getReportForm
        ));
        
    }
}
