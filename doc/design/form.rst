Forms
=====

Symfony2 forms are used to manage forms in the application

Customize type options rendering
--------------------------------

When you configure an entity in Woodwork, you choose fields on your object.
Each of those fields is of a given type (text, integer, price...).

Sometimes, you need to override rendering of one of those forms. If you want
to do so, define blocks prefixed with ``pum_type_options_text``:

{% block pum_type_options_price_widget %}
    <p>You are configuring the price column.</p>
    <p>First, choose a currency: {{ form_widget(form.currency) }}</p>
    <p>Do you want a max value? {{ form_widget(form.max) }}</p>
    <!-- ... -->
{% endblock %}

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
