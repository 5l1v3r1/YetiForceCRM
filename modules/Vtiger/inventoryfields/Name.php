<?php

/**
 * Inventory Name Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Name_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'Name';
	protected $defaultLabel = 'LBL_ITEM_NAME';
	protected $columnName = 'name';
	protected $dbType = 'int DEFAULT 0';
	protected $params = ['modules', 'limit'];
	protected $colSpan = 30;

	/**
	 * Getting value to display
	 * @param type $value
	 * @return type
	 */
	public function getDisplayValue($value)
	{
		if (empty($value)) {
			return '';
		}
		return \App\Record::getLabel($value);
	}

	/**
	 * Getting value to display
	 * @return array
	 */
	public function limitValues()
	{
		return [
			['id' => 0, 'name' => 'LBL_NO'],
			['id' => 1, 'name' => 'LBL_YES']
		];
	}

	public function getConfig()
	{
		return \App\Json::decode($this->get('params'));
	}
}
