<?php
/**
 * Created by PhpStorm.
 * User: Rusinov
 * Date: 28.06.15
 * Time: 13:42
 */

require 'autoload.php';
require 'vendor/autoload.php';

$container = new \Pimple\Container();

$container['config'] = function() {
	return new \Core\Config();
};

$container['StreamSaverWorker'] = $container->factory(function ($c) {
	return new \StreamSaver\Worker(
		$c['config']['stream_url'],
		$c['config']['video_length'],
		$c['config']['video_format'],
		$c['config']['project_root'] . $c['config']['video_dir']
	);
});