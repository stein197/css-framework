<?php
	require_once 'application/functions.php';
	use \System\{Directory, Path, Log, ArrayWrapper};
	$paths = [
		$_SERVER['DOCUMENT_ROOT'].'/css/styles.css',
		'css/styles.css',
		'/js/app.js'
	];
	$ar = new ArrayWrapper([
		1, 2, 6, [2, 'k' => 1], 'j' => 1
	]);
	foreach($ar as $k => $v){
		Log::println("$k => $v");
	}
	$ar->changeKeyCase();
	foreach($ar as $k => $v){
		Log::println("$k => $v");
	}
?>
<html>
	<head>
		<link rel="stylesheet" href="/css/template.min.css">
	</head>
<body>
	<div></div>
	<p>LARGE TEXT <span>small text</span></p>
</body>
</html>