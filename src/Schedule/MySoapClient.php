<?php
/**
 * Created by PhpStorm.
 * User: Rusinov
 * Date: 29.06.15
 * Time: 23:28
 */

namespace Schedule;

/**
 * Стаб для soap-клиента, предоставляющий один метод получения расписания
 */
class MySoapClient extends \SoapClient {

	public function __construct() {}

	/**
	 * @param $fromTime
	 * @param $toTime
	 * @return string
	 */
	public function getInfoByTime($fromTime, $toTime) {
		return json_encode([
			$fromTime => 'Program 1',
			$toTime => 'Program 2'
		]);
	}
}