/**
 * Handle Pum form collections.
 */
pum_decorateCollection = (function() {
    // CSS Animation
    var commonAnimatedClass = 'animated';
    var inAnimatedClass = 'fadeInUp ' + commonAnimatedClass;
    var outAnimatedClass = 'fadeOutRight ' + commonAnimatedClass;

    // Action buttons
    var createLink  = '<hr/><a href="#" class="btn pum-scheme-btn-darkgrass btn-mini btn-action"><i class="pumicon pumicon-plus-3"></i> Add</a>';
    var deleteLink  = '<a href="#" class="pull-right panel-control pum-scheme-link-pomegranate"><i class="pumicon pumicon-trashcan"></i></a>';

    // Wrapping backup
    var elementWrap = '<div class="collection-wrap collection-element clearfix panel pum-scheme-panel ' + commonAnimatedClass + '"></div>';
    var subElementWrap = '<div class="panel-body"></div>';
    var elementSelector = '.panel-title'; // be careful about nested collections!
    var altElementSelector = '.panel-body'; // be careful about nested collections!

    /**
     * Add delete link on a given element.
     */
     function addDeleteLink($element, before)
     {
        var $link = $(deleteLink);
        if (typeof before !== 'undefined' && before === true) {
            $element.prepend($link);
        } else {
            $element.append($link);
        }
        $link.on('click', function(e) {
            e.preventDefault();
            if ($element.hasClass(commonAnimatedClass)) {
                $element.removeClass(inAnimatedClass).addClass(outAnimatedClass);
            } else {
                $element.parents('.' + commonAnimatedClass).removeClass(inAnimatedClass).addClass(outAnimatedClass);
            }
            var wait = window.setTimeout( function(){
                    if ($element.hasClass('collection-wrap')) {
                        $element.remove();
                    } else {
                        $element.parents('.collection-element').remove();
                    }
                },
                500
            );
        });
     }

     /**
      * Add create link on a collection set.
      */
     function addCreateLink($collection)
     {
        var $link = $(createLink);

        $collection.append($link);

        $link.on('click', function(e) {
            e.preventDefault();

            var prototype = $collection.attr('data-prototype');
            var childHtml = prototype.replace(/__name__/g, String(Math.random()).substr(3));
            var $element = $(childHtml);
            $element.addClass('collection-element-new');
            if ($element.hasClass('panel')) {
                var sel = $element.find(elementSelector);
                var before = false;
                if (sel.length === 0) {
                    sel = $element.find(altElementSelector);
                    before = true;
                }
                addDeleteLink(sel, before);
            } else {
                $element.addClass('collection-element');
                $element = $(subElementWrap).append($element);
                $element = $(elementWrap).append($element);
                addDeleteLink($element, true);
            }
            $element.addClass(inAnimatedClass);
            $collection.find('> hr').before($element);
            $collection.find('.panel-collapse.in').collapse('hide');
            $collection.find('.accordion-toggle:not(.collapsed)').addClass('collapsed');
        });
     }

    /**
     * Decorates a given collection set.
     */
    return function decorateCollection(container) {
        if (typeof container === 'undefined' || container === null) {
            container = document.body;
        }

        var $collection;

        $(container).find('.collection-set').each(function (i, e) {
            $collection = $(e);

            // add delete link on existing elements
            if ($collection.attr('data-delete')) {
                $collection.children().each(function(i, e) {
                    var $element = $(e);
                    if ($element.hasClass('panel')) {
                        var sel = $element.find(elementSelector);
                        var before = false;
                        if (sel.length === 0) {
                            sel = $element.find(altElementSelector);
                            before = true;
                        }
                        addDeleteLink(sel, before);
                    } else {
                        $element.addClass('collection-element');
                        $element.wrap(elementWrap).wrap(subElementWrap);
                        addDeleteLink($element, true);
                    }
                });
            }

            // add create link on collection set
            if ($collection.attr('data-prototype')) {
                addCreateLink($collection);
            }
        });
    };
})();

$(function () {
    pum_decorateCollection(document.body);
});
