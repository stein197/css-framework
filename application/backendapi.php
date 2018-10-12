<?php
	require_once 'functions.php';
	header('Content-Type: application/json');
	class A{
		function __construct($a){
			$this->a = $a;
		}
		function getA($b){
			return $b ?: $this->a;
		}
	}
	
	$classname = str_replace('.', '\\', $_GET['class']);
	$instance = new $classname(...$_GET['constr']);
	echo $instance->{$_GET['method']}(...$_GET['args']);
	// echo json_encode($_GET, JSON_UNESCAPED_UNICODE);