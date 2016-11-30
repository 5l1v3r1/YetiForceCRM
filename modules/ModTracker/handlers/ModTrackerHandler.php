<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */
require_once dirname(__FILE__) . '/../ModTracker.php';
require_once 'include/events/VTEntityDelta.php';

class ModTracker_ModTrackerHandler_Handler
{

	/**
	 * EntityAfterLink handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterLink(App\EventHandler $eventHandler)
	{
		$params = $eventHandler->getParams();
		$moduleName = $params['destinationModule'];
		if (!ModTracker::isTrackingEnabledForModule($moduleName)) {
			return false;
		}
		ModTracker::linkRelation($params['sourceModule'], $params['sourceRecordId'], $params['destinationModule'], $params['destinationRecordId']);
		if (AppConfig::module('ModTracker', 'WATCHDOG')) {
			$watchdogTitle = 'LBL_ADDED';
			$watchdogMessage = '<a href="index.php?module=' . $params['sourceModule'] . '&view=Detail&record=' . $params['sourceRecordId'] . '">' . vtlib\Functions::getCRMRecordLabel($params['sourceRecordId']) . '</a>';
			$watchdogMessage .= ' (translate: [LBL_WITH]) ';
			$watchdogMessage .= '<a href="index.php?module=' . $params['destinationModule'] . '&view=Detail&record=' . $params['destinationRecordId'] . '">(general: RecordLabel)</a>';
			$this->addNotification($eventHandler, $watchdogTitle, $watchdogMessage);
		}
	}

	/**
	 * Add notification
	 * @param App\EventHandler $eventHandler
	 * @param string $watchdogTitle
	 * @param string $watchdogMessage
	 */
	public function addNotification(App\EventHandler $eventHandler, $watchdogTitle, $watchdogMessage)
	{
		if ($watchdogTitle) {
			$currentUser = \App\User::getCurrentUserModel();
			$params = $eventHandler->getParams();
			$recordId = $params['destinationRecordId'];
			$moduleName = $params['destinationModule'];
			$watchdogTitle = '(translate: [' . $watchdogTitle . '|||ModTracker]) (general: RecordLabel)';
			$watchdogTitle = $currentUser->getName() . ' ' . $watchdogTitle;
			$watchdog = Vtiger_Watchdog_Model::getInstanceById($recordId, $moduleName);
			$users = $watchdog->getWatchingUsers([\App\User::getCurrentUserRealId()]);
			if (!empty($users)) {
				$relatedField = Vtiger_ModulesHierarchy_Model::getMappingRelatedField($moduleName);
				if ($relatedField) {
					$notification = Vtiger_Record_Model::getCleanInstance('Notification');
					$notification->set('shownerid', $users);
					$notification->set($relatedField, $recordId);
					$notification->set('title', $watchdogTitle);
					$notification->set('description', $watchdogMessage);
					$notification->set('notification_type', $watchdog->noticeDefaultType);
					$notification->set('notification_status', 'PLL_UNREAD');
					$notification->save();
				}
			}
		}
	}
}

class ModTrackerHandler extends VTEventHandler
{

