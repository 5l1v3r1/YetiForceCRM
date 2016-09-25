<?php
/**
 * FCorectingInvoice CRMEntity Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class FCorectingInvoice extends Vtiger_CRMEntity
{

	public var $table_name = 'u_yf_fcorectinginvoice';
	public var $table_index = 'fcorectinginvoiceid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public var $customFieldTable = Array('u_yf_fcorectinginvoicecf', 'fcorectinginvoiceid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public var $tab_name = Array('vtiger_crmentity', 'u_yf_fcorectinginvoice', 'u_yf_fcorectinginvoicecf', 'u_yf_fcorectinginvoice_address');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'u_yf_fcorectinginvoice' => 'fcorectinginvoiceid',
		'u_yf_fcorectinginvoicecf' => 'fcorectinginvoiceid',
		'u_yf_fcorectinginvoice_address' => 'fcorectinginvoiceaddressid'
	);

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public var $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
// tablename should not have prefix 'vtiger_'
		'FL_SUBJECT' => Array('fcorectinginvoice', 'subject'),
		'FL_SALE_DATE' => Array('fcorectinginvoice', 'saledate'),
		'Assigned To' => Array('crmentity', 'smownerid')
	);
	public var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'FL_SUBJECT' => 'subject',
		'FL_SALE_DATE' => 'saledate',
		'Assigned To' => 'assigned_user_id',
	);
// Make the field link to detail view
	public var $list_link_field = 'subject';
// For Popup listview and UI type support
	public var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
// tablename should not have prefix 'vtiger_'
		'FL_SUBJECT' => Array('fcorectinginvoice', 'subject'),
		'FL_SALE_DATE' => Array('fcorectinginvoice', 'saledate'),
		'Assigned To' => Array('vtiger_crmentity', 'assigned_user_id'),
	);
	public var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'FL_SUBJECT' => 'subject',
		'FL_SALE_DATE' => 'saledate',
		'Assigned To' => 'assigned_user_id',
	);
// For Popup window record selection
	public var $popup_fields = Array('subject');
// For Alphabetical search
	public var $def_basicsearch_col = 'subject';
// Column value to use on detail view record text display
	public var $def_detailview_recname = 'subject';
// Used when enabling/disabling the mandatory fields for the module.
// Refers to vtiger_field.fieldname values.
	public var $mandatory_fields = Array('subject', 'assigned_user_id');
	public var $default_order_by = '';
	public var $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function vtlib_handler($moduleName, $eventType)
	{
		$adb = PearDatabase::getInstance();
		if ($eventType == 'module.postinstall') {

		} else if ($eventType == 'module.disabled') {

		} else if ($eventType == 'module.preuninstall') {

		} else if ($eventType == 'module.preupdate') {

		} else if ($eventType == 'module.postupdate') {

		}
	}
}
