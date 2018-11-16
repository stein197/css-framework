<?php
	require_once 'application/functions.php';
	use \System\{Path, Log, ArrayWrapper, File, Directory};

	?>
<html>
	<head>
		<link rel="stylesheet" href="/css/template.min.css">
	</head>
<body>
	<style>
		.wrapper{
			height: 400px;
			width: 33.33333%;
			float: left;
			overflow: hidden;
		}
		.lazyload{
			opacity: 0
		}
	</style>
	<div>
		<div class="wrapper">
			<img src="http://ppu.azpt.ru/img/wm/netcat_files/53/106/DSC_6767_332_.jpg" alt="" class="lazyload js-cover"/>
		</div>
		<div class="wrapper">
			<img src="" data-src="http://ppu.azpt.ru/img/wm/netcat_files/53/106/DSC_6767_332_.jpg" alt="" class="js-lazyload lazyload js-cover"/>
		</div>
		<div class="wrapper">
			<img src="http://ppu.azpt.ru/img/wm/netcat_files/53/106/DSC_6767_332_.jpg" alt="" class=" lazyload js-cover"/>
		</div>
		<div class="wrapper">
			<img src="" data-src="http://ppu.azpt.ru/img/wm/netcat_files/53/106/DSC_6767_332_.jpg" alt="" class="js-lazyload lazyload"/>
		</div>
		<div class="wrapper">
			<img src="" data-src="http://ppu.azpt.ru/img/wm/netcat_files/53/106/DSC_6767_332_.jpg" alt="" class="js-lazyload lazyload js-cover"/>
		</div>
		<div class="wrapper">
			<img src="http://ppu.azpt.ru/img/wm/netcat_files/53/106/DSC_6767_332_.jpg" alt="" class=" lazyload js-cover"/>
		</div>
		<div class="wrapper">
			<img src="" data-src="http://ppu.azpt.ru/img/wm/netcat_files/53/106/DSC_6767_332_.jpg" alt="" class="js-lazyload lazyload js-cover"/>
		</div>
		<div class="wrapper">
			<img src="" data-src="http://ppu.azpt.ru/img/wm/netcat_files/53/106/DSC_6767_332_.jpg" alt="" class="js-lazyload lazyload js-cover"/>
		</div>
		<div class="wrapper">
			<img src="" data-src="http://ppu.azpt.ru/img/wm/netcat_files/53/106/DSC_6767_332_.jpg" alt="" class="js-lazyload lazyload"/>
		</div>
		<div class="wrapper">
			<img src="" data-src="http://ppu.azpt.ru/img/wm/netcat_files/53/106/DSC_6767_332_.jpg" alt="" class="js-lazyload lazyload js-cover"/>
		</div>
		<div class="wrapper">
			<img src="http://ppu.azpt.ru/img/wm/netcat_files/53/106/DSC_6767_332_.jpg" alt="" class=" lazyload js-cover"/>
		</div>
		<div class="wrapper">
			<img src="" data-src="http://ppu.azpt.ru/img/wm/netcat_files/53/106/DSC_6767_332_.jpg" alt="" class="js-lazyload lazyload"/>
		</div>
		<div class="wrapper">
			<img src="" data-src="http://ppu.azpt.ru/img/wm/netcat_files/53/106/DSC_6767_332_.jpg" alt="" class="js-lazyload lazyload js-cover"/>
		</div>
		<div class="wrapper">
			<img src="" data-src="http://ppu.azpt.ru/img/wm/netcat_files/53/106/DSC_6767_332_.jpg" alt="" class="js-lazyload lazyload js-cover"/>
		</div>
		<div class="wrapper">
			<img data-src="http://gfsnt.no/oen/foto/Haegefjell_Jan_2013_Large.jpg" alt="" class="js-lazyload js-cover lazyload"/>
		</div>
		<div style="clear: both"></div>
	</div>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="/js/template.js"></script>
</body>
</html>