	public function handleEvent($eventName, $data)
	{
		$adb = PearDatabase::getInstance();


		if (!is_object($data)) {
			$extendedData = $data;
			$data = $extendedData['entityData'];
		}
		$moduleName = $data->getModuleName();
		$flag = ModTracker::isTrackingEnabledForModule($moduleName);

		if ($flag) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$watchdogTitle = $watchdogMessage = '';
			$db = \App\Db::getInstance();
			switch ($eventName) {
				case 'vtiger.entity.aftersave.final':
					$recordId = $data->getId();
					$columnFields = $data->getData();
					$vtEntityDelta = new VTEntityDelta();
					$delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId, true);

					$newerEntity = $vtEntityDelta->getNewEntity($moduleName, $recordId);
					$newerColumnFields = $newerEntity->getData();
					$newerColumnFields = array_change_key_case($newerColumnFields, CASE_LOWER);
					$delta = array_change_key_case($delta, CASE_LOWER);
					if (is_array($delta)) {
						$inserted = false;
						foreach ($delta as $fieldName => $values) {
							if (!in_array($fieldName, ['modifiedtime', 'modifiedby'])) {
								if (!$inserted) {
									$checkRecordPresentResult = $adb->pquery('SELECT * FROM vtiger_modtracker_basic WHERE crmid = ?', array($recordId));
									if (!$adb->num_rows($checkRecordPresentResult) && $data->isNew()) {
										$status = ModTracker::$CREATED;
										$watchdogTitle = 'LBL_CREATED';
										$watchdogMessage = '(recordChanges: listOfAllValues)';
									} else {
										$status = ModTracker::$UPDATED;
										$watchdogTitle = 'LBL_UPDATED';
										$watchdogMessage = '(recordChanges: listOfAllChanges)';
									}
									$db->createCommand()
										->insert('vtiger_modtracker_basic', [
											'crmid' => $recordId,
											'module' => $moduleName,
											'whodid' => $currentUser->getRealId(),
											'changedon' => $newerColumnFields['modifiedtime'],
											'status' => $status,
											'last_reviewed_users' => '#' . $currentUser->getRealId() . '#'
										])->execute();
									$this->id = $db->getLastInsertID('vtiger_modtracker_basic_id_seq');
									if ($status != ModTracker::$CREATED) {
										ModTracker_Record_Model::unsetReviewed($recordId, $currentUser->getRealId(), $this->id);
									}
									$inserted = true;
								}
								$db->createCommand()->insert('vtiger_modtracker_detail', [
									'id' => $this->id,
									'fieldname' => $fieldName,
									'prevalue' => $values['oldValue'],
									'postvalue' => $values['currentValue']
								])->execute();
							}
						}
					}
					$isExists = (new \App\Db\Query())->from('vtiger_crmentity')->where(['crmid' => $recordId])->andWhere(['<>', 'smownerid', $currentUser->getRealId()])->exists();
					if ($isExists) {
						$db->createCommand()->update('vtiger_crmentity', ['was_read' => 0,], ['crmid' => $recordId])->execute();
					}

					break;
				case 'vtiger.entity.beforedelete':

					$recordId = $data->getId();
					$columnFields = $data->getData();
					$db->createCommand()->insert('vtiger_modtracker_basic', [
						'crmid' => $recordId,
						'module' => $moduleName,
						'whodid' => $currentUser->getRealId(),
						'changedon' => date('Y-m-d H:i:s', time()),
						'status' => ModTracker::$DELETED,
						'last_reviewed_users' => '#' . $currentUser->getRealId() . '#'
					])->execute();
					$id = $db->getLastInsertID('vtiger_modtracker_basic_id_seq');
					ModTracker_Record_Model::unsetReviewed($recordId, $currentUser->getRealId(), $id);

					$isExists = (new \App\Db\Query())->from('vtiger_crmentity')->where(['crmid' => $recordId])->andWhere(['<>', 'smownerid', $currentUser->getRealId()])->exists();
					if ($isExists) {
						$db->createCommand()->update('vtiger_crmentity', ['was_read' => 0,], ['crmid' => $recordId])->execute();
					}
					$watchdogTitle = 'LBL_REMOVED';
					$watchdogMessage = '(recordChanges: listOfAllChanges)';

					break;
				case 'vtiger.entity.afterrestore':
					$recordId = $data->getId();
					$columnFields = $data->getData();
					$db->createCommand()->insert('vtiger_modtracker_basic', [
						'crmid' => $recordId,
						'module' => $moduleName,
						'whodid' => $currentUser->getRealId(),
						'changedon' => date('Y-m-d H:i:s', time()),
						'status' => ModTracker::$RESTORED,
						'last_reviewed_users' => '#' . $currentUser->getRealId() . '#'
					])->execute();
					$id = $db->getLastInsertID('vtiger_modtracker_basic_id_seq');
					ModTracker_Record_Model::unsetReviewed($recordId, $currentUser->getRealId(), $id);
					$isExists = (new \App\Db\Query())->from('vtiger_crmentity')->where(['crmid' => $recordId])->andWhere(['<>', 'smownerid', $currentUser->getRealId()])->exists();
					if ($isExists) {
						$db->createCommand()->update('vtiger_crmentity', ['was_read' => 0,], ['crmid' => $recordId])->execute();
					}
					$watchdogTitle = 'LBL_RESTORED';

					break;
				case 'vtiger.entity.unlink.after':

					$recordId = $extendedData['destinationRecordId'];
					$moduleName = $extendedData['destinationModule'];
					ModTracker::unLinkRelation($extendedData['sourceModule'], $extendedData['sourceRecordId'], $extendedData['destinationModule'], $extendedData['destinationRecordId']);
					$watchdogTitle = 'LBL_REMOVED';
					if (AppConfig::module('ModTracker', 'WATCHDOG')) {
						$watchdogMessage = '<a href="index.php?module=' . $extendedData['sourceModule'] . '&view=Detail&record=' . $extendedData['sourceRecordId'] . '">' . vtlib\Functions::getCRMRecordLabel($extendedData['sourceRecordId']) . '</a>';
						$watchdogMessage .= ' (translate: [LBL_WITH]) ';
						$watchdogMessage .= '<a href="index.php?module=' . $extendedData['destinationModule'] . '&view=Detail&record=' . $extendedData['destinationRecordId'] . '">(general: RecordLabel)</a>';
					}

					break;
				case 'entity.convertlead.after':
					// TODU
					break;
				case 'vtiger.view.detail.before':
					$recordId = $data->getId();
					$db->createCommand()->insert('vtiger_modtracker_basic', [
						'crmid' => $recordId,
						'module' => $moduleName,
						'whodid' => $currentUser->getRealId(),
						'changedon' => date('Y-m-d H:i:s', time()),
						'status' => ModTracker::$DISPLAYED
					])->execute();
					break;
			}
			if (AppConfig::module('ModTracker', 'WATCHDOG') && $watchdogTitle != '') {
				$watchdogTitle = '(translate: [' . $watchdogTitle . '|||ModTracker]) (general: RecordLabel)';
				$watchdogTitle = $currentUser->getName() . ' ' . $watchdogTitle;
				$watchdog = Vtiger_Watchdog_Model::getInstanceById($recordId, $moduleName);
				$users = $watchdog->getWatchingUsers([$currentUser->getRealId()]);
				if (!empty($users)) {
					$relatedField = Vtiger_ModulesHierarchy_Model::getMappingRelatedField($moduleName);
					if ($relatedField !== false) {
						$notification = Vtiger_Record_Model::getCleanInstance('Notification');
						$notification->set('shownerid', $users);
						$notification->set($relatedField, $recordId);
						$notification->set('title', $watchdogTitle);
						$notification->set('description', $watchdogMessage);
						$notification->set('notification_type', $watchdog->noticeDefaultType);
						$notification->set('notification_status', 'PLL_UNREAD');
						$notification->save();
					}
				}
			}
		}
	}
}
