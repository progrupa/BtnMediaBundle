jQuery(function ($) {

    if (typeof window.btnMedia === 'object') {
        return false;
    }

    if (typeof $.fn.modal === 'undefined') {

        return;
    }

    var modal,
        modalWrapper,
        selectMediaBtnText     = 'Select media',
        selectMediaBtnTemplate = '<div class="btn btn-primary">' + selectMediaBtnText + '</div>',
        deleteMediaBtnTemplate = '<div class="btn btn-danger" style="margin:0 0 0 5px;">Delete</div>',
        paginationUrl          = '',
        modalUrl               = $('[data-btn-media]:first').attr('data-btn-media'),
        mediaInputs            = $('[data-btn-media]'),
        mediaInput;

    if (typeof modalUrl === 'undefined') {
        console.error('BtnMediaBundle: No modal url specified');

        return;
    }

    var getPaginationSearchPart = function (el) {
        var page = $(el).find('a').attr('href').split('?')[1];

        return page;
    };
    // update modal-body-content html by $.get response
    var updateModalBody = function (url) {
        modal.find('.modal-body-content').fadeOut(function () {
            $.get(url, function (response) {
                modal.find('.modal-body-content').html(response).fadeIn(function () {
                    bindModalBehaviors(true);
                });
            });
        });
    };

    var bindPagination = function () {
        modal.find('.modal-body .pagination').on('click', 'li', function (e) {
            if (!$(this).hasClass('disabled') && !$(this).hasClass('active')) {
                updateModalBody(paginationUrl + '?' + getPaginationSearchPart(this));
            }

            return false;
        });
    };

    var bindCategoryFilter = function () {
        modal.on('click', '#tree ul li a', function(event) {
            event.stopPropagation();
            var category = $(this).attr('data-btn-media-category');
            updateModalBody(category ? (paginationUrl + '/' + category) : paginationUrl);

            return false;
        });
    };

    var bindModalNavigation = function () {
        bindPagination();
        bindCategoryFilter();

        $('.close').bind('click', function(e) {
            window.top.close();
            window.top.opener.focus();
        });
    };

    var bindModalBehaviors = function (update) {
        update = typeof update === 'undefined' ? false : update;
        //append additional modal styles
        modal.find('style, link').appendTo('head');

        paginationUrl = modal.find('#btn-media-list').attr('data-pagination-url');

        //don't re-bind below behaviors only on modal-body-content update
        if (!update) {

            modal.modal({
                show : false,
                keyboard : true,
                backdrop : !modalWrapper.hasClass('expanded')
            });

            modal.on('click', '#btn-media-list .item img', function (e) {
                $('#btn-media-list .item img').removeClass('selected');
                $(this).addClass('selected');
            });

            modal.find('.submit').on('click', function () {
                var images = $('#btn-media-list .item img.selected');
                if (images.length) {
                    if (!isCke) {
                        updateMediaInput(mediaInput, images);
                    } else {
                        OpenFile(images.attr('data-original'));
                    }
                    modal.modal('hide');
                }
            });

            $(document).on('hidden', '.modal', function () {
                $(this).parent().remove();
            });

            bindModalNavigation();
        };
    };

    function GetUrlParam (paramName) {
        var oRegex = new RegExp('[\?&]' + paramName + '=([^&]+)', 'i');
        var oMatch = oRegex.exec(window.top.location.search);

        if (oMatch && oMatch.length > 1) {
            return decodeURIComponent(oMatch[1]);
        }

        return '';
    }

    function OpenFile (fileUrl) {
        //PATCH: Using CKEditors API we set the file in preview window.

        funcNum = GetUrlParam('CKEditorFuncNum');
        //fixed the issue: images are not displayed in preview window when filename contain spaces due encodeURI encoding already encoded fileUrl
        window.top.opener.CKEDITOR.tools.callFunction(funcNum, fileUrl);

        ///////////////////////////////////
        window.top.close();
        window.top.opener.focus();
    }

    var getModal = function () {
        var xhr = $.get(modalUrl, function (response) {
            modalWrapper = $(response);
            modalWrapper.appendTo('body');

            modal = modalWrapper.find('.modal').show();
        });

        return xhr;
    };

    var onModalReady = function () {
        bindModalBehaviors();

        modal.modal('show');

        $('html, body').animate({
            scrollTop : modal.offset().top
        }, 400);
    };

    var openModal = function () {
        if (!modal) {
            getModal().done(function() {
                onModalReady();
            });
        } else {
            onModalReady();
        }
    };

    var isCke       = false;
    var searchParts = window.location.search.replace('?', '').split('&');

    for (var i in searchParts) {
        if (searchParts[i].split('=')[0] === 'CKEditor') {
            isCke = true;
            break;
        }
    }

    if (isCke) {
        modalWrapper = $('div').first();
        modal = modalWrapper.find('.modal').show();

        openModal();
    }

    var bindMediaModal = function (el, callback) {

        el.click(openModal);

        mediaInput = el;
        mediaInput.callback = callback;
    };

    window.btnMedia = {
        bind : bindMediaModal
    }

    var updateMediaInput = function (input, image) {
        if (image == null) {
            input.val(null);
        } else if (input.is('select')) {
            input.val(image.data('id'));
        } else if (input.is('input')) {
            input.val(image.data('filename'));
        }

        if (image) {
            updateMediaButtons(input, image.data('filename'));
        } else {
            updateMediaButtons(input);
        }
    }

    var updateMediaButtons = function (input, filename) {
        var selectBtn = input.data('select-button'),
            deleteBtn = input.data('delete-button');

        if (filename == null) {
            if (input.is('select')) {
                filename = input.find('option:selected').text();
            } else {
                filename = input.val();
            }
        }

        if (filename) {
            selectBtn.text(filename);
            deleteBtn.show();
        } else {
            selectBtn.text(selectMediaBtnText);
            deleteBtn.hide();
        }
    }

    mediaInputs.each(function () {
        var self = $(this).hide();

        var selectBtn = $(selectMediaBtnTemplate).insertAfter(self);
        var deleteBtn = $(deleteMediaBtnTemplate).hide().insertAfter(selectBtn);

        self.data('select-button', selectBtn);
        self.data('delete-button', deleteBtn);

        selectBtn.on('click', function (e) {
            mediaInput = self;
            openModal();

            return false;
        });

        deleteBtn.on('click', function (e) {
            updateMediaInput(self);

            return false;
        });

        updateMediaButtons(self);
    });
});
