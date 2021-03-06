<?php
/**
 * Main class to integration with magento.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento;

use App\Exceptions\AppException;

/**
 * Magento class.
 */
class Controller
{
	/**
	 * Config.
	 *
	 * @var \App\Integrations\Magento\Config
	 */
	public $config;
	/**
	 * Connector with magento.
	 */
	public $connector;

	/**
	 * Returns connector.
	 *
	 * @return object
	 */
	public function getConnector()
	{
		if (empty($this->connector)) {
			$className = '\\App\\Integrations\\Magento\\Connector\\' . $this->config->get('connector') ?? 'Token';
			if (!class_exists($className)) {
				throw new \App\Exceptions\AppException('ERR_CLASS_NOT_FOUND');
			}
			$this->connector = new $className($this->config);
			if (!$this->connector instanceof \App\Integrations\Magento\Connector\Base) {
				throw new AppException('ERR_CLASS_MUST_BE||\App\Integrations\Magento\Connector\Base');
			}
		}
		return $this->connector;
	}

	/**
	 * Constructor. Connect with magento and authorize.
	 *
	 * @param int $serverId
	 */
	public function __construct(int $serverId)
	{
		$this->config = \App\Integrations\Magento\Config::getInstance($serverId);
		$this->getConnector()->authorize();
	}

	/**
	 * Synchronize categories for products.
	 *
	 * @return void
	 */
	public function synchronizeCategories(): void
	{
		$categorySynchronizator = new Synchronizator\Category($this);
		$categorySynchronizator->process();
	}

	/**
	 * Synchronize currencies for products.
	 *
	 * @return void
	 */
	public function synchronizeCurrencies(): void
	{
		$currencySynchronizator = new Synchronizator\Currency($this);
		$currencySynchronizator->process();
	}

	/**
	 * Synchronize products.
	 *
	 * @return void
	 */
	public function synchronizeProducts(): void
	{
		$productSynchronizator = new Synchronizator\Product($this);
		$productSynchronizator->process();
	}

	/**
	 * Synchronize products.
	 *
	 * @throws AppException
	 */
	public function synchronizeInvoices(): void
	{
		// $invoiceSynchronizator = new Synchronizator\Invoice($this);
		// $invoiceSynchronizator->process();
	}

	/**
	 * Synchronize orders.
	 *
	 * @throws AppException
	 */
	public function synchronizeOrders(): void
	{
		$orderSynchronizator = new Synchronizator\Order($this);
		$orderSynchronizator->process();
	}

	/**
	 * Synchronize orders.
	 *
	 * @throws AppException
	 */
	public function synchronizeCustomers(): void
	{
		$customerSynchronizator = new Synchronizator\Customer($this);
		$customerSynchronizator->process();
	}

	/**
	 * Update inventory stock in all magento.
	 *
	 * @param int    $storageId
	 * @param int[]  $products
	 * @param string $operator
	 *
	 * @return void
	 */
	public static function updateStock(int $storageId, array $products): void
	{
		foreach (Config::getAllServers() as $serverId => $config) {
			if (0 === (int) $config['status'] || 'None' === $config['storage_quantity_location']) {
				continue;
			}
			$customerSynchronizator = new Synchronizator\InventoryStock(new self($serverId));
			$customerSynchronizator->storageId = $storageId;
			$customerSynchronizator->products = $products;
			$customerSynchronizator->process();
		}
	}
}
