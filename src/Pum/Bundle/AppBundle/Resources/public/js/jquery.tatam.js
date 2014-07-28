'use strict';

(function($) {
    var ttLog = function(label, value, css)
    {
        if (typeof css == 'undefined') {
            css = '';
        }

        console.log('%c%s', "font-weight:bold;" + css, label, value);
    };

    var ttEncode = function(value)
    {
        value = value.replace(/(['"])/g, "");

        return value;
    }

    var initTatam = function(ttInitTags, self, debug)
    {
        var taType           = self.data('tatam-ta-type') || 'remote',
            taName           = self.data('tatam-ta-name'),
            taUrl            = self.data('tatam-ta-url'),
            taLimit          = self.data('tatam-ta-limit') || 5,
            taMinlength      = self.data('tatam-ta-minlength') || 1,
            tmMaxTags        = self.data('tatam-tm-maxtags') || 0,
            taHighlight      = self.data('tatam-ta-highlight') || true,
            tmInputName      = self.data('tatam-tm-inputname'),
            tmInputClass     = self.data('tatam-tm-inputclass') || 'tatam-tag-input',
            tmInputContainer = $(self.data('tatam-tm-inputcontainer') || self.parent());

        // TagManager
            var TmOptions = {
                onlyTagList: true,
                tagList: [],
                maxTags: tmMaxTags
            };

            var TmObject = self.tagsManager(TmOptions);
            var TmFinalOptions = self.data('opts');

            self.on('tm:pushed', function(ev, tagObject){
                (debug) ? ttLog('pushed', [tagObject], "color:green") : '';
            });
            self.on('tm:spliced', function(ev, tagObject){
                (debug) ? ttLog('removed', [tagObject], "color: red;") : '';
                $('.tatam-inputed').filter(function(){ return this.dataset['tatamItemId'] == ttEncode(tagObject) }).remove();
            });
            self.on('tm:popped', function(ev, tagObject){
                (debug) ? ttLog('popped', [tagObject], "color: red;") : '';
                $('.tatam-inputed').filter(function(){ return this.dataset['tatamItemId'] == ttEncode(tagObject) }).remove();
            });

            // Bloodhound
            var BhOptions = {
                name: taName,
                limit: taLimit,
                datumTokenizer: function(d) {
                    return Bloodhound.tokenizers.whitespace(d.val);
                }
            };

            if ('remote' == taType) {
                BhOptions.remote = taUrl;
                BhOptions.queryTokenizer = Bloodhound.tokenizers.whitespace;
            } else {
                BhOptions.prefetch = taUrl;
            }

            var BhEngine = new Bloodhound(BhOptions);
            BhEngine.initialize();

            // Typeahead
            var TaOptions = {
                minLength: taMinlength,
                highlight: taHighlight
            };

            var TaDataset = {
                source: BhEngine.ttAdapter()
            };

            var TaObject = self.typeahead(TaOptions, TaDataset);
            self.on('typeahead:selected', function (e, d) {
                var newOpts = TmFinalOptions;
                newOpts.tagList = [d.value];
                self.data('opts', newOpts);
                TmObject.tagsManager("pushTag", d.value);
                self.data('opts', TmFinalOptions);
                tmInputContainer.append('<input type="hidden" data-tatam-item-id="' + ttEncode(d.value) + '" name="' + tmInputName + '" class="tatam-inputed" value="' + d.id + '" />');
            });

            // Init tags
            if (null !== ttInitTags) {
                $.each(ttInitTags, function(i,tag){
                    var newOpts = TmFinalOptions;
                    newOpts.tagList = [tag.value];
                    self.data('opts', newOpts);
                    TmObject.tagsManager("pushTag", tag.value);
                    self.data('opts', TmFinalOptions);
                    tmInputContainer.append('<input type="hidden" data-tatam-item-id="' + ttEncode(tag.value) + '" name="' + tmInputName + '" class="tatam-inputed" value="' + tag.id + '" />');
                });
            }

            // Misc
            self.parent('.twitter-typeahead').addClass('form-control').on('click', function(){
                self.focus();
            });
            self.on('blur', function(){ this.value = ''; });


            // [DEBUG]
            if (debug) {
                console.group('tatam.params');
                    ttLog('taType', taType);
                    ttLog('taName', taName);
                    ttLog('taUrl', taUrl);
                    ttLog('tmInputName', tmInputName);
                console.groupEnd();

                console.group('tatam.Tagsmanager');
                    ttLog('TmOptions', TmOptions);
                    ttLog('TmObject', TmObject);
                console.groupEnd();

                console.group('tatam.Bloodhound');
                    ttLog('BhOptions', BhOptions);
                    ttLog('BhEngine', BhEngine);
                console.groupEnd();

                console.group('tatam.Typeahead');
                    ttLog('TaOptions', TaOptions);
                    ttLog('TaDataset', TaDataset);
                    ttLog('TaObject', TaObject);
                console.groupEnd();
           }
    }

    $.fn.tatam = function(debug)
    {
        if (typeof debug == 'undefined') {
            debug = false;
        }
        $.each(this, function(i, item) {
            var self = $(item),
                ttInitTags = null,
                ttIds = self.data('tatam-ids') || null,
                ttInitUrl = self.data('tatam-init-url') || null;

            // Tatam initial request
            if (null !== ttIds && null !== ttInitUrl) {
                $.ajax({
                    url: ttInitUrl,
                    dataType: 'json',
                    cache: false
                }).success(function(data){
                    initTatam(data, self, debug);
                });
            } else {
                initTatam(null, self, debug);
            }


        });
    };
}(jQuery));