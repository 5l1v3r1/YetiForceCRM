<?php

/**
 * Notifications Dashboard Class
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Notification_NotificationsByRecipient_Dashboard extends Vtiger_IndexAjax_View
{

	/**
	 * Return search params (use to in building address URL to listview)
	 * @param string $owner Name of user
	 * @param array $time
	 * @return string
	 */
	public function getSearchParams($owner, $time)
	{
		$listSearchParams = [];
		$conditions = [];
		if (!empty($time)) {
			$conditions [] = ['createdtime', 'bw', implode(',', $time)];
		}
		if (!empty($owner)) {
			$conditions [] = ['assigned_user_id', 'e', $owner];
		}
		$listSearchParams[] = $conditions;
		return '&viewname=All&search_params=' . json_encode($listSearchParams);
	}

	/**
	 * Function to get data for chart. Return number notification by recipient
	 * @param array $time Contains start and end created time of natification
	 * @return array
	 */
	private function getNotificationByRecipient($time)
	{
		$accessibleUsers = \App\Fields\Owner::getInstance()->getAccessibleUsers();
		$moduleName = 'Notification';
		$listView = Vtiger_Module_Model::getInstance($moduleName)->getListViewUrl();
		$db = PearDatabase::getInstance();
		$time['start'] = DateTimeField::convertToDBFormat($time['start']);
		$time['end'] = DateTimeField::convertToDBFormat($time['end']);
		$query = 'SELECT COUNT(*) AS `count`, smownerid
			FROM vtiger_crmentity 
			WHERE setype = ? AND deleted = ? AND createdtime BETWEEN ? AND ? AND smownerid IN (%s) ' .
			\App\PrivilegeQuery::getAccessConditions($moduleName) .
			' GROUP BY smownerid';
		$query = sprintf($query, generateQuestionMarks($accessibleUsers));
		$params = array_merge([$moduleName, 0, $time['start'], $time['end']], array_keys($accessibleUsers));
		$result = $db->pquery($query, $params);
		$data = [];
		while ($row = $db->getRow($result)) {
			$data [] = [
				$row['count'],
				$accessibleUsers[$row['smownerid']],
				$listView . $this->getSearchParams($row['smownerid'], $time)
			];
		}
		return $data;
	}

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->get('linkid'), Users_Record_Model::getCurrentUserModel()->getId());
		$time = $request->get('time');
		if (empty($time)) {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDate($widget);
			if($time === false) {
				$time['start'] = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
				$time['end'] = date('Y-m-d', mktime(23, 59, 59, date('m') + 1, 0, date('Y')));	
			}
			$time['start'] = \App\Fields\DateTime::currentUserDisplayDate($time['start']);
			$time['end'] = \App\Fields\DateTime::currentUserDisplayDate($time['end']);
		}
		$data = $this->getNotificationByRecipient($time);
		$viewer->assign('DATA', $data);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DTIME', $time);
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/NotificationsBySenderRecipient.tpl', $moduleName);
		}
	}
}
