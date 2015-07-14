<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\EditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Newscoop\EditorBundle\Entity\Settings;
use Newscoop\EditorBundle\Form\Type\SettingType;
use Newscoop\Entity\User;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SettingsController extends Controller
{
    /**
     * @Route("/admin/editor_plugin/settings/", options={"expose"=true}, name="newscoop_admin_aes_settings")
     */
    public function indexAction(Request $request)
    {
        $editorService = $this->get('newscoop_editor.editor_service');
        $userService = $this->get('user');
        $em = $this->get('em');
        $cacheService = $this->get('newscoop.cache');
        $translator = $this->get('translator');
        $user = $userService->getCurrentUser();

        if (!$user) {
            return $this->redirect($this->generateUrl('newscoop_zendbridge_bridge_index'));
        }

        $userSettings = $editorService->getSettingsByUser($user);
        $form = $this->createForm(new SettingType(), $userSettings, array(
            'editorService' => $editorService,
            'user' => $user,
        ));

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $editorService->addSettings($data, $user);
                $cacheService->clearNamespace('editor_title_position');
                $this->get('session')->getFlashBag()->add('success', $translator->trans('aes.alerts.saved'));

                return $this->redirect($this->generateUrl('newscoop_admin_aes_settings'));
            }
        }

        return $this->render('NewscoopEditorBundle:Settings:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/admin/editor_plugin/settings/reset-css/", options={"expose"=true}, name="newscoop_admin_aes_settings_reset_css")
     * @Method("POST")
     */
    public function resetCssAction(Request $request)
    {
        $em = $this->get('em');
        $editorService = $this->get('newscoop_editor.editor_service');
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();
        if (!$user || !$user->hasPermission('plugin_editor_styles')) {
            throw new AccessDeniedException();
        }

        $defaultCss = $editorService->getDefaultCss();
        $cssSetting = $em->getRepository('Newscoop\EditorBundle\Entity\Settings')->findOneBy(array(
            'option' => 'css_custom_style',
            'isGlobal' => true,
        ));

        if ($cssSetting) {
            $cssSetting->setValue($defaultCss);
            $em->flush();

            return new JsonResponse(array(
                'status' => true,
                'css' => $defaultCss,
            ));
        }

        return new JsonResponse(array('status' => false));
    }
}
