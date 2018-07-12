function main(){
	setScalable(); // Убрать зум на смартфонах
	$(".dropdown.dropdown-tabs").each(initDropdown); // Если есть табы превращающиеся в выпадающий список
	$("#burger").click(initBurger); // Переключение бургера
	$(".slider").each(initSlick); // Автоматическая инициализация слайдера
	$("img.img-cover").cover({
		position: "absolute"
	}); // Центрировать изображения
	initMfp(); // Автоматическая инициализация попапа
	$(window).scroll();
	$(window).resize();
	$(".js-anchor").click(anchorView);
}

var $body = $("body");
var $header = $("header");
var $footer = $("footer");
var $menubar = $("#menubar");
var $sidebar = $("#sidebar");
var $burger = $("#burger");
var $modal = $("#modal");

/**
 * Автоматическая инициализация слайдера
**/
function initSlick(){
	var $this = $(this);
	var settings = {};
	var data = $this.data("slick");
	if(data){
		settings = data;
	}
	$wrapper = $this.closest(".slider-wrapper");
	$btnswrapper = $wrapper.find(".slider-btns");
	$dotswrapper = $wrapper.find(".slider-dots");
	if($btnswrapper.length){
		$.extend(settings, {
			appendArrows: $btnswrapper
		});
	}
	if($dotswrapper.length){
		$.extend(settings, {
			appendDots: $dotswrapper
		});
	}
	$this.slick(settings);
}

/**
 * Переключение табов через выпадающий список
**/
function initDropdown(){
	var $this = $(this);
	var $target = $($this.data("target"));
	$this.find(".dropdown-item").click(function($e){
		$e.preventDefault();
		var $item = $(this);
		var link = $item.attr("href");
		$item
			.closest(".dropdown")
			.children("button")
			.text($item.text());
		$target
			.find(sformat("a[href='%1']", link))
			.click();
	});
}
/**
 * Включение-выключение меню для мобильных и планшетов
**/
function initBurger($e){
	var $this = $(this);
	if($menubar.hasClass("expanded")){
		$menubar.removeClass("expanded");
		$menubar.animate({
			"opacity": 0
		}, {
			complete: function(){
				$menubar.css("display", "none");
				$body.removeClass("fixed");
			}
		});
	} else {
		$menubar.addClass("expanded");
		$body.addClass("fixed");
		$menubar.css("display", "block");
		$menubar.animate({
			"opacity": 1
		});
	}
}

/**
 * Автоматическая инициализация галереи
**/
function initMfp(){
	$(".js-gallery").each(function(idx){
		var $this = $(this);
		$this.find(".item-mfp").magnificPopup({
			type: "image",
			gallery: {
				enabled: true
			}
		})
	});
}

/* 
 * Плавная прокрутка
 */
function anchorView($e){
	var $this = $(this);
	var data = $this.attr("href").split("#");
	var page = data[0] || location.pathname;
	var target = data[1];
	if(location.pathname !== page){
		return;
	} else {
		$e.preventDefault();
		if($menubar.is(".expanded")){
			$burger.click();
		}
		$("html, body").animate({
			scrollTop: $(sformat("#%1", target)).offset().top - $header.height()
		}, $this.data("speed") ? $this.data("speed") / 1 : undefined);
	}
}

function initGoogleMaps(selector){
	selector = selector || ".gmap";
	var $maps = $(selector);
	$maps.each(function(){
		var $this = $(this);
		var data = $this.data("map");
		data = $.extend({
			zoom: 13,
			center: null,
			scrollwheel: false,
			zoomControl: true,
			streetViewControl: false,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		}, data);
		var map = new google.maps.Map(this, data);
		var marker = new google.maps.Marker({
			position: new google.maps.LatLng(data.center.lat, data.center.lng),
			map: map,
			icon: data.icon,
		});
		var infowindow = new google.maps.InfoWindow({
			content: format(data.content),
		});
		google.maps.event.addListener(marker, "click", function(){
			infowindow.open(map, marker);
		});
	});
}


