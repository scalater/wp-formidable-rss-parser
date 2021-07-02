var formidableRSSParserData, formidableRSSParserInstance = {
	setShowData: function(data) {
		formidableRSSParserData = data;
	},
	getShowData: function() {
		return formidableRSSParserData;
	},
	validateURL: function(url) {
		return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(
			url);
	},
	showSubmitLoading: function($object) {
		formidableRSSParserInstance.showLoadingIndicator($object);
		formidableRSSParserInstance.disableSubmitButton($object);
		formidableRSSParserInstance.disableSaveDraft($object);
	},
	showLoadingIndicator: function($object) {
		if (!$object.hasClass('frm_loading_form') && !$object.hasClass('frm_loading_prev')) {
			formidableRSSParserInstance.addLoadingClass($object);
			$object.trigger('frmStartFormLoading');
		}
	},
	disableSubmitButton: function($form) {
		$form.find('input[type="submit"], input[type="button"], button[type="submit"]').attr('disabled', 'disabled');
	},
	disableSaveDraft: function($form) {
		$form.find('a.frm_save_draft').css('pointer-events', 'none');
	},
	enableSaveDraft: function($form) {
		$form.find('a.frm_save_draft').css('pointer-events', '');
	},
	enableSubmitButton: function($form) {
		$form.find('input[type="submit"], input[type="button"], button[type="submit"]').prop('disabled', false);
	},
	addLoadingClass: function($object) {
		var loadingClass = 'frm_loading_form';

		$object.addClass(loadingClass);
	},
	removeSubmitLoading: function($object, enable, processesRunning) {
		var loadingForm;

		if (processesRunning > 0) {
			return;
		}

		loadingForm = jQuery('.frm_loading_form');
		loadingForm.removeClass('frm_loading_form');
		loadingForm.removeClass('frm_loading_prev');

		loadingForm.trigger('frmEndFormLoading');

		if (enable === 'enable') {
			formidableRSSParserInstance.enableSubmitButton(loadingForm);
			formidableRSSParserInstance.enableSaveDraft(loadingForm);
		}
	},
	addFieldError: function($fieldCont, key, jsErrors) {
		var input, id, describedBy;
		if ($fieldCont.length && $fieldCont.is(':visible')) {
			$fieldCont.addClass('frm_blank_field');
			input = $fieldCont.find('input, select, textarea');
			id = 'frm_error_field_' + key;
			describedBy = input.attr('aria-describedby');

			$fieldCont.append('<div class="frm_error" id="' + id + '">' + jsErrors[key] + '</div>');

			if (typeof describedBy === 'undefined') {
				describedBy = id;
			} else if (describedBy.indexOf(id) === -1) {
				describedBy = describedBy + ' ' + id;
			}
			input.attr('aria-describedby', describedBy);
		}
		input.attr('aria-invalid', true);

		jQuery(document).trigger('frmAddFieldError', [$fieldCont, key, jsErrors]);
	},
	removeFieldError: function($fieldCont) {
		var errorMessage = $fieldCont.find('.frm_error'),
			errorId = errorMessage.attr('id'),
			input = $fieldCont.find('input, select, textarea'),
			describedBy = input.attr('aria-describedby');

		$fieldCont.removeClass('frm_blank_field has-error');
		errorMessage.remove();
		input.attr('aria-invalid', false);

		if (typeof describedBy !== 'undefined') {
			describedBy = describedBy.replace(errorId, '');
			input.attr('aria-describedby', describedBy);
		}
	},
	shouldJSValidate: function(object) {
		var validate = jQuery(object).hasClass('frm_js_validate');
		if (validate && typeof frmProForm !== 'undefined' && (frmProForm.savingDraft(object) || frmProForm.goingToPreviousPage(object))) {
			validate = false;
		}

		return validate;
	},
	rssParser: function(data) {
		if (!data) {
			return;
		}
		let parserFields = jQuery('[data-parser-path]');
		if (parserFields && parserFields.length > 0) {
			jQuery.each(parserFields, function(i, e) {
				let parserPath = jQuery(e).attr('data-parser-path');
				if (parserPath) {
					let parsedData = _.get(data, parserPath);
					if (parsedData) {
						jQuery(e).val(parsedData);
					}
				}
			});
		}
	},
	importAjax: function(data, selection, onSuccessCallback, onCompleteCallback) {
		let resultSelection = [];
		jQuery.each(selection, function(i, e) {
			resultSelection.push(jQuery(e).val());
		});
		let resultData = JSON.stringify(data);
		jQuery.ajax({
			type: 'POST',
			dataType: 'json',
			url: wpHtmlCssToImageObj.admin_url,
			data: {
				'action': 'formidable_rss_parser_import',
				'nonce': formidableRSSParserObj.nonce,
				'data': resultData,
				'selection': resultSelection,
			},
			success: function(response) {
				console.log(response);
				if (response && response.success && response.status) {
					onSuccessCallback(response.status);
				}
			},
			error: function(request, status, error) {
				throw request.responseText;
			},
			complete: function() {
				onCompleteCallback();
			},
		});
	},
	rssAjax: function(url, onSuccessCallback, onCompleteCallback) {
		jQuery.ajax({
			type: 'POST',
			dataType: 'json',
			url: wpHtmlCssToImageObj.admin_url,
			data: {
				'action': 'formidable_rss_parse',
				'nonce': formidableRSSParserObj.nonce,
				'url': url,
			},
			success: function(response) {
				if (response && response.success && response.data) {
					// response.data.shows = JSON.parse(response.data.shows);
					// onSuccessCallback(response.data);
					console.log(response.data);
				}
			},
			error: function(request, status, error) {
				throw request.responseText;
			},
			complete: function() {
				onCompleteCallback();
			},
		});
	},
	onRssKeyOut: function(callback, delay) {
		delay || (delay = 500);
		var timeoutReference;
		var doneTyping = function(elt) {
			if (!timeoutReference) {
				return;
			}
			timeoutReference = null;
			callback(elt);
		};

		this.each(function() {
			var self = jQuery(this);
			self.on('keyup', function() {
				if (timeoutReference) {
					clearTimeout(timeoutReference);
				}
				timeoutReference = setTimeout(function() {
					doneTyping(self);
				}, delay);
			}).on('blur', function() {
				doneTyping(self);
			});
		});

		return this;
	},
	initFormidableField: function() {
		let containers = jQuery('.formidable-rss-parser-container');
		if (containers && containers.length > 0) {
			jQuery.each(containers, function(i, e) {
				let container = jQuery(e);
				let targetElement = container.find('.formidable-rss-parser');
				if (targetElement && targetElement.length > 0) {
					let targetElementContainerId = targetElement.attr('data-container-id');
					let targetElementId = targetElement.attr('data-field-id');
					let targetElementContainer = container.closest('#' + targetElementContainerId);
					if (targetElementContainer && targetElementContainer.length > 0) {
						let targetFormId = '#frm_form_' + targetElement.attr('data-form-id') + '_container form';
						let targetFormElement = jQuery(targetFormId);
						if (targetFormElement && targetFormElement.length > 0) {
							targetElement.onRssKeyOut(function(e) {
								var rssUrl = jQuery(e).val();
								if (rssUrl) {
									formidableRSSParserInstance.showSubmitLoading(targetFormElement);
									formidableRSSParserInstance.removeFieldError(targetElementContainer);
									let isValidUrl = formidableRSSParserInstance.validateURL(rssUrl);
									if (!isValidUrl) {
										let jsErrors = [];
										jsErrors[targetElementContainerId] = 'Invalid RSS URL';
										formidableRSSParserInstance.addFieldError(targetElementContainer, targetElementContainerId, jsErrors);
									} else {
										formidableRSSParserInstance.rssAjax(rssUrl,
											function(data) {
												if (data.shows) {
													formidableRSSParserInstance.setShowData(data);
													formidableRSSParserInstance.rssParser(data.shows);
												}
											},
											function() {
												formidableRSSParserInstance.removeSubmitLoading(targetFormElement);
											},
										);
									}
								}
							});
						}
					}
				}
			});
		}
	},
	clearShortCodeInput: function(element, container) {
		if (!element) {
			return;
		}
		jQuery(element).val('');
		container.find('.formidable-rss-result-show').hide();
		container.find('.formidable-rss-result-episodes-container').hide();
	},
	shortCodeLoadingAdd: function(button) {
		if (!button) {
			return;
		}
		let text = jQuery(button).text();
		jQuery(button).attr('data-default-text', text).text('Loading...').attr('disabled', 'disabled');
	},
	shortCodeLoadingRemove: function(button) {
		if (!button) {
			return;
		}
		let text = jQuery(button).attr('data-default-text');
		jQuery(button).removeAttr('disabled');
		jQuery(button).text(text);
	},
	onShortCodeSearch: function(e, container, targetElement) {
		console.log('onShortCodeSearch', container);
		const searchButton = container.find('button.search-show');
		let rssUrl = jQuery(targetElement).val();
		if (rssUrl) {
			let isValidUrl = formidableRSSParserInstance.validateURL(rssUrl);
			if (!isValidUrl) {
				//todo add error for the shortcode interface
				console.log('Invalid URL');
			} else {
				formidableRSSParserInstance.shortCodeLoadingAdd(searchButton);
				formidableRSSParserInstance.rssAjax(rssUrl,
					function(data) {
						if (data.count && data.count > 0 && data.shows) {
							formidableRSSParserInstance.setShowData(data);
							let resultContainer = container.find('.formidable-rss-result-show');
							//todo change the way the result list is build when data is an array of results
							if (data.count === 1) {
								let showHtml = '<label class="element-list">' +
									'<div class="element-image">' +
									'<img src="' + data.shows.image.url + '" alt="' + data.shows.image.title + '">' +
									'</div>' +
									'<div class="element-details">' +
									'<div class="element-title">' + data.shows.title + '</div>' +
									'<div class="element-sub-details">' +
									'<span class="element-author">' + data.shows.title + '</span>' +
									'<span class="element-separator">&centerdot;</span>' +
									'<span class="element-episode-amount">' + data.shows.item.length + '</span>' +
									'</div>' +
									'</div>' +
									'</label>';
								resultContainer.html('').html(showHtml);
							}
						}
					},
					function() {
						formidableRSSParserInstance.shortCodeLoadingRemove(searchButton);
						container.find('.formidable-rss-result-show').show();
						container.find('.formidable-rss-result-episodes-container').hide();
					},
				);
			}
		} else {
			//todo add error for the shortcode interface
			console.log('Empty URL');
		}
	},
	onShortCodeShowClick: function(e, container) {
		console.log('onShortCodeShowClick', jQuery(e));
		const searchButton = container.find('button.search-show');
		let data = formidableRSSParserInstance.getShowData();
		if (data && data.shows && data.shows.item) {
			formidableRSSParserInstance.shortCodeLoadingAdd(searchButton);
			window.setTimeout(function() {
				let imageContainer = container.find('.formidable-rss-result-episodes-container .episode-image img');
				let listContainer = container.find('.formidable-rss-result-episodes-container .episodes-list');
				imageContainer.attr('src', data.shows.image.url);
				imageContainer.attr('alt', data.shows.image.title);
				listContainer.html('');
				jQuery.each(data.shows.item, function(i, e) {
					let fullDate = new Date(e['timestamp']);
					var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
					let formatDate = months[fullDate.getMonth()] + ' ' + fullDate.getDate() + ', ' + fullDate.getFullYear(); //May 19, 2021
					let listHtml = '<label class="element-list">' +
						'<div class="element-left">' +
						'<input type="checkbox" name="lorem" value="' + i + '">' +
						'</div>' +
						'<div class="element-details">' +
						'<span class="element-title">' + e['title'] + '</span>' +
						'<div class="element-sub-details">' +
						'<span class="element-date">' + formatDate + '</span>' +
						'<span class="element-separator">&centerdot;</span>' +
						'<span class="element-duration">' + e['itunes:duration'] + '</span>' +
						'</div>' +
						'</div>' +
						'</label>';
					listContainer.append(listHtml);
				});
				formidableRSSParserInstance.shortCodeLoadingRemove(searchButton);
				container.find('.formidable-rss-result-show').hide();
				container.find('.formidable-rss-result-episodes-container').show();
			}, 500);
		} else {
			throw 'No data detected or episodes items';
		}
	},
	onShortCodeImport: function(e, container) {
		const searchButton = container.find('button.search-show');
		let selectedEpisodes = container.find('.formidable-rss-result-episodes label.element-list input[type="checkbox"]:checked');
		let data = formidableRSSParserInstance.getShowData();
		if (data && data.shows && data.shows.item) {
			formidableRSSParserInstance.shortCodeLoadingAdd(searchButton);
			formidableRSSParserInstance.importAjax(data, selectedEpisodes,
				function(status) {
					console.log('onShortCodeImport', status);
				},
				function() {
					formidableRSSParserInstance.shortCodeLoadingRemove(searchButton);
				});
		}
	},
	initShortCode: function() {
		let containers = jQuery('.formidable-rss-parser-container-shortcode');
		if (containers && containers.length > 0) {
			jQuery.each(containers, function(i, e) {
				let container = jQuery(e);
				let targetElement = container.find('.formidable-rss-parser');
				if (targetElement && targetElement.length > 0) {
					let targetFormIdShow = targetElement.attr('data-form-id-show');
					let targetFormIdEpisode = targetElement.attr('data-form-id-episode');
					let targetType = targetElement.attr('data-type');
					jQuery(document).on('click', '.formidable-rss-parser-container-shortcode .formidable-rss-result-show label.element-list', function(e) {
						formidableRSSParserInstance.onShortCodeShowClick(e, container);
					});
					jQuery(document).on('click', '.formidable-rss-parser-container-shortcode .search-container button.search-show', function(e) {
						formidableRSSParserInstance.onShortCodeSearch(e, container, targetElement);
					});
					jQuery(document).on('click', '.formidable-rss-parser-container-shortcode .formidable-rss-result-episodes-container button.import-episodes', function(e) {
						formidableRSSParserInstance.onShortCodeImport(e, container);
					});
					jQuery(document).on('click', '.formidable-rss-parser-container-shortcode .clear-input', function(e) {
						formidableRSSParserInstance.clearShortCodeInput(targetElement, container);
					});
				}
			});
		}
	},
	init: function() {
		jQuery.fn.onRssKeyOut = formidableRSSParserInstance.onRssKeyOut;
		formidableRSSParserInstance.initFormidableField();
		formidableRSSParserInstance.initShortCode();
	},
};

try {
	jQuery(document).ready(function() {
		formidableRSSParserInstance.init();
	});
} catch (e) {
	console.error('[formidableRSSParser] - ' + e);
}
