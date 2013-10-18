<?php

namespace Newscoop\EditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/admin/editor_plugin")
     */
    public function adminAction(Request $request)
    {
        return $this->render('NewscoopEditorBundle:Default:admin.html.twig');
    }
}