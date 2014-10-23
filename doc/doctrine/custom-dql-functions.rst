How to Register custom DQL Functions with Pum
=============================================

Pum is using his own EntityManager, so to register a custom DQL function, you need to do a little extra work.

.. code-block:: yml

    pum_core:
        doctrine:
            _pum:
                dql:
                    datetime_functions:
                        date_format: Acme\PumBundle\DQL\DateTime\DateFormat

For more informations on this topic, read Doctrine's documentation article about "`DQL user defined functions <http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/dql-user-defined-functions.html>`_"

.. code-block:: php

    namespace Acme\PumBundle\DQL\DateTime;

    use Doctrine\ORM\Query\Lexer;
    use Doctrine\ORM\Query\AST\Functions\FunctionNode;

    class DateFormat extends FunctionNode
    {
        ...
    }

