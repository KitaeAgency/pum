+function ($) { "use strict";
    /* Modal
    -------------------------------------------------- */
    var pum_modal = function(ev) {
        ev.stopImmediatePropagation();
        ev.preventDefault();

        var target       = $(ev.currentTarget),
            title        = target.attr('data-text'),
            content      = target.attr('data-content'),
            cancelText   = target.attr('data-cancel'),
            confirmText  = target.attr('data-confirm'),
            type         = target.attr('data-type') ? target.attr('data-type') : 'link',
            callback     = target.attr('data-cta') ? target.attr('data-cta') : null,
            callbackArgs = target.attr('data-cta-args') ? target.attr('data-cta-args') : null,
            modal        = $('#pumModal'),
            doCallBack   = true;

            modal.find('.myModalLabel').html(title);
            modal.find('.myModalContent').html(content);
            modal.find('.myModalcancel').html(cancelText);
            modal.find('.myModalconfirm').html(confirmText);

            if (type === 'link') {
                modal.find('.myModalconfirm').unbind('click');
                modal.find('.myModalcancel').unbind('click');

                var link = modal.find('.myModalconfirm');
                link.click(function (event) {
                    event.preventDefault();

                    document.location = target.attr('href');
                });
            } else if (type === 'submit') {
                modal.find('.myModalconfirm').unbind('click');
                modal.find('.myModalcancel').unbind('click');

                modal.find('.myModalconfirm').click(function() {
                    $('form#'+ target.attr('data-form-id')).submit();
                });
            } else if (type === 'choice') {
                modal.find('.myModalconfirm').unbind('click');
                modal.find('.myModalcancel').unbind('click');

                modal.find('.myModalconfirm').click(function (event) {
                    event.preventDefault();

                    document.location = URI(target.attr('href')).addSearch("choice", "1");
                    modal.modal('hide');
                });
                modal.find('.myModalcancel').click(function (event) {
                    event.preventDefault();

                    document.location = URI(target.attr('href')).addSearch("choice", "0");
                });
            } else if (type === 'ajax') {
                modal.find('.myModalconfirm').unbind('click');
                modal.find('.myModalcancel').unbind('click');

                var link       = modal.find('.myModalconfirm'),
                    doCallBack = false;

                link.click(function (event) {
                    event.preventDefault();

                    $.ajax({
                        url: target.attr('href'),
                    }).done(function(data) {
                        if (data == 'OK') {
                            modal.find('.myModalcancel').trigger( "click" );

                            if (typeof callback === 'string') {
                                callback = window[callback];

                                if (typeof callback === 'function') {
                                    callback(callbackArgs);
                                }
                            }
                        }
                    });
                });
            }

            if (doCallBack) {
                if (typeof callback === 'string') {
                    callback = window[callback];

                    if (typeof callback === 'function') {
                        callback(callbackArgs);
                    }
                }
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
        },
        'mass_property': function(ev)
        {
            ev.stopImmediatePropagation();

            var property = $(this).attr("data-property"),
                selector = $(this).attr("data-selector");
            $(selector).prop(property, $(this).prop(property)).change();
        },
        'mass_selector': function(ev)
        {
            var par = $(this).parents('table');

            par.find('tr td:first-child input[type=checkbox]').prop('checked', $(this).prop('checked')).change();
        },
        'unmass_selector': function(ev)
        {
            var par = $(this).parents('table');
            var headbox = par.find('thead th:first-child input[type=checkbox]');
            var total_checkbox = par.find('tr td:first-child input[type=checkbox]').length;
            var total_checkbox_checked = par.find('tr td:first-child input[type=checkbox]:checked').length;
            var total_checkbox_unchecked = total_checkbox - total_checkbox_checked;

            if ($(this).prop('checked')) {
                $(this).parents('tr').addClass('warning');
            } else {
                $(this).parents('tr').removeClass('warning');
            }

            if (total_checkbox_checked == total_checkbox) {
                headbox.prop('checked', true);
                headbox.prop('indeterminate', false);
            } else if (total_checkbox_unchecked == total_checkbox) {
                headbox.prop('checked', false);
                headbox.prop('indeterminate', false);
            }
            else {
                headbox.prop('indeterminate', true);
            }
        },
        'check_from_wrap': function(ev)
        {
            if (typeof ev.target !== 'undefined' && ev.target.tagName !== 'INPUT') {
                var input = $(this).find('input[type=checkbox]');
                input.prop('checked', !input.prop('checked')).change();
            }
        },
        'moment': function(item, format, interval)
        {
            item.html(moment().format(format));

            return setTimeout(function() { pum_refreshers.moment(item, format); }, interval);
        }
    };

    /* EVENTS
    -------------------------------------------------- */
        /* :: click */
        $(document).on('click', '*[data-pum_class_refresh_target]', pum_refreshers.classchange);
        $(document).on('click', '.clone-property', pum_refreshers.mass_property);
        $(document).on('click', 'thead th:first-child:has(input[type=checkbox]), tbody td:first-child:has(input[type=checkbox])', pum_refreshers.check_from_wrap);
        $(document).on('click', '*[data-confirm]', pum_modal);

        /* :: change */
        $(document).on('change', 'thead th:first-child input[type=checkbox]', pum_refreshers.mass_selector);
        $(document).on('change', 'tbody td:first-child input[type=checkbox]', pum_refreshers.unmass_selector);

        $(document).on('change', '.linked-field-toggle input[type=radio]', function(){
            var matches = $(this).attr('name').match(/\[(.*?)\]/);

            if (matches) {
                var name = matches[1];
            }
            $('.linked-field[name*='+name+']').parent().parent().hide();
            $('.linked-field[name*='+name+'][id$='+$(this).val()+']').parent().parent().slideDown(150);
        });

        /* Yaah success event */
        $(document).on('yaah-js_xhr_beforeInsert', '.yaah-js', function(ev, eventId, target, item, data){
            $(document).one(eventId, function(ev, target, item, data){
                $(target).find('.js-tatam').tatam();
            });
        });


    /* HELPERS
    -------------------------------------------------- */
    var inArray = function(val, arr) {
        return (arr.indexOf(val) != -1);
    }
    var pum_lang = {
        normalizeLanguage: function (key) {
            return key ? key.toLowerCase().replace('_', '-') : key;
        },
        getLangDefinition: function (key, languages) {
            var i = 0, j, lang, next, split;

            var lang = pum_lang.normalizeLanguage(key);

            if (inArray(key, languages)) {
                return key;
            }

            if (inArray(lang, languages)) {
                return lang;
            }

            var split = lang.split('-');
            if (inArray(split[0], languages)) {
                return split[0];
            }
            var lang = split[0] + '_' + split[0].toLowerCase();
            if (inArray(lang, languages)) {
                return lang;
            }

            return false;
        }
    }
    var jQuerySelectorEscape = function(expression) {
        return expression.replace(/[!"#$%&'()*+,.\/:;<=>?@\[\\\]^`{|}~]/g, '\\$&');
    }

    function pumDecorateHtml(element)
    {
        var $element = $(element);

        $element.find('textarea[data-ckeditor]').each(function (i, e) {
            CKEDITOR.replace(e, JSON.parse($(e).attr('data-ckeditor')));
        });
    }

    $(function () {
        pumDecorateHtml(document);
    });


    /* DOMREADY
    -------------------------------------------------- */
    $(document).ready(function(){
        /* MOMENT.JS */
        if (moment && app_locale) {
            moment.lang(app_locale);
        }

        /* JQUERY VALIDATE */
        if ($.validator) {
            var jquery_validate_locales = ['ar', 'bg', 'ca', 'cs', 'da', 'de', 'el', 'es', 'et', 'eu', 'fa', 'fi', 'fr', 'he', 'hr', 'hu', 'it', 'ja', 'ka', 'kk', 'ko', 'lt', 'lv', 'my', 'nl', 'no', 'pl', 'pt_BR', 'pt_PT', 'ro', 'ru', 'si', 'sk', 'sl', 'sr', 'sv', 'th', 'tr', 'uk', 'vi', 'zh', 'zh_TW'];

            var jquery_validate_locale = pum_lang.getLangDefinition(app_locale, jquery_validate_locales);
            if (jquery_validate_locale) {
                var jvl = document.createElement('script');
                    jvl.type = 'text/javascript';
                    jvl.src = JQUERY_VALIDATE_LOCALPATH + 'messages_' + jquery_validate_locale + '.js';
                    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(jvl, s);
            }

            if ($('.pum-core-content form').length) {
                $('.pum-core-content form').validate({
                    'errorClass': 'has-error',
                    'validClass': 'has-success',
                    'wrapper': 'li',
                    'errorElement' : 'span',
                    highlight: function(element, errorClass, validClass) {
                        $(element).parents('.form-group').removeClass(validClass).addClass(errorClass);
                    },
                    unhighlight: function(element, errorClass, validClass) {
                        $(element).parents('.form-group').removeClass(errorClass).addClass(validClass);
                    },
                    errorPlacement: function(error, element) {
                        var container = $(element).parent().find('span.help-block ul');
                        if (!container.length) {
                            $('<span class="help help-block"><ul></ul></span>').appendTo(element.parent());
                            container = $(element).parent().find('span.help-block ul');
                        }

                        error.appendTo(container);
                    }
                });
            }
        }

        /* TOOLTIPS */
        $('*[data-toggle="tooltip"]').tooltip();

        /* IMG POPOVER */
        $('a[rel=popoverimg]').popover({
            html: true,
            trigger: 'hover',
            content: function () {
                var height = '',
                    width = '';
                if (typeof $(this).data('img_width') !== 'undefined') {
                    width = ' width="' + $(this).data('img_width') + '"';
                }
                if (typeof $(this).data('img_height') !== 'undefined') {
                    height = ' height="' + $(this).data('img_height') + '"';

                    if (width === '') {
                        height = ' height="' + width + '"';
                    }
                }
                else if (width !== '') {
                    height = ' height="' + width + '"';
                }
                return '<img src="' + $(this).data('img') + '"' + width + height + ' />';
            }
        });

        /* MOMENT AUTOUPDATE */
        $.each($('*[data-moment=autoupdate]'), function(index, item){
            item = $(item);

            var format = (typeof item.data('moment-format') !== 'undefined') ? item.data('moment-format') : '';
            var interval = (typeof item.data('moment-interval') !== 'undefined') ? item.data('moment-interval') : 1000;

            pum_refreshers.moment(item, format, interval);
        });

        /* DATEPICKER */
        $.each($("form input.datepicker"), function(index, input) {
            $(input).datepicker({
                dateFormat: $(input).data('dateformat') ? $(input).data('dateformat') : "dd/mm/yy",
                defaultDate: null,
                changeYear: true,
                yearRange: $(input).data('yearrange') ? $(input).data('yearrange') : null,
                minDate: $(input).data('mindate') ? new Date(1000*$(input).data('mindate')) : null,
                maxDate: $(input).data('maxdate') ? new Date(1000*$(input).data('maxdate')) : null,
                firstDay: 1,
                onClose: $(input).data('range') && $(input).data('range-type') ? function(selectedDate) {
                    $($(input).data('range')).datepicker('option', $(input).data('range-type'), selectedDate);
                } : null
            });
        });

        /* DATEPTIMEICKER */
        $.each($("form input.datetimepicker"), function(index, input) {
            $(input).datetimepicker({
                dateFormat: $(input).data('dateformat') ? $(input).data('dateformat') : "dd/mm/yy",
                defaultDate: null,
                changeYear: true,
                yearRange: $(input).data('yearrange') ? $(input).data('yearrange') : null,
                minDate: $(input).data('mindate') ? new Date(1000*$(input).data('mindate')) : null,
                maxDate: $(input).data('maxdate') ? new Date(1000*$(input).data('maxdate')) : null,
                firstDay: 1,
                timeFormat: $(input).data('timeformat') ? $(input).data('timeformat') : "hh:mm TT",
                 onClose: $(input).data('range') && $(input).data('range-type') ? function(selectedDate) {
                    $($(input).data('range')).datepicker('option', $(input).data('range-type'), selectedDate);
                } : null
            });
        });

        /* GMAPS Widget */
        window.input_gmaps_widget = $('input[data-gmaps_widget]');
        if (input_gmaps_widget.length > 0) {
            window.gmaps_loaded = function(){
                $.each(input_gmaps_widget, function(index, input){
                    var par = $(input).parents('.form-group');
                    $(par.find('.panel-collapse')).one('shown.bs.collapse', function(ev){
                        par.addClass('gmaps_initialized');

                        // Retrieve lat/lng fields
                        var lat_field = par.find('input[data-gmaps_target=latitude]');
                        var lng_field = par.find('input[data-gmaps_target=longitude]');

                        var map_container = par.find('.pum_gmaps')[0];

                        // Initialize Google Maps
                        var map = new google.maps.Map(map_container, {
                            center: new google.maps.LatLng(lat_field.val(), lng_field.val()),
                            zoom: 17,
                            mapTypeId: google.maps.MapTypeId.ROADMAP
                        });
                        par.data('gmaps_map', map);

                        // Initialize Autocomplete module
                        var autocomplete = new google.maps.places.Autocomplete(input);
                        autocomplete.bindTo('bounds', map);
                        var marker = new google.maps.Marker({
                            map: map,
                            draggable:true,
                            position: new google.maps.LatLng(lat_field.val(), lng_field.val())
                        });
                        par.data('gmaps_autocomplete', autocomplete);

                        // Initialize Autocomplete place change event
                        google.maps.event.addListener(autocomplete, 'place_changed',function(){
                            var place = autocomplete.getPlace();
                                if (place.geometry.viewport) {
                                    map.fitBounds(place.geometry.viewport);
                                } else {
                                    map.setCenter(place.geometry.location);
                                    map.setZoom(17);
                                }
                                marker.setPosition(place.geometry.location);
                                lat_field.val(place.geometry.location.lat());
                                lng_field.val(place.geometry.location.lng());
                        });

                        // Avoid submitting form when pressing "enter" on autocomplete field
                        google.maps.event.addDomListener(input, 'keydown', function(e) {
                            if (e.keyCode == 13) {
                                e.preventDefault();
                            }
                        });

                        // Initialize drag feature on marker to update coordinates
                        google.maps.event.addListener(marker, 'dragend', function() {
                            var pos = marker.getPosition();
                            lat_field.val(pos.lat());
                            lng_field.val(pos.lng());
                        });
                    });

                });
            };

            // Inject Google Maps API w/ callback
            var jq = document.createElement('script');
                jq.type = "text/javascript";
                jq.src = 'https://maps.googleapis.com/maps/api/js?sensor=false&libraries=places&callback=gmaps_loaded';
                $(document.body).append(jq);
        } // end: GMAPS

        /* TATAM JS */
        $('.js-tatam').tatam();

        /* Linked Fields */
        $('.linked-field').parent().parent().hide();

        /* Copy Field Helper */
        $('.copy-input').keyup(function() {
            var el          = $(this),
                copyText    = '',
                targetInput = $(el.data('copy-input'));

            if (el.data('text-prefix')) {
                copyText += el.data('text-prefix');
            }

            copyText += el.val();

            targetInput.val(copyText);
        });
    });

    /* Pager GO To Page */
    $(document).on('keydown', '.pagination_goto input', function (e) {
        var $this = $(this);

        if(e.which == 13) {
            var max      = $this.data('max'),
                href     = $this.data('href'),
                replacer = $this.data('replacer'),
                value    = parseInt($this.val());

                if (isNaN(value)) {
                    value = 1;
                }

                if (value > max) {
                    value = max;
                }

                $this.val(value);

                window.location.replace(href.replace(replacer, value));
        }
    });

    // INPUT COLLAPSE DATA-API
    // =======================
    $(document).on('change.bs.collapse.data-api', '[data-toggle=inputcollapse]', function (e) {
        var $this   = $(this), href;

        var target  = $this.attr('data-target')
            || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, ''); //strip for ie7
        var $target = $(target);
        var data    = $target.data('bs.collapse');
        var option  = data ? 'toggle' : $this.data();
        var parent  = $this.attr('data-parent');
        var $parent = parent && $(parent);

        if (!data || !data.transitioning) {
            if ($parent) $parent.find('[data-toggle=inputcollapse][data-parent="' + parent + '"]').not($this).addClass('collapsed');
            $this[$target.hasClass('in') ? 'addClass' : 'removeClass']('collapsed');
        }

        // console.log($target, option);

        $target.collapse(option);
    });
}(window.jQuery);
