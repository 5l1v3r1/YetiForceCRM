<?php
/**
 * Chat.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace App;

/**
 * Class Chat.
 */
class Chat
{
	/**
	 * Information about the structure of the database.
	 */
	const DB_INFO = [
		'message' => ['crm' => 'u_#__chat_messages_crm', 'group' => 'u_#__chat_messages_group', 'global' => 'u_#__chat_messages_global'],
		'record' => ['crm' => 'crmid', 'group' => 'groupid', 'global' => 'globalid'],
		'room' => ['crm' => 'u_#__chat_rooms_crm', 'group' => 'u_#__chat_rooms_group', 'global' => 'u_#__chat_rooms_global'],
		'recordRoom' => ['crm' => 'crmid', 'group' => 'groupid', 'global' => 'global_room_id'],
	];

	/**
	 * Type of chat room.
	 *
	 * @var string
	 */
	private $roomType;

	/**
	 * ID of chat room.
	 *
	 * @var int
	 */
	private $roomId;

	/**
	 * ID record associated with the chat room.
	 *
	 * @var int|null
	 */
	private $recordId;

	/**
	 * @var []
	 */
	private $room = false;

	/**
	 * User ID.
	 *
	 * @var int
	 */
	private $userId;

	/**
	 * Last message ID.
	 *
	 * @var int|null
	 */
	private $lastMessageId;

	/**
	 * Set current room ID, type.
	 *
	 * @param string $roomType
	 * @param int    $roomId
	 */
	public static function setCurrentRoom(string $roomType, int $roomId)
	{
		$_SESSION['chat'] = ['roomType' => $roomType, 'roomId' => $roomId];
	}

	/**
	 * Get current room ID, type.
	 *
	 * @return []|false
	 */
	public static function getCurrentRoom()
	{
		if (!isset($_SESSION['chat'])) {
			return false;
		}
		return $_SESSION['chat'];
	}

	/**
	 * Get instance by record model.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return \App\Chat
	 */
	public static function getInstanceByRecordModel(\Vtiger_Record_Model $recordModel): \App\Chat
	{
		$instance = new self();
		return $instance;
	}

	/**
	 * Get instance \App\Chat.
	 *
	 * @param null|string $roomType
	 * @param int|null    $roomId
	 *
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return \App\Chat
	 */
	public static function getInstance(?string $roomType = null, ?int $roomId = null): \App\Chat
	{
		if (empty($roomType) || empty($roomId)) {
			$currentRoom = static::getCurrentRoom();
			if ($currentRoom !== false) {
				$roomType = $currentRoom['roomType'];
				$roomId = $currentRoom['roomId'];
			}
		}
		return new self($roomType, $roomId);
	}

	/**
	 * Global list of chat rooms.
	 *
	 * @return array
	 */
	public static function getRoomsGlobal()
	{
		if (Cache::has('Chat', 'chat_global')) {
			return Cache::get('Chat', 'chat_global');
		}
		return Cache::save('Chat', 'chat_global',
			(new Db\Query())->from('u_#__chat_global')->all()
		);
	}

	/**
	 * List of chat room groups.
	 *
	 * @param int|null $userId
	 *
	 * @return array
	 */
	public static function getRoomsGroup(?int $userId = null)
	{
		if (empty($userId)) {
			$userId = User::getCurrentUserId();
		}
		return (new Db\Query())
			->select(['GR.roomid', 'GR.userid', 'recordid' => 'GR.groupid', 'name' => 'VGR.groupname'])
			->from(['GR' => 'u_#__chat_rooms_group'])
			->innerJoin(['VGR' => 'vtiger_groups'], 'VGR.groupid = GR.groupid')
			->where(['GR.userid' => $userId])
			->all();
	}

	/**
	 * CRM list of chat rooms.
	 *
	 * @param int|null $userId
	 *
	 * @return array
	 */
	public static function getRoomsCrm(?int $userId = null)
	{
		if (empty($userId)) {
			$userId = User::getCurrentUserId();
		}
		return (new Db\Query())
			->select(['C.roomid', 'C.userid', 'recordid' => 'C.crmid', 'name' => 'CL.label'])
			->from(['C' => 'u_#__chat_rooms_crm'])
			->leftJoin(['CL' => 'u_yf_crmentity_label'], 'CL.crmid = C.crmid')
			->where(['C.userid' => $userId])
			->all();
	}

	/**
	 * Get all chat rooms by user.
	 *
	 * @param int|null $userId
	 *
	 * @return array
	 */
	public static function getRoomsByUser(?int $userId = null)
	{
		return [
			'crm' => static::getRoomsCrm($userId),
			'group' => static::getRoomsGroup($userId),
			'global' => static::getRoomsGlobal(),
		];
	}

	/**
	 * Chat constructor.
	 *
	 * @param null|string $roomType
	 * @param int|null    $roomId
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function __construct(?string $roomType, ?int $roomId)
	{
		$this->userId = User::getCurrentUserId();
		if (empty($roomType) || empty($roomId)) {
			return;
		}
		$this->roomType = $roomType;
		$this->roomId = $roomId;
		$this->room = $this->getQueryRoom()->one();
		if ($this->isRoomExists() && isset($this->room['record_id'])) {
			$this->recordId = $this->room['record_id'];
		}
	}

	/**
	 * Get table or column for chat.
	 *
	 * @param string $dbType
	 *
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return string
	 */
	private function getDbInfo(string $dbType): string
	{
		if (!isset(static::DB_INFO[$dbType][$this->roomType])) {
			throw new Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||{$dbType}||{$this->roomType}", 406);
		}
		return static::DB_INFO[$dbType][$this->roomType];
	}

