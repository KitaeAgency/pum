+function ($) { "use strict";

    $(document).ready(function(){

        $.each($("div.treeable"), function(index, div) {

            var label_field  = $(div).data('label-field'),
                tree_field   = $(div).data('tree-field'),
                parent_field = $(div).data('parent-field'),
                ajax_url     = $(div).data('ajax-url'),
                params       = jQuery.param({
                    'action' : 'node'
                });

            ajax_url = ajax_url+'?'+params;

            $(div).jstree({
                "core" : {
                    "animation" : 0,
                    "check_callback" : true,
                    "themes" : { "stripes" : true },
                    'data' : {
                      'url' : function (node) {
                        return ajax_url;
                      },
                      'data' : function (node) {
                        return { 'id' : node.id };
                      }
                    }
                }
            });

        });
    });

}(window.jQuery);
