<?php
	require_once 'application/functions.php';
	use \System\{Directory, Path, Log};
	$paths = [
		$_SERVER['DOCUMENT_ROOT'].'/css/styles.css',
		'css/styles.css',
		'/js/app.js'
	];
	$d = new Directory('/test', __DIR__);