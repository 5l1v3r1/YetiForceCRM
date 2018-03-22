/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
App.Fields = {
	'Date': {
		months: ["JS_JAN", "JS_FEB", "JS_MAR", "JS_APR", "JS_MAY", "JS_JUN", "JS_JUL", "JS_AUG", "JS_SEP", "JS_OCT", "JS_NOV", "JS_DEC"],
		fullMonths: ["JS_JANUARY", "JS_FEBRUARY", "JS_MARCH", "JS_APRIL", "JS_MAY", "JS_JUNE", "JS_JULY", "JS_AUGUST", "JS_SEPTEMBER", "JS_OCTOBER", "JS_NOVEMBER", "JS_DECEMBER"],

		/**
		 * Register DatePicker
		 * @param parentElement
		 * @param registerForAddon
		 * @param customParams
		 */
		registerDatePickerFields(parentElement, registerForAddon, customParams) {
			if (typeof parentElement === 'undefined') {
				parentElement = jQuery('body');
			} else {
				parentElement = jQuery(parentElement);
			}
			if (typeof registerForAddon === 'undefined') {
				registerForAddon = true;
			}
			let element = jQuery('.dateField', parentElement);
			if (parentElement.hasClass('dateField')) {
				element = parentElement;
			}
			if (element.length === 0) {
				return;
			}
			if (registerForAddon === true) {
				const parentDateElem = element.closest('.date');
				jQuery('.input-group-addon:not(.notEvent)', parentDateElem).on('click', function inputGroupAddonClickHandler(e) {
					// Using focus api of DOM instead of jQuery because show api of datePicker is calling e.preventDefault
					// which is stopping from getting focus to input element
					jQuery(e.currentTarget).closest('.date').find('input.dateField').get(0).focus();
				});
			}
			// Default first day of the week
			const defaultFirstDay = typeof CONFIG.firstDayOfWeekNo === 'undefined' ? 1 : CONFIG.firstDayOfWeekNo;
			let params = {
				todayBtn: "linked",
				clearBtn: true,
				language: CONFIG.language,
				starts: defaultFirstDay,
				autoclose: true,
				todayHighlight: true
			};
			if (typeof customParams !== 'undefined') {
				params = jQuery.extend(params, customParams);
			}
			element.datepicker(params);
		},

		/*
		 * Initialization datetime fields
		 * @param {jQuery} parentElement
		 * @param {jQuery} customParams
		 */
		registerDateTimePickerFields: function (parentElement, customParams) {
			if (typeof parentElement === 'undefined') {
				parentElement = jQuery('body');
			} else {
				parentElement = jQuery(parentElement);
			}
			let elements = jQuery('.dateTimePickerField', parentElement);
			if (parentElement.hasClass('dateTimePickerField')) {
				elements = parentElement;
			}
			if (elements.length === 0) {
				return;
			}
			jQuery('.input-group-text', elements.closest('.dateTime')).on('click', function (e) {
				jQuery(e.currentTarget).closest('.dateTime').find('input.dateTimePickerField ').get(0).focus();
			});
			let language = CONFIG.language;
			if (typeof $.fn.datepicker.dates[language] === 'undefined') {
				language = Object.keys($.fn.datepicker.dates)[0];
			}
			let dateFormat = CONFIG.dateFormat.toUpperCase();
			const elementDateFormat = elements.data('dateFormat');
			if (typeof elementDateFormat !== 'undefined') {
				dateFormat = elementDateFormat.toUpperCase();
			}
			let hourFormat = CONFIG.hourFormat;
			const elementHourFormat = elements.data('hourFormat');
			if (typeof elementHourFormat !== 'undefined') {
				hourFormat = elementHourFormat;
			}
			let timePicker24Hour = true;
			let timeFormat = 'hh:mm';
			if (hourFormat !== 24) {
				timePicker24Hour = false;
				timeFormat = 'hh:mm A';
			}
			const format = dateFormat + ' ' + timeFormat;
			let params = {
				singleDatePicker: true,
				showDropdowns: true,
				timePicker: true,
				timePicker24Hour: timePicker24Hour,
				timePickerIncrement: 1,
				autoUpdateInput: true,
				autoApply: true,
				opens: "left",
				locale: {
					separator: ',',
					format: format,
					applyLabel: app.vtranslate('JS_APPLY'),
					cancelLabel: app.vtranslate('JS_CANCEL'),
					monthNames: $.fn.datepicker.dates[language].months,
					daysOfWeek: $.fn.datepicker.dates[language].daysMin,
					firstDay: $.fn.datepicker.dates[language].weekStart
				},
			};
			if (typeof customParams !== 'undefined') {
				params = jQuery.extend(params, customParams);
			}
			elements.each(function (index, element) {
				$(element).daterangepicker(params).on('apply.daterangepicker', function applyDateRangePickerHandler(ev, picker) {
					$(this).val(picker.startDate.format(format));
				});
			});
		},

		registerDateRangePickerFields(parentElement, customParams) {
			if (typeof parentElement == 'undefined') {
				parentElement = jQuery('body');
			} else {
				parentElement = jQuery(parentElement);
			}
			if (parentElement.hasClass('dateRangeField')) {
				var elements = parentElement;
			} else {
				var elements = jQuery('.dateRangeField', parentElement);
			}
			if (elements.length == 0) {
				return;
			}
			var language = jQuery('body').data('language');
			let format = CONFIG.dateFormat.toUpperCase();
			const elementDateFormat = elements.data('dateFormat');
			if (typeof elementDateFormat !== 'undefined') {
				format = elementDateFormat.toUpperCase();
			}
			var ranges = {};
			ranges[app.vtranslate('JS_TODAY')] = [moment(), moment()];
			ranges[app.vtranslate('JS_YESTERDAY')] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
			ranges[app.vtranslate('JS_LAST_7_DAYS')] = [moment().subtract(6, 'days'), moment()];
			ranges[app.vtranslate('JS_CURRENT_MONTH')] = [moment().startOf('month'), moment().endOf('month')];
			ranges[app.vtranslate('JS_LAST_MONTH')] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
			ranges[app.vtranslate('JS_LAST_3_MONTHS')] = [moment().subtract(3, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
			ranges[app.vtranslate('JS_LAST_6_MONTHS')] = [moment().subtract(6, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
			if ($.fn.datepicker.dates[language] == undefined) {
				var langCodes = Object.keys($.fn.datepicker.dates);
				language = langCodes[0];
			}
			var params = {
				autoUpdateInput: false,
				autoApply: true,
				ranges: ranges,
				opens: "left",
				locale: {
					separator: ',',
					format: format,
					customRangeLabel: app.vtranslate('JS_CUSTOM'),
					daysOfWeek: $.fn.datepicker.dates[language].daysMin,
					monthNames: $.fn.datepicker.dates[language].months,
					firstDay: $.fn.datepicker.dates[language].weekStart
				},
			};
			if (typeof customParams != 'undefined') {
				params = jQuery.extend(params, customParams);
			}
			elements.each(function (index, element) {
				element = $(element);
				element.daterangepicker(params);
				element.on('apply.daterangepicker', function (ev, picker) {
					$(this).val(picker.startDate.format(format) + ',' + picker.endDate.format(format));
				});
			});

		},
	},
	Colors: {
		/**
		 * Function to check whether the color is dark or light
		 */
		getColorContrast: function (hexcolor) {
			var r = parseInt(hexcolor.substr(0, 2), 16);
			var g = parseInt(hexcolor.substr(2, 2), 16);
			var b = parseInt(hexcolor.substr(4, 2), 16);
			var yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
			return (yiq >= 128) ? 'light' : 'dark';
		},
		getRandomColor: function () {
			var letters = '0123456789ABCDEF'.split('');
			var color = '#';
			for (var i = 0; i < 6; i++) {
				color += letters[Math.floor(Math.random() * 16)];
			}
			return color;
		},
		getRandomColors: function (count) {
			const colors = [];
			for (var i = 0; i < count; i++) {
				colors.push(this.getRandomColor());
			}
			return colors;
		}
	},
	Password: {
		/**
		 * Register clip
		 * @param {string} key
		 * @returns {ClipboardJS}
		 */
		registerCopyClipboard: function (key) {
			if (key == undefined) {
				key = '.clipboard';
			}
			return new ClipboardJS(key, {
				text: function (trigger) {
					Vtiger_Helper_Js.showPnotify({
						text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
						type: 'success'
					});
					trigger = jQuery(trigger);
					var element = jQuery(trigger.data('copyTarget'));
					var val;
					if (typeof trigger.data('copyType') !== 'undefined') {
						if (element.is("select")) {
							val = element.find('option:selected').data(trigger.data('copyType'));
						} else {
							val = element.data(trigger.data('copyType'));
						}
					} else if (typeof trigger.data('copy-attribute') !== 'undefined') {
						val = trigger.data(trigger.data('copy-attribute'));
					} else {
						val = element.val();
					}
					return val;
				}
			});
		},
	},
	DateTime: {},
	Text: {
		/*
		 * Initialization CkEditor
		 * @param {jQuery} parentElement
		 * @param {Object} params
		 */
		registerCkEditor: function (parentElement, params) {
			if (typeof parentElement == 'undefined') {
				parentElement = jQuery('body');
			} else {
				parentElement = jQuery(parentElement);
			}
			if (parentElement.hasClass('js-ckeditor') && !parentElement.prop('disabled')) {
				var elements = parentElement;
			} else {
				var elements = jQuery('.js-ckeditor:not([disabled])', parentElement);
			}
			if (elements.length == 0) {
				return;
			}
			$.each(elements, function (key, element) {
				var ckEditorInstance = new Vtiger_CkEditor_Js();
				ckEditorInstance.loadCkEditor($(element), params);
			});
		},
		/**
		 * Destroy ckEditor
		 * @param {jQuery} element
		 */
		destroyCkEditor: function (element) {
			if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances && element.attr('id') in CKEDITOR.instances) {
				CKEDITOR.instances[element.attr('id')].destroy();
			}
		},
	}
}
