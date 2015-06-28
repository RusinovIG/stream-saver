<?php
/**
 * Created by PhpStorm.
 * User: Rusinov
 * Date: 28.06.15
 * Time: 14:03
 */

namespace StreamSaver;

/**
 * Класс для запуска скрипта записи видео потока в файл и контроля его выполнения
 */
class Worker {

	/**
	 * @var resource a shared memory segment identifier
	 */
	private static $sharedMemoryId;
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
	 * @param $streamUrl
	 * @param $videoLength
	 * @param $videoFormat
	 * @param $storagePath
	 */
	public function __construct(
		$streamUrl,
		$videoLength,
		$videoFormat,
		$storagePath
	) {
		$this->streamUrl = $streamUrl;
		$this->videoLength = $videoLength;
		$this->videoFormat = $videoFormat;
		$this->storagePath = $storagePath;

		self::$sharedMemoryId = shm_attach(ftok(__FILE__, 'A'));
		shm_put_var(self::$sharedMemoryId, $this->getHashKey('pid'), getmypid());
	}

	/**
	 * @return void
	 * @access public
	 * @final
	 */
	final public function __destruct() {
		if (
			@shm_has_var(self::$sharedMemoryId, $this->getHashKey('pid')) &&
			shm_get_var(self::$sharedMemoryId, $this->getHashKey('pid')) == getmypid()
		) {
			shm_remove(self::$sharedMemoryId);
		}
	}

	public function run() {
		$pid = pcntl_fork();
		if ($pid == -1) {
			throw new \RuntimeException('Could not fork');
		} else if ($pid) {
			return;
		}
		$filePath = $this->getFilePath();
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
		shm_put_var(self::$sharedMemoryId, self::$secondsSavedKey, $value);
	}

	/**
	 * Возвращает количество секунд видео, записанных текущим воркером
	 * @return int
	 */
	public function getSecondsSaved() {
		if (@shm_has_var(self::$sharedMemoryId, self::$secondsSavedKey)) {
			return shm_get_var(self::$sharedMemoryId, self::$secondsSavedKey);
		} else {
			return null;
		}
	}

	/**
	 * Генерирует имя файла видео
	 * @return string
	 */
	private function getFilePath() {
		// Создаем путь вида 2015/06/28
		$pathFromDate = date('Y/m/d/');
		$fileDir = $this->storagePath . $pathFromDate;
		if (!is_dir($fileDir)) {
			mkdir($fileDir, 0777, true);
		}
		$filePath = $fileDir . 'video_' . date('H:i:s') . '.' . $this->videoFormat;
		return $filePath;
	}

	/**
	 * @param string $input
	 * @return int
	 */
	private function getHashKey($input) {
		return crc32($input);
	}
}