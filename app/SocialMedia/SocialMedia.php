<?php

namespace App\SocialMedia;

/**
 * SocialMedia class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class SocialMedia
{
	public const ALLOWED_UITYPE = ['twitter' => 313];

	public static function isAllowed($socialMediaType)
	{
		return isset(static::ALLOWED_UITYPE[$socialMediaType]);
	}

	public static function removeAccount($account, $socialMediaType)
	{
	}

	/**
	 * Get social media type. Convert uitype to string.
	 *
	 * @param int $uiType
	 *
	 * @return string|bool
	 */
	public static function getSocialMediaType($uiType)
	{
		if (\in_array($uiType, static::ALLOWED_UITYPE)) {
			return \array_keys(static::ALLOWED_UITYPE, $uiType)[0];
		}
		return false;
	}

	/**
	 * @param $uiType
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \App\SocialMedia\SocialMediaInterface|bool
	 */
	public static function createObjectByUiType($uiType, $accountName)
	{
		$className = static::getClassNameByUitype($uiType);
		if ($className === false) {
			return false;
		}
		return new $className($accountName);
	}

	/**
	 * Get class name.
	 *
	 * @param int $uiType
	 *
	 * @return bool|string
	 */
	public static function getClassNameByUitype($uiType)
	{
		if (\in_array($uiType, static::ALLOWED_UITYPE)) {
			return __NAMESPACE__ . '\\' . ucfirst(\array_keys(static::ALLOWED_UITYPE, $uiType)[0]);
		}
		throw new \App\Exceptions\AppException('Invalid social media type');
		return false;
	}

	/**
	 * Check if it is configured social media by uitype.
	 *
	 * @param int $uiType
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool
	 */
	public static function isConfigured($uiType)
	{
		return call_user_func(static::getClassNameByUitype($uiType) . '::isConfigured');
	}

	/**
	 * @param string|array|null $socialMediaType
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return []
	 */
	public static function getUitypeFromParam($socialMediaType = null)
	{
		if (empty($socialMediaType)) {
			return static::ALLOWED_UITYPE;
		}
		if (!\is_array($socialMediaType)) {
			$socialMediaType = [$socialMediaType];
		}
		$arrUitype = [];
		foreach ($socialMediaType as $val) {
			if (!isset(static::ALLOWED_UITYPE[$val])) {
				throw new \App\Exceptions\AppException('Invalid social media type');
			}
			$arrUitype[$val] = static::ALLOWED_UITYPE[$val];
		}
		return $arrUitype;
	}

	/**
	 * Get all social media accounts.
	 *
	 * @param $socialMediaType
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \App\SocialMedia\SocialMediaInterface|\Generator|void
	 */
	public static function getSocialMediaAccount($socialMediaType)
	{
		$fields = (new \App\Db\Query())
			->select(['columnname', 'tablename', 'uitype'])
			->from('vtiger_field')
			->where(['uitype' => static::getUitypeFromParam($socialMediaType)])
			->andWhere(['presence' => [0, 2]])
			->all();
		if (\count($fields) === 0) {
			return;
		}
		$query = null;
		foreach ($fields as $i => $field) {
			$subQuery = (new \App\Db\Query())
				->select(['account_name' => $field['columnname'], 'uitype' => new \yii\db\Expression($field['uitype'])])
				->distinct()
				->from($field['tablename'])
				->where(['not', [$field['columnname'] => null]])
				->andWhere(['not', [$field['columnname'] => '']]);
			if ($i === 0) {
				$query = $subQuery;
			} else {
				$query->union($subQuery, true);
			}
		}
		$dataReader = $query->createCommand()->query();
		while (($row = $dataReader->read())) {
			yield static::createObjectByUiType((int) $row['uitype'], $row['account_name']);
		}
	}
}
