Modal boxes
===========

We are using Twitter Bootstrap to produce modals in the application.

Confirmation on a link
----------------------

To add a confirmation modal on a link or a button, just fill 4 fields to your html element to provide dynamic modal:

    - data-confirm [confirm button text] [trigger]
    - data-cancel [cancel button text] [optional]
    - data-text [title of your modal] [optional]
    - data-content [content of your modal] [optional]
    - href [location] [required]

.. code-block:: html

    <a data-cancel="cancelButtonText" data-confirm="confirmButtonText" data-text='modalTitle' data-content='modalContent' href="myUrl">
        Click to call modal
    </a>


