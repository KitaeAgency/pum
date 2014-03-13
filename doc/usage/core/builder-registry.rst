The BuilderRegistry
===================

Pūm allows you to manage lot of data types, from text/integer/float to currency/image/HTML.

All of those types are registered in a service called the *BuilderRegistry*, handling a collection of types and behaviors.

.. code-block:: php

    use Pum\Core\BuilderRegistry\CoreBuilderRegistry;

    $registry = new CoreBuilderRegistry();

    $registry->runType('text', null, function ($type) {
        // put yout business here...
    });

This *BuilderRegistry* is used to generate Doctrine entities, forms, validation.

**References**

* `BuilderRegistryInterface <https://github.com/les-argonautes/pum/blob/master/src/Pum/Core/BuilderRegistry/BuilderRegistryInterface.php>`_

**See also**

* `How to create a new data types <../cookbook/new-data-type.rst>`_
