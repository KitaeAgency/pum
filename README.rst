PUM
===

`Documentation <doc/index.rst>`_

Quick setup
-----------

First, configure ``app/config/parameters.yml`` file. In this file, specify
your database or any other parameter you need. You can override any parameter
from ``app/config/parameters.yml``.

When you've done this, run reset script:

.. code-block:: text

    ./reset.sh

This script will download dependencies and initialize project with fixtures.

Quick testing
-------------

To quickly launch automated tests, run the ``test.sh`` script. This script
should always work from a clear ``git clone``. The script should also return
0 if everything went fine, 1 or more if something went wrong.
