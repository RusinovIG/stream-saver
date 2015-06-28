<?php

$baseDir = __DIR__ . '/src/';

/**
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function ($className) use ($baseDir) {
	$pathToClass = $baseDir . '/' . str_replace('\\', '/', $className).'.php';
	if (file_exists($pathToClass)) {
		require $pathToClass;
		return;
	}
});