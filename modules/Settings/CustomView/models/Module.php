<?php

/**
 * CustomView module model class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_CustomView_Module_Model extends Settings_Vtiger_Module_Model
{

	public function getCustomViews($tabId)
	{
		$db = new App\db\Query();
		$db->select('vtiger_customview.*')->from('vtiger_customview')->leftJoin('vtiger_tab', 'vtiger_tab.name = vtiger_customview.entitytype')
			->where(['vtiger_tab.tabid' => $tabId])->orderBy(['vtiger_customview.sequence' => SORT_ASC]);
		$moduleEntity = [];
		$dataReader = $db->createCommand()->query();
		while ($row = $dataReader->read()) {
			$moduleEntity[$row['cvid']] = $row;
		}
		return $moduleEntity;
	}

	public function getFilterPermissionsView($cvId, $action)
	{
		$db = new App\db\Query();
		if ($action == 'default') {
			$db->select('userid')->from('vtiger_user_module_preferences')->where(['default_cvid' => $cvId])->orderBy(['userid' => SORT_ASC]);
		} elseif ($action == 'featured') {
			$db->select('user')->from('a_yf_featured_filter')->where(['cvid' => $cvId])->orderBy(['user' => SORT_ASC]);
		}
		
		$dataReader = $db->createCommand()->query();
		$users = [];
		while ($user = $dataReader->read()) {
			$user = reset($user);
			$members = explode(':', $user);
			$users[$members[0]][] = $user;
		}
		return $users;
	}

	public function setDefaultUsersFilterView($tabid, $cvId, $user, $action)
	{
		if ($action == 'add') {
			$db = new App\db\Query();
			$db->select('vtiger_customview.viewname')->from('vtiger_user_module_preferences')->leftJoin('vtiger_customview', 'vtiger_user_module_preferences.default_cvid = vtiger_customview.cvid')
				->where(['vtiger_user_module_preferences.tabid' => $tabid, 'vtiger_user_module_preferences.userid' => $user]);
			$dataReader = $db->createCommand()->query();
			
			if ($dataReader->count()) {
				$result = $dataReader->read();
				return $result['viewname'];
			}
			$db = \App\DB::getInstance();
			$db->createCommand()->insert('vtiger_user_module_preferences', [
				'userid' => $user,
				'tabid' => $tabid,
				'default_cvid' => $cvId
			])->execute();
		} elseif ($action == 'remove') {
			$db = \App\DB::getInstance();
			$db->createCommand()->delete('vtiger_user_module_preferences', ['userid' => $user, 'tabid' => $tabid, 'default_cvid' => $cvId])->execute();
		}
		return false;
	}

	public static function setFeaturedFilterView($cvId, $user, $action)
	{
		$db = \App\DB::getInstance();
		if ($action == 'add') {
			$db->createCommand()->insert('a_yf_featured_filter', [
				'user' => $user,
				'cvid' => $cvId
			])->execute();
		} elseif ($action == 'remove') {
			$db->createCommand()->delete('a_yf_featured_filter', ['user' => $user, 'cvid' =>$cvId])->execute();
		}
		return false;
	}

	public function delete($params)
	{
		$db = \App\DB::getInstance();
		$cvId = $params['cvid'];
		if (is_numeric($cvId)) {
			$db->createCommand()->delete('vtiger_customview', ['cvid' => $cvId])->execute();
			$db->createCommand()->delete('vtiger_user_module_preferences', ['default_cvid' => $cvId])->execute();
			// To Delete the mini list widget associated with the filter 
			$db->createCommand()->delete('vtiger_module_dashboard_widgets', ['filterid' => $cvId])->execute();
		}
	}

	public static function updateField($params)
	{
		$authorizedFields = ['setdefault', 'privileges', 'featured', 'sort'];
		$db = \App\DB::getInstance();
		$cvid = $params['cvid'];
		$name = $params['name'];
		$mod = $params['mod'];
		if (is_numeric($cvid) && in_array($name, $authorizedFields)) {
			if ($name == 'setdefault' && $params['value'] == 1) {
				$db->createCommand()->update('vtiger_customview', ['setdefault' => 0], ['entitytype' => $mod])->execute();
			}
			$db->createCommand()->update('vtiger_customview', [$name => $params['value']], ['cvid' => $cvid])->execute();
			return true;
		} else {
			return false;
		}
	}

	public static function upadteSequences($params)
	{
		$db = PearDatabase::getInstance();
		$sql = 'UPDATE vtiger_customview SET `sequence` = CASE ';
		foreach ($params as $sequence => $cvId) {
			$sql .= " WHEN `cvid` = $cvId THEN $sequence";
		}
		$sql .= ' END WHERE `cvid` IN (' . implode(',', $params) . ')';
		return $db->query($sql);
	}

	public function GetUrlToEdit($module, $record)
	{
		return "module=CustomView&view=EditAjax&source_module=$module&record=$record";
	}

	public function getCreateFilterUrl($module)
	{
		return 'index.php?module=CustomView&view=EditAjax&source_module=' . $module;
	}

	public function getUrlDefaultUsers($module, $cvid, $isDefault)
	{
		return 'index.php?module=CustomView&parent=Settings&view=FilterPermissions&type=default&sourceModule=' . $module . '&cvid=' . $cvid . '&isDefault=' . $isDefault;
	}

	public function getFeaturedFilterUrl($module, $cvid)
	{
		return 'index.php?module=CustomView&parent=Settings&view=FilterPermissions&type=featured&sourceModule=' . $module . '&cvid=' . $cvid;
	}

	public function getSortingFilterUrl($module, $cvid)
	{
		return 'index.php?module=CustomView&parent=Settings&view=Sorting&type=featured&sourceModule=' . $module . '&cvid=' . $cvid;
	}

	public static function getSupportedModules()
	{
		$db = new App\db\Query();
		$modulesList = [];
		$db->select(['vtiger_tab.tabid', 'vtiger_customview.entitytype'])->from('vtiger_customview')->leftJoin('vtiger_tab', 'vtiger_tab.name = vtiger_customview.entitytype')->distinct();
		$dataReader = $db->createCommand()->query();
		while ($row = $dataReader->read()) {
			$modulesList[$row['tabid']] = $row['entitytype'];
		}
		return $modulesList;
	}

	public static function updateOrderAndSort($params)
	{
		$customViewModel = CustomView_Record_Model::getInstanceById($params['cvid']);
		$moduleName = $customViewModel->get('entitytype');
		$curretView = ListViewSession::getCurrentView($moduleName);
		if ($curretView == $params['cvid']) {
			$sortOrder = explode(',', $params['value']);
			ListViewSession::setSorder($moduleName, $sortOrder[1]);
			ListViewSession::setSortby($moduleName, $sortOrder[0]);
		}
	}
}
