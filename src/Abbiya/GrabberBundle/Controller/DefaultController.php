<?php

namespace Abbiya\GrabberBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AbbiyaGrabberBundle:Default:index.html.twig', array('name' => $name));
    }
}
