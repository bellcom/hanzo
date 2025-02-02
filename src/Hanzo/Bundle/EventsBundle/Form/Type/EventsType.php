<?php
/*
 * This file is part of the hanzo package.
 *
 * (c) Ulrik Nielsen <un@bellcom.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hanzo\Bundle\EventsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class EventsType
 *
 * @package Hanzo\Bundle\EventsBundle\Form\Type
 */
class EventsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('customers_id', 'hidden')
            ->add('type', 'choice', [
                'choices' => [
                    'AR'  => 'events.type.choice.ar',
                    'HUS' => 'events.type.choice.hus',
                ],
                'label' => 'events.type.label',
            ])->add('rsvp_type', 'choice', [
                'choices' => [
                    1 => 'events.rsvp_type.choice.need_to',
                    2 => 'events.rsvp_type.choice.nice_to',
                    3 => 'events.rsvp_type.choice.sms_email',
                ],
                'label'    => 'events.rsvp_type.label',
                'required' => false,
                'empty_value' => 'events.choose.rsvp_type'
            ])->add('event_date', 'text', [
                'attr'  => ['class' => 'datetimepicker'],
                'label' => 'events.event_date.label',
            ])->add('event_end_time', 'text', [
                'attr'     => ['class' => 'datetimepicker'],
                'label'    => 'events.event_end_time.label',
                'required' => false,
            ])->add('host', 'text', [
                'label' => 'events.host.label',
            ])->add('address_line_1', 'text', [
                'label' => 'events.address_line_1.label',
            ])->add('postal_code', 'text', [
                'label' => 'events.postal_code.label',
            ])->add('city', 'text', [
                'label' => 'events.city.label',
            ])->add('phone', 'text', [
                'label' => 'events.phone.label',
            ])->add('email', 'text', [
                'label' => 'events.email.label',
            ])->add('description', 'textarea', [
                'label'    => 'events.description.label',
                'required' => false
            ])->add('public_note', 'textarea', [
                'label'    => 'events.public_note.label',
                'required' => false
            ])->add('notify_hostess', 'checkbox', [
                'label'    => 'events.notify_hostess.label',
                'required' => false
            ]);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'events',
            'data_class'         => 'Hanzo\Model\Events',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'events';
    }
}
