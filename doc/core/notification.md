Notifications
=====

##Configuration
```
pum_core:
    notification:
        from: notification@les-argonautes.com
        content:
            title: "Notification"
```


----------

##Notify a user
```
$this->get('pum.notification')->create(array(
    'content' => array(
        'title' => 'My notification',
        'body' => array(
            'pum://notification/default.html.twig', 
            array('notification' => $notification))
    ),
    'email' => '01:00'
));
```

*Parameters:*

 - Content
     - Title \*: It'll be the title displayed to  in the backend and the email's subject. *This field can either be passed here or to the Symfony2 configuration file.*
     - Body \*: Can either be a array or a string. If it's an array, it'll be processed as a Twig template.
 - Email: If email is a Boolean, this notification will be dispatched immediatly after being created.  Email can also be a DateTime object or a string representative to a DateTime object ( **Y-m-d H:i** or **H:i** ).
 - Users: The notification will be associated to every users passed here.
 - Groups : The notification will be associated to each users of every groups passed here.

\* This fields are required. 

**Important** : Users or Groups parameters have to be a Transversable collection (array, ArrayCollection...). 

**If a notification isn't attached to any user and group, every users from the backend will be notified.**

----------

##Get notifications of an user or a group

Some methods have been added to the NofificationRepository. The notification repository is declared as a service, and can be easily accessed from the Symfony2 container.
```
$container->get('pum.notification_repository')->findByUser($user);
$container->get('pum.notification_repository')->findByGroup($group);
```

##Notification Dispatcher command

```
php app/console pum:notification:dispatch
```
This command send delayed notifications.


