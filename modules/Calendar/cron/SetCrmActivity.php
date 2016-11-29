<?php
/**
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'include/main/WebUI.php';
$current_user = Users::getActiveAdminUser();
$db = PearDatabase::getInstance();
$dataReader = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.setype'])
		->from('vtiger_crmentity')
		->innerJoin('vtiger_entity_stats', 'vtiger_entity_stats.crmid = vtiger_crmentity.crmid')
		->where(['and', ['vtiger_crmentity.deleted' => 0], ['not', ['vtiger_entity_stats.crmactivity' => null]]])
		->createCommand()->query();
while ($row = $dataReader->read()) {
	Calendar_Record_Model::setCrmActivity(array_flip([$row['crmid']]), $row['setype']);
}

