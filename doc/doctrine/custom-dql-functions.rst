How to Register custom DQL Functions with Pum
=============================================

Beucase Pum is using his own EntityManager, you need to do a little extra work to add your own DQL function.

.. code-block:: yml

pum_core:
    doctrine:
        _pum:
            dql:
                datetime_functions:
                    date_format: Acme\PumBundle\DQL\DateTime\DateFormat


.. code-block:: php

    namespace Acme\PumBundle\DQL\DateTime;

    use Doctrine\ORM\Query\Lexer;
    use Doctrine\ORM\Query\AST\Functions\FunctionNode;

    class DateFormat extends FunctionNode
    {
        ...
    }

