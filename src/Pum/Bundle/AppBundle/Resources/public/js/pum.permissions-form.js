$(function() {
    var $attribute = $('#pum_permission_attribute');
    var $project = $('#pum_permission_project');
    var $beam = $('#pum_permission_beam');
    var $object = $('#pum_permission_object');

    if (!$beam.val()) {
        $beam.closest('.form-group').hide();
    } else {
        $project.attr('required', 'required');
    }

    if (!$object.val()) {
        $object.closest('.form-group').hide();
    } else {
        $beam.attr('required', 'required');
    }

    $attribute.change(function() {
        $beam.closest('.form-group').hide();
        $object.closest('.form-group').hide();
        $project.removeAttr('required');
        $beam.removeAttr('required');


        if ($(this).val().indexOf('PUM_BEAM_') == 0) {
            $project.attr('required', 'required');
            $beam.closest('.form-group').show();
        } else if ($(this).val().indexOf('PUM_OBJECT_') == 0) {
            $project.attr('required', 'required');
            $beam.attr('required', 'required');
            $beam.closest('.form-group').show();
            $object.closest('.form-group').show();
        }
    });

    $project.change(function() {
        var $form = $(this).closest('form');
        // Simulate form data, but only include the selected project value.
        var data = {};
        data[$project.attr('name')] = $project.val();
        data['pum_permission[group]'] = $('input[name=pum_permission\\[group\\]]:checked').val();
        $.ajax({
            url : $form.attr('action'),
            type: $form.attr('method'),
            data : data,
            success: function(html) {
                // Replace current beam field ...
                $('#pum_permission_beam').html(
                    // ... with the returned one from the AJAX response.
                    $(html).find('#pum_permission_beam').html()
                );
            }
        });
    });

    $(document).on('change', '#pum_permission_beam', function(){
        var $form = $(this).closest('form');
        var data = {};
        data[$project.attr('name')] = $project.val();
        data[$(this).attr('name')] = $(this).val();
        data['pum_permission[group]'] = $('input[name=pum_permission\\[group\\]]:checked').val();
        $.ajax({
            url : $form.attr('action'),
            type: $form.attr('method'),
            data : data,
            success: function(html) {
                $('#pum_permission_object').html(
                    $(html).find('#pum_permission_object').html()
                );
            }
        });
    });
});
