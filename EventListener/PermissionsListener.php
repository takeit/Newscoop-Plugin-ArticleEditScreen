<?php
/**
 * @package Newscoop\EditorBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\EditorBundle\EventListener;

use Newscoop\EventDispatcher\Events\PluginPermissionsEvent;
use Symfony\Component\Translation\Translator;

class PermissionsListener
{
    /**
     * Translator
     * @var Translator
     */
     protected $translator;

     /**
      * Construct
      *
      * @param Translator $translator Translator object
      */
     public function __construct(Translator $translator)
     {
         $this->translator = $translator;
     }

     /**
      * Register plugin permissions in Newscoop ACL
      *
      * @param PluginPermissionsEvent $event
      */
     public function registerPermissions(PluginPermissionsEvent $event)
     {
         $event->registerPermissions($this->translator->trans('aes.name'), array(
            'plugin_editor_api' => $this->translator->trans('aes.permissions.api'),
            'plugin_editor_permissions' => $this->translator->trans('aes.permissions.permissions'),
            'plugin_editor_styles' => $this->translator->trans('aes.permissions.styles'),
         ));
     }
 }
