<?php
/**
 * @package Newscoop\EditorBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\EditorBundle\EventListener;

use Symfony\Component\EventDispatcher\GenericEvent;
use Newscoop\Services\Plugins\PluginsService;
use Newscoop\Services\UserService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class EditorListener
{
    protected $pluginsService;
    protected $router;
    protected $userService;
    protected $em;

    /**
     * Construct
     *
     * @param PluginsService $pluginsService Plugins service
     * @param Router         $router         Symfony2 router
     * @param UserService    $userService    User service
     * @param EntityManager  $em             Entity manager
     */
    public function __construct(PluginsService $pluginsService, Router $router, UserService $userService, EntityManager $em)
    {
        $this->pluginsService = $pluginsService;
        $this->router = $router;
        $this->userService = $userService;
        $this->em = $em;
    }

    /**
     * Generates editor's link
     *
     * @param GenericEvent $event Symfony Event
     */
    public function generateEditorLink(GenericEvent $event)
    {
        $articleNumber = $event->getArgument('articleNumber');
        $articleLanguage = $event->getArgument('articleLanguage');
        if ($this->pluginsService->isEnabled($this->getPluginName()) && $this->hasAccessToEditor()) {
            $articleLink = $this->router->generate('newscoop_admin_aes', array(
                'articleNumber' => $articleNumber,
                'language' => $articleLanguage,
            ));
        }

        $event->setArgument('link', $articleLink);
    }

    private function getPluginName()
    {
        $composerFile = __DIR__.'/../composer.json';
        $composerDefinitions = json_decode(file_get_contents($composerFile), true);

        return $composerDefinitions['name'];
    }

    private function hasAccessToEditor()
    {
        $user = $this->userService->getCurrentUser();
        $userPermission = $this->em->getRepository('Newscoop\EditorBundle\Entity\Permissions')->findOneByUser($user);
        if ($userPermission && $userPermission->getIsAssigned()) {
            return true;
        }

        return false;
    }
}
