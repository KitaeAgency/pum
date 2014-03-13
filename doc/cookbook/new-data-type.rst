How to create a new data type
=============================

To create a new data type, you will need to define a new class. The best way to get the job done is to copy an existing type:

* `TextType <https://github.com/les-argonautes/pum/blob/master/src/Pum/Core/Extension/Core/Type/TextType.php>`_
* `DatetimeType <https://github.com/les-argonautes/pum/blob/master/src/Pum/Core/Extension/Core/Type/DatetimeType.php>`_
* `PasswordType <https://github.com/les-argonautes/pum/blob/master/src/Pum/Core/Extension/Core/Type/PasswordType.php>`_
* `view all core types <https://github.com/les-argonautes/pum/tree/master/src/Pum/Core/Extension/Core/Type>`_

When you have created the class, using `standard edition <https://github.com/les-argonautes/pum-standard-edition>`_, just declare and tag your service:

.. code-block:: xml

    <service id="pum.type.password" class="MyCustomType">
        <tag name="pum.type" alias="custom" /> <!-- alias should be same as getName() result -->
    </service>
