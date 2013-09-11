
+function ($) { "use strict";

    
    /* CKEDITOR */
    // test basic options
    $('textarea.ckeditor').each(function( i ) {
        CKEDITOR.replace( $(this).attr('id'), {
            toolbar: [
                ['Source', '-', 'Bold', 'Italic', 'Link']
            ],
            uiColor: $(this).attr('data-uicolor')
        });
    });


}(window.jQuery);