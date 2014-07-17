<?php

namespace Newscoop\EditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Newscoop\EventDispatcher\Events\GenericEvent;

class DefaultController extends Controller
{
    /**
     * @Route("/admin/editor_plugin", options={"expose":true}, name="newscoop_admin_aes")
     * @Template()
     */
    public function adminAction(Request $request)
    {   
		$ladybug = \Zend_Registry::get('container')->getService('ladybug');
		$em = $this->container->get('em');
		$preferencesService = $this->container->get('system_preferences_service');
		$client = $em->getRepository('\Newscoop\GimmeBundle\Entity\Client')->findOneByName('newscoop_aes_'.$preferencesService->SiteSecretKey);
		$ladybug->log($client);

		//$dispatcher = $this->get('event_dispatcher'); 
        //$dispatcher->dispatch('plugin.install.newscoop_editor_bundle', new GenericEvent());

        return array('clientId'=>$client->getPublicId());
    }

    /**
     * @Route("/bundles/newscoopeditor/views/toolbar/navigation.html")
     */
    public function navAction(Request $request)
    {   
        return $this->render("NewscoopNewscoopBundle::admin_menu.html.twig");
    }
}