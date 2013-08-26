
+function ($) { "use strict";
    var pum_refreshers = {
        'classchange': function(ev)
        {
            var from = $(this);
            if (typeof from.data('pum_class_refresh_target') !== 'undefined' && typeof from.data('pum_class_refresh_name') !== 'undefined') {
                var sel = from.data('pum_class_refresh_target');
                var name = from.data('pum_class_refresh_name');
                var oldname = '';
                if (typeof $(sel).data('pum_class_refresh_oldname') !== 'undefined') {
                    oldname = $(sel).data('pum_class_refresh_oldname');
                }

                $(sel).data('pum_class_refresh_oldname', name).removeClass(oldname).addClass(name);
            }
        }
    };

    // Events
        // :: click
        $(document.body).on('click', '*[data-pum_class_refresh_target]', pum_refreshers.classchange);
}(window.jQuery);