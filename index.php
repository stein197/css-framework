<?php
	require_once 'application/functions.php';
	use \System\{
		File, TextFile, BinaryFile, Directory, Log, Path, ArraySort
	};
?>
<html>
	<head>
		<link rel="stylesheet" href="/css/template.min.css">
	</head>
	<body>
		<style>
			*{
				position: relative;
				box-sizing: border-box;
			}
			.item{
				width: 50px;
				position: absolute;
				background-color: rgb(185, 226, 73);
				border: 5px solid rgb(136, 172, 39);
				bottom: 0;
			}
			#sort::after{
				content: "";
				clear: both;
				display: block;
			}
			#sort{
				height: 200px;
			}
			.pointer{
				position: absolute;
				bottom: -20px;
				border: 10px solid transparent;
				border-bottom: 10px solid red;
			}
			#pointerA{
				left: 15px;
			}
			#pointerB{
				left: 65px;
			}
			#pointerA::after{
				content: "a";
				position: absolute;
				top: 10px;
			}
			#pointerB::after{
				content: "b";
				position: absolute;
				top: 10px;
			}
			#sort *{
				transition: all .3s linear;
			}
		</style>
		<div id="sort">
			<div class="item"></div>
			<div class="item"></div>
			<div class="item"></div>
			<div class="item"></div>
			<div class="item"></div>
			<div class="item"></div>
			<div class="item"></div>
			<div class="item"></div>
			<div class="item"></div>
			<div class="item"></div>
			<div id="pointerA" class="pointer"></div>
			<div id="pointerB" class="pointer"></div>
		</div>
		<script>
			// window.addEventListener("DOMContentLoaded", e => {
			// 	items = document.querySelectorAll(".item");
			// 	maxHeight = 0;
			// 	for(let i = 0; i < items.length; i++){
			// 		height = rand(20, 200);
			// 		items[i].style.height = height + "px";
			// 		items[i].style.left = i * 50 + "px";
			// 		if(height > maxHeight)
			// 			maxHeight = height;
			// 	}
			// 	Sort.sort(items, function(a, b){
			// 			var aH = +a.style.height.slice(0, -2);
			// 			var bH = +b.style.height.slice(0, -2);
			// 			if(aH > bH)
			// 				return 1;
			// 			else if(bH > aH)
			// 				return -1;
			// 			else
			// 				return 0;
			// 		}, false, Sort.SORT_BUBBLE
			// 	);
			// });
			function rand(a, b){
				return Math.round(Math.random() * (b - a)) + a;
			}
			function sleep(miliseconds) {
				var currentTime = new Date().getTime();
				while (currentTime + miliseconds >= new Date().getTime());
			}
			var Sort = {
				SORT_BUBBLE: 0,
				SORT_SELECT: 1,
				sort: function(ar, f, reverse, algorithm){
					f = this.getFunc(f);
					console.log("Algorithm: ", f);
					switch(algorithm){
						case this.SORT_BUBBLE:
							return this.bubbleSort(ar, f, reverse);
						case this.SORT_SELECT:
							return this.selectionSort(ar, f, reverse);
						default:
							throw new Error("There is no algorithm with given name");
					}
				},
				bubbleSort: function(ar, f, reverse){
					for(j = 0; j < ar.length - 1; j++){
						for(i = 0; i < ar.length - 1 - j; i++){
							this.swapPointers(j, i);
							sleep(300);
							a = ar[i];
							b = ar[i + 1];
							comp = f(a, b);
							// if(comp > 0);
								// :swap(ar, i, i + 1);
						}
					}
				},
				selectionSort: function(ar, f, reverse){

				},
				getFunc: function(f){
					return f ? f : function(a, b){
						if(a > b)
							return 1;
						else if(a < b)
							return -1;
						else
							return 0;
					}
				},
				swap: function(ar, a, b){
					
				},
				swapPointers: function(aOffset, bOffset){
					var pointers = this.getPointers();
					pointers.a.style.left = aOffset * 50 + 15 + "px";
					pointers.b.style.left = bOffset * 50 + 15 + "px";
					// console.log(aOffset, bOffset);
				},
				getPointers: function(){
					return {
						a: document.getElementById("pointerA"),
						b: document.getElementById("pointerB"),
					}
				}
			}
			function parsebracket(str, depth){
				var res = [''];
				var curD = 0;
				var curs = '';
				var resD = [];
				for(var i = 0; i < str.length; i++){
					var char = str[i];
					if(char === '{')
						curD++;
					else if(char === '}')
						curD--;
					
					var isInside = curD >= depth;
					isInside &= char === '{' ? curD > depth : true;
					if(isInside)
						curs += char;
				}
				if(curD < 0)
					throw new Error("Too many closing braces");
				if(curD > 0)
					throw new Error("Too many opening braces");
				return curs;
			}
			function parsebrackettest(str, depth){
				var res = [];
				var curD = 0;
				var curs = null;
				for(var i = 0; i < str.length; i++){
					var char = str[i];
					if(char === '{' && ++curD === depth){
						curs = '';
						continue;
					}
					else if(char === '}')
						curD--;
					var isInside = curD >= depth;
					if(isInside){
						// if(curs === null)
							// curs = '';
						curs += char;
					} else {
						if(curs !== null){
							res.push(curs);
							curs = null;
						}
					}
				}
				if(curD < 0)
					throw new Error("Too many closing braces");
				if(curD > 0)
					throw new Error("Too many opening braces");
				console.log(res);
				return curs;
			}
		</script>
	</body>
</html>