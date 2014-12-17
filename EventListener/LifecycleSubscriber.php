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

/**
 * Event lifecycle management
 */
class LifecycleSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct($em, $clientManager, $syspref)
    {
        $this->em = $em;
        $this->clientManager = $clientManager;
        $this->syspref = $syspref;
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
    }

    public function update(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);

        $this->em->getProxyFactory()->generateProxyClasses($this->getClasses(), __DIR__ . '/../../../../library/Proxy');
    }

    public function remove(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->dropSchema($this->getClasses(), true);
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
}
