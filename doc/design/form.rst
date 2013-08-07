Forms
=====

Symfony2 forms are used to manage forms in the application

Easy tabs
---------

Given you have a form with many fields, you may want to group them in tabs. To do so,
you can use embed feature "form tabs" like this.

Previously, your code was:

.. code-block:: php

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('initials', 'text')
            ->add('emails', 'collection', array('type' => 'email'))
            ->add('addresses', 'collection', array('type' => 'address'))
            ->add('submit', 'submit')
        ;
    }

To create 3 tabs, do so:

.. code-block:: php

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($builder->create('tabs', 'ww_tabs')
                ->add($builder->create('informations', 'ww_tab')
                    ->add('firstname', 'text')
                    ->add('lastname', 'text')
                    ->add('initials', 'text')
                )
                ->add($builder->create('emails', 'ww_tab')
                    ->add('emails', 'collection', array('type' => 'email'))
                )
                ->add($builder->create('addresses', 'ww_tab')
                    ->add('addresses', 'collection', array('type' => 'address'))
                )
            )
            ->add('submit', 'submit')
        ;
    }

That's it!
