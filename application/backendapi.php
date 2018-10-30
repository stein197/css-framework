<?php
	require_once 'functions.php';
	header('Content-Type: application/json');
	// if(empty($_GET)){
	// 	// header('Location: /');
	// 	exit;
	// }
	// function f(){
	// 	return 0x20;
	// }
	echo json_encode(['d' => 2]);
	exit;
	$classname = str_replace('.', '\\', $_GET['class']);
	$instance = new $classname(...$_GET['constr']);
	echo $instance->{$_GET['method']}(...$_GET['args']);
	// echo json_encode($_GET, JSON_UNESCAPED_UNICODE);