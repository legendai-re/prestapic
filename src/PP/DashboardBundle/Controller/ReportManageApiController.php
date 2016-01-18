<?php

namespace PP\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use PP\DashboardBundle\JsonModel\JsonAllReportsModel;
use PP\DashboardBundle\JsonModel\JsonRequestModel;
use PP\DashboardBundle\JsonModel\JsonUserReportedModel;
use PP\ReportBundle\JsonModel\JsonReportReasonModel;
use PP\ReportBundle\JsonModel\JsonReportTicketModel;
use PP\MessageBundle\JsonModel\JsonUserModel;
use PP\DashboardBundle\JsonModel\JsonPropositionReportedModel;

class ReportManageApiController extends Controller
{
    /**
    * @Security("has_role('ROLE_MODERATOR')")
    */
    public function getReportedObjectsAction(Request $request)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/x-javascript');
        $currentUser = $this->getUser();
        
        if ($currentUser != null) {
            $em = $this->getDoctrine()->getManager();
            $imageRequestRepository = $em->getRepository('PPRequestBundle:ImageRequest');
            $userRepository = $em->getRepository('PPUserBundle:User');
            $propositionRepository = $em->getRepository('PPPropositionBundle:Proposition');
            $reportTicketRepository = $em->getRepository('PPReportBundle:ReportTicket');                        
            
            $reportedProposition = $propositionRepository->getReportedProposition();
            $reportImageRequest = $imageRequestRepository->getReportedImageRequest();
            $reportedUser = $userRepository->getReportedUser();
            $jsonReportedImageRequests = array();
            foreach ($reportImageRequest as $imageRequest){
                $jsonReportedImageRequests[$imageRequest->getId()] = new JsonRequestModel(
                                    $imageRequest->getId(),
                                    $imageRequest->getTitle(),
                                    $imageRequest->getRequest(),
                                    new JsonUserModel($imageRequest->getAuthor()->getId(), $imageRequest->getAuthor()->getName(), $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$imageRequest->getAuthor()->getProfilImage()->getWebPath('70x70')),
                                    array(),
                                    $imageRequest->getReportNb(),
                                    $imageRequest->getCreatedDate()
                );
            }
            
            $jsonReportUser = array();            
            foreach ($reportedUser as $user){
                $coverImageUrl = null;
                if($user->getCoverImage() != null){
                    $coverImageUrl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$user->getCoverImage()->getWebPath('1500x500');
                }
                $jsonReportUser[$user->getId()] = new JsonUserReportedModel(
                                    $user->getId(),
                                    $user->getName(),
                                    $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$user->getProfilImage()->getWebPath('70x70'),
                                    $coverImageUrl,
                                    $user->getReportNb()                                    
                );
            }
            
            $jsonReportedProposition = array();
            foreach ($reportedProposition as $proposition){
                
                $jsonReportedProposition[$proposition->getId()] = new JsonPropositionReportedModel(
                                    $proposition->getId(),
                                    $proposition->getTitle(),
                                    $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$proposition->getImage()->getWebPath('selected'),                                    
                                    new JsonUserModel($proposition->getAuthor()->getId(), $proposition->getAuthor()->getName(), $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$proposition->getAuthor()->getProfilImage()->getWebPath('70x70')),
                                    $proposition->getReportNb()                                    
                );
            }
            
            $reportObjects = new JsonAllReportsModel($jsonReportedImageRequests, $jsonReportedProposition, $jsonReportUser);
            echo json_encode($reportObjects);
            
        }else $response->setStatusCode(Response::HTTP_FORBIDDEN);
        
        return $response;
    }
    
    /**
    * @Security("has_role('ROLE_MODERATOR')")
    */
    public function getReportTicketAction(Request $request){
        
        $response = new Response();
        $response->headers->set('Content-Type', 'application/x-javascript');
        
        $type = $request->get("type");
        $targetId = $request->get("targetId");
        $em = $this->getDoctrine()->getManager();            
        $reportTicketRepository = $em->getRepository('PPReportBundle:ReportTicket');
        
        if($type != null && $targetId != null){
            $ticketList = $reportTicketRepository->getTicketByType($type, $targetId);
            $jsonTicketList = array();

            foreach ($ticketList as $ticket){
                array_push($jsonTicketList, new JsonReportTicketModel(
                                        $ticket->getId(),
                                        $type,
                                        $targetId,
                                        new JsonReportReasonModel($ticket->getReason()->getId(), $ticket->getReason()->getName()),
                                        $ticket->getDetails(),
                                        new JsonUserModel($ticket->getAuthor()->getId(), $ticket->getAuthor()->getName(), $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .'/'.$ticket->getAuthor()->getProfilImage()->getWebPath('70x70')),
                                        $ticket->getCreatedDate()
                ));
            }
        
            echo json_encode($jsonTicketList);
        }
        return $response;
    }
    
    private function getViewHandler()
    {
        return $this->container->get('fos_rest.view_handler');
    }
}
