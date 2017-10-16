<?php
/**
 * Tools for datetime class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
namespace App\Fields;

/**
 * DateTime class
 */
class Date
{

	public static $jsDateFormat = [
		'dd-mm-yyyy' => 'd-m-Y',
		'mm-dd-yyyy' => 'm-d-Y',
		'yyyy-mm-dd' => 'Y-m-d',
		'dd.mm.yyyy' => 'd.m.Y',
		'mm.dd.yyyy' => 'm.d.Y',
		'yyyy.mm.dd' => 'Y.m.d',
		'dd/mm/yyyy' => 'd/m/Y',
		'mm/dd/yyyy' => 'm/d/Y',
		'yyyy/mm/dd' => 'Y/m/d',
	];

	/**
	 * Current user JS date format.
	 * @param boolean $format
	 * @return boolean|string
	 */
	public static function currentUserJSDateFormat($format = false)
	{
		if ($format) {
			return static::$jsDateFormat[$format];
		} else {
			return static::$jsDateFormat[\App\User::getCurrentUserModel()->getDetail('date_format')];
		}
	}

	/**
	 * This function returns the date in user specified format.
	 * limitation is that mm-dd-yyyy and dd-mm-yyyy will be considered same by this API.
	 * As in the date value is on mm-dd-yyyy and user date format is dd-mm-yyyy then the mm-dd-yyyy
	 * value will be return as the API will be considered as considered as in same format.
	 * this due to the fact that this API tries to consider the where given date is in user date
	 * format. we need a better gauge for this case.
	 * @global Users $current_user
	 * @param Date $cur_date_val the date which should a changed to user date format.
	 * @return Date
	 */
	public static function currentUserDisplayDate($value)
	{
		$date = new \DateTimeField($value);
		return $date->getDisplayDate();
	}

	/**
	 * Convert date to single items 
	 * @param string $date
	 * @param string|bool $format Date format
	 * @return array Array date list($y, $m, $d)
	 */
	public static function explode($date, $format = false)
	{
		if (empty($format)) {
			$format = 'yyyy-mm-dd';
		}
		switch ($format) {
			case 'dd-mm-yyyy': list($d, $m, $y) = explode('-', $date, 3);
				break;
			case 'mm-dd-yyyy': list($m, $d, $y) = explode('-', $date, 3);
				break;
			case 'yyyy-mm-dd': list($y, $m, $d) = explode('-', $date, 3);
				break;
			case 'dd.mm.yyyy': list($d, $m, $y) = explode('.', $date, 3);
				break;
			case 'mm.dd.yyyy': list($m, $d, $y) = explode('.', $date, 3);
				break;
			case 'yyyy.mm.dd': list($y, $m, $d) = explode('.', $date, 3);
				break;
			case 'dd/mm/yyyy': list($d, $m, $y) = explode('/', $date, 3);
				break;
			case 'mm/dd/yyyy': list($m, $d, $y) = explode('/', $date, 3);
				break;
			case 'yyyy/mm/dd': list($y, $m, $d) = explode('/', $date, 3);
				break;
		}
		return [$y, $m, $d];
	}
}
