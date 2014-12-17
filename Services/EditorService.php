<?php
/**
 * @package Newscoop\EditorBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\EditorBundle\Services;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Newscoop\EditorBundle\Entity\Settings;
use Newscoop\Entity\User;
use Doctrine\ORM\EntityManager;

/**
 * Editor service
 */
class EditorService extends Controller
{
    /**
     * Entity Manager
     * @var EntityManager
     */
    protected $em;

    /**
     * Construct
     *
     * @param EntityManager $em Entity Manager
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * [addSettings description]
     * @param array $settings [description]
     * @param User  $user     [description]
     */
    public function addSettings(array $settings, User $user)
    {
        foreach ($settings as $option => $value) {
            $setting = $this->em->getRepository("Newscoop\EditorBundle\Entity\Settings")
                ->createQueryBuilder('s')
                ->where('s.user = :user')
                ->andWhere('s.option = :option')
                ->setParameters(array(
                    'user' => $user,
                    'option' => $option
                ))
                ->getQuery()
                ->getOneOrNullResult();

            if (!$setting) {
                $setting = new Settings();
                $setting->setOption($option);
                $setting->setValue($value);
                $setting->setUser($user);
                $this->em->persist($setting);

                continue;
            }

            if ($setting->getValue() != $value) {
                $setting->setValue($value);
            }
        }

        $this->em->flush();
    }

    /**
     * Get settings by user. It assigns default settings,
     * if there are no settings for given user
     *
     * @param User $user User
     *
     * @return array Array of settings
     */
    public function getSettingsByUser(User $user)
    {
        $userSettings = $this->em->getRepository("Newscoop\EditorBundle\Entity\Settings")
            ->createQueryBuilder('s')
            ->where('s.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();

        $options = array();
        foreach ($userSettings as $setting) {
            $options[$setting['option']] = $setting['value'];
        }

        $defaultSettings = $this->getDefaultSettings();
        foreach ($defaultSettings as $option => $value) {
            if (!array_key_exists($defaultOption, $options)) {
                $options[$option] = $value;
            }
        }

        return $options;
    }

    /**
     * Get default settings.
     *
     * @return array Array of default settings
     */
    public function getDefaultSettings()
    {
        $qb = $this->em->createQueryBuilder();
        $settings = $qb
            ->select('s')
            ->from('Newscoop\EditorBundle\Entity\Settings', 's')
            ->where($qb->expr()->isNull('s.user'))
            ->getQuery()
            ->getArrayResult();

        $options = array();
        foreach ($settings as $setting) {
            $options[$setting['option']] = $setting['value'];
        }

        return $options;
    }
}
