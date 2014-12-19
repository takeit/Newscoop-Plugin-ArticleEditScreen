<?php
/**
 * @package Newscoop\EditorBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\EditorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SettingType extends AbstractType
{
    const MAX_LIMIT = 9999;
    const MIN_LIMIT = 1;
    const PERCENTAGE_MAX_LIMIT = 100;
    const PERCENTAGE_MIN_LIMIT = 1;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];
        $builder
            ->add('mobileview', 'integer', array(
                'label' => 'aes.settings.form.viewports.mobile',
                'error_bubbling' => true,
                'required' => true,
                'attr' => array('max' => self::MAX_LIMIT, 'min' => self::MIN_LIMIT),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'aes.form.viewports.mobile.blank')),
                    new Assert\Range(array(
                        'max' => self::MAX_LIMIT,
                        'min' => self::MIN_LIMIT,
                        'minMessage' => 'aes.form.viewports.mobile.min',
                        'maxMessage' => 'aes.form.viewports.mobile.max'
                    ))
                )
            ))
            ->add('tabletview', 'integer', array(
                'label' => 'aes.settings.form.viewports.tablet',
                'error_bubbling' => true,
                'required' => true,
                'attr' => array('max' => self::MAX_LIMIT, 'min' => self::MIN_LIMIT),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'aes.form.viewports.tablet.blank')),
                    new Assert\Range(array(
                        'max' => self::MAX_LIMIT,
                        'min' => self::MIN_LIMIT,
                        'minMessage' => 'aes.form.viewports.tablet.min',
                        'maxMessage' => 'aes.form.viewports.tablet.max'
                    ))
                )
            ))
            ->add('desktopview', 'integer', array(
                'label' => 'aes.settings.form.viewports.desktop',
                'error_bubbling' => true,
                'required' => true,
                'attr' => array('max' => self::MAX_LIMIT, 'min' => self::MIN_LIMIT),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'aes.form.viewports.desktop.blank')),
                    new Assert\Range(array(
                        'max' => self::MAX_LIMIT,
                        'min' => self::MIN_LIMIT,
                        'minMessage' => 'aes.form.viewports.desktop.min',
                        'maxMessage' => 'aes.form.viewports.desktop.max'
                    ))
                )
            ))
            ->add('imagesmall', 'integer', array(
                'label' => 'aes.settings.form.images.small',
                'error_bubbling' => true,
                'required' => true,
                'attr' => array('max' => self::PERCENTAGE_MAX_LIMIT, 'min' => self::PERCENTAGE_MIN_LIMIT),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'aes.form.images.small.blank')),
                    new Assert\Range(array(
                        'max' => self::PERCENTAGE_MAX_LIMIT,
                        'min' => self::PERCENTAGE_MIN_LIMIT,
                        'minMessage' => 'aes.form.images.small.min',
                        'maxMessage' => 'aes.form.images.small.max'
                    ))
                )
            ))
            ->add('imagemedium', 'integer', array(
                'label' => 'aes.settings.form.images.medium',
                'error_bubbling' => true,
                'required' => true,
                'attr' => array('max' => self::PERCENTAGE_MAX_LIMIT, 'min' => self::PERCENTAGE_MIN_LIMIT),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'aes.form.images.medium.blank')),
                    new Assert\Range(array(
                        'max' => self::PERCENTAGE_MAX_LIMIT,
                        'min' => self::PERCENTAGE_MIN_LIMIT,
                        'minMessage' => 'aes.form.images.medium.min',
                        'maxMessage' => 'aes.form.images.medium.max'
                    ))
                )
            ))
            ->add('imagelarge', 'integer', array(
                'label' => 'aes.settings.form.images.large',
                'error_bubbling' => true,
                'required' => true,
                'attr' => array('max' => self::PERCENTAGE_MAX_LIMIT, 'min' => self::PERCENTAGE_MIN_LIMIT),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'aes.form.images.large.blank')),
                    new Assert\Range(array(
                        'max' => self::PERCENTAGE_MAX_LIMIT,
                        'min' => self::PERCENTAGE_MIN_LIMIT,
                        'minMessage' => 'aes.form.images.large.min',
                        'maxMessage' => 'aes.form.images.large.max'
                    ))
                )
            ))
            ->add('showswitches', 'choice', array(
                'label' => 'aes.settings.form.switches',
                'choices'   => array(
                    'Y' => 'aes.settings.label.yes',
                    'N' => 'aes.settings.label.no'
                ),
                'error_bubbling' => true,
                'multiple' => false,
                'expanded' => true,
                'required' => true,
            ))
            ->add('placeholder', null, array(
                'label' => 'aes.settings.form.placeholder',
                'error_bubbling' => true,
                'required' => false,
                'attr' => array('placeholder' => 'aes.settings.form.defaultplaceholder'),
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => 100,
                        'maxMessage' => 'aes.form.placeholder.max'
                    ))
                )
            ))
            ->add('positions', 'collection', array('type' => new PositionType(), 'options'  => array(
                'editorService' => $options['editorService']
            )));

            if ($user->hasPermission("plugin_editor_api")) {
                $builder->add('apiendpoint', null, array(
                    'label' => 'aes.settings.form.apiendpoint',
                    'error_bubbling' => true,
                    'required' => false,
                    'constraints' => array(
                        new Assert\NotBlank(array('message' => 'aes.form.apiendpoint.blank')),
                        new Assert\Length(array(
                            'max' => 20,
                            'maxMessage' => 'aes.form.apiendpoint.max'
                        ))
                    )
                ));
            }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'editorService',
            'user'
        ));
    }

    public function getName()
    {
        return 'settings_form';
    }
}
