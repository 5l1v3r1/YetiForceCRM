<?php
/**
 * Clear cache cron
 * @package YetiForce.Cron
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Michał Lorencik <m.lorencik.com>
 */
$limitView = AppConfig::performance('BROWSING_HISTORY_VIEW_LIMIT');

$subQuery = (new App\Db\Query())->select(['sub.view_date'])
	->from(['sub' => 'u_#__browsinghistory'])
	->where('t.userid=sub.userid')
	->orderBy(['sub.view_date' => SORT_ASC])
	->limit(1)
	->offset($limitView);
$result = (new \App\Db\Query())->select(['record_count' => 'count(t.id)', 't.userid', 'view_date' => $subQuery])
	->from(['t' => 'u_#__browsinghistory'])
	->groupBy(['t.userid'])
	->having('record_count > :limit', ['limit' => $limitView])
	->all();

foreach ($result as $record) {
	(new \App\Db\Query())->createCommand()
		->delete('u_#__browsinghistory', 'userid=:userId and view_date < :viewDate', ['userId' => $record['userid'], 'viewDate' => $record['view_date']])
		->execute();
}
