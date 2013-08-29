<?php

namespace Pum\Bundle\TypeExtraBundle\Form\Type;

use Pum\Bundle\TypeExtraBundle\Model\Price;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * We manually map coordinates because it's a value object (not modifiable).
 */
class PriceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', 'text', array('mapped' => false))
            ->add('currency', 'choice', array('mapped' => false, 'choices' => array('EUR' => '€', 'USD' => '$')))

            // When developer creates the form
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                if (null === $data) {
                    return;
                }

                if (!$data instanceof Price) {
                    throw new \InvalidArgumentException(sprintf('Expected a price, got a "%s".', is_object($object) ? get_class($object) : gettype($object)));
                }

                $form->get('value')->setData($data->getValue());
                $form->get('currency')->setData($data->getCurrency());
            })

            // When form is submitted
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                if (null === $data) {
                    return;
                }

                if (!$data instanceof Price) {
                    throw new \InvalidArgumentException(sprintf('Expected a price, got a "%s".', is_object($object) ? get_class($object) : gettype($object)));
                }

                $value = $form->get('value')->getData();
                $currency = $form->get('currency')->getData();

                if (null === $value) {
                    $data = null;
                } elseif (null === $currency) {
                    throw new \InvalidArgumentException(sprintf('Expected value and currency to be defined, got only one (value: "%s", currency: "%s")', $value, $currency));
                } else {
                    $data = new Price($value, $currency);
                }

                $event->setData($data);
            })
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Bundle\TypeExtraBundle\Model\Price',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pum_price';
    }
}
