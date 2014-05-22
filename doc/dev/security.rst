Security in the application
===========================

Roles in Woodwork
-----------------

You can use any user provider in the application until you provide basic
permissions to your users to access backend applications:

**Back-office permissions**

* ``ROLE_WW_USERS``: manage user permissions
* ``ROLE_WW_BEAMS``: manage beams
* ``ROLE_WW_LOGS``: access logs informations
* ``ROLE_WW_PROJECTS``: manage projects

Roles in ProjectAdmin
---------------------

In the ProjectAdmin, to manage permissions on the objects, PUM provides an
ACL like system. An interface to manage permissions is available in Woodwork.

The implementation uses a table to record the:
``group_id``, ``attribute``, ``project_id``, ``beam_id``, ``object_id``, ``instance``

The values for the ``attribute`` are:

* ``PUM_OBJ_VIEW``: The user can view the object
* ``PUM_OBJ_EDIT``: The user can view and edit the object
* ``PUM_OBJ_CREATE``: The user can create new objects
* ``PUM_OBJ_DELETE``: The user can delete the object
* ``PUM_OBJ_MASTER``: The user can view/edit/delete the object and create new ones

The ``instance``, ``beam_id`` and ``object_id`` can be null in order to provide a way
to inherit permissions progressively:

- If ``instance`` is null, the permission applies on all instance of an object
- If ``instance`` and ``object_id`` are null, the permission applies on all objects of a beam
- If ``instance``, ``object_id`` and ``beam_id`` are null, the permission applies on all objects of a project

**Twig templates**

In Twig templates, security checks are performed using the `is_granted` function.
For example, to check if the user has permission to create a resource, you can do something like:

    is_granted('PUM_OBJ_CREATE', {project: pum_projectName(), beam: beam.name, object:object_definition.name})

NB: This deprecates the use of ``PA_ROLE_*`` roles

Login form... or not!
---------------------

As default, PUM is provided with a login form and a regular **database user
storage**. Using this, you can use Woodwork to administrate your users.

Hopefuly, you're free to change everything.

You can change two things:

* the user provider
* the authentication method (default to form_login)

To do so, just change the ``app/config/security.yml`` like any other Symfony
application and configure your own authentication method. Feel free to use:

* LDAP
* OAuth, Facebook, Twitter
* X509
* whatever you want ...

You must be aware that if you're not using the PUM provider, you won't be
able to use the user management feature. You'll have to administrate your
users on your own.
