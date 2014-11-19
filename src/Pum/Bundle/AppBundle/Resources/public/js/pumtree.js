+function ($) { "use strict";

    $(document).ready(function(){

        var initTree = function(el, ajax_url, namespace) {

            // events on object
            el.on('load_node.jstree', function (node, data) {
                var status = data.status,
                    node   = data.node;

                if (status && node.id != '#') {
                    addNode(node.id, namespace);
                }
            });
            el.on('open_node.jstree', function (node, data) {
                var node   = data.node;

                if (node.id != '#') {
                    addNode(node.id, namespace);
                }
            });
            el.on('close_node.jstree', function (node, data) {
                var node   = data.node;

                if (node.id != '#') {
                    removeNode(node.id, namespace);
                }
            });

            // create the instance
            el.jstree({
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
        };

        var addNode = function(node_id, namespace) {
            var values = getCookie(namespace);

            if (values) {
                values = JSON.parse(values);
                values.push(node_id);
                values = values.filter(function(itm,i,a){
                    return i==a.indexOf(itm);
                });
            } else {
                values = [node_id];
            }

            setCookie(namespace, JSON.stringify(values));
        };

        var removeNode = function(node_id, namespace) {
            var values = getCookie(namespace);

            if (values) {
                values = JSON.parse(values);
                values = jQuery.grep(values, function(value) {
                  return value != node_id;
                });
            } else {
                values = [node_id];
            }

            setCookie(namespace, JSON.stringify(values));
        };

        var setCookie = function(cname, value, exdays) {
            if (exdays) {
                var date = new Date();
                date.setTime(date.getTime()+(exdays*24*60*60*1000));
                var expires = "; expires="+date.toGMTString();
            } else {
                var expires = "";
            }

            document.cookie = cname+"="+value+expires+";";
        };

        var getCookie = function(cname) {
            var nameEQ = cname + "=";
            var ca = document.cookie.split(';');
            for(var i=0;i < ca.length;i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1,c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
            }

            return null;
        };

        var deleteCookie = function(cname) {
            document.cookie = cname + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        };

        $.each($("div.treeable"), function(index, div) {
            var namespace    = $(div).data('namespace'),
                params       = jQuery.param({
                    'action' : 'node'
                }),
                ajax_url     = $(div).data('ajax-url')+'?'+params;

            // Here we go 
            initTree($(div), ajax_url, namespace);
        });


    });

}(window.jQuery);
