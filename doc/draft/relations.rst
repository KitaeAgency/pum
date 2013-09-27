Relations
=========

Relations are defined through type "relation" and properties.
By now, it is explicitly defined on both sides of the relation.
For this reason, if we don't change anything, user might come
in situations where relations are inconsistently defined.

To avoid this situation, we will block user from editing raw
relation informations and provide him a detailed beam relation
view.

Relation view
-------------

A new view should be available to the user : the relations view

In this view, we will focus on one thing: editing relations

**RelationSchema**

To ease process of changing relations (and subsequently form creation),
we will create an intermediate schema : the **RelationSchema**.

.. code-block:: php

    $schema = new Schema($beam);

    $schema->getRelations();
    $schema->addRelation(Relation);
    $schema->removeRelation(Relation);

    $relation->setFrom(...);
    $relation->setTo(...);
    $relation->setFromType(1-*);
    $relation->setToType(1-*);
    $relation->setFromName(1-*);
    $relation->setToName(1-*);

    $relation->isFromBeam();
    $relation->isToBeam();
    $relation->isExternal();

Those methods should modify the beam instantly.

Relation forms
--------------

We must be able to create new form types for relations :

**a select list**

* A simple select list (for a *-to-1)
* A multiple select list (for a *-to-*, 1-to-*)

**an ajax search field**

* Select one or many elements
* Dynamically create new element (via FormView)
* Dynamically select an element (via TableView)

The type of widget should be configurable on FormView.

As default, we'll use simple select lists. We might
choose Ajax later.

**Ajax controller details**

  GET /_search?type=french-team:blog_post&q=xxxx

  {
    "32": "Ceci est le texte",
    "43": "Ceci est le texte",
  }
