$(function() {
    var $beam = $('#pa_custom_view_beam');
    var $object = $('#pa_custom_view_object');
    var $tableview = $('#pa_custom_view_tableView');

    disabled($object);
    disabled($tableview);

    function enabled(el)
    {
        el.removeAttr('disabled');
    }

    function disabled(el)
    {
        el.attr('disabled', 'disabled');
    }

    function keepFirst(el)
    {
        el.html(el.children(":first"));
    }
    

    $beam.change(function() {
        if ($beam.val()) {
            keepFirst($object);
            keepFirst($tableview);
            disabled($object);
            disabled($tableview);

            var $form = $(this).closest('form'),
                 data = {};

            data['pa_custom_view[beam]'] = $beam.val();

            $.ajax({
                url : $form.attr('action'),
                type: $form.attr('method'),
                data : data,
                success: function(html) {
                    // Replace current beam field contents ...
                    $object.html(
                        // ... with the returned one from the AJAX response.
                        $(html).find('#pa_custom_view_object').html()
                    );
                    enabled($object);
                }
            });
        }
    });

    $object.change(function() {
        if ($object.val()) {
            keepFirst($tableview);
            disabled($tableview);

            var $form = $(this).closest('form'),
                 data = {};

            data['pa_custom_view[beam]']   = $beam.val();
            data['pa_custom_view[object]'] = $object.val();

            $.ajax({
                url : $form.attr('action'),
                type: $form.attr('method'),
                data : data,
                success: function(html) {
                    $tableview.html(
                        $(html).find('#pa_custom_view_tableView').html()
                    );
                    enabled($tableview);
                }
            });
        }
    });
});
