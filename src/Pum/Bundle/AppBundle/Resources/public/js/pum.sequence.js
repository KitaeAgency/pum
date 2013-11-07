/**
 * Handle Pum sequences.
 */
pum_handleSequence = (function() {
    var sequences_selector = 'input[data-sequence]',
        inject_selector = '.collection-element > :first-child',
        row_selector = '.form-group',
        controls_selector = '.sequence-group > a',
        collection_element_selector = '.collection-element',
        sequence_control = '<div class="sequence-group"><a data-direction="up" class="pum-scheme-text_colored"><i class="pumicon pumicon-arrow-up5"></i></a><a data-direction="down" class="pum-scheme-text_colored"><i class="pumicon pumicon-arrow-down6"></i></a></div>';


    return function initSequences(container)
    {
        if (typeof container === 'undefined' || container === null) {
            container = document.body;
        }
        container = $(container);


        var $collection = container.find(sequences_selector);
        $collection.each(function(i, e){
            e = $(e);
            type = e.data('sequence');

            if (type == 'single') {
                e.parent().addClass('hidden');
            } else {
                e.parents(row_selector).addClass('hidden');
            }
            e.parents(collection_element_selector).addClass('is_orderable');
            e.parents(inject_selector).prepend(sequence_control);
        });

        container.on('click', controls_selector, function(ev){
            var direction = $(this).data('direction'),
                collection_item = $(this).parents(collection_element_selector),
                from_position_value,
                to_position_value;

            if (direction === 'up') {
                to = collection_item.prev();
                if (to.length === 0) {
                    to = collection_item.siblings(collection_element_selector).last();
                    direction = 'last';
                }
            } else {
                to = collection_item.next();
                if (to.length === 0) {
                    to = collection_item.siblings(collection_element_selector).first();
                    direction = 'first';
                }
            }
            from_sequence_field = collection_item.find(sequences_selector);
            to_sequence_field = to.find(sequences_selector);

            from_sequence_value = from_sequence_field.val();
            to_sequence_value = to_sequence_field.val();

            from_sequence_field.val(to_sequence_value);
            to_sequence_field.val(from_sequence_value);

            collection_item.swap({
                target: to,
                speed: 400,
                callback: function() {
                    var clone_collection_item = collection_item.clone();
                    collection_item.replaceWith(to.clone().attr('style', ''));
                    to.replaceWith(clone_collection_item.attr('style', ''));
                }
            });
        });
    };
})();

$(function () {
    pum_handleSequence(document.body);
});
