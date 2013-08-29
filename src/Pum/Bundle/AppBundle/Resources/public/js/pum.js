
+function ($) { "use strict";

    /* Modal
    -------------------------------------------------- */
    var pum_modal = function(ev) {
        ev.stopImmediatePropagation();
        ev.preventDefault();

        var target      = $(ev.currentTarget),
            title       = target.attr('data-text'),
            content     = target.attr('data-content'),
            cancelText  = target.attr('data-cancel'),
            confirmText = target.attr('data-confirm'),
            type        = target.attr('data-type') ? target.attr('data-type') : 'link',
            modal       = $('#pumModal');

            modal.find('.myModalLabel').html(title);
            modal.find('.myModalContent').html(content);
            modal.find('.myModalcancel').html(cancelText);
            modal.find('.myModalconfirm').html(confirmText);

            if (type === 'link') {
                modal.find('.myModalconfirm').attr('href', target.attr('href'));
            } else if (type === 'submit') {
                modal.find('.myModalconfirm').unbind('click');
                modal.find('.myModalconfirm').click(function() {
                    $('form#'+ target.attr('data-form-id')).submit();
                });
            }

            modal.modal();

        return false;
    };


    /* Refreshers
    -------------------------------------------------- */
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

    /* EVENTS
    -------------------------------------------------- */
        /* :: click */
        $(document).on('click', '*[data-pum_class_refresh_target]', pum_refreshers.classchange);
        $(document).on('click', '*[data-confirm]', pum_modal);


    /* DOMREADY
    -------------------------------------------------- */
    $(document).ready(function(){

        /* TOOLTIPS */
        $('*[data-toggle="tooltip"]').tooltip();

        /* DATEPICKER */
        $.each($("form input.datepicker"), function(index, input) {
            $(input).datepicker({
                dateFormat: $(input).attr('data-dateformat') ? $(input).attr('data-dateformat') : "dd/mm/yy",
                defaultDate: null,
                changeYear:true,
                yearRange: $(input).attr('data-year-range'),
                minDate: $(input).attr('data-mindate') ? new Date(1000*$(input).attr('data-mindate')) : null,
                maxDate: $(input).attr('data-maxdate') ? new Date(1000*$(input).attr('data-maxdate')) : null,
                firstDay:1
            });
        });

        /* DATEPTIMEICKER */
        $.each($("form input.datetimepicker"), function(index, input) {
            $(input).datetimepicker({
                dateFormat: $(input).attr('data-dateformat') ? $(input).attr('data-dateformat') : "dd/mm/yy",
                defaultDate: null,
                changeYear:true,
                yearRange: $(input).attr('data-yearrange') ? $(input).attr('data-yearrange') : null,
                minDate: $(input).attr('data-mindate') ? new Date(1000*$(input).attr('data-mindate')) : null,
                maxDate: $(input).attr('data-maxdate') ? new Date(1000*$(input).attr('data-maxdate')) : null,
                firstDay:1,
                timeFormat: $(input).attr('data-timeformat') ? $(input).attr('data-timeformat') : "hh:mm TT"
            });
        });

        /* CLONE PROPERTY TO SELECTOR BY CLICK */
        $.each($(".clone-property"), function(index, el) {
            $(el).click(function(event) {
                event.stopImmediatePropagation();

                var property = $(el).attr("data-property"),
                    selector = $(el).attr("data-selector");

                $(selector).prop(property, $(el).prop(property));
            });
        });
    });
}(window.jQuery);