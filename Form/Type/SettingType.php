<?php
/**
 * @package Newscoop\NewscoopBundle
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\EditorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mobileview', 'integer', array(
                'label' => 'aes.settings.form.viewports.mobile',
                'error_bubbling' => true,
                'required' => true,
                'attr' => array('max' => 9999, 'min' => 1),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'aes.form.viewports.mobile.blank')),
                    new Assert\Range(array(
                        'max' => 9999,
                        'min' => 1,
                        'minMessage' => 'aes.form.viewports.mobile.min',
                        'maxMessage' => 'aes.form.viewports.mobile.max'
                    ))
                )
            ))
            ->add('tabletview', 'integer', array(
                'label' => 'aes.settings.form.viewports.tablet',
                'error_bubbling' => true,
                'required' => true,
                'attr' => array('max' => 9999, 'min' => 1),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'aes.form.viewports.tablet.blank')),
                    new Assert\Range(array(
                        'max' => 9999,
                        'min' => 1,
                        'minMessage' => 'aes.form.viewports.tablet.min',
                        'maxMessage' => 'aes.form.viewports.tablet.max'
                    ))
                )
            ))
            ->add('desktopview', 'integer', array(
                'label' => 'aes.settings.form.viewports.desktop',
                'error_bubbling' => true,
                'required' => true,
                'attr' => array('max' => 9999, 'min' => 1),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'aes.form.viewports.desktop.blank')),
                    new Assert\Range(array(
                        'max' => 9999,
                        'min' => 1,
                        'minMessage' => 'aes.form.viewports.desktop.min',
                        'maxMessage' => 'aes.form.viewports.desktop.max'
                    ))
                )
            ))
            ->add('imagesmall', 'integer', array(
                'label' => 'aes.settings.form.images.small',
                'error_bubbling' => true,
                'required' => true,
                'attr' => array('max' => 100, 'min' => 1),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'aes.form.images.small.blank')),
                    new Assert\Range(array(
                        'max' => 100,
                        'min' => 1,
                        'minMessage' => 'aes.form.images.small.min',
                        'maxMessage' => 'aes.form.images.small.max'
                    ))
                )
            ))
            ->add('imagemedium', 'integer', array(
                'label' => 'aes.settings.form.images.medium',
                'error_bubbling' => true,
                'required' => true,
                'attr' => array('max' => 100, 'min' => 1),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'aes.form.images.medium.blank')),
                    new Assert\Range(array(
                        'max' => 100,
                        'min' => 1,
                        'minMessage' => 'aes.form.images.medium.min',
                        'maxMessage' => 'aes.form.images.medium.max'
                    ))
                )
            ))
            ->add('imagelarge', 'integer', array(
                'label' => 'aes.settings.form.images.large',
                'error_bubbling' => true,
                'required' => true,
                'attr' => array('max' => 100, 'min' => 1),
                'constraints' => array(
                    new Assert\NotBlank(array('message' => 'aes.form.images.large.blank')),
                    new Assert\Range(array(
                        'max' => 100,
                        'min' => 1,
                        'minMessage' => 'aes.form.images.large.min',
                        'maxMessage' => 'aes.form.images.large.max'
                    ))
                )
            ))
            ->add('titleposition', 'choice', array(
                'label' => 'aes.settings.form.title.position',
                'choices'   => array(1 => '1', 2 => '2', 3 => '3'),
                'required'  => false,
                'empty_value'  => null
            ))
            ->add('showtopics', 'choice', array(
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
        ;
    }

    public function getName()
    {
        return 'settings_form';
    }
}
