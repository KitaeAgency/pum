// =========================================================================
// PumTrees - v0.1
// =========================================================================
// Needs YAAH.js and jsTree

;(function($, window, document) {
    'use strict';

    var PumTree = function(el, options) {
        this.options = $.extend(this.options, options);
        this._init($(el));
    };

    PumTree.prototype = {

        tree    : null,
        labels  : {
            _create: 'Create',
            _rename: 'Rename',
            _delete: 'Delete'
        },
        options : {},

        _init : function(el)
        {
            var self       = this,
                el         = $(el),
                namespace  = el.data('namespace'),
                create_url = el.data('create-url'),
                ajax_url   = el.data('ajax-url');

                self.labels._create = el.data('label-create');
                self.labels._rename = el.data('label-rename');
                self.labels._delete = el.data('label-delete');

            self._initTree(el, ajax_url, create_url, namespace);

            /* Yaah success event */
            $(document).on('yaah-js_xhr_beforeInsert', '.yaah-js', function(ev, eventId, target, item, data){
                $(document).one(eventId, function(ev, target, item, data){
                    // Reinit Yaah
                    self._reloadYaah(250);

                    // Refresh node
                    if ($(item).hasClass('pum_create')) {
                        var node_id = $(item).data('parent');

                        if (node_id) {
                            self._refreshNode(node_id);
                        }

                    } else if ($(item).hasClass('pum_edit')) {
                        var node_id = $(item).data('node-id');

                        if (node_id) {
                            self._refreshParentNode(node_id);
                        }
                    }

                    // Reset trigger
                    $("#yaah_trigger").text('');
                });
            });
        },

        _initTree : function(el, ajax_url, create_url, namespace)
        {
            var self = this;

            // Create the instance
            self.tree = el.jstree({
                "core" : {
                    "animation" : 0,
                    'check_callback' : function (operation, node, node_parent, node_position, more) {
                        // operation can be 'create_node', 'rename_node', 'delete_node', 'move_node' or 'copy_node'
                        // in case of 'rename_node' node_position is filled with the new node name
                        return true;
                    },
                    "multiple": false,
                    "themes" : {
                        "stripes" : false,
                        "icons" : true
                    },
                    'data' : {
                        'url' : function (node) {
                            var params       = jQuery.param({
                                'action' : 'node'
                            });

                            return ajax_url+'?'+params;
                        },
                        'data' : function (node) {
                            return { 'id' : node.id };
                        }
                    }
                },
                "plugins" : [ "dnd", "types", "state", "contextmenu" ],
                "contextmenu": {
                    "items": self._customMenu(ajax_url, create_url)
                }
            });

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
                var node = data.node;

                if (node.id != '#') {
                    self._addNode(node.id, namespace);
                }

                self._reloadYaah();
            });

            el.on('after_close.jstree', function (node, data) {
                var node = data.node;

                if (node.id != '#') {
                    self._removeNode(node.id, namespace);
                }
            });

            el.on("move_node.jstree", function (node, data) {
                var params       = jQuery.param({
                    'action'     : 'move_node',
                    'id'         : data.node.id,
                    'new_pos'    : data.position,
                    'old_pos'    : data.old_position,
                    'new_parent' : data.parent,
                    'old_parent' : data.old_parent
                });

                self._callAjax(ajax_url + '?' + params);
            });

            el.on("create_node.jstree.jstree", function (node, data) {
                // Tree behavior
                /*var params       = jQuery.param({
                    'action'     : 'create_node',
                    'id'         : data.node.id,
                    'label'      : data.node.text,
                    'parent'     : data.parent,
                    'position'   : data.position
                });

                self._callAjax(ajax_url + '?' + params);*/
            });

            el.on("delete_node.jstree.jstree", function (node, data) {
                var params       = jQuery.param({
                    'action'     : 'delete_node',
                    'id'         : data.node.id
                });

                self._callAjax(ajax_url + '?' + params, {
                    callback : function(response) {
                        if (response === 'ERROR') {
                            if (data.node.parent) {
                                data.instance.refresh_node(data.node.parent);
                            } else {
                                data.instance.refresh();
                            }
                        }
                    }
                });
            });

            el.on("rename_node.jstree.jstree", function (node, data) {
                if (data.text != data.old) {
                    var params       = jQuery.param({
                        'action'     : 'rename_node',
                        'id'         : data.node.id,
                        'label'      : data.text
                    });

                    self._callAjax(ajax_url + '?' + params, {
                        callback : function(response) {
                            if (response === 'ERROR') {
                                data.node.text = data.old;
                                data.instance.rename_node(data.node, data.old);
                            }
                        }
                    });
                }
            });

            return $(this);
        },

        _customMenu : function(ajax_url, create_url)
        {
            var self  = this,
                items = {
                "create" : {
                    "separator_before"  : false,
                    "separator_after"   : false,
                    "_disabled"         : false,
                    "label"             : self.labels._create,
                    "action"            : function (data) {
                        var inst = $.jstree.reference(data.reference),
                            obj  = inst.get_node(data.reference);

                        self._loadCreateNodeForm(obj, create_url);
                    }
                },
                "rename" : {
                    "separator_before"  : false,
                    "separator_after"   : false,
                    "_disabled"         : false,
                    "label"             : self.labels._rename,
                    "action"            : function (data) {
                        var inst = $.jstree.reference(data.reference),
                            obj  = inst.get_node(data.reference);

                        inst.edit(obj);
                    }
                },
                "remove" : {
                    "separator_before"  : false,
                    "icon"              : false,
                    "separator_after"   : false,
                    "label"             : self.labels._delete,
                    "_disabled"         : function (data) {
                        var inst = $.jstree.reference(data.reference),
                            obj  = inst.get_node(data.reference);

                        return !inst.is_leaf(obj);
                    },
                    "action"            : function (data) {
                        var inst = $.jstree.reference(data.reference),
                            obj  = inst.get_node(data.reference);

                        if(inst.is_selected(obj)) {
                            inst.delete_node(inst.get_selected());
                        }
                        else {
                            inst.delete_node(obj);
                        }
                    }
                }/*,
                "ccp" : {
                    "separator_before"  : true,
                    "icon"              : false,
                    "separator_after"   : false,
                    "label"             : "Edit",
                    "action"            : false,
                    "submenu" : {
                        "cut" : {
                            "separator_before"  : false,
                            "separator_after"   : false,
                            "label"             : "Cut",
                            "action"            : function (data) {
                                var inst = $.jstree.reference(data.reference),
                                    obj = inst.get_node(data.reference);
                                if(inst.is_selected(obj)) {
                                    inst.cut(inst.get_selected());
                                }
                                else {
                                    inst.cut(obj);
                                }
                            }
                        },
                        "copy" : {
                            "separator_before"  : false,
                            "icon"              : false,
                            "separator_after"   : false,
                            "label"             : "Copy",
                            "action"            : function (data) {
                                var inst = $.jstree.reference(data.reference),
                                    obj = inst.get_node(data.reference);
                                if(inst.is_selected(obj)) {
                                    inst.copy(inst.get_selected());
                                }
                                else {
                                    inst.copy(obj);
                                }
                            }
                        },
                        "paste" : {
                            "separator_before"  : false,
                            "icon"              : false,
                            "_disabled"         : function (data) {
                                return !$.jstree.reference(data.reference).can_paste();
                            },
                            "separator_after"   : false,
                            "label"             : "Paste",
                            "action"            : function (data) {
                                var inst = $.jstree.reference(data.reference),
                                    obj = inst.get_node(data.reference);
                                inst.paste(obj);
                            }
                        }
                    }
                }*/
            };

            return items;
        },

        _callAjax : function(url, options)
        {
            var self = this;

            var data = {};
            var type = 'GET';

            if (options && options.data) {
                data = options.data;
            }

            if (options && options.type) {
                type = options.type;
            }

            $.ajax({
                url: url,
                type: type,
                data: data
            }).done(function(response) {
                if (options && options.callback) {
                    options.callback(response);
                }
            });
        },

        _loadCreateNodeForm : function(node, create_url)
        {
            // Do it a better way with yaah :)
            var self   = this,
                params = jQuery.param({
                    'parent_id' : node.id
                });

            $("#yaah_trigger").append('<a class="jstree-anchor yaah-js" href="#" data-ya-target="#pumAjaxModal .modal-content" data-ya-location="inner" data-ya-href="'+create_url+'?'+params+'" data-toggle="modal" data-target="#pumAjaxModal"></a>');

            self._reloadYaah();

            setTimeout(function(){
                $('#yaah_trigger a:last-child').trigger('click');
            }, 50);
        },

        _refreshNode : function(node_id)
        {
            this.tree.jstree(true).refresh_node(node_id);

            return $(this);
        },

        _refreshParentNode : function(node_id)
        {
            /*var ins = this.tree.jstree(true);

            ins.refresh_node(ins.get_node(node_id).parent);

            return $(this);*/
        },

        _addNode : function(node_id, namespace)
        {
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

        _removeNode : function(node_id, namespace)
        {
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

        _setCookie : function(cname, value, exdays)
        {
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

        _getCookie : function(cname)
        {
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

        _deleteCookie : function(cname)
        {
            document.cookie = cname + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';

            return $(this);
        },

        _reloadYaah : function(time)
        {
            if (typeof window.Yaah != 'undefined') {
                time = time ? time : 0;

                setTimeout(function(){
                    window.Yaah._ya_reload()
                }, time);
            }

            return $(this);
        },

        _reinit : function(options)
        {
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
        window.PumTree = new PumTree('#tree_container');
    });

})(window.jQuery, window, document);