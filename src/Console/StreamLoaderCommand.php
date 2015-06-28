<?php
/**
 * Created by PhpStorm.
 * User: Rusinov
 * Date: 28.06.15
 * Time: 18:43
 */

namespace Console;

use Pimple\Container;
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
	 * @param Container $container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
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
		$worker = $this->container['StreamSaverWorker'];
		$worker->run();
	}
}