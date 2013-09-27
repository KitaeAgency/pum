PUM Routing
===========

Une route
---------

Route = un projet + un pattern + des options

Edition d'une route
-------------------

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

* Faire le moteur qui permet d'étendre le routing SF2 avec des routes custom - commencer par un modèle simple : route = pattern + controleur
* Faire un CRUD pour les gérer
* Ajout de paramètres dynamiques --> vérifier qu'ils sont bien passés au contrôleur
* Ajouter l'option regex permettant de spécifier un pattern
* Ajouter la résolution "redirection URL"
* Ajouter la résolution "template" qui passe les variables à un template, configuré dans le form d'édition de la route

  * Passer les variables telles que nommées dans la route
  * Ajouter l'option "tpl_name" sur les fields + changer nom pour le rendu twig

* Ajouter l'option "object" et "field" permettant de transformer une chaîne en objet
* Ajouter l'option "equals" permettant de contraindre une valeur donnée

  * Envisager un forward pour pouvoir vérifier les attributs avant d'exécuter son contrôleur
