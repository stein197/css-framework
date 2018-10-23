<?php
	require_once 'application/functions.php';
	use \System\{Directory, Path, Log};
	$paths = [
		$_SERVER['DOCUMENT_ROOT'].'/css/styles.css',
		'css/styles.css',
		'/js/app.js'
	];
	$d = new Directory('/test', __DIR__);
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