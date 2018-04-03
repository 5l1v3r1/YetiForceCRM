<?php
/**
 * Multi image class to handle files.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Image class to handle files.
 */
class Vtiger_MultiImage_File extends Vtiger_Basic_File
{
	/**
	 * Storage name.
	 *
	 * @var string
	 */
	public $storageName = 'MultiImage';

	/**
	 * File type.
	 *
	 * @var string
	 */
	public $fileType = 'image';
	/**
	 * Default image limit.
	 *
	 * @var int
	 */
	public static $defaultLimit = 10;

	/**
	 * View image.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function get(\App\Request $request)
	{
		if ($request->isEmpty('key', 2)) {
			throw new \App\Exceptions\NoPermitted('Not Acceptable', 406);
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		$key = $request->getByType('key', 2);
		$value =  \App\Json::decode($recordModel->get($request->getByType('field', 2)));
		foreach ($value as $item) {
			if ($item['key'] === $key) {
				$file = \App\Fields\File::loadFromInfo([
					'path' => ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $item['path'],
					'name' => $item['name'],
				]);
				header('Content-Type: ' . $file->getMimeType());
				header('Content-Transfer-Encoding: binary');
				header('Pragma: public');
				header('Cache-Control: max-age=86400');
				header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
				if ($request->getBoolean('download')) {
					header('Content-disposition: attachment; filename="' . $item['name'] . '"');
				}
				readfile($file->getPath());
			}
		}
	}
}
