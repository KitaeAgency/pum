$(function() {
    var $project = $('#pum_permission_project');
    var $beam = $('#pum_permission_beam');
    var $object = $('#pum_permission_object');
    var $instance = $('#pum_permission_instance');
    var $emptyValue = "<option>All objects</option>";

    function toggleInstance(object)
    {
        if (object) {
            $instance.removeAttr('disabled');
        } else {
            $instance.val('');
            $instance.attr('disabled', 'disabled');
        }
    }

    toggleInstance($object.val());

    $object.change(function(){
        toggleInstance($object.val());
    });

    $project.change(function() {
        $beam.attr('disabled', 'disabled');
        var $form = $(this).closest('form');
        // Simulate form data, but only include the selected project value.
        var data = {};
        data['pum_permission[project]'] = $project.val();
        data['pum_permission[group]'] = $('input[name=pum_permission\\[group\\]]:checked').val();
        $.ajax({
            url : $form.attr('action'),
            type: $form.attr('method'),
            data : data,
            success: function(html) {
                // Replace current beam field contents ...
                $beam.html(
                    // ... with the returned one from the AJAX response.
                    $(html).find('#pum_permission_beam').html()
                );
                $beam.removeAttr('disabled');
                $object.html($emptyValue);
                toggleInstance();
            }
        });
    });

    $beam.change(function() {
        $object.attr('disabled', 'disabled');
        var $form = $(this).closest('form');
        var data = {};
        data['pum_permission[project]'] = $project.val();
        data['pum_permission[beam]'] = $beam.val();
        data['pum_permission[group]'] = $('input[name=pum_permission\\[group\\]]:checked').val();
        $.ajax({
            url : $form.attr('action'),
            type: $form.attr('method'),
            data : data,
            success: function(html) {
                $object.html(
                    $(html).find('#pum_permission_object').html()
                );
                $object.removeAttr('disabled');
                toggleInstance($object.val());
            }
        });
    });


    // Tests checkbox stuff
    var projects = $('.project-wrapper');

    function toggleLine(siblingCheckboxes, isChecked){ // Toggle all checkboxes from current line
        siblingCheckboxes.each( function(key, checkbox){
            checkbox.checked = isChecked;
        });
    }

    function toggleColumn(childrenCheckboxes, isChecked){  // Toggle all checkboxes from current line
        childrenCheckboxes.each( function(key, checkbox){
            checkbox.checked = isChecked;
        });
    }
    function permissionHasParent(clickedCheckbox){ // Return true or false
        console.log('has parent?');
        var hasParent = $(clickedCheckbox).closest('.panel-collapse').length ? hasParent = true : hasParent = false;
        return hasParent;
    }
    function permissionHasChildren(clickedCheckbox){ // Return true or false
        console.log('has children?');
        var hasChildren = $(clickedCheckbox).closest('.beam-object').length ? hasChildren = false : hasChildren = true;
        return hasChildren;
    }

    // Onload
    projects.each( function(key, project){
        var projectId = $(project).find('.panel-heading').first().attr('id');

        // Which Project ?
        console.log( "============" );
        console.log( "Project Id: ", projectId );
        console.log( project );

        // Is Activated ?
        console.log( "Is The project activated ?" );

        // All "all" checkboxes
        $('#'+projectId+'_wrapper input[class^="all"]').on('click', function(ev){
            var siblingsCheckboxes =  $(this).closest('.row').find('input:not([class^="all"])');
            var isChecked = this.checked;
            toggleLine(siblingsCheckboxes,isChecked);
            var hasParents = permissionHasParent(this);
            var hasChildren = permissionHasChildren(this);
            console.log( 'hasChildren', hasChildren );
            console.log( 'hasParents', hasParents );
        });

        //
        $(project).find('input:not([class^="all"])').on('change', function(ev){
            var childrenCheckboxes = $(this).closest('.panel-heading').next().find('.'+this.className);
            var isChecked = this.checked;
            toggleColumn(childrenCheckboxes, isChecked);
        });
        console.log( "============" );

    });


});