var Form = {
	init: function(selector){
		$("input[name='user-phone']").inputmask({
			mask: "+7(999)999-99-99",
			showMaskOnHover: false
		});
		$(".btn-validate").click(Form.validate);
		Form.selector = selector || "form";
	},

	validate: function($e){
		$e.preventDefault();
		var $this = $(this);
		var $form = $this.closest(Form.selector);
		var $inputs = $form.find("*.required");
		var $checkbox = $form.find("input[type='checkbox']");
		var isValid = true;
		var regexp, value, input;
		// Р вЂ™Р В°Р В»Р С‘Р Т‘Р В°РЎвЂ Р С‘РЎРЏ Р С—Р С•Р В»Р ВµР в„–
		$inputs.each(function(){
			regexp = new RegExp(this.getAttribute("data-regexp"));
			value = this.value;
			var matches = regexp.test(value);
			isValid &= matches;
			if(matches){
				Form.onSuccess(this);
			} else {
				Form.onFailure(this);
			}
		});
		// Р вЂ™Р В°Р В»Р С‘Р Т‘Р В°РЎвЂ Р С‘РЎРЏ РЎвЂћР В»Р В°Р В¶Р С”Р В°
		if(!$checkbox.is(":checked")){
			Form.onFailure($checkbox.get(0));
			isValid = false;
		} else {
			Form.onSuccess($checkbox.get(0));
		}

		if(isValid){
			if($form.is(".ajax")){
				var data = {};
				var $input;
				$form.find("input:not([type='checkbox']), textarea").each(function(){
					$input = $(this);
					data[$input.attr("name")] = $input.val();
				});
				$form.find("input[type='checkbox']").each(function(){
					$input = $(this);
					data[$input.attr("name")] = $input.is(":checked");
				});
				$.post($this.attr("href"), data, function(data, status){
					console.log(data);
				});
			} else {
				$form.submit();
			}
		}
	},

	onFailure: function(context){
		var $input = $(context);
		if($input.is("[type='checkbox']")){
			$input
				.next()
				.addClass("error");
		} else {
			$input
				.addClass("error")
				.removeClass("success")
				.next("span.warn")
				.addClass("active");
		}
	},

	onSuccess: function(context){
		var $input = $(context);
		if($input.is("[type='checkbox']")){
			$input
				.next()
				.removeClass("error");
		} else {
			$input
				.removeClass("error")
				.addClass("success")
				.next("span.warn")
				.removeClass("active");
		}
	}
}

// Убирает зум на сматрфонах
function setScalable(){
	if(screen.width < 768){
		var $viewport = $("meta[name='viewport']");
		var metadata = $viewport.attr("content");
		$viewport.attr("content", sformat("%1, user-scalable=no", metadata));

	}
}

!function($){
	$.fn.cover = function(opts){
		options = $.extend({
			position: "relative",
			adaptive: true,
			center: true,
			setOverflow: true,
			logs: false
		}, opts);
		var cssObj;
		var $selector = this;
		if(options.center){
			cssObj = {
				position: options.position,
				top: "50%",
				left: "50%",
				"transform": "translate(-50%, -50%)",
				"o-transform": "translate(-50%, -50%)",
				"ms-transform": "translate(-50%, -50%)",
				"moz-transform": "translate(-50%, -50%)",
				"webkit-transform": "translate(-50%, -50%)",
			};
		} else {
			cssObj = {
				"position": options.position
			};
		}
		// @this <img>
		this.each(function(idx){
			var $this = $(this);
			$this.css(cssObj);
			var $parent = $this.parent();
			if(options.setOverflow){
				$parent.css("overflow", "hidden");
			}
			var dimens_parent = {
				x: $parent.innerWidth(),
				y: $parent.innerHeight(),
			}
			var dimens_img = {
				x: $this.width(),
				y: $this.height(),
			}
			if(dimens_parent.x / dimens_parent.y > dimens_img.x / dimens_img.y){
				$this.css({
					width: "100%",
					height: "auto"
				});
			} else {
				$this.css({
					width: "auto",
					height: "100%"
				});
			}
			if(options.logs){
				console.log(sformat("Item %1:\n\tWidth: %2px\n\tHeight: %3px", idx, dimens_parent.x, dimens_parent.y))
			}
		});
		if(options.adaptive){
			var $window = $(window);
			$window.resize(function(){
				$selector.css({
					width: "auto",
					height: "auto",
					position: "relative"
				});
				$selector.cover({
					position: options.position,
					adaptive: false,
					center: options.center,
					setOverflow: options.setOverflow,
					logs: options.logs
				});
			});
		}
		return this;
	}
}(jQuery);

