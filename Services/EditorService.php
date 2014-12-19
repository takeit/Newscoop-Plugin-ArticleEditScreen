<?php
/**
 * @package Newscoop\EditorBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\EditorBundle\Services;

use Newscoop\EditorBundle\Entity\Settings;
use Newscoop\Entity\User;
use Doctrine\ORM\EntityManager;
use Newscoop\EditorBundle\Model\Position;
use Doctrine\Common\Collections\ArrayCollection;
use Newscoop\Services\UserService;
use Newscoop\Services\CacheService;

/**
 * Editor service
 */
class EditorService
{
    const FIELD_TYPE_MARKER = "_field";

    /**
     * Entity Manager
     * @var EntityManager
     */
    protected $em;

    /**
     * User object
     * @var Newscoop\Entity\User
     */
    protected $user;

    /**
     * Cache service
     * @var Cache Service
     */
    protected $cacheService;

    /**
     * Construct
     *
     * @param EntityManager $em           Entity Manager
     * @param UserService   $userService  User Service
     * @param CacheService  $cacheService Cache service
     */
    public function __construct(EntityManager $em, UserService $userService, CacheService $cacheService)
    {
        $this->em = $em;
        $this->user = $userService->getCurrentUser();
        $this->cacheService = $cacheService;
    }

    /**
     * Add settings for given user
     *
     * @param array $settings Array of settings
     * @param User  $user     User
     */
    public function addSettings(array $settings, User $user)
    {
        $settings = $this->preprocessSettingsBeforeSave($settings);
        foreach ($settings as $option => $value) {
            $setting = $this->getSingleSettingByUserAndOption($user, $option);
            if (!$setting) {
                $this->updateOrCreateSetting($option, $value);

                continue;
            }

            if ($setting->getValue() != $value) {
                $setting->setValue($value);
            }
        }

        $this->em->flush();
    }

    /**
     * Preprocesses user settings before save. Removes ArrayCollection
     * and leaves only an associative array with user settings
     *
     * @param array $settings Array of settigns
     *
     * @return array Preprocessed settings
     */
    private function preprocessSettingsBeforeSave(array $settings)
    {
        $transformedPositionsArray = $this->transformTitlePositions($settings['positions']);
        unset($settings['positions']);

        return $this->mergeTwoArraysIntoOne($settings, $transformedPositionsArray);
    }

    /**
     * Transform title position for article type fields into simple
     * associative array
     *
     * @param ArrayCollection $positions Title position in article type field
     *
     * @return array Array of title position for specific article type field
     */
    private function transformTitlePositions(ArrayCollection $positions)
    {
        $transformedPositions = array();
        foreach ($positions as $key => $position) {
            $fieldType = $position->getName() . self::FIELD_TYPE_MARKER;
            $transformedPositions[$fieldType] = $position->getPosition();
        }

        return $transformedPositions;
    }

    /**
     * Gets single setting by user and option name
     *
     * @param User   $user   User
     * @param string $option Option name e.g. "mobileview"
     *
     * @return Settings|null Settings object or null
     */
    public function getSingleSettingByUserAndOption(User $user, $option)
    {
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

        return $setting;
    }

    /**
     * Updates default setting or creates new setting for the user
     *
     * @param string     $option [description]
     * @param string|int $value  [description]
     *
     * @return void
     */
    private function updateOrCreateSetting($option, $value)
    {
        $qb = $this->em->createQueryBuilder();
        $setting = $qb
            ->select('s')
            ->from('Newscoop\EditorBundle\Entity\Settings', 's')
            ->where($qb->expr()->isNull('s.user'))
            ->andWhere('s.option = :option')
            ->setParameter('option', $option)
            ->getQuery()
            ->getOneOrNullResult();

        if ($setting) {
            $setting->setValue($value);

            return;
        }

        $setting = new Settings();
        $setting->setOption($option);
        $setting->setValue($value);
        $setting->setUser($user);
        $this->em->persist($setting);
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
        $this->validateAndRemoveUserSettings($options, $defaultSettings);
        foreach ($defaultSettings as $option => $value) {
            if (!array_key_exists($option, $options)) {
                $options[$option] = $value;
            }
        }

        $options['positions'] = $this->reverseTitlePositionsTransform($options);

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

        $fieldsAndPositions = array();
        foreach ($this->getArticleTypesCollection() as $key => $value) {
            $fieldProperties = $this->getAvailableArticleTypeFields($value);
            if (count($fieldProperties) > 0) {
                $fieldsAndPositions[$value->getName() . self::FIELD_TYPE_MARKER] = 1; // set 1st position by default
            }
        }

        return $this->mergeTwoArraysIntoOne($options, $fieldsAndPositions);
    }

