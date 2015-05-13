PUM Woodwork Search API
==========================

1. How to activate Woodwork search
----------------------------------

Go in Woodwork configs at /woodwork/config/edit and check to enable the search.


2.Override Woodwork search
--------------------------

By default, Woodwork Search API returns five types of objects depending on your permissions :
    - Project
    - Beam
    - ObjectDefinition
    - Group
    - User

But you have the opportunity to change the results of this research.
For that you will need the compilerpass and do the following operation.

.. code-block:: php

    $definition = $container->getDefinition('woodwork.search.api');
    $definition->replaceArgument(0, new Reference('your_new_search_api_service'));

Your new search api service must implements \Pum\Bundle\WoodworkBundle\Extension\Search\SearchInterface,
and have to return a JSON response that matchs this shape :

.. code-block:: php

    array(
        0 => array(
            "label": "Project X",
            "type": "project",
            "class": "concrete",
            "path": "/woodwork/projects/projectx/edit"
        ),
        1 => array(
            "label": "Beam X",
            "type": "beam",
            "class": "concrete",
            "path": "/woodwork/beams/beamx/edit"
        )
    );

