/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

class Vtiger_ConditionBuilder_Js {

	/**
	 * Constructor
	 * @param {jQuery} container
	 */
	constructor(container) {
		this.container = container;
	}

	/**
	 * Register events when change operator
	 * @param {jQuery} container
	 */
	registerChangeOperators(container) {
		let self = this;
		container.find('.js-conditions-operator').on('change', function (e) {
			AppConnector.request({
				module: app.getModuleName(),
				view: 'ConditionBuilder',
				fieldname: container.find('.js-conditions-fields').val(),
				operator: $(e.currentTarget).val(),
			}).done(function (data) {
				container.html($(data).html());
				App.Fields.Picklist.showSelect2ElementView(container.find('select.select2'));
				self.registerChangeFields(container);
				self.registerChangeOperators(container);
				self.registerDateFields();
			});
		});
	}

	/**
	 * Register event when the date field is selected
	 */
	registerDateFields() {
		let element = this.container.find('.js-condition-builder-conditions-row .js-date-field');
		if (element.length) {
			App.Fields.Date.register(element);
		}
	}

	/**
	 * Register events when change field
	 * @param {jQuery} container
	 */
	registerChangeFields(container) {
		let self = this;
		container.find('.js-conditions-fields').on('change', function (e) {
			AppConnector.request({
				module: app.getModuleName(),
				view: 'ConditionBuilder',
				fieldname: $(e.currentTarget).val()
			}).done(function (data) {
				container.html($(data).html());
				App.Fields.Picklist.showSelect2ElementView(container.find('select.select2'));
				self.registerChangeFields(container);
				self.registerChangeOperators(container);
				self.registerDateFields();
			});
		});
	}

	/**
	 * Register events to add condition
	 */
	registerAddCondition() {
		let self = this;
		this.container.on('click', '.js-condition-add', function (e) {
			let container = $(e.currentTarget).closest('.js-condition-builder-group-container').find('> .js-condition-builder-conditions-container');
			AppConnector.request({
				module: app.getModuleName(),
				view: 'ConditionBuilder'
			}).done(function (data) {
				data = $(data);
				App.Fields.Picklist.showSelect2ElementView(data.find('select.select2'));
				self.registerChangeFields(data);
				self.registerChangeOperators(data);
				container.append(data);
			});
		});
	}

	/**
	 * Register events to add group
	 */
	registerAddGroup() {
		var self = this;
		this.container.on('click', '.js-group-add', function (e) {
			let template = self.container.find('.js-condition-builder-group-template').clone();
			template.removeClass('hide');
			$(e.currentTarget).closest('.js-condition-builder-group-container').find('> .js-condition-builder-conditions-container').append(template.html());
		});
	}

	/**
	 * Register events to remove group
	 */
	registerDeleteGroup() {
		this.container.on('click', '.js-group-delete', function (e) {
			$(e.currentTarget).closest('.js-condition-builder-group-container').remove();
		});
	};

	/**
	 * Register events to remove condition
	 */
	registerDeleteCondition() {
		this.container.on('click', '.js-condition-delete', function (e) {
			$(e.currentTarget).closest('.js-condition-builder-conditions-row').remove();
		});
	};

	/**
	 * Read conditions in group
	 * @param {jQuery} container
	 * @returns {object}
	 */
	readCondition(container) {
		let self = this;
		let condition = container.find('> .js-condition-switch .js-condition-switch-value').hasClass('active') ? 'AND' : 'OR';
		let arr = {};
		arr['condition'] = condition;
		let rules = [];
		container.find('> .js-condition-builder-conditions-container >').each(function () {
			if ($(this).hasClass('js-condition-builder-conditions-row')) {
				rules.push({
					'fieldname': $(this).find('.js-conditions-fields').val(),
					'operator': $(this).find('.js-conditions-operator').val(),
					'value': $(this).find('.listSearchContributor').val(),
				});
			} else if ($(this).hasClass('js-condition-builder-group-container')) {
				rules.push(self.readCondition($(this)));
			}
		})
		arr['rules'] = rules;
		return arr;
	}

	/**
	 * Returns conditions
	 */
	getConditions() {
		return this.readCondition(this.container.find('> .js-condition-builder-group-container'));
	}

	/**
	 * Main function to regsiter events
	 */
	registerEvents() {
		this.registerAddCondition();
		this.registerAddGroup();
		this.registerDeleteGroup();
		this.registerDeleteCondition();
	}
};
