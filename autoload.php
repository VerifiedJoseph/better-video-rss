<?php
/*
	Class auto load
*/
spl_autoload_register(function ($name) {
	$name = str_replace('\\', '/', $name);
	require __DIR__ . '/include/' . $name . '.php';
});
