<?php
/**
 * Created by PhpStorm.
 * User: Rusinov
 * Date: 29.06.15
 * Time: 22:47
 */

namespace Schedule;

/**
 * Класс для получения и сохранения расписаний в заданном интервале времени через soap сервис
 */
class SoapScheduleProvider {

	/**
	 * @var \SoapClient
	 */
	private $scheduleSoapClient;

	/**
	 * @param \SoapClient $scheduleSoapClient
	 */
	public function __construct(\SoapClient $scheduleSoapClient) {
		$this->scheduleSoapClient = $scheduleSoapClient;
	}

	/**
	 * Сохраняет расписание передач в заданном интервале в БД
	 * @param \DateTime $fromTime
	 * @param \DateTime $toTime
	 */
	public function saveSchedules(\DateTime $fromTime, \DateTime $toTime) {
		try {
			$programs = $this->scheduleSoapClient->getInfoByTime(
				$fromTime->format('Y-m-d H:i'),
				$toTime->format('Y-m-d H:i')
			);
			$programsArray = json_decode($programs, true);
		} catch (\Exception $e) {
			// TODO: Залогировать ошибку
			return;
		}
		// TODO: код сохранения расписания
	}
}