
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
                var link = modal.find('.myModalconfirm');
                link.click(function (event) {
                    event.preventDefault();

                    document.location = target.attr('href');
                });
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


    /* DOMREADY
    -------------------------------------------------- */
    $(document).ready(function(){

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
    });
}(window.jQuery);
