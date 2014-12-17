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
use Newscoop\EditorBundle\Entity\Settings;
use Newscoop\EditorBundle\Form\Type\SettingType;
use Newscoop\Entity\User;

class SettingsController extends Controller
{
    /**
     * @Route("/admin/editor_plugin/settings/", options={"expose":true}, name="newscoop_admin_aes_settings")
     */
    public function indexAction(Request $request)
    {
        $editorService = $this->get('newscoop_editor.editor_service');
        $userService = $this->get('user');
        $translator = $this->get('translator');
        $user = $userService->getCurrentUser();

        if (!$user) {
            return $this->redirect($this->generateUrl('newscoop_zendbridge_bridge_index'));
        }

        $form = $this->createForm(new SettingType(), $editorService->getSettingsByUser($user));

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $editorService->addSettings($data, $user);
                $this->get('session')->getFlashBag()->add('success', $translator->trans('aes.alerts.saved'));

                return $this->redirect($this->generateUrl('newscoop_admin_aes_settings'));
            }
        }

        return $this->render("NewscoopEditorBundle:Settings:index.html.twig", array(
            'form' => $form->createView()
        ));
    }
}
