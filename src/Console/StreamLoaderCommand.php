<?php
/**
 * Created by PhpStorm.
 * User: Rusinov
 * Date: 28.06.15
 * Time: 18:43
 */

namespace Console;

use Pimple\Container;
use StreamSaver\Worker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Класс для запуска процесса сохранения видео потока в файлы
 */
class StreamLoaderCommand extends SingletonCommand {

	/**
	 * @var Container
	 */
	private $container;
	/**
	 * @var int
	 */
	private $videoLength;
	/**
	 * @var int
	 */
	private $videoIntersectionLength;

	/**
	 * @param Container $container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
		$this->videoLength = $container['config']['video_length'];
		$this->videoIntersectionLength = $container['config']['video_intersection_length'];

		parent::__construct($container['config']['project_root'] . $container['config']['tmp_dir']);
	}

	protected function configure() {
		$this
			->setName('stream:save')
			->setDescription('Starts to save stream to files');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$i = 0;
		while (($i++) < 5) {
			/** @var Worker $worker */
			$worker = $this->container['StreamSaverWorker'];
			$worker->run();
			$output->writeln('Worker ' . $i . ' started at ' . date('H:i:s Y.m.d'));
			// Если запись идет в реальном времени,
			// результат нет смысла проверять раньше чем через videoLength - videoIntersectionLength секунд
			sleep($this->videoLength - $this->videoIntersectionLength);

			// Далее начинаем проверять процесс выполнения каждую секунду
			while (!$this->isTimeToStartNewProcess($worker->getSecondsSaved())) {
				sleep(1);
			}
		}
	}

	/**
	 * Возвращает true, если до конца записи осталось меньше videoIntersectionLength секунд
	 * и пора запускать сохранение нового файла
	 * @param $secondsSaved
	 * @return bool
	 */
	private function isTimeToStartNewProcess($secondsSaved) {
		return $secondsSaved > $this->videoLength - $this->videoIntersectionLength;
	}
}