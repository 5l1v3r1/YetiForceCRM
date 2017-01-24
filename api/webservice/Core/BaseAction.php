<?php
namespace Api\Core;

/**
 * Base action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class BaseAction
{

	/** @var string */
	protected $allowedMethod;

	/** @var \Api\Controller */
	public $controller;

	/** @var \App\Base */
	public $session;
	public $user;

	public function checkAction()
	{
		if ((isset($this->allowedMethod) && !in_array($this->controller->method, $this->allowedMethod)) || !method_exists($this, $this->controller->method)) {
			throw new \Api\Core\Exception('Invalid method', 405);
		}
		if (!$this->checkPermission()) {
			throw new \Api\Core\Exception('Invalid permission', 401);
		}
		/*
		  $acceptableUrl = $this->controller->app['acceptable_url'];
		  if ($acceptableUrl && rtrim($this->controller->app['acceptable_url'], '/') != rtrim($params['fromUrl'], '/')) {
		  throw new \Api\Core\Exception('LBL_INVALID_SERVER_URL', 401);
		  }
		 */
		return true;
	}

	public function checkPermission()
	{
		if (empty($this->controller->headers['X-TOKEN'])) {
			throw new \Api\Core\Exception('Invalid token', 401);
		}
		$apiType = strtolower($this->controller->app['type']);
		$sessionTable = "w_#__{$apiType}_session";
		$userTable = "w_#__{$apiType}_user";
		$db = \App\Db::getInstance('webservice');
		$row = (new \App\Db\Query())->select(["$sessionTable.*"])
				->from($sessionTable)->innerJoin($userTable, "$sessionTable.user_id = $userTable.id")
				->where(["$sessionTable.id" => $this->controller->headers['X-TOKEN'], "$userTable.status" => 1])->one($db);
		if (empty($row)) {
			throw new Core\Exception('Invalid token', 401);
		}
		$this->session = new \App\Base();
		$this->session->setData($row);
		return true;
	}

	public function preProcess()
	{
		$language = $this->getLanguage();
		if ($language) {
			\Vtiger_Language_Handler::$language = $language;
		}
	}

	public function getLanguage()
	{
		$language = '';
		if (!empty($this->controller->headers['Accept-Language'])) {
			$language = $this->controller->headers['Accept-Language'];
		}
		if ($this->session && !$this->session->isEmpty('language')) {
			$language = $this->session->get('language');
		}
		return $language;
	}
}
