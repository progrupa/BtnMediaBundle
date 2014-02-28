jQuery(function ($) {
    var mediaList = $('#bitnoise-media-list');

    var newCategory = $('.btn-media-add-new-category');
    var newCategoryInput = newCategory.find('.input');

    var removeUrl = mediaList.attr('data-remove-url');
    var editUrl = mediaList.attr('data-edit-url');

    var setBehaviors = function () {
        mediaList.on('click', '.delete', onDeleteButtonClick);
        mediaList.on('click', '.submit', onSubmitButtonClick);

        newCategory.on('click', '.add', onAddNewCategoryClick);
        newCategory.on('click', '.new', onAddNewCategoryBtnClick);

        $('select.set-category').on('change', onAddNewCategorySelectChange);
    };

    var getItemId = function (el) {
        var item = $(el).closest('.item');
        var itemId = item.attr('data-id');

        return itemId;
    }

    var onAddNewCategoryBtnClick = function () {
        var addCategoryEl = $('.btn-media-add-category');
        var val = $(this).parent().find('input').val();
        var url = addCategoryEl.attr('data-url');

        $.getJSON(url + '?name=' + val, function (response) {
            if (response.result) {
                window.location = window.location;
            }
        });

        return false;
    }

    var onSubmitButtonClick = function (event) {
        var itemId = getItemId(this);
        var form = $(this).parent().siblings();
        var type = form.attr('data-type');
        var value = form.val();
        var that = this;

        updateItem(itemId, type, value, function (data, textStatus, xhr) {
            if (data.result === true) {
                $(that).fadeOut().fadeIn();
            }
            else {
                console.log(data.result);
            }
        });

        return false;
    };

    var addNewOption = function (value) {
        return $('<option selected="selected">' + value + '</option>');
    };

    var updateItem = function (itemId, type, value, callback) {
        $.ajax({
            url : editUrl,
            type : 'POST',
            dataType : 'json',
            data : {
                id : itemId,
                type : type,
                value : value
            },
            success : callback
        });
    }

    var onAddNewCategoryClick = function () {
        $(this).siblings('div').removeClass('hidden');
        return false;
    };

    var onAddNewCategorySelectChange = function () {
        var itemId = getItemId(this);
        var type = 'category';
        var value = $(this).val();

        updateItem(itemId, type, value, function (data) {
            window.location = window.location;
        });
    }

    var onDeleteButtonClick = function (event) {
        var item = $(this).parent('.item');
        var itemId = item.attr('data-id');

        $.ajax({
            url : removeUrl,
            type : 'GET',
            dataType : 'json',
            data : {id : itemId},
            success : function (data, textStatus, xhr) {
                if (data.result === true) {
                    $(item).fadeOut().remove();
                } else {
                    console.log(data.result);
                }
            }
        });

        return false;
    };

    setBehaviors();
    $('.bitnoise-media-uploader').mediaUploader();
});
