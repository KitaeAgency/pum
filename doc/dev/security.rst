Security in the application
===========================

Abstraction
------------

You can use any user provider in the application until you provide basic
permissions to your users to access backend applications:

**Back-office permissions**

* ``ROLE_WW_USERS``: manage user permissions
* ``ROLE_WW_BEAMS``: manage beams
* ``ROLE_WW_SCHEMA``: access low-schema informations
* ``ROLE_WW_PROJECTS``: manage projects

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
