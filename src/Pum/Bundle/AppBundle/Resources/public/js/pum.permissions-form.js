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

    // Onload
    projects.each( function(key, project){
        // Current Project Id
        var projectId = $(project).find('.panel-heading').first().attr('id');

        function isMaster(checkbox){
            var master = checkbox.data('type') == 'master' ? master = true : master = false;
            return master;
        }
        function getCheckboxType(checkbox){

            return $(checkbox).data('type');
        }
        function getCurrentLevel(checkbox){

            return $(checkbox).closest('.level');
        }
        function getSiblings(checkbox){
            var type = getCheckboxType(checkbox);
            return getCurrentLevel(checkbox).find('input[type="checkbox"]:not([id$="activation"], [data-type="master"], [data-type="'+type+'"])');
        }
        function getAllSiblings(checkbox){
            var type = getCheckboxType(checkbox);
            return getCurrentLevel(checkbox).find('input[type="checkbox"]:not([id$="activation"], [data-type="master"])');
        }
        function toggleSiblings(checkbox){
            getSiblings(checkbox).each(function(key, item){
                item.checked = checkbox[0].checked;
            });
            return this;
        }
        function toggleAllSiblings(checkbox){
            var checkboxValue = checkbox[0].checked;
            getAllSiblings(checkbox).each(function(key, item){
                item.checked = checkboxValue;
                $(item).trigger('change');
            });
            return this;
        }
        function areAllSiblingschecked(checkbox){
            var areChecked  = false,
            siblings        = getAllSiblings(checkbox),
            siblingsNumber  = siblings.length,
            checkedSiblings = 0,
            allChecked      = false;

            siblings.each(function(key, item){
                if ( item.checked ) { checkedSiblings++; }
            });

            if ( siblingsNumber == checkedSiblings ){
                allChecked = true
            } else if( checkedSiblings > 0 ){
                allChecked = 'indeterminate';
            } else {
                allChecked = false;
            }

            return allChecked;
        }
        function getMaster(checkbox){

            return getCurrentLevel(checkbox).find('input[type="checkbox"][data-type="master"]');
        }
        function setMaster(checkbox){
            if ( areAllSiblingschecked(checkbox) == true ){
                getMaster(checkbox)[0].checked = true;
                getMaster(checkbox)[0].indeterminate = false;
            } else if ( areAllSiblingschecked(checkbox) == 'indeterminate' ){
                getMaster(checkbox)[0].checked = false;
                getMaster(checkbox)[0].indeterminate = true;
            } else {
                getMaster(checkbox)[0].checked = false;
                getMaster(checkbox)[0].indeterminate = false;
            }
            // getMaster(checkbox).trigger('change');
            return this;
        }
        function hasParent(checkbox){
            var hasParent  = getCurrentLevel(checkbox).closest('.sublevel').length ? hasParent = true : hasParent = false;
            return hasParent;
        }
        function getParent(checkbox){
            var levelNumber = getCurrentLevel(checkbox).closest('.sublevel').data('level'),
            checkboxType    = getCheckboxType(checkbox),
            parentLevel     = $('.level[data-level="'+levelNumber+'"]');
            return parentLevel.find('input[type="checkbox"][data-type="'+checkboxType+'"]');
        }
        function getCousins(checkbox){
            var levelNumber = getCurrentLevel(checkbox).data('level'),
            checkboxType    = getCheckboxType(checkbox),
            parentLevel     = $('.level[data-level="'+levelNumber+'"]'),
            currentSublevel = getCurrentLevel(checkbox).closest('.sublevel'),
            levelLength     = parentLevel.data('level').length,
            cousins         = [];

            currentSublevel.find('input[type="checkbox"][data-type="'+checkboxType+'"]').each( function(key, item ){
                if ( getCurrentLevel(item).data('level').length == levelLength ){
                    cousins.push(item);
                }
            });

            return cousins;
        }
        function areAllCousinschecked(checkbox){
            var areChecked  = false,
            cousins         = getCousins(checkbox),
            cousinsNumber   = cousins.length,
            checkedCousins  = 0,
            allChecked      = false;


            $(cousins).each(function(key, item){
                if ( item.checked ) { checkedCousins++; }
            });

            if ( cousinsNumber == checkedCousins ){
                allChecked = true
            } else if( checkedCousins > 0 ){
                allChecked = 'indeterminate';
            } else {
                allChecked = false;
            }

            return allChecked;
        }
        function setParent(checkbox){
            if ( areAllCousinschecked(checkbox) == true ){
                getParent(checkbox)[0].checked = true;
                getParent(checkbox)[0].indeterminate = false;
            } else if ( areAllCousinschecked(checkbox) == 'indeterminate' ){
                getParent(checkbox)[0].checked = false;
                getParent(checkbox)[0].indeterminate = true;
            } else {
                getParent(checkbox)[0].checked = false;
                getParent(checkbox)[0].indeterminate = false;
            }
            // getParent(checkbox).trigger('change');
            return this;
        }
        function hasChild(checkbox){
            var levelNumber = getCurrentLevel(checkbox).data('level'),
            hasChild = $('.sublevel[data-level="'+levelNumber+'"]').length ? hasChild = true : hasChild = false;
            return hasChild;
        }
        function getChildren(checkbox){
            var levelNumber = getCurrentLevel(checkbox).data('level'),
            checkboxType = getCheckboxType(checkbox),
            children = $('.sublevel[data-level="'+levelNumber+'"] input[type="checkbox"][data-type="'+checkboxType+'"]');
            return children;
        }
        function toggleChildren(checkbox){
            var checkboxValue = checkbox[0].checked;
            getChildren(checkbox).each(function(key, item){
                item.checked = checkboxValue;
                $(item).trigger('change');
            });
            return this;
        }


        $(project).find('input[type="checkbox"]:not([id$="activation"])').on('change',function(ev){ // For All checkboxes except activation checkboxes
            //

            if ( isMaster( $(this) ) ){ // this master button

                toggleAllSiblings( $(this) );

            } else { // not master button

                if ( hasChild( $(this) ) ){
                    toggleChildren( $(this) );
                } else {
                }

                setMaster( $(this) );
            }

            if ( hasParent( $(this) ) ){
                setParent( $(this) );
            }


        });

    });

    function toggleActivation(checkbox){
        if ( $(checkbox)[0].checked ){
            // Display first level
            $(checkbox).closest('.panel-nested').find('.project-global-permissions').addClass('show-permissions');
            // Enable checkboxes
            $(checkbox).closest('.panel-nested').find('input[type="checkbox"]:not([id$="activation"])').removeAttr('disabled');
        } else {
            // Hide first level
            $(checkbox).closest('.panel-nested').find('.project-global-permissions').removeClass('show-permissions');
            // Enable checkboxes
            $(checkbox).closest('.panel-nested').find('input[type="checkbox"]:not([id$="activation"])').attr('disabled',true);
        }
    }

    $('document').ready(function(){
        $('input[type="checkbox"]:checked:not([id$="activation"]').trigger('change');

        $('input[type="checkbox"][id$="activation"]').on('change',function(ev){
            toggleActivation( this );
        });
    });


});
