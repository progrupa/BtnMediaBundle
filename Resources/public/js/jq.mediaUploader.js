;
(function ($, window, document, undefined) {

    // Create the defaults once
    var pluginName = 'mediaUploader',
        defaults = {
            //nothing here
        };

    // The actual plugin constructor
    function Plugin (element, options) {
        this.element = element;

        this.options = $.extend({}, defaults, options);

        this._defaults = defaults;
        this._name = pluginName;

        this.init();              //initialize
    }

    // Plugin constructor
    Plugin.prototype.init = function () {
        //reset
        window.uploading = false;

        //Assign uploader
        this.attachUploader();
    };

    Plugin.prototype.attachUploader = function () {

        var self = this;

        //attach uploader
        $(self.element).fineUploader({
            debug : $(self.element).attr('data-debug') == 'true',
            multiple : $(self.element).attr('data-multiple') == 'true',
            text : {
                uploadButton : $(self.element).attr('data-button-upload-text') || 'Upload media'
            },
            validation : {
                sizeLimit : 0,
                allowedExtensions : $(self.element).attr('data-allowed-extensions').split(',')
            },
            request : {
                endpoint : $(self.element).attr('data-href'),
                params : {
                    categoryId : $(self.element).attr('data-category-id')
                }
            }
        }).on('allComplete', function (e, succeeded, failed) {
            window.location.reload();
        });
    };

    // A plugin wrapper around the constructor,
    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                    new Plugin(this, options));
            }
        });
    };

})(jQuery, window, document);
