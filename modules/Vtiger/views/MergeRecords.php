<?php
/**
 * Merge records view.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Merge records class.
 */
class Vtiger_MergeRecords_View extends \App\Controller\Modal
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'Merge')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public $modalSize = 'modal-fullscreen';

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(\App\Request $request)
	{
		$moduleName = $request->getModule($request);
		$this->modalIcon = 'fa fa-code';
		$this->initializeContent($request);
		parent::preProcessAjax($request);
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->view('MergeRecords.tpl', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function initializeContent(\App\Request $request)
	{
		$count = 0;
		$fields = [];
		$recordModels = [];
		$queryGenerator = Vtiger_Mass_Action::getQuery($request);
		if ($queryGenerator) {
			$moduleModel = $queryGenerator->getModuleModel();
			foreach ($queryGenerator->getModuleFields() as $field) {
				if ($field->isEditable()) {
					$fields[] = $field->getName();
				}
			}
			$queryGenerator->setFields($fields);
			$queryGenerator->setField('id');
			$query = $queryGenerator->createQuery();
			$count = $query->count();
			$dataReader = $query->limit(\AppConfig::performance('MAX_MERGE_RECORDS'))->createCommand()->query();
			while ($row = $dataReader->read()) {
				$recordModels[$row['id']] = $moduleModel->getRecordFromArray($row);
			}
			$dataReader->close();
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('COUNT', $count);
		$viewer->assign('RECORD_MODELS', $recordModels);
		$viewer->assign('FIELDS', $fields);
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcessAjax(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule($request);
		if (($var = $viewer->getTemplateVars('RECORD_MODELS')) && count($var) > 1) {
			$viewer->assign('BTN_SUCCESS', 'LBL_MERGE');
		}
		$viewer->assign('BTN_DANGER', $this->dangerBtn);
		$viewer->view('Modals/Footer.tpl', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPageTitle(\App\Request $request)
	{
		$moduleName = $request->getModule();
		return \App\Language::translate('LBL_MERGE_RECORDS_IN', $moduleName) . ': ' . \App\Language::translate($moduleName, $moduleName);
	}
}
