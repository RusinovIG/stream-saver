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

$container['DB'] = function ($c) {
	return new \Core\DB(
		$c['config']['server_name'],
		$c['config']['db_user'],
		$c['config']['db_pass'],
		$c['config']['db_name']
	);
};

$container['StreamSaverWorker'] = $container->factory(function ($c) {
	return new \StreamSaver\Worker(
		$c['config']['stream_url'],
		$c['config']['video_length'],
		$c['config']['video_format'],
		$c['config']['project_root'] . $c['config']['video_dir'],
		$c['DB']
	);
});

// Реальный соап-клиент
//$container['ScheduleSoapClient'] = function ($c) {
//	return new SoapClient(
//		$c['config']['schedule_wsdl_url']
//	);
//};
// Стаб соап-клиента
$container['ScheduleSoapClient'] = function ($c) {
	return new \Schedule\MySoapClient(
		$c['config']['schedule_wsdl_url']
	);
};

$container['SoapScheduleProvider'] = function ($c) {
	return new \Schedule\SoapScheduleProvider(
		$c['ScheduleSoapClient'],
		$c['DB']
	);
};