Get pum entities in Twig
========================

Get a repository for an object in order to use methods

.. code-block:: twig

    {% set friends = pum_repository('user').acceptedFriends %}
    {% for friend in friends %}
        {{ friend.firstame }}
    {% endfor %}


Get single entity by id

.. code-block:: twig

    {% set user = pum_entity('user', id) %}
    {{ user.firstame }}


Get all entities 

.. code-block:: twig

    {% for user in pum_entities('user') %}
        {{ user.firstame }}
    {% endfor %}


Get all entities with single criteria

.. code-block:: twig

    {% for user in pum_entities('user', {firstname: 'Jean'}) %}
        {{ user.firstame }}
    {% endfor %}


Get all entities with multiple criterias

.. code-block:: twig

    {% for me in pum_entities('user', [{firstname: 'Jean'}, {status: 'ACCEPTED'}]) %}
        {{ user.firstame }}
    {% endfor %}


Get all entities with multiple criterias and custom order

.. code-block:: twig

    {% for me in pum_entities('user', [{firstname: 'Jean'}, {status: 'ACCEPTED'}], {id : 'desc'}) %}
        {{ me.firstame }}
    {% endfor %}


Get all entities with multiple criterias and custom order with limit and offset

.. code-block:: twig

    {% for me in pum_entities('user', [{firstname: 'Jean'}, {status: 'ACCEPTED'}], {id : 'desc'}, limit, offset) %}
        {{ me.firstame }}
    {% endfor %}


By default criteria are checked with the operator '=' (eq) with method andWhere
But you can change it in criterias array.
Example : you want all users who are under 18 years old or older than 35.

- Avalaible operator => "andX", "orX", "eq", "gt", "lt", "lte", "gte", "neq", "isNull", "in", "notIn"
- Avalaible method   => "andWhere", "orWhere"

.. code-block:: twig

    {% for me in pum_entities('user', [{age: [18, 'lt']}, {age: [35, 'gt', 'orWhere']}], {id : 'desc'}, limit, offset) %}
        {{ me.firstame }}
    {% endfor %}

