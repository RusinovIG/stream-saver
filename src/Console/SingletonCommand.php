<?php
/**
 * Created by PhpStorm.
 * User: Rusinov
 * Date: 28.06.15
 * Time: 19:02
 */

namespace Console;

use Symfony\Component\Console\Command\Command;

/**
 * Добавляем в стандартную команду Symfony проверку, что команда запущена только 1 раз
 */
class SingletonCommand extends Command {

	/**
	 * Путь к директории с lock-файлами команд
	 * @var string
	 */
	protected $lockFilePath;
	/**
	 * @var resource
	 */
	private $lock;
	/**
	 * @var
	 */
	private $initialPid;

	/**
	 * @param null|string $lockFilePath
	 * @param $name
	 */
	public function __construct(
		$lockFilePath,
		$name = null
	) {
		$this->lockFilePath = $lockFilePath;
		$this->initialPid = getmypid();
		$this->lock();
		parent::__construct($name);
	}

	/**
	 * При завершении команды - разблокируем lock-файл
	 */
	public function __destruct() {
		$this->unlock();
	}

	public function lock() {
		$path = $this->getLockFilePath();
		if (!$this->lock = fopen($path, "w")) {
			throw new \RuntimeException('Can\t create lock for command: ' . $this->getName());
		}
		# Attempt to lock the file we've just opened
		if (!flock($this->lock, LOCK_EX | LOCK_NB)) {
			fclose($this->lock);
			throw new \RuntimeException('Command ' . $this->getName() . ' already running');
		}
	}

	/**
	 * Разблокируем lock-файл при завершении команды
	 * @return void
	 */
	public function unlock() {
		if (!$this->lock) {
			return;
		}
		// Если завершается неосновной поток, разблокировать lock-файл рано
		if ($this->initialPid != getmypid()) {
			return;
		}
		flock($this->lock, LOCK_UN);
		fclose($this->lock);
		$lockFile = $this->getLockFilePath();
		if (file_exists($lockFile)) {
			unlink($lockFile);
		}
	}

	/**
	 * Возвращает полный путь до lock-файла текущей команды
	 * @return string
	 */
	private function getLockFilePath() {
		return $this->lockFilePath . static::class;
	}
}