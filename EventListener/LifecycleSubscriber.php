<?php
/**
 * @package Newscoop\EditorBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\EditorBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Newscoop\EventDispatcher\Events\GenericEvent;
use Newscoop\EditorBundle\Entity\Settings;

/**
 * Event lifecycle management
 */
class LifecycleSubscriber implements EventSubscriberInterface
{
    private $em;

    private $clientManager;

    private $syspref;

    private $translator;

    private $pluginsService;

    public function __construct($em, $clientManager, $syspref, $translator, $pluginsService)
    {
        $this->em = $em;
        $this->clientManager = $clientManager;
        $this->syspref = $syspref;
        $this->translator = $translator;
        $this->pluginsService = $pluginsService;
    }

    public function install(GenericEvent $event)
    {
        $publications = $this->em->getRepository('Newscoop\Entity\Publication')->findAll();
        $publication = $publications[0];
        $client = $this->clientManager->createClient();
        $client->setName('newscoop_aes_'.$this->syspref->SiteSecretKey);
        $client->setPublication($publication);
        $client->setRedirectUris(array($publication->getDefaultAlias()->getName()));
        $client->setAllowedGrantTypes(array('authorization_code'));
        $client->setTrusted(true);
        $this->clientManager->updateClient($client);
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);

        $this->em->getProxyFactory()->generateProxyClasses($this->getClasses(), __DIR__ . '/../../../../library/Proxy');
        $this->setPermissions();
        $this->addDefaultSettings();
    }

    public function update(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);

        $this->em->getProxyFactory()->generateProxyClasses($this->getClasses(), __DIR__ . '/../../../../library/Proxy');
        $this->setPermissions();
        $this->addDefaultSettings();
    }

    public function remove(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->dropSchema($this->getClasses(), true);
        $this->removePermissions();
    }

    public static function getSubscribedEvents()
    {
        return array(
            'plugin.install.newscoop_article_edit_screen' => array('install', 1),
            'plugin.update.newscoop_article_edit_screen' => array('update', 1),
            'plugin.remove.newscoop_article_edit_screen' => array('remove', 1),
        );
    }

    private function getClasses()
    {
        return array(
          $this->em->getClassMetadata('Newscoop\EditorBundle\Entity\Settings')
        );
    }

    /**
     * Add default settings to database
     */
    private function addDefaultSettings()
    {
        $settings = array(
            'mobileview' => 320,
            'tabletview' => 600,
            'desktopview' => 920,
            'imagesmall' => 30,
            'imagemedium' => 50,
            'imagelarge' => 100,
            'showswitches' => true,
            'placeholder' => $this->translator->trans("aes.settings.label.defaultplaceholder")
        );

        $globalSettings = array(
            'apiendpoint' => "/api",
            'default_image_size' => 'medium'
        );

        $this->setUpSettings($settings);
        $this->setUpSettings($globalSettings, true);
    }

    /**
     * Setting up new default settings, when isGlobal is set to true for given setting,
     * there will be the possibility to change this setting for all users.
     *
     * @param array   $settings Array of settings
     * @param boolean $isGlobal If edit settings for all users
     */
    private function setUpSettings($settings, $isGlobal = false)
    {
        $qb = $this->em->createQueryBuilder();
        foreach ($settings as $option => $value) {
            $setting = $this->em->getRepository('Newscoop\EditorBundle\Entity\Settings')
                ->createQueryBuilder('s')
                ->select('s')
                ->where($qb->expr()->isNull('s.user'))
                ->andWhere('s.option = :option')
                ->setParameter('option', $option)
                ->getQuery()
                ->getOneOrNullResult();

            if (!$setting) {
                $setting = new Settings();
                $setting->setOption($option);
                $setting->setValue($value);
                $setting->setUser(null);
                $setting->setIsGlobal($isGlobal);
                $this->em->persist($setting);
            } else {
                $setting->setValue($value);
            }
        }

        $this->em->flush();
    }

    /**
     * Remove plugin permissions
     */
    private function removePermissions()
    {
        $this->pluginsService->removePluginPermissions($this->pluginsService->collectPermissions($this->translator->trans('aes.name')));
    }

    /**
     * Collect plugin permissions
     */
    private function setPermissions()
    {
        $this->pluginsService->savePluginPermissions($this->pluginsService->collectPermissions($this->translator->trans('aes.name')));
    }
}
