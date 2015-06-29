<?php
/**
 * Created by PhpStorm.
 * User: Rusinov
 * Date: 28.06.15
 * Time: 14:03
 */

namespace StreamSaver;

use Core\DB;

/**
 * Класс для запуска скрипта записи видео потока в файл и контроля его выполнения
 */
class Worker {

	/**
	 * @var int
	 */
	private static $secondsSavedKey = 1234;
	/**
	 * @var string
	 */
	private $streamUrl;
	/**
	 * @var int
	 */
	private $videoLength;
	/**
	 * @var string
	 */
	private $videoFormat;
	/**
	 * @var string
	 */
	private $storagePath;
	/**
	 * @var resource a shared memory segment identifier
	 */
	private $sharedMemoryId;
	/**
	 * @var DB
	 */
	private $db;

	/**
	 * @param $streamUrl
	 * @param $videoLength
	 * @param $videoFormat
	 * @param $storagePath
	 * @param DB $db
	 */
	public function __construct(
		$streamUrl,
		$videoLength,
		$videoFormat,
		$storagePath,
		DB $db
	) {
		$this->streamUrl = $streamUrl;
		$this->videoLength = $videoLength;
		$this->videoFormat = $videoFormat;
		$this->storagePath = $storagePath;
		$this->db = $db;

		$this->sharedMemoryId = shm_attach(ftok(__FILE__, 'A') . time()%1000);
		shm_put_var($this->sharedMemoryId, $this->getHashKey('pid'), getmypid());
	}

	/**
	 * @return void
	 * @access public
	 * @final
	 */
	final public function __destruct() {
		if (
			@shm_has_var($this->sharedMemoryId, $this->getHashKey('pid')) &&
			shm_get_var($this->sharedMemoryId, $this->getHashKey('pid')) == getmypid()
		) {
			shm_remove($this->sharedMemoryId);

		}
	}

	public function run() {
		$videoStartTime = new \DateTime();
		$videoEndTime = new \DateTime('+' . $this->videoLength . ' seconds');
		$filePath = $this->getFilePath($videoStartTime);
		$this->saveVideoInfo($filePath, $videoStartTime, $videoEndTime);
		$pid = pcntl_fork();
		if ($pid == -1) {
			throw new \RuntimeException('Could not fork');
		} else if ($pid) {
			return;
		}
		$cmd = 'ffmpeg -i ' . $this->streamUrl . ' -strict -2 -t ' . $this->videoLength . ' ' . $filePath . ' 2>&1';
		$descriptors = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w')
		);
		$process = proc_open($cmd, $descriptors, $pipes, __DIR__, []);
		if (!is_resource($process)) {
			throw new \RuntimeException("Can't run process");
		}
		$line = '';
		/*
		 * Т.к. ffmpeg не добавляет корректный перенос строки при выводе прогресса сохранения потока,
		 * fgets выдает весь прогресс одной строкой.
		 * Пришлось считывать поток посимвольно и самому разбивать его на строки
		 *
		 */
		while (!feof($pipes[1])) {
			$char = fgetc($pipes[1]);
			$line .= $char;
			if (in_array($char, ["\n", "\r"])) {
				if (preg_match('/(?<=time=)\d*\.\d+/', $line, $matches)) {
					$this->setSecondsSaved($matches[0]);
				}
				$line = '';
			}
		}
		$this->setSecondsSaved($this->videoLength);
		proc_close($process);
		exit;
	}

	/**
	 * @param int $value
	 */
	public function setSecondsSaved($value) {
		shm_put_var($this->sharedMemoryId, self::$secondsSavedKey, $value);
	}

	/**
	 * Возвращает количество секунд видео, записанных текущим воркером
	 * @return int
	 */
	public function getSecondsSaved() {
		if (@shm_has_var($this->sharedMemoryId, self::$secondsSavedKey)) {
			return shm_get_var($this->sharedMemoryId, self::$secondsSavedKey);
		} else {
			return null;
		}
	}

	/**
	 * Генерирует имя файла видео
	 * @param \DateTime $videoStartTime
	 * @return string
	 */
	private function getFilePath(\DateTime $videoStartTime) {
		// Создаем путь вида 2015/06/28
		$pathFromDate = $videoStartTime->format('Y/m/d/');
		$fileDir = $this->storagePath . $pathFromDate;
		if (!is_dir($fileDir)) {
			mkdir($fileDir, 0777, true);
		}
		$filePath = $fileDir . 'video_' . $videoStartTime->format('H:i:s') . '.' . $this->videoFormat;
		return $filePath;
	}

	/**
	 * @param string $input
	 * @return int
	 */
	private function getHashKey($input) {
		return crc32($input);
	}

	/**
	 * @param $filePath
	 * @param \DateTime $videoStartTime
	 * @param \DateTime $videoEndTime
	 */
	private function saveVideoInfo($filePath, \DateTime $videoStartTime, \DateTime $videoEndTime) {
		$this->db->insertArray(
			'video',
			[
				'start_time' => $videoStartTime->format('Y-m-d H:i:s'),
				'end_time' => $videoEndTime->format('Y-m-d H:i:s'),
				'url' => str_replace($this->storagePath, '', $filePath)
			]
		);
	}
}