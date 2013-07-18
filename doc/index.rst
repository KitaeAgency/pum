PUM Documentation
=================

PUM Core
--------

Provides dynamic model manipulation and usage. Core provides the manager,
which is the entry point to access dynamic model:

.. code-block:: php

    $manager = $this->get('pum');

    $manager->saveDefinition(
        ObjectDefinition::create('person')
            ->createField('name', 'text')
            ->createField('age', 'integer')
    );


    $user = $manager->createObject('person')
        ->set('name', 'Alice')
        ->set('age',  32)
    ;

    $manager->persist($user);
    $manager->flush();

Awesome, isn't it?
