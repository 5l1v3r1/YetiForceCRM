/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/** Class representing a calendar. */
window.Calendar_Js = class {
	/**
	 * Create calendar's options
	 * @param {jQuery} container
	 * @param {bool} readonly
	 */
	constructor(container = $('.js-base-container'), readonly = false) {
		this.calendarView = false;
		this.calendarCreateView = false;
		this.container = container;
		this.readonly = readonly;
		this.browserHistoryConfig = readonly ? {} : this.setBrowserHistoryOptions();
		this.calendarOptions = this.setCalendarOptions();
	}

	/**
	 * Set calendar's options
	 * @returns {object}
	 */
	setCalendarOptions() {
		return Object.assign(this.setCalendarBasicOptions(), this.setCalendarAdvancedOptions(), this.setCalendarModuleOptions(), this.browserHistoryConfig);
	}

	/**
	 * Set calendar's basic options
	 * @returns {object}
	 */
	setCalendarBasicOptions() {
		let eventLimit = app.getMainParams('eventLimit'),
			userDefaultActivityView = app.getMainParams('activity_view'),
			defaultView = app.moduleCacheGet('defaultView'),
			userDefaultTimeFormat = app.getMainParams('time_format');
		if (eventLimit == 'true') {
			eventLimit = true;
		} else if (eventLimit == 'false') {
			eventLimit = false;
		} else {
			eventLimit = parseInt(eventLimit) + 1;
		}
		if (userDefaultActivityView === 'Today') {
			userDefaultActivityView = app.getMainParams('dayView');
		} else if (userDefaultActivityView === 'This Week') {
			userDefaultActivityView = app.getMainParams('weekView');
		} else {
			userDefaultActivityView = 'month';
		}
		if (defaultView != null) {
			userDefaultActivityView = defaultView;
		}
		if (userDefaultTimeFormat == 24) {
			userDefaultTimeFormat = 'H:mm';
		} else {
			userDefaultTimeFormat = 'h:mmt';
		}
		let options = {
			timeFormat: userDefaultTimeFormat,
			slotLabelFormat: userDefaultTimeFormat,
			defaultView: userDefaultActivityView,
			slotMinutes: 15,
			defaultEventMinutes: 0,
			forceEventDuration: true,
			defaultTimedEventDuration: '01:00:00',
			eventLimit: eventLimit,
			eventLimitText: app.vtranslate('JS_MORE'),
			selectHelper: true,
			scrollTime: app.getMainParams('startHour') + ':00',
			monthNamesShort: [app.vtranslate('JS_JAN'), app.vtranslate('JS_FEB'), app.vtranslate('JS_MAR'),
				app.vtranslate('JS_APR'), app.vtranslate('JS_MAY'), app.vtranslate('JS_JUN'), app.vtranslate('JS_JUL'),
				app.vtranslate('JS_AUG'), app.vtranslate('JS_SEP'), app.vtranslate('JS_OCT'), app.vtranslate('JS_NOV'),
				app.vtranslate('JS_DEC')],
			dayNames: [app.vtranslate('JS_SUNDAY'), app.vtranslate('JS_MONDAY'), app.vtranslate('JS_TUESDAY'),
				app.vtranslate('JS_WEDNESDAY'), app.vtranslate('JS_THURSDAY'), app.vtranslate('JS_FRIDAY'),
				app.vtranslate('JS_SATURDAY')],
			buttonText: {
				today: app.vtranslate('JS_CURRENT'),
				year: app.vtranslate('JS_YEAR'),
				month: app.vtranslate('JS_MONTH'),
				week: app.vtranslate('JS_WEEK'),
				day: app.vtranslate('JS_DAY')
			},
			allDayText: app.vtranslate('JS_ALL_DAY'),
		};
		if (app.moduleCacheGet('start') !== null) {
			let s = moment(app.moduleCacheGet('start')).valueOf();
			let e = moment(app.moduleCacheGet('end')).valueOf();
			options.defaultDate = moment(moment(s + ((e - s) / 2)).format('YYYY-MM-DD'));
		}
		return Object.assign(this.setCalendarMinimalOptions(), options);
	}

	/**
	 * Set calendar's minimal options
	 * @returns {object}
	 */
	setCalendarMinimalOptions() {
		let hiddenDays = [];
		if (app.getMainParams('switchingDays') === 'workDays') {
			hiddenDays = app.getMainParams('hiddenDays', true);
		}
		return {
			firstDay: CONFIG.firstDayOfWeekNo,
			selectable: true,
			hiddenDays: hiddenDays,
			monthNames: [app.vtranslate('JS_JANUARY'), app.vtranslate('JS_FEBRUARY'), app.vtranslate('JS_MARCH'),
				app.vtranslate('JS_APRIL'), app.vtranslate('JS_MAY'), app.vtranslate('JS_JUNE'), app.vtranslate('JS_JULY'),
				app.vtranslate('JS_AUGUST'), app.vtranslate('JS_SEPTEMBER'), app.vtranslate('JS_OCTOBER'),
				app.vtranslate('JS_NOVEMBER'), app.vtranslate('JS_DECEMBER')],
			dayNamesShort: [app.vtranslate('JS_SUN'), app.vtranslate('JS_MON'), app.vtranslate('JS_TUE'),
				app.vtranslate('JS_WED'), app.vtranslate('JS_THU'), app.vtranslate('JS_FRI'),
				app.vtranslate('JS_SAT')],
		};
	}

	/**
	 * Set calendar's advanced options
	 * @returns {object}
	 */
	setCalendarAdvancedOptions() {
		let self = this;
		return {
			header: {
				left: 'month,' + app.getMainParams('weekView') + ',' + app.getMainParams('dayView'),
				center: 'title today',
				right: 'prev,next'
			},
			allDaySlot: app.getMainParams('allDaySlot'),
			views: {
				basic: {
					eventLimit: false,
				}
			},
			eventDrop: function (event, delta, revertFunc) {
				self.updateEvent(event, delta, revertFunc);
			},
			eventResize: function (event, delta, revertFunc) {
				self.updateEvent(event, delta, revertFunc);
			},
			viewRender: function () {
				self.loadCalendarData();
			},
			eventRender: self.eventRenderer,
			height: this.setCalendarHeight(this.container)
		}
	}

	/**
	 * Update calendar's event
	 * @param {Object} event
	 * @param {Object} delta
	 * @param {Object} revertFunc
	 */
	updateEvent(event, delta, revertFunc) {
		let progressInstance = jQuery.progressIndicator({blockInfo: {enabled: true}});
		let start = event.start.format();
		let params = {
			module: CONFIG.module,
			action: 'Calendar',
			mode: 'updateEvent',
			id: event.id,
			start: start,
			delta: delta._data,
			allDay: event.allDay
		};
		AppConnector.request(params).done(function (response) {
			if (!response['result']) {
				Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_EDIT_PERMISSION'));
				revertFunc();
			}
			progressInstance.progressIndicator({'mode': 'hide'});
		}).fail(function () {
			progressInstance.progressIndicator({'mode': 'hide'});
			Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_EDIT_PERMISSION'));
			revertFunc();
		});
	}

	/**
	 * Render event
	 * @param {Object} event
	 * @param {jQuery} element
	 */
	eventRenderer(event, element) {
		if (event.rendering === 'background') {
			element.append(`<span class="js-popover-text d-block"><span class="${event.icon} js-popover-icon mr-1"></span>${event.title}</span>`);
			element.addClass('js-popover-tooltip--ellipsis').attr('data-content', event.title);
			app.registerPopoverEllipsis(element);
		}
	}

	/**
	 * Returns counted calendar height
	 * @returns {(number|string)}
	 */
	setCalendarHeight() {
		let calendarH;
		if ($(window).width() > 993) {
			let calendarContainer = this.container.find('.js-calendar__container'),
				calendarPadding;
			if (this.container.hasClass('js-modal-container')) {
				calendarPadding = this.container.find('.js-modal-header').outerHeight(); // modal needs bigger padding to prevent modal's scrollbar
			} else {
				calendarPadding = this.container.find('.js-contents-div').css('margin-left').replace('px', ''); //equals calendar padding bottom to left margin
			}
			let setCalendarH = () => {
				return $(window).height() - this.container.find('.js-calendar__container').offset().top - $('.js-footer').height() - calendarPadding;
			};
			calendarH = setCalendarH();
			new ResizeSensor(this.container.find('.contentsDiv'), () => {
				calendarContainer.fullCalendar('option', 'height', setCalendarH());
				calendarContainer.height(calendarH + 10); // without this line calendar scroll stops working
			});
		} else if ($(window).width() < 993) {
			calendarH = 'auto';
		}
		return calendarH;
	}

	/**
	 * Set calendar module's options
	 * @returns {object}
	 */
	setCalendarModuleOptions() {
		return {};
	}

	/**
	 * Set calendar options from browser history
	 * @returns {object}
	 */
	setBrowserHistoryOptions() {
		let historyParams = app.getMainParams('historyParams', true),
			options;
		if (historyParams && (historyParams.length || Object.keys(historyParams).length) && app.moduleCacheGet('browserHistoryEvent')) {
			options = {
				start: historyParams.start,
				end: historyParams.end,
				user: historyParams.user.split(",").map((x) => {
					return parseInt(x)
				}),
				time: historyParams.time,
				hiddenDays: historyParams.hiddenDays.split(",").map((x) => {
					return parseInt(x)
				}),
				cvid: historyParams.cvid,
				defaultView: historyParams.viewType
			};
			let s = moment(options.start).valueOf();
			let e = moment(options.end).valueOf();
			options.defaultDate = moment(moment(s + ((e - s) / 2)).format('YYYY-MM-DD'));
			Object.keys(options).forEach(key => options[key] === 'undefined' && delete options[key]);
			app.moduleCacheSet('browserHistoryEvent', false)
		} else {
			options = null;
		}
		window.addEventListener('popstate', function (event) {
			app.moduleCacheSet('browserHistoryEvent', true)
		}, false);
		return options;
	}

	/**
	 * Register events
	 * @returns {object}
	 */
	registerEvents() {
		this.renderCalendar();
		this.registerSitebarEvents();
		this.registerButtonSelectAll();
		this.registerAddButton();
	}

	/**
	 * Invokes fullcalendar with merged options
	 */
	renderCalendar() {
		this.getCalendarView().fullCalendar(this.calendarOptions);
	}

	registerSitebarEvents() {
		$('.bodyContents').on('Vtiger.Widget.Load.undefined', () => {
			this.registerSelect2Event()
		});
	}

	loadCalendarData() {
		let progressInstance = jQuery.progressIndicator();
		let self = this;
		self.getCalendarView().fullCalendar('removeEvents');
		let view = self.getCalendarView().fullCalendar('getView');
		let start_date = view.start.format();
		let end_date = view.end.format();
		let user = [],
			types;
		if (app.moduleCacheGet('calendar-users')) {
			user = app.moduleCacheGet('calendar-users');
		} else {
			user = CONFIG.userId;
		}
		if (app.moduleCacheGet('calendar-types')) {
			types = app.moduleCacheGet('calendar-types');
		} else {
			types = null;
		}
		if (user.length !== 0 && (types === null || types.length !== 0)) {
			let params = {
				module: CONFIG.module,
				action: 'Calendar',
				mode: 'getEvent',
				start: start_date,
				end: end_date,
				user: user,
				types: types
			};
			AppConnector.request(params).done(function (events) {
				self.getCalendarView().fullCalendar('addEventSource', events.result);
				progressInstance.hide();
			});
		} else {
			self.getCalendarView().fullCalendar('removeEvents');
			progressInstance.hide();
		}
	}

	registerSelect2Event() {
		let self = this;
		$('.siteBarRight .select2').each(function (index) {
			let name = $(this).attr('id');
			let value = app.moduleCacheGet(name);
			let element = $('#' + name);
			if (element.length > 0 && value != null) {
				if (element.prop('tagName') == 'SELECT') {
					element.val(value);
				}
			}
		});
		$('.siteBarRight .select2, .siteBarRight .filterField').off('change');
		App.Fields.Picklist.showSelect2ElementView($('#calendar-users, #calendar-types'));
		$('.siteBarRight .select2, .siteBarRight .filterField').on('change', function () {
			let element = $(this);
			let value = element.val();
			if (value == null) {
				value = '';
			}
			if (element.attr('type') == 'checkbox') {
				value = element.is(':checked');
			}
			app.moduleCacheSet(element.attr('id'), value);
			self.loadCalendarData();
		});
	}

	registerButtonSelectAll() {
		let selectBtn = $('.selectAllBtn');
		selectBtn.on('click', function (e) {
			let selectAllLabel = $(this).find('.selectAll');
			let deselectAllLabel = $(this).find('.deselectAll');
			if (selectAllLabel.hasClass('d-none')) {
				selectAllLabel.removeClass('d-none');
				deselectAllLabel.addClass('d-none');
				$(this).closest('.quickWidget').find('select option').prop("selected", false);
			} else {
				$(this).closest('.quickWidget').find('select option').prop("selected", true);
				deselectAllLabel.removeClass('d-none');
				selectAllLabel.addClass('d-none');
			}
			$(this).closest('.quickWidget').find('select').trigger("change");
		});
	}

	registerAddButton() {
		const self = this;
		$('.js-add').on('click', (e) => {
			self.getCalendarCreateView().done((data) => {
				const headerInstance = new Vtiger_Header_Js();
				headerInstance.handleQuickCreateData(data, {
					callbackFunction: (data) => {
						self.addCalendarEvent(data.result);
					}
				});
			});
		});
	}

	getCalendarCreateView() {
		let self = this;
		let aDeferred = jQuery.Deferred();

		if (this.calendarCreateView !== false) {
			aDeferred.resolve(this.calendarCreateView.clone(true, true));
			return aDeferred.promise();
		}
		let progressInstance = jQuery.progressIndicator();
		this.loadCalendarCreateView().done(function (data) {
			progressInstance.hide();
			self.calendarCreateView = data;
			aDeferred.resolve(data.clone(true, true));
		}).fail(function () {
			progressInstance.hide();
		});
		return aDeferred.promise();
	}

	loadCalendarCreateView() {
		let aDeferred = jQuery.Deferred();
		let moduleName = app.getModuleName();
		let url = 'index.php?module=' + moduleName + '&view=QuickCreateAjax';
		let headerInstance = Vtiger_Header_Js.getInstance();
		headerInstance.getQuickCreateForm(url, moduleName).done(function (data) {
			aDeferred.resolve(jQuery(data));
		}).fail(function (textStatus, errorThrown) {
			aDeferred.reject(textStatus, errorThrown);
		});
		return aDeferred.promise();
	}

	addCalendarEvent(calendarDetails, dateFormat) {
	}

	getCalendarView() {
		if (this.calendarView == false) {
			this.calendarView = this.container.find('.js-calendar__container');
		}
		return this.calendarView;
	}
};

