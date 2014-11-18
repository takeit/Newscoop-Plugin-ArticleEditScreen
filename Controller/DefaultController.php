<?php

namespace Newscoop\EditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
                    'locked' => false,
                    'publicationId' => $article->getPublicationId(),
                    'issueId' => $article->getIssueId(),
                    'sectionId' =>$article->getSectionId(),
                    'languageId' => $article->getLanguageId()
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
                    'locked' => true,
                    'articleNumber' => $articleNumber,
                    'language' => $language,
                    'publicationId' => $article->getPublicationId(),
                    'issueId' => $article->getIssueId(),
                    'sectionId' => $article->getSectionId(),
                    'languageId' => $article->getLanguageId()
                ));
            }
        }

        $client = $em->getRepository('\Newscoop\GimmeBundle\Entity\Client')->findOneByName('newscoop_aes_'.$preferencesService->SiteSecretKey);

        return array(
            'clientId' => $client->getPublicId(),
            'articleNumber' => $articleNumber,
            'language' => $language
        );
    }

    /**
     * @Route("/bundles/newscoopeditor/views/toolbar/navigation.html")
     */
    public function navAction(Request $request)
    {
        return $this->render("NewscoopNewscoopBundle::admin_menu.html.twig");
    }

    /**
     * @Route("/admin/editor_plugin/{language}/{articleNumber}/unlock", options={"expose":true}, name="newscoop_admin_aes_unlock_article")
     */
    public function unlockAction(Request $request, $language, $articleNumber)
    {
        $em = $this->container->get('em');
        $article = $em->getRepository('Newscoop\Entity\Article')
            ->getArticle($articleNumber, $language)
            ->getOneOrNullResult();

        if (!$article) {
            throw new NewscoopException('Article does not exist');
        }

        if ($article->isLocked()) {
            $article->setLockUser();
            $article->setLockTime();
            $em->flush();
        }

        return new RedirectResponse($this->generateUrl('newscoop_admin_aes', array(
            'language' => $language,
            'articleNumber' => $articleNumber
        )));
    }
}
