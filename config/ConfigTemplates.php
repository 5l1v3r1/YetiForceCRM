<?php
/**
 * Main config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'api' => [
		'enabledServices' => [
			'default' => [],
			'description' => 'List of active services. Available: dav, webservices, webservice',
			'validation' => function () {
				$arg = func_get_arg(0);
				return is_array($arg) && empty(array_diff($arg, ['dav', 'webservices', 'webservice']));
			}
		],
		'enableBrowser' => [
			'default' => false,
			'description' => 'Dav configuration. Available: false, true',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'enableCardDAV' => [
			'default' => false,
			'description' => 'Dav configuration. Available: false, true',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'enableCalDAV' => [
			'default' => false,
			'description' => 'Dav configuration. Available: false, true',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'enableWebDAV' => [
			'default' => false,
			'description' => 'Dav configuration. Available: false, true',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'ENCRYPT_DATA_TRANSFER' => [
			'default' => false,
			'description' => 'Webservice config. Available: false, true',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'AUTH_METHOD' => [
			'default' => 'Basic',
			'description' => 'Webservice config.',
			'validation' => function () {
				return func_get_arg(0) === 'Basic';
			}
		],
		'PRIVATE_KEY' => [
			'default' => 'config/private.key',
			'description' => 'Webservice config.',
			'validation' => function () {
				return true;
			}
		],
		'PUBLIC_KEY' => [
			'default' => 'config/public.key',
			'description' => 'Webservice config.',
			'validation' => function () {
				return true;
			}
		]
	]
];