    /**
     * Gets article types array collection needed for the form
     *
     * @return ArrayCollection
     */
    private function getArticleTypesCollection()
    {
        $articleTypes = $this->em->getRepository('Newscoop\Entity\ArticleType')->getAllTypes()->getArrayResult();
        $settings = new Settings();
        foreach ($articleTypes as $key => $type) {
            $position = new Position();
            $position->articleTypeName = $type['name'];
            $settings->getPositions()->add($position);
        }

        return $settings->getPositions();
    }

    /**
     * Get article type fields and their properties where "showInEditor" property is set to 1.
     * In other words, it display only these article type fields which are
     * marked to be displayed in Article Edit Screen.
     *
     * @param Position $positionObject Position object
     *
     * @return array Array with article type fields
     */
    private function getAvailableArticleTypeFields(Position $positionObject)
    {
        $articleTypesFields = $this->em->getRepository('Newscoop\Entity\ArticleTypeField')
            ->getFieldsForType($positionObject)
            ->getArrayResult();

        foreach ($articleTypesFields as $key => $value) {
            if (null === $value['showInEditor'] || 0 === $value['showInEditor']) {
                 unset($articleTypesFields[$key]);
            }
        }

        return $articleTypesFields;
    }

    /**
     * Merge two arrays into single array
     *
     * @param array $firstArray  First array
     * @param array $secondArray Second array
     *
     * @return array Merged arrays
     */
    private function mergeTwoArraysIntoOne(array $firstArray, array $secondArray)
    {
        return array_merge($firstArray, $secondArray);
    }

    /**
     * Validates user settings, removes settings which doesn't exist anymore
     * e.g. when article type field will be removed setting for this specific
     * field will also be removed from the database for currently logged in user.
     *
     * @param array $userSettings    User settings
     * @param array $defaultSettigns Default settings
     *
     * @return void
     */
    private function validateAndRemoveUserSettings(array $userSettings, array $defaultSettings)
    {
        if (!empty($userSettings)) {
            foreach ($userSettings as $option => $value) {
                if (!array_key_exists($option, $defaultSettings)) {
                    $setting = $this->getSingleSettingByUserAndOption($this->user, $option);
                    $this->em->remove($setting);
                }
            }

            $this->em->flush();
        }
    }

    /**
     * Transforms associative array with user settings into Array Collection
     * e.g. article type field: "news_field" will be transferend into Position
     * object of ArrayCollection.
     *
     * @param array $options User settings
     *
     * @return ArrayCollection
     */
    private function reverseTitlePositionsTransform(array $options)
    {
        $transformedPositions = array();
        $arrayCollection = new ArrayCollection();
        foreach ($options as $fieldName => $fieldValue) {
            if (strpos($fieldName, self::FIELD_TYPE_MARKER) !== false) {
                $extractedFieldName = explode("_", $fieldName, 2);
                $position = new Position();
                $position->setName($extractedFieldName[0]);
                $position->setPosition($fieldValue);
                $arrayCollection->add($position);
            }
        }

        return $arrayCollection;
    }

    /**
     * Generate available positions based on article type fields.
     * If showInEditor is null for all fields then first position is set.
     *
     * @param Position $positionObject Title position object
     *
     * @return array Array of positions to choose
     */
    public function generatePositions(Position $positionObject)
    {
        $repository = $this->em->getRepository('Newscoop\Entity\ArticleTypeField');
        $cacheKey = $this->cacheService->getCacheKey(array('ArticleTypeField', $positionObject->getName()), 'article_type');
        $choicesArray = array();
        if ($this->cacheService->contains($cacheKey)) {
            $choicesArray = $this->cacheService->fetch($cacheKey);
        } else {
            $articleTypesFields = $this->em->getRepository('Newscoop\Entity\ArticleTypeField')
                ->getFieldsForType($positionObject)
                ->getArrayResult();

            foreach ($articleTypesFields as $key => $value) {
                if (null === $value['showInEditor'] || 0 === $value['showInEditor']) {
                     unset($articleTypesFields[$key]);
                }
            }

            for ($i = 1; $i < count($articleTypesFields) + 2; $i++) {
                $choicesArray[$i] = $i;
            }

            if (empty($choicesArray)) {
                $choicesArray[1] = 1;
            }

            $this->cacheService->save($cacheKey, $choicesArray);
        }

        return $choicesArray;
    }
}
