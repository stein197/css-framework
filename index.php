<!DOCTYPE html>
<html>
<head></head>
<body style="margin: 0">
	<svg viewbox="0 0 500 500">
		<path stroke="black" fill="none" d="M100,100C0,100,200,100,200,0l10,10q0,100 100 100"/>
	</svg>
	<canvas id="canvas" width="1920" height="900"></canvas>
	<script src="/js/canvas.js?<?= filemtime($_SERVER['DOCUMENT_ROOT'].'\\js\\canvas.js') ?>"></script>
</body>
</html>