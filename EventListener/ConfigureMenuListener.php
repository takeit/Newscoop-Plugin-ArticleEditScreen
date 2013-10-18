<?php
namespace Newscoop\EditorBundle\EventListener;

use Newscoop\NewscoopBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    /**
     * @param ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menu[getGS('Plugins')]->addChild(
                'Editor Plugin', 
                array('uri' => $event->getRouter()->generate('newscoop_editor_default_admin'))
        );
    }
}