!function($){
	$.fn.justifyItemsHeight = function(options){
		var $this = this;
		options = $.extend({
			adaptive: false
		}, options);
		var maxHeight = 1;
		this.each(function(){
			var curHeight = $(this).outerHeight();
			if(curHeight > maxHeight){
				maxHeight = curHeight;
			}
		});
		this.css("height", maxHeight);
		if(options.adaptive){
			$(window).resize(function(){
				var innerMaxHeight = 0;
				$this.each(function(){
					console.log(1);
					$(this).attr("style", "");
					var curHeight = $(this).outerHeight();
					if(curHeight > innerMaxHeight){
						innerMaxHeight = curHeight;
					}
				});
				$this.css("height", maxHeight);
			});
		}
	}
}(jQuery);

/**
 * Форматирует строку с указанными параметрами
 * @param {string} str Строка для форматирования
 * @param {(object|array)} [data=window] Данные
 * @param {string} [delimiter="%"] Разделитель
 * @param {string} [levelDelimiter] Разделитель уровней, если объект имеет сложную структуру
 * @return {string}
 * @version 1.1
**/
function format(str, data, delimiter, levelDelimiter){
	// Установка стандартных настроек
	data = data || window;
	delimiter = delimiter || "%";
	levelDelimiter = levelDelimiter || ".";
	// Получение регулярного выражения
	var reg = new RegExp(delimiter + ".+?" + delimiter, "g");
	var formatted = str.replace(reg, function(match, offset, string){
		match = match.slice(1, -1);
		var levels = match.split(levelDelimiter);
		var nest = levels.length;
		var curLvl = data[levels[0]];
		if(curLvl === undefined){
			return "";
		}
		for(var i = 1; i < nest; i++){
			curLvl = curLvl[levels[i]];
			if(curLvl === undefined){
				return "";
			}
		}
		return curLvl;
	});
	return formatted;
}

/**
 * Простое форматирование текста данными. Принимает неограниченное количество аргументов
 * @param {string} str Строка для форматирования
 * @return {string} Отформатированную строку
 * @version 1.2
**/
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

/**
* Устанавливает, меняет и удаляет куки. Для работы нужен class.js
* @param {string|object} [key] Имя куки или набор куки
* @param {string} [value] Значение куки
* @param {number} [lifetime] Время жизни кук в секундах
* @param {string} [path] Путь до куки
* @return {(string|object|undefined|boolean)} Одиночное значение | Все куки | Куки не найдено | Установлено новое значение
* @version 1.3
**/
function $_COOKIE(key, value, path, lifetime){
	if(!Array.prototype.find) throw new Error("This browser does not support Array.prototype.find method");
	var cookie = document.cookie.split(";");
	// Получить все куки массивом
	if(key === undefined){
		var result = {
			keys: [],
			values: []
		}
		var item = "";
		for(var i = 0; i < cookie.length; i++){
			item = cookie[i].trim().split("=");
			result.keys.push(item[0]);
			result.values.push(item[1]);
		}
		return result;
	// Установить/удалить набор
	} else if(value === undefined){
		// Вернуть одно значение
		if(typeof key === "string"){
			var keyName = "";
			var result = cookie.find(function(v, i, a){
				keyName = v.trim().split("=")[0];
				return keyName === key;
			});
			if(result === undefined) return undefined;
			else return result.split("=")[1];
		// Установить набор значений или удалить все куки
		} else {
			// Если key - массив содержащий хотя бы один ключ
			if(Array.isArray(key) && key.length){
				var l = key.length;
				for(var i = 0; i < l; i++){
					$_COOKIE(key[i].name, key[i].value, key[i].lifetime, key[i].path);
				}
			// Если key - объект содержащий хотя бы один ключ
			} else if(Object.keys(key).length > 0){
				for(var prop in key){
					$_COOKIE(prop, key[prop]);
				}
			// Удалить все куки
			} else {
				cookie = $_COOKIE();
				var cookieLength = cookie.keys.length;
				for(var i = 0; i < cookieLength; ++i){
					$_COOKIE(cookie.keys[i], "");
				}
				return true;
			}
		}
	// Установить одно значение или удалить
	} else {
		// Удалить куки
		if(value === ""){
			document.cookie = key + "=;max-age=-99999999;expires=Thu, 01 Jan 1970 00:00:01 GMT";
			if($_COOKIE(key) !== undefined) throw new Error(sformat("Cookie with key \"%1\" cannot be removed", key));
		// Установить одно значение
		} else {
			path = path || "/";
			// Если указано время жизни
			if(lifetime !== undefined){
				document.cookie = format("%key%=%value%;max-age=%lifetime%;path=%path%", {
					key: key,
					value: value,
					path: path,
					lifetime: lifetime,
				});
			// Если был указан путь
			} else {
				document.cookie = format("%key%=%value%;path=%path%", {
					key: key,
					value: value,
					path: path,
				});
			}
		}
		return true;
	}
}

