View
====

**PHP API**

.. code-block:: php

    $view->hasSource($path);
    $view->removeSource($path);

    $view->storeSource('/beam/blog/post.html.twig', $src);

    $source = $view->getSource('/beam/blog/post.html.twig');

**Twig API**

.. code-block:: html+jinja

    {{ include('pum://path/to/template.html.twig') }}

    {{ pum_field(object, 'avatar', view='default') }}

**Path conventions**

* /accueil.html.twig
* /beam/<beam>/<tpl>
* /field/<type>/<view>.html.twig
* /object/<beam>/<object>/<view>.html.twig
* /project/<project>/<any previous path> *(allow project overriding)*

Beam templates
::::::::::::::

**PHP API**

.. code-block:: php

    $beam->getTemplates(); // returns a collection of BeamTemplate

    Pum::TemplateInterface
        $template->getPath(); // post.html.twig, author_span.html.twig
        $template->getSource(); // ...
        $template->isEditable(); // indicates if user can change the template. It will be used to update beam templates easily when beam updates

Field type templates
::::::::::::::::::::

**PHP API**

.. code-block:: php

    interface ViewFeatureInterface
    {
        public function getViewTemplates(); // an array of PumTemplateInterface
    }
