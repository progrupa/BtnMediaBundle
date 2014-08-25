jQuery(function ($) {
    //get base url for $.get content
    var baseUrl  = $('#btn-media-list').attr('data-pagination-url'),
        btnMedia = $('.btn-media-modal');

    var getPaginationSearchPart = function (el) {

        return $(el).find('a').attr('href').split('?')[1];
    };
    //prepare url param for CKEDITOR
    var getUrlParam = function (paramName) {
        var oRegex = new RegExp('[\?&]' + paramName + '=([^&]+)', 'i'),
            oMatch = oRegex.exec(window.top.location.search);

        return oMatch && oMatch.length > 1 ? decodeURIComponent(oMatch[1]) : '';
    };
    // update media-content html by $.get response
    var updateMediaContent = function (url) {
        btnMedia.find('.media-content').slideUp(function () {
            $.get(url, function (response) {
                btnMedia.find('.media-content').html(response).slideDown();
            });
        });
    };
    //bind events - reload content on pagination/category click
    btnMedia
        .on('click', '#btn-media-tree ul li a', function(event) {
            var category = $(this).attr('btn-media-category');
            updateMediaContent(category ? (baseUrl + '/' + category) : baseUrl);

            return false;
        })
        .on('click', '.modal-body .pagination li', function (e) {
            if (!$(this).hasClass('disabled') && !$(this).hasClass('active')) {
                updateMediaContent(baseUrl + '?' + getPaginationSearchPart(this));
            }

            return false;
        })
        //mark selected image
        .on('click', '#btn-media-list .item img', function () {
            $('#btn-media-list .item img').removeClass('selected');
            $(this).addClass('selected');
        })
        //submit choosen button to CKEDITOR
        .on('click', '[btn-media-submit]', function () {
            var images = $('#btn-media-list .item img.selected');
            if (images.length) {
                //PATCH: Using CKEditors API we set the file in preview window.
                funcNum = getUrlParam('CKEditorFuncNum');
                //images are not displayed in preview window when filename contain spaces due encodeURI encoding already encoded fileUrl
                window.top.opener.CKEDITOR.tools.callFunction(funcNum, images.attr('data-original'));
                window.top.close();
                window.top.opener.focus();
            }
        });
});
