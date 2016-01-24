<?php

namespace PP\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PageController extends Controller
{
    public function aboutAction()
    {
        return $this->render('PPPageBundle:about:about.html.twig');
    }
    
    public function termsAction()
    {
        return $this->render('PPPageBundle:terms:terms.html.twig');
    }
    
    public function cookiesAction()
    {
        return $this->render('PPPageBundle:cookies:cookies.html.twig');
    }
    
    public function privacyAction()
    {
        return $this->render('PPPageBundle:privacy:privacy.html.twig');
    }
    
    public function licenseAction()
    {
        return $this->render('PPPageBundle:license:license.html.twig');
    }
}
