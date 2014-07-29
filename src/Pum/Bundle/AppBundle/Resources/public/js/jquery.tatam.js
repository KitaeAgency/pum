/* ===================================================
 * tatam.js v1.0
 *
 * ===================================================
 * Copyright 2014 Kitae
 *
 * Licensed under the Mozilla Public License, Version 2.0 You may not use this work except in compliance with the License.
 *
 * http://www.mozilla.org/MPL/2.0/
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */
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

    var ttTagExists = function(value, container)
    {
        var value = ttEncode(value);
        var length = container.find('.tatam-inputed').filter(function(){ return this.dataset['tatamItemId'] == value }).length;

        if (length > 0) {
            return true
        }

        return false;
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

            if (debug) {
                self.on('tm:duplicated', function(ev, tagObject){
                    ttLog('duplicated', [tagObject, this, ev], "color:violet");
                });

                self.on('tm:pushing', function(ev, tagObject){
                    ttLog('trying to push', [tagObject, this, ev], "color:darkorange");
                });
            }

            self.on('tm:pushed', function(ev, tagObject){
                (debug) ? ttLog('pushed', [tagObject, this, ev], "color:green") : '';
            });
            self.on('tm:spliced', function(ev, tagObject){
                (debug) ? ttLog('removed', [tagObject, this, ev], "color: red;") : '';
                $('.tatam-inputed').filter(function(){ return this.dataset['tatamItemId'] == ttEncode($.trim(tagObject)) }).remove();
            });
            self.on('tm:popped', function(ev, tagObject){
                (debug) ? ttLog('popped', [tagObject, this, ev], "color: red;") : '';
                $('.tatam-inputed').filter(function(){ return this.dataset['tatamItemId'] == ttEncode($.trim(tagObject)) }).remove();
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
                var tagValue = $.trim(d.value);

                var newOpts = TmFinalOptions;
                newOpts.tagList = [tagValue];
                self.data('opts', newOpts);

                (debug) ? ttLog('pushstart', [tagValue, d], "color:darkblue") : '';
                TmObject.tagsManager("pushTag", tagValue);
                self.data('opts', TmFinalOptions);

                (debug) ? ttLog('exists?', [tagValue, ttTagExists(tagValue, tmInputContainer)], "font-weight:bold") : '';
                if (!ttTagExists(tagValue, tmInputContainer)) {
                    tmInputContainer.append('<input type="hidden" data-tatam-item-id="' + ttEncode(tagValue) + '" name="' + tmInputName + '" class="tatam-inputed" value="' + d.id + '" />');
                }
            });

            // Init tags
            if (null !== ttInitTags) {
                $.each(ttInitTags, function(e, d){
                    var tagValue = $.trim(d.value);

                    var newOpts = TmFinalOptions;
                    newOpts.tagList = [tagValue];
                    self.data('opts', newOpts);

                    (debug) ? ttLog('pushstart', [tagValue, d], "color:darkblue") : '';
                    TmObject.tagsManager("pushTag", tagValue);
                    self.data('opts', TmFinalOptions);

                    (debug) ? ttLog('exists?', [tagValue, ttTagExists(tagValue, tmInputContainer)], "font-weight:bold") : '';
                    if (!ttTagExists(tagValue, tmInputContainer)) {
                    tmInputContainer.append('<input type="hidden" data-tatam-item-id="' + ttEncode(tagValue) + '" name="' + tmInputName + '" class="tatam-inputed" value="' + d.id + '" />');
                    }
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