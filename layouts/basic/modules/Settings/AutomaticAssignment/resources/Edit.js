/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class('Settings_AutomaticAssignment_Edit_Js', {}, {
	container: false,
	getContainer: function () {
		if (this.container == false) {
			this.container = jQuery('div.contentsDiv');
		}
		return this.container;
	},
	registerBasicEvents: function (container) {
		var thisInstance = this;
		var form = container.find('form');
		if (form.length) {
			form.validationEngine(app.validationEngineOptions);
			form.find(":input").inputmask();
		}
		container.find('.select2noactive').each(function (index, domElement) {
			var select = $(domElement);
			if (!select.data('select2')) {
				app.showSelect2ElementView(select, {placeholder: app.vtranslate('JS_SELECT_AN_OPTION')});
			}
		});
		var table = app.registerDataTables(container.find('.dataTable'));
		if (table) {
			table.$('.changeRoleType').on('click', function (e) {
				e.stopPropagation();
				e.preventDefault();
				var element = jQuery(e.currentTarget);
				var dataElement = element.closest('tr');
				app.saveAjax('changeRoleType', dataElement.data('value'), {'record': app.getMainParams('record')}).then(function (data) {
					thisInstance.refreshTab();
				});
			});
			table.$('.delete').on('click', function (e) {
				e.stopPropagation();
				e.preventDefault();
				var element = jQuery(e.currentTarget);
				var dataElement = element.closest('tr');
				var params = {
					record: app.getMainParams('record'),
					value: dataElement.data('value'),
					name: dataElement.data('name')
				};
				app.saveAjax('deleteElement', null, params).then(function (data) {
					thisInstance.refreshTab();
				});
			});
		}
		container.find('.fieldContainer').on('click', function (e) {
			e.stopPropagation();
			e.preventDefault();
		});

		container.find('.saveValue').on('click', function (e) {
			var button = jQuery(e.currentTarget);
			var fieldContainer = button.closest('.fieldContainer');
			var fieldName = fieldContainer.data('name');
			var baseFieldName = fieldContainer.data('dbname');
			var fieldElement = fieldContainer.find('[name="' + fieldName + '"]');
			if (fieldElement.validationEngine('validate')) {
				return false;
			}
			var params = [];
			params[baseFieldName] = fieldElement.val();
			app.saveAjax('save', jQuery.extend({}, params), {'record': app.getMainParams('record')}).then(function (respons) {
				thisInstance.refreshTab();
			});
		});
	},
	refreshTab: function () {
		var thisInstance = this;
		var tabContainer = this.getContainer().find('.tab-pane.active');
		AppConnector.request(tabContainer.data('url')).then(
				function (data) {
					tabContainer.html(data);
					thisInstance.registerBasicEvents(tabContainer.closest('.tab-content'));
				},
				function (textStatus, errorThrown) {
					app.errorLog(textStatus, errorThrown);
				}
		);
	},
	registerEvents: function () {
		this.registerBasicEvents(this.getContainer());
	}
})
