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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PositionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $editorService = $options['editorService'];
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($editorService) {
            $positionObject = $event->getData();
            $form = $event->getForm();
            $form->add('titlePosition', 'choice', array(
                'choices'   => $editorService->generatePositions($positionObject),
                'required'  => false,
                'empty_value'  => null
            ));
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Newscoop\EditorBundle\Model\Position',
        ));

        $resolver->setRequired(array(
            'editorService'
        ));
    }

    public function getName()
    {
        return 'positions';
    }
}