/**
* Устанавливает, меняет и удаляет параметры query-строки. Доступно в IE10+
* @param {string|object} [key] Имя параметра или набор параметров
* @param {string} [value] Значение параметра
* @return {(string|object|undefined|boolean)} Одиночное значение | Все get-параметры | Параметр(-ы) не найдены | Установлено новое значение
* @throws {Error} Если браузер не поддерживает метод
* @version 1.2
**/
function $_GET(key, value){
	if(!history.pushState) throw new Error("Your browser does not support \"history.pushState\" method");
	var url = location.href;
	var query = url.split("?")[1];
	// Вернуть список всех query-параметров или undefined, если список пуст
	if(!key){
		if(!query) return undefined;
		query = query.split("&");
		var queryLength = query.length;
		var result = param =[];
		result = {
			keys: [],
			values: []
		};
		for(var i = 0; i < queryLength; ++i){
			param = query[i].split("=");
			result.keys.push(param[0]);
			result.values.push(param[1]);
		}
		return result;
	// Вернуть одно значение или undefined, если нет, или очистить всю query-строку
	} else if(value === undefined){
		if(typeof key === "string"){
			if(!query) return undefined;
			query = query.split("&");
			var val = query.find(function(v, i, a){
				var keyName = v.split("=")[0];
				return keyName === key;
			});
			if(val === undefined) return undefined;
			else return val.split("=")[1];
		// Установить набор значений
		} else if(Object.keys(key).length){
			for(var prop in key){
				$_GET(prop, key[prop]);
			}
		// Очистить query-строку
		} else {
			var params = $_GET();
			for(var i = 0; i < params.keys.length; i++){
				$_GET(params.keys[i], "");
			}
		}
	// Установаить или удалить одно значение
	} else {
		// Удалить одно значение
		if(value === ""){
			if(!$_GET(key)) return true;
			query = query.split("&");
			query = query.filter(function(v, i, a){
				var curVal = v.split("=")[0];
				if(key === curVal) return false;
				return true;
			});
			query = query.join("&");
			url = location.protocol + "//" + location.host + location.pathname + (query ? "?" + query : "");
			history.pushState({
				path: url
			}, "", url);
			return true;
		// Установить
		} else {
			query = query ? query.split("&") : [];
			var val = $_GET(key);
			if(val == value) return true;
			if(val === undefined) query.push(key + "=" + value);
			else{
				var index = query.indexOf(key + "=" + val);
				query[index] = key + "=" + value;
			}
			query = query.join("&");
			url = location.protocol + "//" + location.host + location.pathname + "?" + query;
			history.pushState({
				path: url
			}, "", url);
			return true;
		}
	}
}

$(main);