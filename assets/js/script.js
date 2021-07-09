var formidableRSSParserData;
var formidableRSSParserInstance = {
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
	importAjax: function(url, selection, targetFormIdShow, targetFormIdEpisode, onSuccessCallback, onCompleteCallback) {
		let resultSelection = [];
		jQuery.each(selection, function(i, e) {
			resultSelection.push(jQuery(e).val());
		});
		jQuery.ajax({
			type: 'POST',
			dataType: 'json',
			url: formidableRSSParserObj.admin_url,
			data: {
				'action': 'formidable_rss_parser_import',
				'nonce': formidableRSSParserObj.nonce,
				'url': url,
				'selection': resultSelection,
				'target_form_id_show': targetFormIdShow,
				'target_form_id_episode': targetFormIdEpisode,
			},
			success: function(response) {
				console.log(response);
				if (response && response.success && response.data) {
					onSuccessCallback(response.data);
				}
			},
			error: function(request, status, error) {
				if(request.responseJSON && !request.responseJSON.success) {
					console.error('[formidableRSSParser] - ' + request.responseJSON.data);
				}
			},
			complete: function() {
				onCompleteCallback();
			},
		});
	},
	rssAjax: function(url, container, targetElement, onSuccessCallback, onCompleteCallback) {
		formidableRSSParserInstance.onValid(container);
		jQuery.ajax({
			type: 'POST',
			dataType: 'json',
			url: formidableRSSParserObj.admin_url,
			data: {
				'action': 'formidable_rss_parse',
				'nonce': formidableRSSParserObj.nonce,
				'url': url,
			},
			success: function(response) {
				if (response && response.success && response.data) {
					// response.data.rss = JSON.parse(response.data.rss);
					onSuccessCallback(response.data);
					console.log(response.data);
				}
			},
			error: function(request, status, error) {
				if(request.responseJSON && !request.responseJSON.success) {
					console.error('[formidableRSSParser] - ' + request.responseJSON.data);
					formidableRSSParserInstance.onError(request.responseJSON.data, container, targetElement);
				}
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
												if (data.rss) {
													formidableRSSParserInstance.setShowData(data);
													formidableRSSParserInstance.rssParser(data.rss);
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
		formidableRSSParserInstance.onValid(container);
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
			formidableRSSParserInstance.onValid(container);
			let isValidUrl = formidableRSSParserInstance.validateURL(rssUrl);
			if (!isValidUrl) {
				console.log('Invalid URL');
				formidableRSSParserInstance.onError('Invalid URL', container, targetElement);
			} else {
				formidableRSSParserInstance.onValid(container);
				formidableRSSParserInstance.shortCodeLoadingAdd(searchButton);
				formidableRSSParserInstance.rssAjax(rssUrl, container, targetElement,
					function(data) {
						if (data.count && data.count > 0 && data.rss) {
							formidableRSSParserInstance.setShowData(data);
							let resultContainer = container.find('.formidable-rss-result-show');
							//todo change the way the result list is build when data is an array of results
							if (data.count === 1) {
								let showHtml = '<label class="element-list">' +
									'<div class="element-image">' +
									'<img src="' + data.rss.image.url + '" alt="' + data.rss.image.title + '">' +
									'</div>' +
									'<div class="element-details">' +
									'<div class="element-title">' + data.rss.title + '</div>' +
									'<div class="element-sub-details">' +
									'<span class="element-author">' + data.rss.title + '</span>' +
									'<span class="element-separator">&centerdot;</span>' +
									'<span class="element-episode-amount">' + data.rss.item.length + '</span>' +
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
			console.log('Empty URL');
			formidableRSSParserInstance.onError('Empty URL', container, targetElement);
		}
	},
	onShortCodeShowClick: function(e, container) {
		console.log('onShortCodeShowClick', jQuery(e));
		const searchButton = container.find('button.search-show');
		let data = formidableRSSParserInstance.getShowData();
		if (data && data.rss && data.rss.item) {
			formidableRSSParserInstance.shortCodeLoadingAdd(searchButton);
			window.setTimeout(function() {
				let imageContainer = container.find('.formidable-rss-result-episodes-container .episode-image img');
				let listContainer = container.find('.formidable-rss-result-episodes-container .episodes-list');
				imageContainer.attr('src', data.rss.image.url);
				imageContainer.attr('alt', data.rss.image.title);
				listContainer.html('');
				listContainer.scrollTop(0);

				jQuery.each(data.rss.item, function (i, e) {
					let fullDate = new Date(parseInt(e.timestamp) * 1000);
					var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
					let formatDate = months[fullDate.getMonth()] + ' ' + fullDate.getDate() + ', ' + fullDate.getFullYear(); //May 19, 2021
					const hide = i >= 5 ? 'hide' : '';
					let listHtml = `
					<label class="element-list ${hide}">
						<div class="element-left">
							<input type="checkbox" name="lorem" value="${i}">
						</div>
						<div class="element-details">
							<span class="element-title">${e['title']}</span>
							<div class="element-sub-details">
								<span class="element-date">${formatDate}</span>
								<span class="element-separator">&centerdot;</span>
								<span class="element-duration">${e['itunes_duration']}</span>
							</div>
						</div>
					</label>
					`;
					listContainer.append(listHtml);
				});
				formidableRSSParserInstance.shortCodeLoadingRemove(searchButton);
				container.find('.formidable-rss-result-show').hide();
				container.find('.formidable-rss-result-episodes-container').show();
			}, 250);
		} else {
			throw new Error('No data detected or episodes items');
		}
	},
	onError: function(reason, container, targetElement) {
		jQuery(container).find('label.input-url').addClass('err');
		jQuery(container).find('label.input-url .search-container .input-error').html(reason);
		jQuery(targetElement).select();
	},
	onValid: function(container) {
		jQuery(container).find('label.input-url').removeClass('err');
		jQuery(container).find('label.input-url .search-container .input-error').html('');
	},
	onShortCodeImport: function(e, container, targetElement, targetFormIdShow, targetFormIdEpisode, redirect) {
		const searchButton = container.find('button.search-show');
		const importButton = container.find('button.import-episodes');
		let selectedEpisodes = container.find('.formidable-rss-result-episodes label.element-list input[type="checkbox"]:checked');
		let data = formidableRSSParserInstance.getShowData();

		if (data && data.rss && data.rss.item) {
			formidableRSSParserInstance.shortCodeLoadingAdd(searchButton);
			formidableRSSParserInstance.shortCodeLoadingAdd(importButton);
			let rssUrl = jQuery(targetElement).val();
			if (rssUrl) {
				formidableRSSParserInstance.onValid(container);
				let isValidUrl = formidableRSSParserInstance.validateURL(rssUrl);
				if (!isValidUrl) {
					formidableRSSParserInstance.onError('Invalid URL', container, targetElement);
					console.log('Invalid URL');
				} else {
					formidableRSSParserInstance.onValid(container);
					formidableRSSParserInstance.importAjax(rssUrl, selectedEpisodes, targetFormIdShow, targetFormIdEpisode,
						function(status) {
							if(redirect && status){
								document.location.href = redirect;
							}
						},
						function() {
							formidableRSSParserInstance.shortCodeLoadingRemove(searchButton);
							formidableRSSParserInstance.shortCodeLoadingRemove(importButton);
						});
				}
			} else {
				formidableRSSParserInstance.onError('Empty URL', container, targetElement);
				console.log('Empty URL');
			}
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
					let redirect = targetElement.attr('data-redirect');
					jQuery(document).on('click', '.formidable-rss-parser-container-shortcode .formidable-rss-result-show label.element-list', function(e) {
						formidableRSSParserInstance.onShortCodeShowClick(e, container);
					});
					jQuery(document).on('click', '.formidable-rss-parser-container-shortcode .search-container button.search-show', function(e) {
						formidableRSSParserInstance.onShortCodeSearch(e, container, targetElement);
					});
					jQuery(document).on('click', '.formidable-rss-parser-container-shortcode .formidable-rss-result-episodes-container button.import-episodes', function(e) {
						formidableRSSParserInstance.onShortCodeImport(e, container, targetElement, targetFormIdShow, targetFormIdEpisode, redirect);
					});
					jQuery(document).on('click', '.formidable-rss-parser-container-shortcode .clear-input', function(e) {
						formidableRSSParserInstance.clearShortCodeInput(targetElement, container);
					});
				}
				jQuery('.formidable-rss-result-episodes-container .episodes-list, .formidable-rss-result-episodes-container .formidable-rss-result-episodes').on('scroll', function (e) {
					const attr = 'data-wait';
					const height = jQuery(this).height();
					const scrollHeight = jQuery(this)[0].scrollHeight;
					const scrollTop = jQuery(this).scrollTop();
					const elements = jQuery(this).find('label.element-list.hide');
					const newScroll = (scrollHeight - scrollTop - (height * 2) < 0) && (elements.length > 0) && (!jQuery(this).attr(attr));

					if (newScroll) {
						jQuery(this).attr(attr, true);
						jQuery(elements).each(function (index) {
							if (index > 5) return false;

							jQuery(this).removeClass('hide');
						});

						jQuery(this).removeAttr(attr);
					}
				});
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
