<?php
/**
 * Created by PhpStorm.
 * User: Rusinov
 * Date: 28.06.15
 * Time: 23:13
 */

namespace Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SingletonApplication extends Application {

	/**
	 * Runs the current command.
	 *
	 * If an event dispatcher has been attached to the application,
	 * events are also dispatched during the life-cycle of the command.
	 *
	 * @param SingletonCommand $command A Command instance
	 * @param InputInterface $input   An Input instance
	 * @param OutputInterface $output  An Output instance
	 *
	 * @return int 0 if everything went fine, or an error code
	 *
	 * @throws \Exception when the command being run threw an exception
	 */
	protected function doRunCommand(SingletonCommand $command, InputInterface $input, OutputInterface $output) {
		try {
			$command->lock();
			return parent::doRunCommand($command, $input, $output);
		} catch (\Exception $e) {
			$command->unlock();
		}
	}
}