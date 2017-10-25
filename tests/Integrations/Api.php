<?php
/**
 * Integrations test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
namespace Tests\Integrations;

class Api extends \Tests\Base
{

	/**
	 * Api server id
	 * @var int
	 */
	private static $serverId;

	/**
	 * Api user id
	 * @var int
	 */
	private static $apiUserId;

	/**
	 * Request options
	 * @var array
	 */
	private static $requestOptions = [
		'auth' => ['portal', 'portal']
	];

	/**
	 * Request headers
	 * @var array
	 */
	private static $requestHeaders = [
		'Content-Type' => 'application/json',
		'X-ENCRYPTED' => 0,
	];

	/**
	 * Details about logged in user
	 * @var array
	 */
	private static $authUserParams;

	/**
	 * Testing add configuration
	 */
	public function testAddConfiguration()
	{
		$webserviceApps = \Settings_WebserviceApps_Record_Model::getCleanInstance();
		$webserviceApps->set('type', 'Portal');
		$webserviceApps->set('status', 1);
		$webserviceApps->set('name', 'portal');
		$webserviceApps->set('acceptable_url', 'http://portal2/');
		$webserviceApps->set('pass', 'portal');
		$webserviceApps->save();
		static::$serverId = $webserviceApps->getId();

		$row = (new \App\Db\Query())->from('w_#__servers')->where(['id' => static::$serverId])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$serverId);
		$this->assertEquals($row['type'], 'Portal');
		$this->assertEquals($row['status'], 1);
		$this->assertEquals($row['name'], 'portal');
		$this->assertEquals($row['pass'], 'portal');
		static::$requestHeaders['X-API-KEY'] = $row['api_key'];

		$webserviceUsers = \Settings_WebserviceUsers_Record_Model::getCleanInstance('Portal');
		$webserviceUsers->save([
			'server_id' => static::$serverId,
			'status' => '1',
			'user_name' => 'demo@yetiforce.com',
			'password_t' => 'demo',
			'type' => '1',
			'language' => 'pl_pl',
			'popupReferenceModule' => 'Contacts',
			'crmid' => 0,
			'crmid_display' => '',
			'user_id' => \App\User::getActiveAdminId(),
		]);
		static::$apiUserId = $webserviceUsers->getId();
		$row = (new \App\Db\Query())->from('w_#__portal_user')->where(['id' => static::$apiUserId])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$apiUserId);
		$this->assertEquals($row['server_id'], static::$serverId);
		$this->assertEquals($row['user_name'], 'demo@yetiforce.com');
		$this->assertEquals($row['password_t'], 'demo');
		$this->assertEquals($row['language'], 'pl_pl');
	}

	/**
	 * Testing login
	 */
	public function testLogIn()
	{
		$request = \Requests::post('http://yeti/api/webservice/Users/Login', static::$requestHeaders, \App\Json::encode([
					'userName' => 'demo@yetiforce.com',
					'password' => 'demo'
				]), static::$requestOptions);
		$response = \App\Json::decode($request->body, 0);
		$this->assertEquals($response->status, 1, $response->error->message);
		$this->authUserParams = $response->result;
		static::$requestHeaders['X-TOKEN'] = $this->authUserParams->token;
	}

	/**
	 * Testing record list
	 */
	public function testAddRecord()
	{
		$recordData = [
			'accountname' => 'Api YetiForce Sp. z o.o.',
			'addresslevel5a' => 'Warszawa',
			'addresslevel8a' => 'Marszałkowska',
			'buildingnumbera' => 111,
			'legal_form' => 'PLL_GENERAL_PARTNERSHIP',
		];
		$request = \Requests::post('http://yeti/api/webservice/Accounts/Record', static::$requestHeaders, \App\Json::encode($recordData), static::$requestOptions);
		$response = \App\Json::decode($request->body, 1);
		$this->assertEquals($response['status'], 1, $response['error']['message']);
	}

	/**
	 * Testing record list
	 */
	public function testRecordList()
	{
		$request = \Requests::get('http://yeti/api/webservice/Accounts/RecordsList', static::$requestHeaders, static::$requestOptions);
		$response = \App\Json::decode($request->body, 1);
		$this->assertEquals($response['status'], 1, $response['error']['message']);
	}

	/**
	 * Testing delete configuration
	 */
	public function testDeleteConfiguration()
	{
		\Settings_WebserviceUsers_Record_Model::getInstanceById(static::$apiUserId, 'Portal')->delete();
		\Settings_WebserviceApps_Record_Model::getInstanceById(static::$serverId)->delete();

		$this->assertFalse(( new \App\Db\Query())->from('w_#__servers')->where(['id' => static::$serverId])->exists(), 'Record in the database should not exist');
		$this->assertFalse(( new \App\Db\Query())->from('w_#__portal_user')->where(['id' => static::$apiUserId])->exists(), 'Record in the database should not exist');
	}
}
