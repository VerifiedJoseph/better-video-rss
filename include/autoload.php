<?php

spl_autoload_register(function ($class_name) {
	require __DIR__ . '/' . $class_name . '.class.php';
});
