<?php
/**
 * Created by PhpStorm.
 * User: Rusinov
 * Date: 28.06.15
 * Time: 13:55
 * Скрипт для запуска сохранения видео
 */

require 'bootstrap.php';

$worker = $container['StreamSaverWorker'];
$worker->run();