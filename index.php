<?php
	require_once 'application/functions.php';
	use \System\{Path, Log, ArrayWrapper, File, Directory};
	$f = new File('/test/test2.txt');
	$d = new Directory('/test2');
	$f->copy($d, 'test_2.txt');
	// Log::dump($f->lastModified());
	// Log::dump($f->lastAccess());
	Log::println(php_uname());
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