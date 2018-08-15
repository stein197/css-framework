<?
	require_once 'php/functions.php';
	import('System.Path');
	import('Math.Matrix');
	import('Math.SquareMatrix');
	use System\Path;
	use Math\Matrix;
	use Math\SquareMatrix;
	echo '<pre>';
	$mx = new SquareMatrix([
		[1, 0, 0],
		[0, 1, 0],
		[0, 0, 1],
	]);
	// exit;
	// var_dump(function_exists('PDF_activate_item') || class_exists('PDFlib'));
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
	<link rel="stylesheet" href="/css/bootstrap.min.css"/>
	<link rel="stylesheet" href="/css/bootstrap-grid.min.css"/>
	<!--[if IE]>
		<link href="/css/bootstrap-ie9.min.css" rel="stylesheet">
		<script src="https://cdn.jsdelivr.net/g/html5shiv@3.7.3"></script>
	<![endif]-->
	<link rel="stylesheet" href="/css/slick.min.css"/>
	<link rel="stylesheet" href="/css/magnific-popup.min.css"/>
	<link rel="stylesheet" href="/css/template.min.css?v=<?= filemtime("{$_SERVER['DOCUMENT_ROOT']}/css/template.min.css") ?>"/>
	<title></title>
	<meta name="description" content=""/>
	<meta name="keywords" content=""/>
</head>
<body>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="/js/bootstrap.min.js"></script>
	<script src="/js/slick.min.js"></script>
	<script src="/js/jquery.magnific-popup.min.js"></script>
	<script src="/js/jquery.inputmask.bundle.min.js"></script>
	<script src="/js/jquery.placeholder.min.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBl2Ifc-eVEmfjSPG-DaA5pih_79CJkvyU&#038;ver=4.8.2"></script>
	<script src="/js/template.js?v=<?= filemtime("{$_SERVER['DOCUMENT_ROOT']}/js/template.js") ?>"></script>
	<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript">
    </script>
	<script>
		function sformat(str){
			var data = arguments;
			var length = data.length - 1;
			var reg = new RegExp("%\\d+", "g");
			var formatted = str.replace(reg, function(match){
				var pos = match.slice(1) / 1;
				if(pos >= data.length){
					return "";
				}
				var rest = "";
				while(length < pos){
					rest = pos.toString().slice(-1) + rest;
					pos = Math.floor(pos / 10);
				}
				return data[pos] + rest;
			});
			return formatted;
		}
	</script>
</body>
</html>