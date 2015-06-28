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
	}

	public function run() {
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
					$secondsSaved = $matches[0];
				}
				$line = '';
			}
		}
		proc_close($process);
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
}