PUM FormType 'pum_object'
=========================

By creating a formtype 'pum_object', you might want to add an event subscriber to the form to manipulate it.
There are two way to do that.


1. Overall form listener
------------------------

To activate the dispatching of the 5 events of 'pum_object' form

* EVENTS::OBJECT_FORM_PRE_SET_DATA
* EVENTS::OBJECT_FORM_POST_SET_DATA
* EVENTS::OBJECT_FORM_PRE_SUBMIT
* EVENTS::OBJECT_FORM_SUBMIT
* EVENTS::OBJECT_FORM_POST_SUBMIT

You just need to active an option : 'dispatch_events' (default to false)

.. code-block:: php

    $form = $this->createForm('pum_object', $object, array(
        'form_view' => $formView,
        'dispatch_events' => true
    ));

And listen it into a class which implements EventSubscriberInterface

.. code-block:: php

    class PumFormListener implements EventSubscriberInterface
    {
        /**
         * {@inheritdoc}
         */
        static public function getSubscribedEvents()
        {
            return array(
                Events::OBJECT_FORM_SUBMIT => 'onSubmit'
            );
        }

        public function onSubmit(FormEvent $event)
        {
            // Do your stuff
        }


2. Specific subscriber
----------------------

You have to set the option 'event_subscriber' with an instance of a class which implements EventSubscriberInterface and the magic begin

.. code-block:: php

    $form = $this->createForm('pum_object', $object, array(
        'form_view' => $formView,
        'event_subscriber' => new \MyBundle\Extention\Form\Listener\PumObjectSubscriber()
    ));


    // Your subscriber class
    class PumObjectSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents()
        {
            return array(
                FormEvents::PRE_SET_DATA  => 'preSetData',
                FormEvents::POST_SET_DATA => 'postSetData',
                FormEvents::PRE_SUBMIT    => 'preSubmit',
                FormEvents::SUBMIT        => 'submit',
                FormEvents::POST_SUBMIT   => 'postSubmit'
            );
        }

        // Do your stuff
