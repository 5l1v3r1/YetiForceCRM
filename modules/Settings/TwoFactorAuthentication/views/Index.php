<?php
/**
 * Two factor authentication class for config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Settings_TwoFactorAuthentication_Index_View extends Settings_Vtiger_Index_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$userAuthyExceptions = AppConfig::security('USER_AUTHY_TOTP_EXCEPTIONS');
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('AVAILABLE_METHODS', Users_Totp_Authmethod::ALLOWED_USER_AUTHY_MODE);
		$viewer->assign('USER_EXCEPTIONS', $userAuthyExceptions['TOTP'] ?? []);
		$viewer->assign('USER_AUTHY_MODE', AppConfig::security('USER_AUTHY_MODE'));
		$viewer->assign('USER_AUTHY_TOTP_NUMBER_OF_WRONG_ATTEMPTS', AppConfig::security('USER_AUTHY_TOTP_NUMBER_OF_WRONG_ATTEMPTS'));
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
