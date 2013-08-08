Dashboard
=====

##Widgets

###Modules counter
Widget used for display an icon, title and a counter with a link to a module, with the module's color theme.

```html
<div class="col-2 pum-dash_widget_wrapper">
    <a href="{URL}" class="pum-dash_widget pum-scheme-widget-{COLOR_NAME}">
        <h3>{TITLE}</h3>
        <i class="pumicon pumicon-{ICON_NAME}"></i>
        <big>{COUNTER}</big>
    </a>
</div>
```

* `{URL}`: The url you want to link to
* `{COLOR_NAME}` : The [name of the color](colors.md) you want to use
* `{TITLE}` : The text to show on the bottom of the widget
* `{ICON_NAME}` : The [name of the icon](icons.md) you want to use
* `{COUNTER}` : The number you want to display on the right side

You can change the `col-2` class to upper number (*up to 12, from the Bootstrap grid*).