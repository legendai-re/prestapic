<?php

namespace PP\ImageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('PPImageBundle:Default:index.html.twig', array('name' => $name));
    }
}
