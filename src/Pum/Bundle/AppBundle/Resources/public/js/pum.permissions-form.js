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


    // checkbox stuff
    var projects = $('.project-wrapper');

    // Methods
    function isMaster($checkbox)
    {
        var master = $checkbox.data('type') == 'master' ? master = true : master = false;

        return master;
    }

    function getCheckboxType($checkbox)
    {
        return $checkbox.data('type');
    }

    function getCurrentLevel($checkbox)
    {
        return $checkbox.closest('.level');
    }

    function getSiblings($checkbox)
    {
        var type = getCheckboxType($checkbox);

        return getCurrentLevel($checkbox).find('input[type="checkbox"]:not([id$="activation"], [data-type="master"], [data-type="'+type+'"])');
    }

    function getAllSiblings($checkbox)
    {
        var type = getCheckboxType($checkbox);

        return getCurrentLevel($checkbox).find('input[type="checkbox"]:not([id$="activation"], [data-type="master"])');
    }

    function toggleSiblings($checkbox)
    {
        var $siblings = getSiblings($checkbox),
            isChecked = $checkbox[0].checked;

        $siblings.each(function(key, item){
            item.checked = isChecked;
        });

        return this;
    }

    function toggleAllSiblings($checkbox)
    {
        var checkboxValue = $checkbox[0].checked,
            $siblings     = getAllSiblings($checkbox);

        $siblings.each(function(key, item){
            item.checked = checkboxValue;
            $(item).trigger('change');
        });
        return this;
    }

    function areAllSiblingschecked($checkbox)
    {
        var areChecked      = false,
            $siblings       = getAllSiblings($checkbox),
            siblingsNumber  = $siblings.length,
            checkedSiblings = $siblings.filter(':checked').length,
            allChecked      = false;

        if ( siblingsNumber == checkedSiblings ){
            allChecked = true
        } else if( checkedSiblings > 0 ){
            allChecked = 'indeterminate';
        } else {
            allChecked = false;
        }

        return allChecked;
    }

    function getMaster($checkbox)
    {
        return getCurrentLevel($checkbox).find('input[type="checkbox"][data-type="master"]');
    }

    function setMaster($checkbox)
    {
        var master = getMaster($checkbox)[0];

        if ( areAllSiblingschecked($checkbox) == true ){
            master.checked = true;
            master.indeterminate = false;
        } else if ( areAllSiblingschecked($checkbox) == 'indeterminate' ){
            master.checked = false;
            master.indeterminate = true;
        } else {
            master.checked = false;
            master.indeterminate = false;
        }

        return this;
    }

    function hasParent($checkbox)
    {
        var hasParent  = getCurrentLevel($checkbox).closest('.sublevel').length ? hasParent = true : hasParent = false;

        return hasParent;
    }

    function getParent($checkbox)
    {
        var levelNumber     = getCurrentLevel($checkbox).closest('.sublevel').data('level'),
            checkboxType    = getCheckboxType($checkbox),
            parentLevel     = $('.level[data-level="'+levelNumber+'"]');

        return parentLevel.find('input[type="checkbox"][data-type="'+checkboxType+'"]');
    }

    function setElders($checkbox)
    {
        var $currentCheckbox = $checkbox,
            elders           = [];

        while ( hasParent($currentCheckbox) ){
            elders.push($currentCheckbox);
            $currentCheckbox = getParent($currentCheckbox);
        }

        var eldersLength = elders.length,
            $elders      = $(elders);

        $elders.each( function(key, item){
            setParent($(item));
        });

        return this;
    }

    function getCousins($checkbox)
    {
        var levelNumber      = getCurrentLevel($checkbox).data('level'),
            checkboxType     = getCheckboxType($checkbox),
            $parentLevel     = $('.level[data-level="'+levelNumber+'"]'),
            $currentSublevel = getCurrentLevel($checkbox).closest('.sublevel'),
            levelLength      = $parentLevel.data('level').length;

        var $cousins = $currentSublevel.find('input[type="checkbox"][data-type="'+checkboxType+'"]').filter( function(key, item ){
            return ( getCurrentLevel($(item)).data('level').length == levelLength )
        });

        return $cousins;
    }

    function areAllCousinschecked($checkbox)
    {
        var areChecked      = false,
            $cousins        = getCousins($checkbox),
            cousinsNumber   = $cousins.length,
            checkedCousins  = $cousins.filter(':checked').length,
            allChecked      = false;

        if ( cousinsNumber == checkedCousins ){
            allChecked = true
        } else if( checkedCousins > 0 ){
            allChecked = 'indeterminate';
        } else {
            allChecked = false;
        }

        return allChecked;
    }

    function setParent($checkbox)
    {
        var parent = getParent($checkbox)[0];

        if ( areAllCousinschecked($checkbox) == true ){
            parent.checked = true;
            parent.indeterminate = false;
        } else if ( areAllCousinschecked($checkbox) == 'indeterminate' ){
            parent.checked = false;
            parent.indeterminate = true;
        } else {
            parent.checked = false;
            parent.indeterminate = false;
        }

        setMaster($(parent));

        return this;
    }

    function hasChild($checkbox)
    {
        var levelNumber = getCurrentLevel($checkbox).data('level'),
            hasChild    = $('.sublevel[data-level="'+levelNumber+'"]').length ? hasChild = true : hasChild = false;

        return hasChild;
    }

    function getChildren($checkbox)
    {
        var levelNumber     = getCurrentLevel($checkbox).data('level'),
            checkboxType    = getCheckboxType($checkbox),
            $children       = $('.sublevel[data-level="'+levelNumber+'"] input[type="checkbox"][data-type="'+checkboxType+'"]');

        return $children;
    }

    function toggleChildren($checkbox)
    {
        var checkboxValue = $checkbox[0].checked,
            $children     = getChildren($checkbox);

        $children.each(function(key, item){
            item.checked = checkboxValue;
            $(item).trigger('change');
        });

        return this;
    }


    // Onload
    projects.each(function(key, project) {
        // Current Project Id
        var projectId = $(project).find('.panel-heading').first().attr('id');

        $(project).find('input[type="checkbox"]:not([id$="activation"])').on('change',function(ev){ // For All checkboxes except activation checkboxes
            var $this = $(this);

            if (isMaster($this)) { // this master button
                toggleAllSiblings($this);
            } else { // not master button
                if (hasChild($this)) {
                    toggleChildren($this);
                }
                setMaster($this);
            }
            setElders($this);
        });
    });

    function toggleActivation(checkbox){
        var $checkbox     = $(checkbox),
            $nestedPanels = $checkbox.closest('.panel-nested');

        if ( $checkbox[0].checked ){
            // Display first level
            $nestedPanels.find('.project-global-permissions').addClass('show-permissions');
            // Enable checkboxes
            $nestedPanels.find('input[type="checkbox"]:not([id$="activation"])').removeAttr('disabled');
        } else {
            // Hide first level
            $nestedPanels.find('.project-global-permissions').removeClass('show-permissions');
            // Enable checkboxes
            $nestedPanels.find('input[type="checkbox"]:not([id$="activation"])').attr('disabled',true);
        }
    }

    $('document').ready(function(){
        $('input[type="checkbox"]:checked:not([id$="activation"]').trigger('change');

        $('input[type="checkbox"][id$="activation"]').on('change',function(ev){
            toggleActivation( this );
        });
    });
});
