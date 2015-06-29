<?php
/**
 * Created by PhpStorm.
 * User: Rusinov
 * Date: 29.06.15
 * Time: 22:47
 */

namespace Schedule;

use Core\DB;

/**
 * Класс для получения и сохранения расписаний в заданном интервале времени через soap сервис
 */
class SoapScheduleProvider {

	/**
	 * @var \SoapClient
	 */
	private $scheduleSoapClient;
	/**
	 * @var DB
	 */
	private $db;

	/**
	 * @param \SoapClient $scheduleSoapClient
	 * @param DB $db
	 */
	public function __construct(
		\SoapClient $scheduleSoapClient,
		DB $db
	) {
		$this->scheduleSoapClient = $scheduleSoapClient;
		$this->db = $db;
	}

	/**
	 * Сохраняет расписание передач в заданном интервале в БД
	 * @param \DateTime $fromTime
	 * @param \DateTime $toTime
	 */
	public function saveSchedules(\DateTime $fromTime, \DateTime $toTime) {
		try {
			$programs = $this->scheduleSoapClient->getInfoByTime(
				$fromTime->format('Y-m-d H:i:s'),
				$toTime->format('Y-m-d H:i:s')
			);
			$programsArray = json_decode($programs, true);
		} catch (\Exception $e) {
			// TODO: Залогировать ошибку
			return;
		}
		foreach ($programsArray as $startTime => $program) {
			$this->db->insertArray(
				'programs',
				[
					'time' => $startTime,
					'program' => $program
				]
			);
		}
	}
}