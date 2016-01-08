<?php

namespace PP\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ReportController extends Controller
{
   /**
   * @Security("has_role('ROLE_MODERATOR')")
   */
    public function indexAction()
    {        
        $em = $this->getDoctrine()->getManager();
        
        $reportReasonList = $em->getRepository("PPReportBundle:ReportReason")->findAll();            
                    
        $disableTicketForm = $this->get('form.factory')->createNamedBuilder('pp_report_api_post_disable_ticket_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_report_api_post_disable_ticket', array(), true))
            ->getForm()
            ->createView();
        
        $ignoreTicketsForm = $this->get('form.factory')->createNamedBuilder('pp_report_api_patch_ignore_tickets_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_report_api_patch_ignore_tickets', array(), true))
            ->getForm()
            ->createView();        
        
        $getReportObjectsForm = $this->get('form.factory')->createNamedBuilder('pp_dashboard_report_api_get_reported_objects_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_dashboard_report_api_get_reported_objects', array(), true))
            ->getForm()
            ->createView();
        
        $getReportTicketForm = $this->get('form.factory')->createNamedBuilder('pp_dashboard_report_api_get_report_ticket_form', 'form', array(), array())         
            ->setAction($this->generateUrl('pp_dashboard_report_api_get_report_ticket', array(), true))
            ->getForm()
            ->createView();
        
        return $this->render('PPDashboardBundle:Report:manage_report.html.twig', array(
            "reportReasonList" => $reportReasonList,
            "disableTicketForm" => $disableTicketForm,
            "ignoreTicketsForm" => $ignoreTicketsForm,
            "getReportObjectsForm" => $getReportObjectsForm,            
            "getReportTicketForm" => $getReportTicketForm
        ));
    }
}
