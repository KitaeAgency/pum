##Localization convention

To translate Pūm app, we use a "translation key" to identify each text to be localized in views.
Translation keys are following a naming convention:  

###First & Second level folders###
* `common.*` : for common term between WW and PA  (*ie: common.config.top_btn_edit_settings*)
* `*.topnav.*` : for items in top navbar (*ie: common.topnav.link_woodwork*)
* `*.macro.*` : for any term in macro  (*ie: macro.collapsedlist.item*)
* `*.modal.*` : for any term in modals  (*ie: common.modal.title_confirm_action*)

###Single item Prefixes###
* `*.table_th_*` : used to identify a head cell of a table (*ie: common.config.table_th_key*)


###Suffixes###
* `*.title` : title of the page, above the breadcrumb (*ie: common.config.edit.breadcrumb*)
* `*.description` : description of the page, below the title (*ie: common.config.title*)
* `*.breadcrumb` : title used in the breadcrumb for the section (*ie: common.config.description*)

###Placeholders###
For only one term to replace, we use `%name%`. If there is more, use a explicit key relative to the content that will be replaced (*following a var naming convention*).

###Links###
Sometimes, translations need to wrap text with an anchor. In this case, we use `%link%` for `<a href="">` and `%/link%` for `</a>`.