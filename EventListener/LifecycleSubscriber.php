<?php
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
    }

    public function update(GenericEvent $event)
    {
    }

    public function remove(GenericEvent $event)
    {
    }

    public static function getSubscribedEvents()
    {
        return array(
            'plugin.install.terwey_plugin_newscoop_articleeditscreen' => array('install', 1)
        );
    }
}