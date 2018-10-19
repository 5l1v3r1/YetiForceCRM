<?php

/**
 * Chat Entries Action Class.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Chat_Room_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Constructor with a list of allowed methods.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getAll');
		$this->exposeMethod('create');
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
	}

	/**
	 * Add entries function.
	 *
	 * @param \App\Request $request
	 */
	public function getAll(\App\Request $request)
	{
		$response = new Vtiger_Response();
		$response->setResult([
		]);
		$response->emit();
	}

	/**
	 * Create new room.
	 *
	 * @param \App\Request $request
	 */
	public function create(\App\Request $request)
	{
		$roomType = $request->getByType('roomType');
		$recordId = $request->getInteger('recordId');
		\App\Chat::createRoom($roomType, $recordId);

		$response = new Vtiger_Response();
		$response->setResult([
		]);
		$response->emit();
	}
}
