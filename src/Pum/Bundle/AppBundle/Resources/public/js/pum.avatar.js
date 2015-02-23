String.prototype.getInitials = function(glue){
    if (typeof glue == "undefined") {
        var glue = true;
    }

    var initials = this.match(/\b\w/g);

    if (glue) {
        return initials.join('');
    }

    return  initials;
};

(function ($) {
    $.fn.initialAvatar = function (options) {

        // Defining Colors
        var colors = ["#ECF0F1"];

        return this.each(function () {

            var e = $(this);
            var settings = $.extend({
                // Default settings
                initialsName: 'Name',
                charCount: 1,
                textColor: '#7F8C8D',
                height: 64,
                width: 64,
                fontSize: 40,
                fontWeight: 300,
                fontFamily: '"Lato","Helvetica Neue",Helvetica,sans-serif'
            }, options);

            // overriding from data attributes
            settings = $.extend(settings, e.data());

            // making the text object
            if (typeof settings.initialsLetters != 'undefined') {
                var c = settings.initialsLetters.toUpperCase();
            } else {
                var c = settings.initialsName.getInitials().toUpperCase();
            }

            var cobj = $('<text text-anchor="middle"></text>').attr({
                'y': '50%',
                'x': '50%',
                'dy' : '0.35em',
                'pointer-events':'auto',
                'fill': settings.textColor,
                'font-family': settings.fontFamily
            }).html(c).css({
                'font-weight': settings.fontWeight,
                'font-size': settings.fontSize/(c.length*0.75)+'px',
            });

            var colorIndex = Math.floor((c.charCodeAt(0) - 65) % colors.length);

            var svg = $('<svg></svg>').attr({
                'xmlns': 'http://www.w3.org/2000/svg',
                'pointer-events':'none',
                'width': settings.width,
                'height': settings.height
            }).css({
                'background-color': colors[colorIndex],
                'width': settings.width+'px',
                'height': settings.height+'px'
            });

            svg.append(cobj);
           // svg.append(group);
            var svgHtml = window.btoa(unescape(encodeURIComponent($('<div>').append(svg.clone()).html())));

            e.attr("src", 'data:image/svg+xml;base64,' + svgHtml);

        })
    };
}(jQuery));