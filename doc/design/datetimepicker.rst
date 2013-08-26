Datetimepicker
===========

We are using Jquery UI to produce Datetimepicker in the application.

Datetimepicker on an input
--------------------------

To add a Datepicker/Datetimepicker on an input, just add the following class to your input "datepicker or datetimepicker" and fill fields to your html element to provide dynamic Datepicker/Datetimepicker:

    - data-yearrange [years range of the datepicker default to null]
    - data-mindate [start date default to null]
    - data-maxdate [end date default to null]
    - data-timeformat [time format default to "hh:mm TT"]
    - data-dateFormat [date format default to "dd/mm/yy"]


Example : Create a form with a datepicker input
-----------------------------------------------

.. code-block:: php

    $form->add($name, 'date', array(
        'widget' => 'single_text',
        'format' => "dd/MM/yyyy hh:mm a",
        'attr' => array(
            'class' => 'datetimepicker',
            'data-yearrange'   => "-35:+35", 
            'data-mindate'     => new \Datetime('01/01/1970'),
            'data-maxdate'     => new \Datetime('01/01/2038'),
            'data-timeformat'  => "hh:mm TT",
            'data-dateFormat'  => "dd/mm/yy"
        )
    ));
