<?php

/**
 * Relation action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailView_Relation_Action extends Vtiger_Action_Controller
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($moduleName)) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		if (!\App\Privilege::isPermitted($moduleName, 'ReloadRelationRecord')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		if (!\App\Privilege::isPermitted($request->get('moduleName'), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$recordModel->setReloadRelationRecord($request->get('moduleName'), $request->getInteger('record'));

		$response = new Vtiger_Response();
		$response->setResult(\App\Language::translate('LBL_SET_RELOAD_RELATIONS', $moduleName));
		$response->emit();
	}
}
