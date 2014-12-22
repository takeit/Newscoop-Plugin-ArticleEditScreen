<?php
/**
 * @package Newscoop\EditorBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\EditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/admin/editor_plugin/{language}/{articleNumber}", options={"expose":true}, name="newscoop_admin_aes")
     * @Route("/admin/editor_plugin/")
     */
    public function adminAction(Request $request, $language = null, $articleNumber = null)
    {
        $em = $this->container->get('em');
        $preferencesService = $this->container->get('system_preferences_service');
        $translator = $this->get('translator');
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();
        if (!$user) {
            return $this->redirect($this->generateUrl('newscoop_zendbridge_bridge_index'));
        }

        if (!$language || !$articleNumber) {
            return new RedirectResponse($this->generateUrl('newscoop_zendbridge_bridge_index') . 'articles/add_move.php');
        }

        if (null !== $articleNumber) {
            $article = $em->getRepository('Newscoop\Entity\Article')
                ->getArticle($articleNumber, $language)
                ->getArrayResult();

            if (empty($article)) {
                return $this->render("NewscoopEditorBundle:Alerts:alert.html.twig", array(
                    'message' => $translator->trans('aes.alerts.notfound'),
                    'locked' => false,
                    'articleNumber' => $articleNumber,
                    'language' => $language
                ));
            }

            $lockTime = $article[0]['lockTime'];
            $lockUser = $article[0]['lockUser'];
            if ($lockTime && $lockUser) {
                $timeDiffrence = $lockTime->diff(new \DateTime());

                return $this->render("NewscoopEditorBundle:Alerts:alert.html.twig", array(
                    'message' => $translator->trans('aes.alerts.locked', array(
                        '%realname%' => $lockUser['first_name'] . ' ' . $lockUser['last_name'],
                        '%username%' => $lockUser['username'],
                        '%hours%' => $timeDiffrence->h,
                        '%minutes%' => $timeDiffrence->i

                    )),
                    'locked' => true,
                    'article' => $article[0]
                ));
            }
        }

        $editorService = $this->get('newscoop_editor.editor_service');
        $userSettings = $editorService->getSettingsByUser($user);

        $client = $em->getRepository('\Newscoop\GimmeBundle\Entity\Client')->findOneByName('newscoop_aes_'.$preferencesService->SiteSecretKey);
        $settigns = $this->createSettingsJson($request, $userSettings, $client);

        return $this->render("NewscoopEditorBundle:Default:admin.html.twig", array(
            'clientId' => $client->getPublicId(),
            'articleNumber' => $articleNumber,
            'language' => $language,
            'userSettings' => $settigns
        ));
    }

    /**
     * Creates json with user settings for the editor
     *
     * @param Request                            $request      Request object
     * @param array                              $userSettings $userSettings
     * @param Newscoop\GimmeBundle\Entity\Client $client       OAuth client
     *
     * @return string JSON string
     */
    private function createSettingsJson(Request $request, $userSettings, $client)
    {
        $translator = $this->get('translator');
        $redirectUris = $client->getRedirectUris();
        $types = array();
        foreach ($userSettings['positions'] as $key => $value) {
            $types[] = array(
                $value->getName() => array(
                    'title' => array(
                        'name' => 'title',
                        'displayName' => $translator->trans('aes.fields.title'),
                        'order' => $value->getPosition()
                    )
                )
            );
        }

        $settings = array(
            'API' => array(
                'rootURI' => $request->getUriForPath(null),
                'endpoint' => $userSettings["apiendpoint"],
                'full' => $request->getUriForPath(null) . $userSettings["apiendpoint"]
            ),
            'auth' => array(
                'client_id' => $client->getPublicId(),
                'redirect_uri' => $redirectUris[0],
                'server' => $request->getUriForPath($this->generateUrl('fos_oauth_server_authorize'))
            ),
            'article' => array(
                'width' => array(
                    'desktop' => $userSettings['desktopview'],
                    'tablet' => $userSettings['tabletview'],
                    'phone' => $userSettings['mobileview']
                )
            ),
            'image' => array(
                'width' => array(
                    'small' => $userSettings['imagesmall'] . "%",
                    'medium' => $userSettings['imagemedium'] . "%",
                    'big' => $userSettings['imagelarge'] . "%"
                ),
                'float' => 'none'
            ),
            'placeholder' => $userSettings['placeholder'],
            'showSwitches' => $userSettings['showswitches'],
            'articleTypeFields' => $types
        );

        return json_encode($settings, JSON_NUMERIC_CHECK);
    }

    /**
     * @Route("/bundles/newscoopeditor/views/toolbar/navigation.html")
     */
    public function navAction(Request $request)
    {
        return new JsonResponse($this->renderView("NewscoopNewscoopBundle::admin_menu.html.twig"));
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
