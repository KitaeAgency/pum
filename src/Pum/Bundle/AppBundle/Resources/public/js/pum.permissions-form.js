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

        // Which Project ?
        // console.log( "============" );
        // console.log( "Project Id: ", projectId );
        // console.log( project );

        // Is Activated ?
        // console.log( "Is The project activated ?" );

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
            // console.log('siblings ',getCurrentLevel(checkbox).find('input[type="checkbox"]:not([id$="activation"], [data-type="master"], [data-type="'+type+'"])'));
            return getCurrentLevel(checkbox).find('input[type="checkbox"]:not([id$="activation"], [data-type="master"], [data-type="'+type+'"])');
        }
        function getAllSiblings(checkbox){
            var type = getCheckboxType(checkbox);
            // console.log('siblings ',getCurrentLevel(checkbox).find('input[type="checkbox"]:not([id$="activation"], [data-type="master"])'));
            return getCurrentLevel(checkbox).find('input[type="checkbox"]:not([id$="activation"], [data-type="master"])');
        }
        function toggleSiblings(checkbox){
            getSiblings(checkbox).each(function(key, item){
                item.checked = checkbox[0].checked;
                // console.log(item);
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
            // console.log( siblingsNumber + '  ' +checkedSiblings);

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
                // console.log('all siblings checked');
                getMaster(checkbox)[0].checked = true;
                getMaster(checkbox)[0].indeterminate = false;
            } else if ( areAllSiblingschecked(checkbox) == 'indeterminate' ){
                // console.log('some siblings checked');
                getMaster(checkbox)[0].checked = false;
                getMaster(checkbox)[0].indeterminate = true;
            } else {
                // console.log('no siblings checked');
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

            // console.log( cousins );
            return cousins;
        }
        function areAllCousinschecked(checkbox){
            var areChecked  = false,
            cousins         = getCousins(checkbox),
            cousinsNumber   = cousins.length,
            checkedCousins  = 0,
            allChecked      = false;

            // console.log('cousins', cousins);

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
                // console.log('all siblings checked');
                getParent(checkbox)[0].checked = true;
                getParent(checkbox)[0].indeterminate = false;
            } else if ( areAllCousinschecked(checkbox) == 'indeterminate' ){
                // console.log('some siblings checked');
                getParent(checkbox)[0].checked = false;
                getParent(checkbox)[0].indeterminate = true;
            } else {
                // console.log('no siblings checked');
                getParent(checkbox)[0].checked = false;
                getParent(checkbox)[0].indeterminate = false;
            }
            // console.log( getParent(checkbox)[0] );
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

        // console.log( $(project).find('input[type="checkbox"]:not([id$="activation"])') );

        $(project).find('input[type="checkbox"]:not([id$="activation"])').on('change', function(ev){ // For All checkboxes except activation checkboxes
            // console.log( 'change' );
            // console.log( 'this: ', $(this) );
            // console.log( 'is_checked: ', $(this)[0].checked );
            // console.log( 'is_indeterminate: ', $(this)[0].indeterminate );
            //
                console.log( $(this) );

            if ( isMaster( $(this) ) ){ // this master button
                // console.log('========');
                // console.log('isMaster');
                // console.log('========');

                toggleAllSiblings( $(this) );

            } else { // not master button
                // console.log('========');
                // console.log('isSlave');
                // console.log('========');

                if ( hasChild( $(this) ) ){
                    // console.log('has child(ren)');
                    // console.log('==============');
                    toggleChildren( $(this) );
                } else {
                    // console.log('has no child');
                }

                // console.log('are All siblings checked ?' , areAllSiblingschecked( $(this) ));
                setMaster( $(this) );
            }

            if ( hasParent( $(this) ) ){
                // console.log('hasParent');
                setParent( $(this) );
            }

            // console.log( 'this: ', $(this) );
            // console.log( 'is master? ', isMaster( $(this) ) );
            // console.log( 'get current Level: ', getCurrentLevel( $(this) ) );
            // console.log( 'get siblings ', getSiblings( $(this) ) );
            // console.log( 'get master ', getMaster( $(this) ) );
            // console.log( 'has parent ', hasParent( $(this) ) );
            // console.log( 'get parent ', getParent( $(this) ) );
            // console.log( 'has child ', hasChild( $(this) ) );
            // console.log( 'get children ', getChildren( $(this) ) );
            // console.log( 'check children ', checkChildren( $(this) ) );
            // console.log( 'check siblings ', checkSiblings( $(this) ) );
            // console.log( 'are siblings checked', areAllSiblingschecked( $(this) ) );
            // console.log( 'set Master ', setMaster( $(this) ) );
            // console.log( 'get Cousins ', getCousins( $(this) ) );

        });

    });

    $('document').ready(function(){
        // console.log(  $('input[type="checkbox"]:checked:not([id$="activation"]') );
            $('input[type="checkbox"]:checked:not([id$="activation"]').trigger('change');
    });


});
