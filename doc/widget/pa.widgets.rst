PUM Project admin wigdets
==========================

In project admin, the left menu is a collection of widgets.
You can easily add or remove dynamically widgets with the WidgetFactory.


1. What is a widget
--------------------

A widget must implements a WidgetInterface to work.

Pum has its own widget definition :
    * Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widget

You could extend this class ou create your own widget class implementing the interface :
    * Pum\Bundle\ProjectAdminBundle\Extension\Widget\WidgetInterface


2. Widget's properties
----------------------

.. code-block:: php

    private $name;   // Unique name to identity widget
    private $label;  // Translation Label displayed
    private $color;  // Color default to concrete
    private $icon;   // Icon default to settings2
    private $weight; // To order widget default to 20 (lower is higher priority)

    private $route;  // Path name on click
    private $routeParameters; // Parameter for the route path

    private $permission; // Permission required
    private $permissionParameters; // Permission paramters


* `Color scheme <design/colors.md>`_
* `Icon scheme <design/src/icons.md>`_

3. Pum widgets
--------------------

By default, Pum has a 3 kind of widget in project admin
    - Beams
        - [Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widgets\Beams]
    - Views manager
        - [Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widgets\Views]
    - Vars manager
        - [Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widgets\Vars]


4. Add a new widget
--------------------

As said above, to add a new widget you have to create a class which implements WidgetInterface or an ArrayCollection of widget.
The service of this class must be tagged with 'pum.project.admin.widget'.


5. Example : add a Hello world widget
-------------------------------------

.. code-block:: xml

    <service id="pum.project.admin.widgets.hello_worl" class="...\HelloWorld">
        <argument>widget.hello.world</argument>
        <argument>settings2</argument>
        <argument>asbestos</argument>
        <argument>15</argument>
        <tag name="pum.project.admin.widget" />
    </service>
        
        
.. code-block:: php

    use Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widget;

    class HelloWorl extends Widget
    {
        public function __construct($name='pum_vars', $icon=null, $color=null, $weight=null)
        {
            parent::__construct($name, $icon, $color, $weight);

            $this
                ->setLabel('common.vars.hello.world')
                ->setRoute('my_hello_world')
                ->setPermission('CAN_SEE_MY_WIDGET')
            ;
        }
    }


And that's it :)!


6. Widgets service
-------------------

The service id is 'pum.project.admin.widgets'.
You can manage widgets though this service with add, has, and remove method.
