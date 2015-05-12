How to change ElasticSearch hosts
=============================================

By default, ElasticSearch is using the localhost address and the port 9200.
You can change it two different ways.

With parameters

.. code-block:: yml

    parameters:
        # your parameters.yml
        pum.elasticsearch.params:
            hosts: [127.0.0.1:9200]

With Pum core configuration

.. code-block:: yml

    pum_core:
        elasticsearch:
            hosts: [127.0.0.1:9200]
