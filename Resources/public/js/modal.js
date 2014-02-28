jQuery(function ($) {

    if (typeof window.btnMedia === 'object') {
        return false;
    }

    var modal,
        modalWrapper,
        selectMediaBtnText = 'Select media',
        selectMediaBtnTemplate = '<div class="btn btn-primary">' + selectMediaBtnText + '</div>',
        deleteMediaBtnTemplate = '<div class="btn btn-danger">Delete</div>',
        paginationUrl = '',
        modalUrl = $('script[data-remote-url]').attr('data-remote-url'),
        mediaInputs = $('.btn-media'),
        mediaInput;

    if (typeof modalUrl === 'undefined') {
        console.log('No modal url specified');
        return;
    }

    var getPaginationSearchPart = function (el) {
        var page = $(el).find('a').attr('href').split('?')[1];

        return page;
    };

    var updateModalBody = function (url) {
        $.get(url, function (response) {
            modal.find('.modal-body').fadeOut(function () {
                $(this).html(response).fadeIn(function () {
                    bindModalBehaviors();
                });
            });
        });
    };

    var bindPagination = function () {
        modal.find('.modal-body .pagination').on('click', 'li', function (e) {
            if (!$(this).hasClass('disabled') && !$(this).hasClass('active')) {
                var urlSearchPart = getPaginationSearchPart(this);
                var url = paginationUrl + '?' + urlSearchPart;

                updateModalBody(url);
            }

            return false;
        });
    };

    var bindCategoryFilter = function () {
        modal.find('.category-filter').change(function () {
            var val = $(this).val();
            if (val) {
                updateModalBody(paginationUrl + '?category=' + val);
            } else {
                updateModalBody(paginationUrl);
            }
        });
    };

    var bindModalNavigation = function () {
        bindPagination();
        bindCategoryFilter();
    };

    var bindModalBehaviors = function () {
        modal.find('.modal-body style, .modal-body link').appendTo('head');

        paginationUrl = modal.find('#bitnoise-media-list').attr('data-pagination-url');

        modal.modal({
            show : false,
            keyboard : true,
            backdrop : !modalWrapper.hasClass('expanded')
        });

        modal.find('#bitnoise-media-list .item img').on('click', function (e) {
            $('#bitnoise-media-list .item img').removeClass('selected');
            $(this).addClass('selected');
        });

        modal.find('.submit').on('click', function () {
            var images = $('#bitnoise-media-list .item img.selected');
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
        $.get(modalUrl, function (response) {
            modalWrapper = $(response);

            modal = modalWrapper.find('.modal').show();
        });
    };

    var openModal = function () {
        bindModalBehaviors();

        modalWrapper.appendTo('body');
        modal.modal('show');

        $('html, body').animate({
            scrollTop : modal.offset().top
        }, 400);
    };

    var bindMediaModal = function (el, callback) {
        getModal();

        el.click(openModal);

        mediaInput = el;
        mediaInput.callback = callback;
    };

    if (mediaInputs.length) {
        getModal();
    }

    var isCke = false;
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
