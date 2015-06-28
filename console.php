<?php
/**
 * Created by PhpStorm.
 * User: Rusinov
 * Date: 28.06.15
 * Time: 18:41
 */

require 'bootstrap.php';

$application = new \Symfony\Component\Console\Application();
$application->add(new \Console\StreamLoaderCommand($container));
$application->run();