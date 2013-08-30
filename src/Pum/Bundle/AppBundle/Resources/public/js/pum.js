
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

        /* GMAP AUTOCOMPLETE */
        window.input_gmaps_autocomplete = $('input[data-gmaps_autocomplete]');
        if (input_gmaps_autocomplete.length > 0) {
            window.gmaps_loaded = function(){
                $.each(input_gmaps_autocomplete, function(index, input){
                    var par = $(input).parents('.form-group');
                    $(par.find('.panel-collapse')).one('shown.bs.collapse', function(ev){
                        var lat_field = par.find('input[data-gmaps_target=latitude]');
                        var lng_field = par.find('input[data-gmaps_target=longitude]');

                        var map_container = par.find('.pum_gmaps')[0];
                        var map = new google.maps.Map(map_container, {
                            center: new google.maps.LatLng(lat_field.val(), lng_field.val()),
                            zoom: 17,
                            mapTypeId: google.maps.MapTypeId.ROADMAP
                        });
                        var autocomplete = new google.maps.places.Autocomplete(input);
                        autocomplete.bindTo('bounds', map);
                        var marker = new google.maps.Marker({
                            map: map,
                            draggable:true,
                            position: new google.maps.LatLng(lat_field.val(), lng_field.val())
                        });

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
                        google.maps.event.addDomListener(input, 'keydown', function(e) {
                            if (e.keyCode == 13) {
                                e.preventDefault();
                            }
                        });

                        google.maps.event.addListener(marker, 'dragend', function() {
                            var pos = marker.getPosition();
                            lat_field.val(pos.lat());
                            lng_field.val(pos.lng());
                        });

                        // google.maps.event.addListenerOnce(map, 'idle', function(){
                        //     par.find('.pum_gmaps_wrap').addClass('collapse');
                        // });
                    });

                });
            };

            var jq = document.createElement('script');
                jq.type = "text/javascript";
                jq.src = 'https://maps.googleapis.com/maps/api/js?sensor=false&libraries=places&callback=gmaps_loaded';
                $(document.body).append(jq);
        }

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