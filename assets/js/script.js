var formidableRSSParserInstance = {
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
	rssAjax: function(url, targetFormElement) {
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
				console.log(response);
				if (response && response.success && response.data) {
					formidableRSSParserInstance.rssParser(response.data);
				}
			},
			error: function(request, status, error) {
				alert(request.responseText);
			},
			complete: function() {
				formidableRSSParserInstance.removeSubmitLoading(targetFormElement);
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
										formidableRSSParserInstance.rssAjax(rssUrl, targetFormElement);
									}
								}
							});
						}
					}
				}
			});
		}
	},
	clearShortCodeInput: function(element) {
		if (!element) {
			return;
		}
		jQuery(element).val('');
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
					container.find('.clear').on('click', function() {
						formidableRSSParserInstance.clearShortCodeInput(targetElement);
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

jQuery(document).ready(function() {
	formidableRSSParserInstance.init();
});
