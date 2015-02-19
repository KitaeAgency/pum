Logs
=====

##Configuration
```
pum_core:
    log:
        disabled: false
        classes:
            acme:
                object: AcmeBundle\Entity\Entity
                route:
                    name: acme_entity_edit
                    parameters [ id: this.getId() ]

```

Logs are directly listening to Doctrine's events to get every changes done to any of the specified entities.
When a object is flushed this Unit of Work of Doctrine, each changes done to this entity are saved to the log entry.

There is 2 different doctrine's listener:

 - The first one is handling classic Doctrine entities. This listener is also used for custom classes set to the configuration.
 - The second one is listening to Pum entities.

If you want to disable only Project-admin entities, you can use this configuration
```
pum_core:
    log:
        disabled:
            project_admin: true
```

For now, the log's user is only working with the AppBundle\Entity\User entity.

A Symfony2 service is available to create a specific log entry.

```
<?php
    $this->get('pum_core.log')->create(object $entity, integer $event, array $options);
?>
```

You can also disable the log during batch process or whatever by calling the setEnabled method from the service.

```
<?php
    $this->get('pum_core.log')->setEnabled(false);
?>

Some events are declared as constante into the Log entity:

 - Log::EVENT_NONE
 - Log::EVENT_CREATE
 - Log::EVENT_UPDATE
 - Log::EVENT_DELETE

Options can be :

 - description: A description text of what is about to be logged
 - em: The entity manager handling the entity. If the entity manager is used, the service will calculate what changed will be done to the entity and store it to the log entry.
 - tags: A list of tags used to easily identified the log entry

```
<?php
    $this->get('pum_core.log')->create($user, Log::EVENT_CREATE, array(
        'description' => "I create a new user",
        'tags' => array('pum', 'user'),
        'em' => $em
    ));
?>
```
