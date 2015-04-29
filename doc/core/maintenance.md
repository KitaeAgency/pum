Maintenance
=====

Pum allow you to easily switch your website to a maintenance mode.
Your application will be closed except for people whitelisted by their IP address.

You can either add some IP to the configuration, or from the Pum global parameters form.

Even in maintenance mode, the backend side will still be available for administrators.

##Configuration
```
pum_core:
    maintenance:
        template: 'pum://my_maintenance.html.twig'
        whiteIps: [ 127.0.0.1 ]
        whitePaths: [ 'login' ]
```