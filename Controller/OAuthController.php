<?php
/**
 * @package Newscoop\EditorBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\EditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class OAuthController extends Controller
{
    /**
     * @Route("/editor_plugin/oauth/result/", options={"expose"=true}, name="aes_oauth_result")
     * @Method("GET")
     */
    public function oauthResultAction()
    {
        return $this->render("NewscoopEditorBundle:OAuth:result.html.twig");
    }
}
