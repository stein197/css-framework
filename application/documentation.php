<?php
	/* 
		$_GET[ns] - название пространства имен
		$_GET[class] - название класса
	*/
	require_once 'functions.php';
	function getParentChain(string $classname){

	}
	use System\Log;
	if(!isset($_GET['ns']) || !isset($_GET['class'])){
		exit;
	}
	$fullpath = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.str_replace('.', DIRECTORY_SEPARATOR, $_GET['ns']).DIRECTORY_SEPARATOR.$_GET['class'].'.php';
	$classname = str_replace('.', '\\', $_GET['ns']).'\\'.$_GET['class'];
	$classDesc = new ReflectionClass($classname);
	foreach(getClassHierarchy($classDesc) as $i => $name){
		Log::$level = $i;
		Log::println($name);
	}
	Log::$level = 0;
	Log::dump(\System\PHPDocComment::getDescription($classDesc->getDocComment()));