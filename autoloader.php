<?php
function loadClass($class) {
    $splits = explode('\\', $class);
    $splits[0] = $splits[0] . '/src';
    $file = dirname(__FILE__) . '/' . implode('/', $splits) . '.php';
	if (file_exists($file)) {
		require_once $file;
	}
}
spl_autoload_register('loadClass');