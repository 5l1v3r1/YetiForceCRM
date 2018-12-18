<?php

/**
 * YetiForce registration modal view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
class Settings_YetiForce_RegistrationOnlineModal_View extends \App\Controller\ModalSettings
{

	public function preProcessAjax(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$this->pageTitle = \App\Language::translate('YetiForce', $qualifiedModuleName) . ' - ' . \App\Language::translate('LBL_REGISTRATION_ONLINE_MODAL', $qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/**
	 * Process user request.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('REGISTER_COMPANIES', $this->prepareCompanies());
		$viewer->view('RegistrationOnlineModal.tpl', $request->getModule(false));
	}

	public function prepareCompanies(): array
	{
		$data = ['users' => [], 'integrators' => [], 'suppliers' => []];
		foreach (\App\Company::getAll() as $company) {
			switch ($company['type']) {
				case 2:
					$key = 'integrators';
					break;
				case 3:
					$key = 'suppliers';
					break;
				default:
					$key = 'users';
			}
			$data[$key][] = $company;
		}
		return $data;
	}
}
