<?php
	require_once 'application/functions.php';
	use System\Path;
	use Math\Matrix;
	use Math\SquareMatrix;
	echo '<pre>';
	$mx = new SquareMatrix([
		[2, 5, 7],
		[6, 3, 4],
		[5, -2, -3]
	]);
