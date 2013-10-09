$(function () {
    $(document).on('change', 'select.field-type', function (event) {
        var $select = $(event.currentTarget);
        var url = $select.attr('data-url');

        $.ajax({
            url: url,
            data: { type: $select.val() },
            success: function (content) {
                var $current = $select;
                var $optionsWrapper = $current.find('.type-options');
                while ($optionsWrapper.length == 0) {
                    $current = $($current.parent());
                    $optionsWrapper = $current.find('.type-options');
                }

                content = content.replace(/__field_type_name__/g, $select.attr('data-name'));

                if (content.length == 0) {
                    $current.find('*[data-autohide]').addClass('vhidden');
                    $current.find('.in').collapse('hide');
                    $current.find('.accordion-toggle').addClass('collapsed');
                }
                else {
                    $current.find('*[data-autohide]').removeClass('vhidden');
                    $current.find('.collapse').collapse('show');
                    $current.find('.accordion-toggle').removeClass('collapsed');
                }

                var node = $optionsWrapper.html(content);
                pum_decorateCollection(node);
            },
            error: function () {
                console.log('Error while fetching type options');
            }
        })
    })
});
