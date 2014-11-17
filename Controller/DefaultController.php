<?php

namespace Newscoop\EditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/admin/editor_plugin/{language}/{articleNumber}", options={"expose":true}, name="newscoop_admin_aes")
     * @Template()
     */
    public function adminAction(Request $request, $language = null, $articleNumber = null)
    {
        $em = $this->container->get('em');
        $preferencesService = $this->container->get('system_preferences_service');
        $translator = $this->get('translator');

        if (null !== $articleNumber) {
            $article = $em->getRepository('Newscoop\Entity\Article')
                ->getArticle($articleNumber, $language)
                ->getOneOrNullResult();

            if (!$article) {
                return $this->render("NewscoopEditorBundle:Alerts:alert.html.twig", array(
                    'message' => $translator->trans('aes.alerts.notfound'),
                    'locked' => false
                ));
            }

            if ($article->isLocked()) {
                $timeDiffrence = $article->getLockTimeDiffrence();

                return $this->render("NewscoopEditorBundle:Alerts:alert.html.twig", array(
                    'message' => $translator->trans('aes.alerts.locked', array(
                        '%realname%' => $article->getLockUser()->getRealName(),
                        '%username%' => $article->getLockUser()->getUsername(),
                        '%hours%' => $timeDiffrence['hours'],
                        '%minutes%' => $timeDiffrence['minutes']

                    )),
                    'locked' => true
                ));
            }
        }

        $client = $em->getRepository('\Newscoop\GimmeBundle\Entity\Client')->findOneByName('newscoop_aes_'.$preferencesService->SiteSecretKey);

        return array(
            'clientId' => $client->getPublicId()
        );
    }

    /**
     * @Route("/bundles/newscoopeditor/views/toolbar/navigation.html")
     */
    public function navAction(Request $request)
    {
        return $this->render("NewscoopNewscoopBundle::admin_menu.html.twig");
    }
}
