// =========================================================================
// PumTrees - v0.1
// =========================================================================
// Needs jQuery JAAH and jsTree

;(function($, window, document) {
    'use strict';

    var PumTrees = function(options) {
        this.options = $.extend( this.defaults, options );
        this._init();
    };

    PumTrees.prototype = {

        defaults : {
            bindingClass: '.treeable', // The class to put on the element
        },

        _init : function(){
            this.bindingClass = this.defaults.bindingClass,
            this.allTrees     = $(this.defaults.bindingClass);

            this._initTrees(this.allTrees);
        },

        _initTrees : function(trees){ // Init Data needed from each tree
            var self = this;

            $(trees).each(function(key,item) {
                var namespace    = $(item).data('namespace'),
                    params       = jQuery.param({
                        'action' : 'node'
                    }),
                    ajax_url     = $(item).data('ajax-url')+'?'+params;

                self._initTree($(item), ajax_url, namespace);

                $(item).on("yaah-js_xhr_complete", "a.yaah-js", function() {
                    // TODO reload tatam
                });
            });

            return $(this);
        },

        _initTree : function(el, ajax_url, namespace) {
            var self = this;

            // events on object
            el.on('load_node.jstree', function (node, data) {
                var status = data.status,
                    node   = data.node;

                if (status && node.id != '#') {
                    self._addNode(node.id, namespace);
                }

                self._reloadYaah(250);
            });
            el.on('after_open.jstree', function (node, data) {
                var node   = data.node;

                if (node.id != '#') {
                    self._addNode(node.id, namespace);
                }

                self._reloadYaah();
            });
            el.on('after_close.jstree', function (node, data) {
                var node   = data.node;

                if (node.id != '#') {
                    self._removeNode(node.id, namespace);
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
                },
                "plugins" : [ "dnd", "types" ]
            });

            return $(this);
        },

        _addNode : function(node_id, namespace) {
            var self = this,
                values = self._getCookie(namespace);

            if (values) {
                values = JSON.parse(values);
                values.push(node_id);
                values = values.filter(function(itm,i,a){
                    return i==a.indexOf(itm);
                });
            } else {
                values = [node_id];
            }

            self._setCookie(namespace, JSON.stringify(values));

            return $(this);
        },

        _removeNode : function(node_id, namespace) {
            var self = this,
                values = self._getCookie(namespace);

            if (values) {
                values = JSON.parse(values);
                values = jQuery.grep(values, function(value) {
                  return value != node_id;
                });
            } else {
                values = [node_id];
            }

            self._setCookie(namespace, JSON.stringify(values));

            return $(this);
        },

        _setCookie : function(cname, value, exdays) {
            var self = this;

            if (exdays) {
                var date = new Date();
                date.setTime(date.getTime()+(exdays*24*60*60*1000));
                var expires = "; expires="+date.toGMTString();
            } else {
                var expires = "";
            }

            document.cookie = cname+"="+value+expires+";";

            return $(this);
        },

        _getCookie : function(cname) {
            var nameEQ = cname + "=",
                ca = document.cookie.split(';');

            for(var i=0;i < ca.length;i++) {
                var c = ca[i];

                while (c.charAt(0)==' ') c = c.substring(1,c.length);
                if (c.indexOf(nameEQ) == 0) {
                    return c.substring(nameEQ.length,c.length);
                }
            }

            return null;
        },

        _deleteCookie : function(cname) {
            document.cookie = cname + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';

            return $(this);
        },

        _reloadYaah : function(time) {
            time = time ? time : 0;

            if (typeof window.Yaah != 'undefined') {
                setTimeout(function(){
                    window.Yaah._ya_reload()
                }, time);
            }

            return $(this);
        },

        _reinit : function(options) {
            if (typeof options != 'undefined' && typeof options.bindingClass != 'undefined') {
                this.defaults.bindingClass = options.bindingClass;
            } else {
                this.defaults.bindingClass = '.treeable';
            }

            if (typeof $.jstree != 'undefined') {
                $.jstree.destroy();
            }

            this._init();
        },
    };

    $(document).ready(function(){
        window.PumTrees = new PumTrees();
    });

})(window.jQuery, window, document);