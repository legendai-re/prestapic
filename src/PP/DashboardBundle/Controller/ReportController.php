<?php

namespace PP\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ReportController extends Controller
{
    public function indexAction()
    {
        
        return $this->render('PPDashboardBundle:Report:manage_report.html.twig', array());
    }
}
