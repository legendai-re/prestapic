<?php

namespace PP\PropositionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;

use PP\PropositionBundle\Form\Type\PropositionType;
use PP\PropositionBundle\Entity\Proposition;



class PropositionController extends Controller
{
    public function displayPorpositionsAction(Request $request, $id)
    {
        /* init repositories */
        $em = $this->getDoctrine()->getManager();
        $propositionRepository = $em->getRepository('PPPropositionBundle:Proposition');

        /* create new proposition form */
        $proposition = new Proposition();
        $form = $this->get('form.factory')->create(new PropositionType, $proposition);

        $formRequest =  $this->container->get('request_stack')->getParentRequest();        
               
        /* handle proposition POST data */
        if ($formRequest->isMethod('POST')) {
                $form->handleRequest($formRequest);                
                if ($form->isValid()) {                                                    
                                $em = $this->getDoctrine()->getManager();
                                $em->persist($proposition);                                                                
                                
                                $em->flush();
                                $request->getSession()->getFlashBag()->add('notice', 'Proposition bien enregistrée.'); 
                                
                                /* reset the form */
                                $proposition = new Proposition();
                                $form = $this->get('form.factory')->create(new PropositionType, $proposition);
                                                                                                
                                return  $this->forward('PPRequestBundle:Request:view', array(
                                    'id' => $id
                                ));
                }
        }
        
        /* get all propositions */
        $propositionsList = $propositionRepository->findAll();
        
        return $this->render('PPPropositionBundle:propositions:propositions.html.twig', array(
            'form' => $form->createView(),
            'propositionsList' => $propositionsList
        ));
    }
}
