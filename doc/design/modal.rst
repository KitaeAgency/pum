Design Documentation
=================

Modals
--------

Just fill 4 fields to your html element to provide dynamic modal :

    - data-confirm [confirm button text] [trigger]
    - data-cancel [cancel button text] [optional]
    - data-text [title of your modal] [optional]
    - href [location] [required]

.. code-block:: html

    <a data-cancel="cancelButtonText" data-confirm="confirmButtonText" data-text='modalTitle' href="myUrl">
        Click to call modal
    </a>