/**
 *  Class representing a calendar with creating events by day click instead of selecting days.
 * @extends Calendar_Js
 */
window.Calendar_Unselectable_Js = class extends Calendar_Js {

	setCalendarModuleOptions() {
		let self = this;
		return {
			allDaySlot: false,
			dayClick: function (date) {
				self.dayClick(date.format());
				self.getCalendarView().fullCalendar('unselect');
			},
			selectable: false
		};
	}

	dayClick(date) {
		let self = this;
		self.getCalendarCreateView().done(function (data) {
			if (data.length <= 0) {
				return;
			}
			let dateFormat = data.find('[name="date_start"]').data('dateFormat').toUpperCase(),
				timeFormat = data.find('[name="time_start"]').data('format'),
				defaultTimeFormat = 'hh:mm A';
			if (timeFormat == 24) {
				defaultTimeFormat = 'HH:mm';
			}
			let startDateInstance = Date.parse(date);
			let startDateString = moment(date).format(dateFormat);
			let startTimeString = moment(date).format(defaultTimeFormat);
			let endDateInstance = Date.parse(date);
			let endDateString = moment(date).format(dateFormat);

			let view = self.getCalendarView().fullCalendar('getView');
			let endTimeString;
			if ('month' == view.name) {
				let diffDays = parseInt((endDateInstance - startDateInstance) / (1000 * 60 * 60 * 24));
				if (diffDays > 1) {
					let defaultFirstHour = app.getMainParams('startHour');
					let explodedTime = defaultFirstHour.split(':');
					startTimeString = explodedTime['0'];
					let defaultLastHour = app.getMainParams('endHour');
					explodedTime = defaultLastHour.split(':');
					endTimeString = explodedTime['0'];
				} else {
					let now = new Date();
					startTimeString = moment(now).format(defaultTimeFormat);
					endTimeString = moment(now).add(15, 'minutes').format(defaultTimeFormat);
				}
			} else {
				endTimeString = moment(endDateInstance).add(30, 'minutes').format(defaultTimeFormat);
			}
			data.find('[name="date_start"]').val(startDateString);
			data.find('[name="due_date"]').val(endDateString);
			data.find('[name="time_start"]').val(startTimeString);
			data.find('[name="time_end"]').val(endTimeString);

			let headerInstance = new Vtiger_Header_Js();
			headerInstance.handleQuickCreateData(data, {
				callbackFunction(data) {
					self.addCalendarEvent(data.result, dateFormat);
				}
			});
		});
	}
};