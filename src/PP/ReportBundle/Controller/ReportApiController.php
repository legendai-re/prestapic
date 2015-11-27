<?php

namespace PP\ReportBundle\Controller;

use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use PP\RequestBundle\Constant\Constants;
use PP\ReportBundle\Constant\ReportTicketType;

use PP\ReportBundle\Entity\DisableTicket;
use PP\ReportBundle\Entity\ReportTicket;
use PP\ReportBundle\Entity\ReportReason;

class ReportApiController extends Controller
{
    public function postReportTicketAction(Request $request){
        $response = new Response();
        $currentUser = $this->getUser();
        
        if ($this->get('security.context')->isGranted('ROLE_USER') && $currentUser != null) {
            if($request->get("ticketType")!=null && $request->get("reasonId")!=null && $request->get("targetId")!=null){
                
                $targetId = $request->get("targetId");
                $em = $this->getDoctrine()->getManager();
                $imageRequestRepository = $em->getRepository("PPRequestBundle:ImageRequest");
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
                                $reportTicket->setReportTicketType(ReportTicketType::IMAGE_REQUEST);
                                $reportTicket->setTargetId($imageRequest->getId());
                                $em->persist($reportTicket);
                                $em->flush();                                
                            }
                            break;
                    }
                }
            }                        
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
                $reason = $em->getRepository("PPReportBundle:ReportReason")->find($request->get("reasonId")); 
                if($reason != null){
                    $disableTicket = new DisableTicket();                    
                    $disableTicket->setAuthor($currentUser);
                    $disableTicket->setReason($reason);
                    if($request->get("details")!=null)$disableTicket->setDetails ($request->get("details"));
                    
                    switch ($request->get("ticketType")){
                        case ReportTicketType::IMAGE_REQUEST:
                            $imageRequest = $imageRequestRepository->find($targetId);
                            if($imageRequest != null && ($this->get('security.context')->isGranted('ROLE_MODERATOR') || $currentUser->getId() == $imageRequest->getAuthor()->getId())){                                
                                $disableTicket->setDisableTicketType(ReportTicketType::IMAGE_REQUEST);
                                $disableTicket->setTargetId($imageRequest->getId());
                                $imageRequest->setDisableTicket($disableTicket);
                                $imageRequest->setEnabled(false);
                                $em->persist($disableTicket);
                                $em->persist($imageRequest);
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
    
    private function getViewHandler()
    {
        return $this->container->get('fos_rest.view_handler');
    }
}
