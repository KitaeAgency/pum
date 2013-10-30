<?php

namespace Pum\Bundle\TypeExtraBundle\Form\Type;

use Pum\Bundle\TypeExtraBundle\Model\Coordinate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * We manually map coordinates because it's a value object (not modifiable).
 */
class CoordinateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('latitude', 'text', array('mapped' => false, 'attr' => array('placeholder' => 'Latitude')))
            ->add('longitude', 'text', array('mapped' => false, 'attr' => array('placeholder' => 'Longitude')))

            // When developer creates the form
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                if (null === $data) {
                    return;
                }

                if (!$data instanceof Coordinate) {
                    throw new \InvalidArgumentException(sprintf('Expected a coordinate, got a "%s".', is_object($data) ? get_class($data) : gettype($data)));
                }

                $form->get('latitude')->setData($data->getLat());
                $form->get('longitude')->setData($data->getLng());
            })

            // When form is submitted
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                if (null === $data) {
                    return;
                }

                if (!$data instanceof Coordinate) {
                    throw new \InvalidArgumentException(sprintf('Expected a coordinate, got a "%s".', is_object($data) ? get_class($data) : gettype($data)));
                }

                $lat = $form->get('latitude')->getData();
                $lng = $form->get('longitude')->getData();

                $data = new Coordinate($lat, $lng);

                $event->setData($data);
            })
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Bundle\TypeExtraBundle\Model\Coordinate',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pum_coordinate';
    }
}
