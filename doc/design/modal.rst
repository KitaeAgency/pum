Modal boxes
===========

We are using Twitter Bootstrap to produce modals in the application.

Confirmation on a link or form submit
-------------------------------------

To add a confirmation modal on a link or a button, just fill 5 fields to your html element to provide dynamic modal:

    - data-confirm [confirm button text] [trigger]
    - data-cancel  [cancel button text] [optional]
    - data-text    [title of your modal] [optional]
    - data-content [content of your modal] [optional]
    - data-type    [link(default case) or submit] [optional]

Currently, there are 2 two kinds of modal :
    - link confirmation
    - form submit confirmation

In the case of "link confirmation" :
    - You have to fill the href property of the trigger element

In the case of "form submit confirmation" :
    - You have to fill an additional attribut data :
        - data-form-id [put your form ID here]

.. code-block:: html

    <a data-cancel="cancelButtonText" data-confirm="confirmButtonText" data-text='modalTitle' data-content='modalContent' href="myUrl">
        Click to call modal to confirm link
    </a>

    <a data-type="submit" data-form-id="entititesList" data-cancel="cancelButtonText" data-confirm="confirmButtonText" data-text='modalTitle' data-content='modalContent' href="#">
        Click to call modal to confirm form submit
    </a>


