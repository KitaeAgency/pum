PUM Routing
===========

To use Pum Routing, you have to enable the routing behavior at least on one Objectdefinition.
You have to choose a field which will be used as your unique seoKey(slug).

Once, the routing behavior est enabled, you have to determine the priority and the default template for each object.
Configure it here : /woodwork/routing


Pum Path
-------------------

To generate a path in twig

.. code-block:: html+jinja

    {{ pum_path(objects, parameters, routename, seoKey) }}

* 'objects' is an array of objects or a single object
* 'parameters' is basically the same as for a regular route path
* 'routename' you have to handle that routename in a controller to work with
* 'seoKey' by default is 'seo'


Controller
--------------------

1. **Generating a path**

Given you have declare the following path in twig

.. code-block:: html+jinja

    {{ pum_path(mytopic, {}, 'cms_topic') }}
    # will generate something like '/topic/mytopicslug'


2. **Handle route with controller**

To handle that path you will have something similar in your controllers

.. code-block:: php

    /**
     * @Route(path="/topic/{seo}", name="cms_topic", defaults={"_project"="your_project_name"}, requirements={"seo" = ".+"})
     */
    public function cmsTopicAction($seo)
    {
        list($template, $vars, $errors) = $this->get('pum.routing')->handleSeo($seo);

        if (!empty($errors)) {
            //You can handle errors here
        }

        return $this->render($template, $vars);
    }

The service 'pum.routing' will handle your seo request through the method 'handleSeo' with the seoKey in args.
It resolves the default template and generate vars for the template and an array of possible errors.
You are free to do whatever you want with theses variables.
In our case, we simply render the template including the vars


3. **How default template is determined ?**

.. code-block:: html+jinja

    {{ pum_path(mytopic, {}, 'cms_topic') }}

When only one object is passed to the function, it is clear that the object carries the template.

.. code-block:: html+jinja

    {{ pum_path([mytopic, myarticle, myauthor], {}, 'cms_topic') }}

When there are different types of object, the template is carried by the object with highest priority.

.. code-block:: html+jinja

    {{ pum_path([mytopic, mytopic1, mytopic2], {}, 'cms_topic') }}

When there are several objects of the same type, the template is carried by the first or the last object in the list
depeding on your config 'ww_reverse_seo_object_template_handler'. When this config var is true, the template is 
carried by the last object in the list, otherwise it's the first object.


4. **What vars do we have in vars ?**

If you have a single object, your matching object with the seoKey will be stored in the var 'object'.
If you have several objects, you will get vars called 'object_{order}'.


But you also can access to these vars with the type of your object.

.. code-block:: html+jinja

    {{ pum_path(mytopic, {}, 'cms_topic') }}

Given mytopic is a topic type.
You can simply access to your mytopic through the var 'topic'

.. code-block:: html+jinja

    {{ pum_path([mytopic1, mytopic2, mytopic3], {}}, 'cms_topic') }}

Given mytopic1, mytopic2, mytopic3, are topic type.
You can access to your mytopic1, mytopic2, mytopic3 through the vars' topic[0]', 'topic[1]', 'topic[2]'


You also can access to vars passed in parameters.
Example :

.. code-block:: html+jinja

    {{ pum_path(mytopic, {cache: 'true', main_topic: 5}, 'cms_topic') }}

The var 'cache' will have the value of true and the var 'main_topic' 5.


5. **What errors do we have in errors ?**

You can handle errors with the array of errors.
Each array of error is composed of two entries 'key' and 'message'.
The entry 'key' will determined the type of error and the entry 'message' will give you more details.
