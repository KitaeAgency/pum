Doctrine Entity Manager Factory
===============================

This extension is probably the biggest extension of PUM:
it provides persistence layer for PUM objects.

Without using this extension, you're already able to do
a lot of business:

.. code-block:: php

    $beam = Beam::create('blog')
        ->addObject($object = ObjectDefinition::create('blog_post')
            ->createField('title',   'text', array('length' => 255))
            ->createField('content', 'text', array('length' => 60000))
        )
    ;
    $pum->saveBeam($beam);

    $project = Project::create('my-team')
        ->addBeam($beam)
    ;
    $pum->saveProject($project);

    $of = pum->getObjectFactory('my-team');

    $post = $of->createObject('blog_post');

Doctrine internals
::::::::::::::::::

To understand things here, you need to understand how Doctrine works internally.

An **entity manager** is composed of following two major components:

* A **ClassMetadataFactory**, providing information about entities persisted
  by Doctrine. This factory is overridden to load from objet definitions.

 * A **MetadataDriver**, responsible of actual metadata loading.

* A **UnitOfWork**, storing orders to execute on next transaction: inserts,
  updates and deletes
