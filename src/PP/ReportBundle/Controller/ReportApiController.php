<?php

namespace PP\ReportBundle\Controller;

use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use PP\ReportBundle\Constant\ReportTicketType;

use PP\ReportBundle\Entity\DisableTicket;
use PP\ReportBundle\Entity\ReportTicket;

class ReportApiController extends Controller
{
    
    public function getReportFormAction(Request $request){
        $response = new Response();
        $currentUser = $this->getUser();
        
        $type = $request->get("type");
        
        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER') && $currentUser != null && $type!=null) {
            
            $em = $this->getDoctrine()->getManager();
            $reportReasonRepository = $em->getRepository("PPReportBundle:ReportReason");
            
            $reportReasons = $reportReasonRepository->findByType($type);
            $reportTicketForm = $this->get('form.factory')->createNamedBuilder('pp_report_api_post_report_ticket_form', 'form', array(), array())         
                ->setAction($this->generateUrl('pp_report_api_post_report_ticket', array(), true))
                ->getForm()
                ->createView();
            
            $view = View::create()
                ->setData(array(
                    "reportReasons" => $reportReasons,
                    "reportTicketForm" => $reportTicketForm
                ))
                ->setTemplate(new TemplateReference('PPReportBundle', 'Popup', 'reportForm'));   
             
            return $this->getViewHandler()->handle($view);
        }else return new Response();
        
    }
    
    
    public function postReportTicketAction(Request $request){
        $response = new Response();
        $currentUser = $this->getUser();
        
        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER') && $currentUser != null) {            
            if($request->get("ticketType")!=null && $request->get("reasonId")!=null && $request->get("targetId")!=null){
                
                $targetId = $request->get("targetId");
                $em = $this->getDoctrine()->getManager();
                $imageRequestRepository = $em->getRepository("PPRequestBundle:ImageRequest");
                $userRepository = $em->getRepository("PPUserBundle:User");
                $propositionRepository = $em->getRepository("PPPropositionBundle:Proposition");
                $reason = $em->getRepository("PPReportBundle:ReportReason")->find($request->get("reasonId")); 
                if($reason != null){
                    $reportTicket = new ReportTicket();                    
                    $reportTicket->setAuthor($currentUser);
                    $reportTicket->setReason($reason);
                    $reportTicket->setFinished(false);
                    $reportTicket->setAccepted(false);                    
                    if($request->get("details")!=null)$reportTicket->setDetails ($request->get("details"));
                    
                    switch ($request->get("ticketType")){
                        case ReportTicketType::IMAGE_REQUEST:
                            $imageRequest = $imageRequestRepository->find($targetId);
                            if($imageRequest != null){
                                $imageRequest->addReportNb();
                                $reportTicket->setReportTicketType(ReportTicketType::IMAGE_REQUEST);
                                $reportTicket->setTargetId($imageRequest->getId());
                                $em->persist($imageRequest);
                                $em->persist($reportTicket);
                                $em->flush();                                
                            }
                            break;
                        case ReportTicketType::USER:
                            $user = $userRepository->find($targetId);
                            if($user != null){
                                $user->addReportNb();
                                $reportTicket->setReportTicketType(ReportTicketType::USER);
                                $reportTicket->setTargetId($user->getId());
                                $em->persist($reportTicket);
                                $em->persist($user);
                                $em->flush();
                            }
                            break;
                        case ReportTicketType::PROPOSITION:
                            $proposition = $propositionRepository->find($targetId);
                            if($proposition!=null){
                                $proposition->addReportNb();
                                $reportTicket->setReportTicketType(ReportTicketType::PROPOSITION);
                                $reportTicket->setTargetId($proposition->getId());
                                $em->persist($reportTicket);
                                $em->persist($proposition);
                                $em->flush();
                            }
                    }
                }
            }else{$response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);}                        
        }else {$response->setStatusCode(Response::HTTP_FORBIDDEN);}
        
        return $response;
    }
    
     public function postDisableTicketAction(Request $request){
        $response = new Response();
        $currentUser = $this->getUser();
        
        if ($currentUser != null) {
            if($request->get("ticketType")!=null && $request->get("reasonId")!=null && $request->get("targetId")!=null){
                
                $targetId = $request->get("targetId");
                $em = $this->getDoctrine()->getManager();
                $imageRequestRepository = $em->getRepository("PPRequestBundle:ImageRequest");
                $reportTicketRepository = $em->getRepository("PPReportBundle:ReportTicket");
                $userRepository = $em->getRepository("PPUserBundle:User");
                $propositionRepository = $em->getRepository("PPPropositionBundle:Proposition");
                $reason = $em->getRepository("PPReportBundle:ReportReason")->find($request->get("reasonId")); 
                if($reason != null){
                    $disableTicket = new DisableTicket();                    
                    $disableTicket->setAuthor($currentUser);
                    $disableTicket->setReason($reason);
                    if($request->get("details")!=null)$disableTicket->setDetails ($request->get("details"));
                    
                    switch ($request->get("ticketType")){
                        case ReportTicketType::IMAGE_REQUEST:
                            $imageRequest = $imageRequestRepository->find($targetId);
                            if($imageRequest != null && ($this->get('security.authorization_checker')->isGranted('ROLE_MODERATOR') || $currentUser->getId() == $imageRequest->getAuthor()->getId())){                                
                                $disableTicket->setDisableTicketType(ReportTicketType::IMAGE_REQUEST);
                                $disableTicket->setTargetId($imageRequest->getId());
                                $imageRequest->setDisableTicket($disableTicket);
                                $imageRequest->setEnabled(false);
                                $em->persist($disableTicket);
                                $em->persist($imageRequest);
                                $em->flush();                                                                
                            }
                            break;
                        case ReportTicketType::USER:
                            $user = $userRepository->find($targetId);
                            if($user != null && ($this->get('security.authorization_checker')->isGranted('ROLE_MODERATOR') || $currentUser->getId() == $user->getId())){
                                $user->addReportNb();
                                $disableTicket->setDisableTicketType(ReportTicketType::USER);
                                $disableTicket->setTargetId($user->getId());
                                $user->setEnabled(false);
                                $user->setDisableTicket($disableTicket);
                                $em->persist($disableTicket);
                                $em->persist($user);
                                $em->flush();
                            }
                            break;
                        case ReportTicketType::PROPOSITION:
                            $proposition = $propositionRepository->find($targetId);
                            if($proposition != null && ($this->get('security.authorization_checker')->isGranted('ROLE_MODERATOR') || $proposition->getAuthor()->getId() == $user->getId())){
                                $proposition->addReportNb();
                                $disableTicket->setDisableTicketType(ReportTicketType::PROPOSITION);
                                $disableTicket->setTargetId($proposition->getId());
                                $proposition->setEnabled(false);
                                $proposition->setDisableTicket($disableTicket);
                                $em->persist($disableTicket);
                                $em->persist($proposition);
                                $em->flush();
                            }
                            break;
                    }
                    
                    $ticketToFinish = $reportTicketRepository->getTicketByType($request->get("ticketType"), $targetId);
                    foreach($ticketToFinish as $ticket){
                        $ticket->setFinished(true);
                        $em->persist($ticket);
                    }
                    $em->flush();
                }
            }                        
        }else {$response->setStatusCode(Response::HTTP_FORBIDDEN);}
        
        return $response;
     }
    
    /**
    * @Security("has_role('ROLE_MODERATOR')")
    */
    public function patchIgnoreTicketsAction(Request $request){
        $response = new Response();
        
        $type = $request->get("ticketType");
        $targetId = $request->get("targetId");
        
        if($type != null && $targetId != null){
            $em = $this->getDoctrine()->getManager();            
            $reportTicketRepository = $em->getRepository('PPReportBundle:ReportTicket');
            $imageRequestRepository = $em->getRepository("PPRequestBundle:ImageRequest");
            $userRepository = $em->getRepository("PPUserBundle:User");
            $propositionRepository = $em->getRepository("PPPropositionBundle:Proposition");
            
            $ticketList = $reportTicketRepository->getTicketByType($type, $targetId);
            
             switch ($request->get("ticketType")){
                case ReportTicketType::IMAGE_REQUEST:
                   $imageRequest = $imageRequestRepository->find($targetId);
                   $imageRequest->setReportNb(0);
                   $em->persist($imageRequest);
                   break;
                case ReportTicketType::USER:
                   $user = $userRepository->find($targetId);
                   $user->setReportNb(0);
                   $em->persist($user);
                   break;
                case ReportTicketType::PROPOSITION:
                   $proposition = $propositionRepository->find($targetId);
                   $proposition->setReportNb(0);
                   $em->persist($proposition);
                   break;
             }
            
            foreach($ticketList as $ticket){
                $ticket->setFinished(true);
                $em->persist($ticket);
            };
            $em->flush();
        }else {$response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);}
        
        return $response;
    }
    
    private function getViewHandler()
    {
        return $this->container->get('fos_rest.view_handler');
    }
}
