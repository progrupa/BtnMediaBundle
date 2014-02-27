(function ($) {
	if(typeof window.btnMedia === 'object') {
		return false;
	}

	$(document).ready(function(){
		var modalEl			= null;
		var modalElWrap		= null;
		var button 			= '<div class="btn btn-primary">Select media</div>';
		var deleteButton    = '<div class="btn btn-danger" style="margin:0 0 0 5px;">Delete</div>';
		var openedFrom 		= null;
		var paginationUrl 	= '';
		var modalUrl 		= $('script[data-remote-url]').attr('data-remote-url');
		var mediaSelects 	= $('input.btn-media');

		if(typeof modalUrl === 'undefined') {
			console.log('No modal url specified');
			return;
		}

		var updateButton = function(el, select) {
			$(el).text($(select).find('option:selected').text());
		};

		var resetButton = function(el) {
			$(el).text('Choose image');
		};

		var getPaginationSearchPart = function(el) {
			var page 		= $(el).find('a').attr('href').split('?')[1];

			return page;
		};

		var updateModalBody = function(url) {
			$.get(url, function(response){
				modalEl.find('.modal-body').fadeOut(function(){
					$(this).html(response).fadeIn(function(){
						bindModalBehaviors();
					});
				});
			});
		};

		var bindPagination = function() {
			modalEl.find('.modal-body .pagination').on('click', 'li', function(e) {
				if(!$(this).hasClass('disabled') && !$(this).hasClass('active')) {
					var urlSearchPart 	= getPaginationSearchPart(this);
					var url 			= paginationUrl + '?' + urlSearchPart;

					updateModalBody(url);
				}

				return false;
			});
		};

		var bindCategoryFilter = function() {
			modalEl.find('.category-filter').change(function(){
				var val = $(this).val();

				if(val != 0) {
					var url = paginationUrl + '?category=' + val;
					updateModalBody(url);
				}
				else {
					updateModalBody(paginationUrl);
				}
			});
		};

		var bindModalNavigation = function() {
			bindPagination();
			bindCategoryFilter();
		};

		var callback = function(id) {
			var select 	= $(openedFrom).prev('input.btn-media');

			select.val(id);
			updateButton(openedFrom, select);
		};

		var bindModalBehaviors = function() {
			modalEl.find('.modal-body style, .modal-body link').appendTo($('head'));

			paginationUrl = modalEl.find('#bitnoise-media-list').attr('data-pagination-url');

			modalEl.modal({
					show 	: false,
					keyboard: true,
					backdrop: (!modalElWrap.hasClass('expanded'))
				});

			modalEl.find('#bitnoise-media-list .item img').click(function(){
				$('#bitnoise-media-list .item img').removeClass('selected');
				$(this).addClass('selected');
			});

			modalEl.find('.submit').click(function(){
				var selected = $('#bitnoise-media-list .item img.selected');

				if(selected.length > 0) {
					var id = selected.parent().attr('data-id');

					if(!isCke) {
						if(typeof openedFrom.callback !== 'undefined') {
							openedFrom.callback(id);
						}
						else {
							callback(id);
						}
					}
					else {
						OpenFile($(selected).attr('data-original'));
					}
					openedFrom.delButton.show();
					modalEl.modal('hide');
				}
			});

			$(document).on('hidden', '.modal', function () {
			    $(this).parent().remove();
			});

			bindModalNavigation();
		};

		function GetUrlParam( paramName )
		{
			var oRegex = new RegExp( '[\?&]' + paramName + '=([^&]+)', 'i' ) ;
			var oMatch = oRegex.exec( window.top.location.search ) ;

			if (oMatch && oMatch.length > 1 )
				return decodeURIComponent( oMatch[1] ) ;
			else
				return '' ;
		}

		function OpenFile( fileUrl )
		{
			//PATCH: Using CKEditors API we set the file in preview window.

			funcNum = GetUrlParam('CKEditorFuncNum') ;
			//fixed the issue: images are not displayed in preview window when filename contain spaces due encodeURI encoding already encoded fileUrl
			window.top.opener.CKEDITOR.tools.callFunction( funcNum, fileUrl);

			///////////////////////////////////
			window.top.close() ;
			window.top.opener.focus();
		}


		var getModal = function() {
			$.get(modalUrl, function(response){
				modalElWrap = $(response);

				modalEl 	= modalElWrap.find('.modal');
				modalEl.show();
			});
		};


		var openModal = function() {
			bindModalBehaviors();

			modalElWrap.appendTo($('body'));
			modalEl.modal('show');

			$('html, body').animate({
		        scrollTop: modalEl.offset().top
		    }, 400);
		};

		var bindMediaModal = function(el, callback) {
			getModal();

			el.click(openModal);

			openedFrom 			= el;
			openedFrom.callback = callback;
		};

		var setDeleteButton = function() {
			var newEl = $(deleteButton);

			//hide if image wasn't choose
			if (mediaSelects.val() == '') {
				newEl.hide();
			}
			//bind reset hidden select and button text
			newEl.click(function(){
				mediaSelects.val(null);
				if (openedFrom == null) {
					resetButton(newEl.prev());
				} else {
					resetButton(openedFrom);
				}
				$(this).hide();
	        });

	        return newEl;
		};

		if(mediaSelects.length > 0) {
			getModal();
		}

		var isCke 		= false;
		var searchParts = window.location.search.replace('?', '').split('&');

		for(var i in searchParts) {
			if(searchParts[i].split('=')[0] === 'CKEditor') {
				isCke = true;
				break;
			}
		}

		if(isCke) {
			// console.log($('div'));
			modalElWrap = $('div').first();
			modalEl 	= modalElWrap.find('.modal');
			modalEl.show();

			openModal();
		}

		window.btnMedia = {
			bind : bindMediaModal
		};

		$.each(mediaSelects, function(key, el){
	        $(el).hide();

	        var newEl = $(button);
	        var delButton = setDeleteButton();

	        newEl.delButton = delButton;

			updateButton(newEl, el);

	        newEl.click(function(){
	        	openedFrom = newEl;
	            openModal();
	        });

	        newEl.insertAfter(el);
	        delButton.insertAfter(newEl);
	    });
	});
})(jQuery);
