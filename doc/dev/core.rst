Core architecture
=================

The core of PUM is the big magic box providing a lot of love:

* Dynamic definition of a schema

  * Define advanced field types
  * Relations between objects

* Extensions

  * Doctrine entities & entity managers
  * Symfony Forms
  * Symfony Validation
  * Project Admin
  * Woodwork

.. image:: core-schema.png
   :align: center

PUM is composed of two very different worlds:

* The **schema world**, where you define how things are constructed, you are
  being a *Developer* or a *Project owner*: You are **specifying project schemas**
* The **usage world** is the application creating **instances** of your schema, when you
  benefit from the schema you defined above.

In the second part (usage world), things should go fast. We might be on a highly
popular TV, and our website might be receiving a lot of traffic.

In the first part (schema world), we can take time and resources to update properly
schema. But once it's done, we need to be sure that production caches are cleaned
and modifications should be handled properly.

Structure
:::::::::

* ``app/`` - a Symfony2 standard application folder, describing the application
* ``src/``

  * ``Pum\Bundle`` - contains Symfony2 full-stack framework bundles

    * ``AppBundle`` - foundation for web applications
    * ``CoreBundle`` - integrates PUM inside Symfony2 full-stack framework
    * ``ProjectAdminBundle`` - application to administrate project datas
    * ``TypeExtraBundle`` - provides extra data types (geolocation, prices...)
    * ``WoodworkBundle`` - application to manage schema

  * ``Pum\Core`` - the essential of PUM (see details below)

  * ``Pum\QA`` - code for quality assurance (see `How to use Behat <../testing/behat.rst>`_)

Architecture details
::::::::::::::::::::

Schema Definition
-----------------

This is the model of your application : projects, beams, objects and fields.
Here, you are saying "*hey, I wanna add a column ``is_highlight`` to my blog post entities:

.. code-block:: php

    $pum = $this->get('pum'); // a SchemaManager instance

    $beam = $pum->getBeam('blog');

    $beam
        ->getObject('blog_post')
        ->createField('is_highlight', 'boolean')
    ;

    $pum->saveBeam($beam);

That's it! A simple PHP interface to manipulate the schema definition. This API is
mainly used by Woodwork.

TypeFactory
-----------

The TypeFactory contains all type objects, instances providing data types in PUM:
text, integer, boolean, choice list, price, geolocation...

Object Factory
--------------

The object factory is responsible of generating classes from the schema definition.

Extension
---------

Some behaviors have been moved out to extension, to have a modular architecture.

Those extensions listen to schema manager events about Beams, Projects and even
Objects. To get a full list, take a look at ``Pum\Core\Events`` PHP class.

As soon as one of those events occurs, they do their business (EmFactory will update
DB schema according to modifications or Logger will log a record).

The production needings
:::::::::::::::::::::::

When you're in production, **SchemaDefinition** should never be accessed. This part
is costful and memory-consuming: manipulation of it implies many objects hydrations.

For this reason, we only address project and objects through their names (represented
as strings). Extensions should rely on cache to avoid hitting the schema definition.
