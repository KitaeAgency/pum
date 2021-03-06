$(function() {
    var $project     = $('#pum_permission_project'),
        $beam        = $('#pum_permission_beam'),
        $object      = $('#pum_permission_object'),
        $instance    = $('#pum_permission_instance'),
        $emptyValue  = "<option>All objects</option>";

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


    // ==============
    // Checkbox Stuff
    // ==============
    var projects = $('.project-wrapper');

    // =======
    // Methods
    // =======
    function isMaster($checkbox)                                                // Return [Bool]
    {
        var master = $checkbox.data('type') == 'master' ? master = true : master = false;

        return master;
    }

    function getCheckboxType($checkbox)                                         // Return [String]
    {
        return $checkbox.data('type');
    }

    function getCurrentLevel($checkbox)                                         // Return [HTMLElement]
    {
        return $checkbox.closest('.level');
    }

    function getSiblings($checkbox)                                          // Return [HTMLElements]
    {
        return getCurrentLevel($checkbox).find('input[type="checkbox"]:not([id$="activation"],[data-type="master"])');
    }

    function toggleSiblings($checkbox)                                       // Return [This]
    {
        var checkboxValue = $checkbox[0].checked,
            $siblings     = getSiblings($checkbox);

        $siblings.each(function(key, item){
            item.checked = checkboxValue;       // Check / Uncheck the siblings
            $(item).trigger('change');          // Trigger change event for all siblings except master brother
        });

        setMaster($checkbox);                   // Update the master sibling
        return this;
    }

    function areAllSiblingschecked($checkbox)                                   // Return [Bool]
    {
        var $siblings       = getSiblings($checkbox),            // Get list of siblings including current
            siblingsNumber  = $siblings.length,                     // Get total number
            checkedSiblings = $siblings.filter(':checked').length,  // Get :checked number
            allChecked      = false,                                // init return value
            indeterminateNb = 0;

            $siblings.each( function(key,item){
                if (item.indeterminate==true){
                    indeterminateNb++;
                }
            });

        if ( siblingsNumber == checkedSiblings ){                   // All checked
            allChecked = true;
        } else if (checkedSiblings > 0 || indeterminateNb > 0){     // Indeterminate
            allChecked = 'indeterminate';
        } else {                                                    // All Unchecked
            allChecked = false;
        }

        return allChecked;
    }

    function getMaster($checkbox)                                               // Return [HTMLElement]
    {
        return getCurrentLevel($checkbox).find('input[type="checkbox"][data-type="master"]');
    }

    function setMaster($checkbox)                                               // Return [This]
    {
        var master = getMaster($checkbox)[0];

        if (areAllSiblingschecked($checkbox) == true){
            master.checked = true;
            master.indeterminate = false;
        } else if (areAllSiblingschecked($checkbox) == 'indeterminate'){
            master.checked = false;
            master.indeterminate = true;
        } else {
            master.checked = false;
            master.indeterminate = false;
        }

        return this;
    }

    function hasParent($checkbox)                                               // Return [Bool]
    {
        var hasParent  = getCurrentLevel($checkbox).closest('.sublevel').length ? hasParent = true : hasParent = false;

        return hasParent;
    }

    function getParent($checkbox)                                               // Return [HtmlElement] or false
    {
        var levelNumber     = getCurrentLevel($checkbox).closest('.sublevel').data('level'),
            checkboxType    = getCheckboxType($checkbox),
            parentLevel     = $('.level[data-level="'+levelNumber+'"]'),
            parent          = parentLevel.find('input[type="checkbox"][data-type="'+checkboxType+'"]'),
            response        = parent.length ? parent : false;

        return response;
    }

    function setElders($checkbox)                                               // Return [this]
    {
        var $currentCheckbox = $checkbox,
            elders           = [];

        while (hasParent($currentCheckbox)){
            elders.push($currentCheckbox);
            $currentCheckbox = getParent($currentCheckbox);
        }

        var eldersLength = elders.length,
            $elders      = $(elders);

        $elders.each(function(key, item){
            setParent($(item));
            setMaster(getParent($(item)));
        });

        return this;
    }

    function getCousins($checkbox)                                              // Return [HtmlElements] or false
    {
        var levelNumber      = getCurrentLevel($checkbox).data('level'),
            checkboxType     = getCheckboxType($checkbox),
            $parentLevel     = $('.level[data-level="'+levelNumber+'"]'),
            $currentSublevel = getCurrentLevel($checkbox).closest('.sublevel'),
            levelLength      = $parentLevel.data('level').length,
            response         = false,
            $cousins         = $currentSublevel.find('input[type="checkbox"][data-type="'+checkboxType+'"]').filter( function( key, item ){
                return ( getCurrentLevel( $(item) ).data('level').length == levelLength )
            });

        response = $cousins.length ? $cousins : false;

        return response;
    }

    function areAllCousinschecked($checkbox)                                    // Return [Bool] or string
    {
        var $cousins        = getCousins($checkbox),
            cousinsNumber   = $cousins.length,
            checkedCousins  = $cousins.filter(':checked').length,
            allChecked      = false,
            indeterminateNb = 0;

        $cousins.each(function(key,item){
            if (item.indeterminate==true){
                indeterminateNb++;
            }
        });

        if (cousinsNumber == checkedCousins){
            allChecked = true
        } else if (checkedCousins > 0 || indeterminateNb > 0){
            allChecked = 'indeterminate';
        } else {
            allChecked = false;
        }

        return allChecked;
    }

    function setParent($checkbox)                                               // Return this
    {
        if (hasParent($checkbox)){
            var parent = getParent($checkbox)[0];

            if (areAllCousinschecked($checkbox) == true){
                parent.checked = true;
                parent.indeterminate = false;
            } else if (areAllCousinschecked($checkbox) == 'indeterminate'){
                parent.checked = false;
                parent.indeterminate = true;
            } else {
                parent.checked = false;
                parent.indeterminate = false;
            }
        }

        return this;
    }

    function hasChild($checkbox)                                                // Return [Bool]
    {
        var levelNumber = getCurrentLevel($checkbox).data('level'),
            hasChild    = $('.sublevel[data-level="'+levelNumber+'"]').length ? hasChild = true : hasChild = false;

        return hasChild;
    }

    function getChildren($checkbox)                                             // Return [HtmlElements]
    {
        var levelNumber     = getCurrentLevel($checkbox).data('level'),
            checkboxType    = getCheckboxType($checkbox),
            $children       = $('.sublevel[data-level="'+levelNumber+'"] input[type="checkbox"][data-type="'+checkboxType+'"]');

        return $children;
    }

    function toggleChildren($checkbox)                                          // Return [this]
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
        $(project).find('input[type="checkbox"]:not([id$="activation"])').on('change',function(ev){ // For All checkboxes except activation checkboxes
            var $this = $(this);

            if (isMaster($this)) {
                toggleSiblings($this);
            } else {
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
            $nestedPanel  = $checkbox.closest('.panel-nested');

        if ( $checkbox[0].checked ){
            // Display first level
            $nestedPanel.addClass('activated');
            // Enable checkboxes
            $nestedPanel.find('input[type="checkbox"]:not([id$="activation"])').removeAttr('disabled');
        } else {
            // Hide first level
            $nestedPanel.removeClass('activated');
            // Enable checkboxes
            $nestedPanel.find('input[type="checkbox"]:not([id$="activation"])').attr('disabled',true);
        }
    }

    $('document').ready(function(){
        $('input[type="checkbox"]:checked:not([id$="activation"])').trigger('change');

        $('input[type="checkbox"][id$="activation"]').on('change',function(ev){
            toggleActivation( this );
        });
    });
});
