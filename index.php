<?php
	require_once 'application/functions.php';
	use \System\{Directory, Path, Log, ArrayWrapper};
	$paths = [
		$_SERVER['DOCUMENT_ROOT'].'/css/styles.css',
		'css/styles.css',
		'/js/app.js'
	];
	$ar = new ArrayWrapper([[3]], true);
	$ar->push(1);
	$ar->push(5);
	$ar->push([2]);
	$ar->pop();
	foreach($ar as $v){
		var_dump($v);
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