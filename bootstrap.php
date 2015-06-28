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
