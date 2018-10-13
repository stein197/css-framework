<<<<<<< HEAD
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
	echo $mx->transpose();
=======
<html>
	
	<head>
	
	</head>
	<body>
	<?php
		require_once 'application/functions.php';
		use System\Collection;
		use System\Database\PostgreSQL;
		use System\DocComment;
		use System\DocAnnotation;
		use System\Log;

		$rc = new ReflectionClass(DocComment::class);
		$rm = $rc->getMethods();
		foreach($rm as $method){
			$doc = new DocComment($method->getDocComment());
			Log::dump($doc->description);
			$doc->description = 10;
		}
		/**
		 * @property-read int $peNR
		 * @property-write int $peNW
		 * @property-write int $pdNW
		 */
		class gg{
			use System\PropertyAccess;
			private $peNR = 1;
			protected $pdNR = 2;
			public $pcNR = 3;
			private $peNW = 1;
			protected $pdNW = 2;
			public $pcNW = 3;
		}
		$gg = new gg;
	?>
		<script src="/js/class.js"></script>
		<script>
			function BackendAPI(classname){
				this.className = classname;
			}
			function constructQuery(params, name){
				var result = '';
				if(Array.isArray(params)){
					var l = params.length;
					for(var i = 0; i < l; i++){
						result += name + "[]=" + params[i] + "&";
					}
					return result;
				} else {
					for(var propName in params){
						if(Array.isArray(params[propName])){
							result += constructQuery(params[propName], propName);
						} else {
							result += propName + "=" + params[propName] + "&";
						}
					}
					return result;
				}
			}
			Class.extend(BackendAPI, {
				created: false,
				data: {},
				callMethod: function(method){
					if(!this.created){
						return;
					}
					var request = new XMLHttpRequest();
					var args = Array.from(arguments);
					args.shift();
					this.data.class = this.className;
					this.data.method = method;
					this.data.args = args;
					var query = constructQuery(this.data);
					request.open('get', '/application/backendapi.php?' + query, true);
					request.send();
					request.onreadystatechange = function(){
						if(request.readyState === XMLHttpRequest.DONE){
							return request.response;
						}
					}
				},
				callStaticMethod: function(method){

				},
				getField: function(name){

				},
				getStaticField: function(name){

				},
				create: function(){
					this.created = true;
					this.data.constr = Array.from(arguments);
				}
			});
			f = new BackendAPI('A');
			f.create('end');
			f.callMethod('getA', 'start');
		</script>
	</body>
</html>
>>>>>>> 4bdf8c63a9f7c058ea239173d3bec27c13e81225
