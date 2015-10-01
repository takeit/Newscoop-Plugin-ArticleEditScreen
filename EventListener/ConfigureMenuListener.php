<?php

namespace Newscoop\EditorBundle\EventListener;

use Newscoop\NewscoopBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Translation\TranslatorInterface;

class ConfigureMenuListener
{
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menu[$this->translator->trans('Plugins')]->addChild(
                $this->translator->trans('aes.name'),
                array('uri' => $event->getRouter()->generate('newscoop_admin_aes_settings'))
        );
    }
}
