<?php
/**
 * @package Newscoop\EditorBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\EditorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Asserts;
use Symfony\Component\Validator\Constraints\NotBlank;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'error_bubbling' => true,
            'required' => true,
            'constraints' => array(
                new Asserts\NotBlank(),
                new Asserts\Length(array(
                    'max' => 60,
                )),
            ),
        ))
        ->add('message', 'textarea', array(
            'error_bubbling' => true,
            'required' => true,
            'constraints' => array(
                new Asserts\NotBlank(),
                new Asserts\Length(array(
                    'max' => 1000,
                )),
            ),
        ))
        ->add('email', 'email', array(
            'error_bubbling' => true,
            'required' => true,
            'constraints' => array(
                new Asserts\NotBlank(),
                new Asserts\Email(),
            ),
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'feedback';
    }
}
