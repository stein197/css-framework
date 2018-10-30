<?php
	require_once 'application/functions.php';
	use \System\{Directory, Path, Log, ArrayWrapper};
	$paths = [
		$_SERVER['DOCUMENT_ROOT'].'/css/styles.css',
		'css/styles.css',
		'/js/app.js'
	];
	$ar = new ArrayWrapper([
		1, 2, 6, [2, 'k' => 1], 'K' => 1, 'S' => [22]
	]);
	// Log::dump((new ReflectionClass(ArrayWrapper::class))->getTraits());
	// Log::dump($ar);
	var_dump($ar);
?>
<!-- <html>
	<head>
		<link rel="stylesheet" href="/css/template.min.css">
	</head>
<body>
	<div></div>
	<p>LARGE TEXT <span>small text</span></p>
	<script src="/js/functions.js"></script>
	<script src="/js/Class.js"></script>
	<script src="/js/BackendAPI.js"></script>
</body>
</html> -->