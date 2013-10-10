PUM Routing
===========

Une route
---------

Route = un projet + un pattern + des options

Edition d'une route
-------------------

.. code-block:: yaml

    /blog/{post}
        post:   {regex: "[^/]+", object: "blog_post", field: slug }

    /blog/{blog}/{post}
        blog:   { regex: "[^/]+", object: blog,     equals: post.blog }
        post:   { regex: "[^/]+", object: blog_post }

    /blog/{blog}/{category}/{post}
        category: { regex: "[^/]+", field: slug, object: blog_category, equals: post.category }
        blog:     { regex: "[^/]+", field: slug, object: blog,          equals: post.blog }
        post:     { regex: "[^/]+", field: slug, object: blog_post, table_view: actifs }

    Default arg:
        - regex: [^/]      # for URLMatcher
        - object: null     # null means "no object transformation"
        - field:  null     # requires object to be defined
        - equals: null     # indicates a constraint
        - table_view: null # constraint selection on a table view
        - tpl_name: null   # used in case of "template" resolution

    Résolution

      [ ] Template
      [ ] Contrôleur SF2
      [ ] Redirection URL

Task split
----------

1. **Modéliser les routes**

.. code-block:: php

    $project->getRoutes();
    $project->addRoute($route);
    $project->removeRoute($route);

    $route->getPattern();
    $route->getController();

2. **Charger à partir de configuration**

Format de la configuration:

.. code-block:: yaml

    a_route_name:
      pattern: /some/path
      controller: SomeBundle:ToTest:theFeature

Et l'emplacement dans le bundle :

.. code-block:: text

    @SomeBundle
      Resources/
        pum_routes/project.yml

La commande va chercher un projet "project" et intégrer les routes trouvées dedans.

3. **Evenement**

Ajouter un événement ``PROJECT_ROUTING_UPDATE`` pour déclencher plus tard la mise à jour
du cache quand le routing est changé.

4. **Etendre le routing**

Objectif : charger ça en cache !

Utiliser l'événement pour refaire le cache quand une route change.


5. **Paramètres dynamiques**

Objectif : vérifier qu'ils sont bien passés au contrôleur

6. **Option regex**

7. **controller > resolution**

Plutôt que spécifier un contrôleur, on veut spécifier une résolution (qui elle déterminera
un contrôleur).

8. **Résolution "redirection vers URL"**

9. **Résolution "template"**

* Passer les variables telles que nommées dans la route
* Ajouter l'option "tpl_name"

10. **Option "object" et "field"**

*  Permettre de transformer une chaîne en objet

11. **Option "equals"**

* Contraindre une valeur donnée
* Envisager un forward pour pouvoir vérifier les attributs avant d'exécuter son contrôleur

12. **CRUD**

* Faire un CRUD pour les gérer