	/**
	 * Check if chat room exists.
	 *
	 * @return bool
	 */
	public function isRoomExists(): bool
	{
		return $this->room !== false;
	}

	/**
	 * Add new message to chat room.
	 *
	 * @param string $message
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function addMessage(string $message): int
	{
		$this->insertMessage($message);
		return $this->lastMessageId;
	}

	/**
	 * Get entries function.
	 *
	 * @param null|int $messageId
	 *
	 * @return array
	 */
	public function getEntries(?int $messageId = null)
	{
		if (!$this->isRoomExists()) {
			return [];
		}
		$query = $this->getQueryMessage();
		if (!\is_null($messageId)) {
			$query->andWhere(['>', 'C.id', $messageId]);
		}
		$rows = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$row['image'] = \Vtiger_Record_Model::getInstanceById($row['userid'], 'Users')->getImage();
			$row['created'] = date('Y-m-d H:i:s', $row['created']);
			$row['time'] = Fields\DateTime::formatToViewDate($row['created']);
			$rows[] = $row;
		}
		$dataReader->close();
		return $rows;
	}

	/**
	 * Get a query for chat messages.
	 *
	 * @return \App\Db\Query
	 */
	private function getQueryMessage(bool $isLimit = true): Db\Query
	{
		$query = null;
		switch ($this->roomType) {
			case 'crm':
				$query = (new Db\Query())
					->select(['C.*', 'U.user_name', 'U.last_name'])
					->from(['C' => 'u_#__chat_messages_crm'])
					->leftJoin(['U' => 'vtiger_users'], 'U.id = C.userid')
					->where(['crmid' => $this->recordId]);
				break;
			case 'group':
				$query = (new Db\Query())
					->select(['C.*', 'U.user_name', 'U.last_name'])
					->from(['C' => 'u_#__chat_messages_group'])
					->leftJoin(['U' => 'vtiger_users'], 'U.id = C.userid')
					->where(['groupid' => $this->recordId]);
				break;
			case 'global':
				$query = (new Db\Query())
					->select(['C.*', 'U.user_name', 'U.last_name'])
					->from(['C' => 'u_#__chat_messages_global'])
					->leftJoin(['U' => 'vtiger_users'], 'U.id = C.userid')
					->where(['globalid' => $this->roomId]);
				break;
			default:
				throw new Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||$this->roomType", 406);
		}
		$query->orderBy(['created' => \SORT_DESC]);
		if ($isLimit) {
			$query->limit(\AppConfig::module('Chat', 'ROWS_LIMIT'));
		}
		return $query;
	}

	/**
	 * Get a query for chat room.
	 *
	 * @return \App\Db\Query
	 */
	private function getQueryRoom(): Db\Query
	{
		switch ($this->roomType) {
			case 'crm':
				return (new Db\Query())
					->select(['CR.roomid', 'CR.userid', 'record_id' => 'CR.crmid', 'CR.last_message'])
					->from(['CR' => 'u_#__chat_rooms_crm'])
					->where(['CR.roomid' => $this->roomId]);
			case 'group':
				return (new Db\Query())
					->select(['CR.roomid', 'CR.userid', 'record_id' => 'CR.groupid', 'CR.last_message'])
					->from(['CR' => 'u_#__chat_rooms_group'])
					->where(['CR.roomid' => $this->roomId]);
			case 'global':
				return (new Db\Query())
					->select(['CR.roomid', 'CR.userid', 'record_id' => 'CR.global_room_id', 'CR.last_message'])
					->from(['CR' => 'u_#__chat_rooms_global'])
					->where(['CR.global_room_id' => $this->roomId]);
		}
		throw new Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||$this->roomType", 406);
	}

	/**
	 * Insert a message to the chat room.
	 *
	 * @param string $message
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	private function insertMessage(string $message): int
	{
		$table = $this->getDbInfo('message');
		$db = Db::getInstance();
		$db->createCommand()->insert($table, [
			'userid' => $this->userId,
			'messages' => $message,
			'created' => date('Y-m-d H:i:s'),
			$this->getDbInfo('record') => $this->recordId
		])->execute();
		return $this->lastMessageId = (int) $db->getLastInsertID("{$table}_id_seq");
	}

	/**
	 * Insert a user to the chat room.
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 */
	private function inserRoom()
	{
		Db::getInstance()->createCommand()->insert($this->getDbInfo('room'), [
			'userid' => $this->userId,
			'last_message' => $this->lastMessageId,
			$this->getDbInfo('recordRoom') => $this->recordId
		])->execute();
	}

	/**
	 * Update last message ID.
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 */
	private function updateRoom()
	{
		Db::getInstance()
			->createCommand()
			->update($this->getDbInfo('room'), ['last_message' => $this->lastMessageId], [
				'roomid' => $this->roomId,
				'userid' => $this->userId
			])->execute();
	}
}
