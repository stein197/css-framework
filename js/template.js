function main(){
	setScalable(); // Убрать зум на смартфонах
	$(".dropdown.dropdown-tabs").each(initDropdown); // Если есть табы превращающиеся в выпадающий список
	$("#burger").click(initBurger); // Переключение бургера
	$(".slider").each(initSlick); // Автоматическая инициализация слайдера
	$.lz();
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

function changeFilename(wrapper, label, close){
	wrapper = wrapper || ".file";
	label = label || ".filename";
	close = close || ".fileremove";
	var defaultText = $(label).text();
	// var maxwidth = $(label).outerWidth();
	// $(label).css("max-width", maxwidth);

	// Show filename
	$("input[type='file']").change(function($e){
		var $this = $(this);
		var $wrapper = $this.closest(wrapper);
		var $label = $wrapper.find(label);
		var $close = $wrapper.find(close);
		var filename = $this
			.val()
			.replace(/.*\\(.*)/, "$1")
			.replace(/.*\/(.*)/, "$1");
		$label.text(filename ? filename : defaultText);
		$close
			.removeClass("o-0")
			.addClass("cp");
	});
	// Remove file
	$(close).click(function($e){
		var $this = $(this);
		$this
			.addClass("o-0")
			.removeClass("cp")
			.closest(wrapper)
			.find(label)
			.val("")
			.text(defaultText)
	})
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
		// Р’Р°Р»РёРґР°С†РёСЏ РїРѕР»РµР№
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
		// Р’Р°Р»РёРґР°С†РёСЏ С„Р»Р°Р¶РєР°
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
	var opts = {
		cover: {
			class: "js-cover",
			adaptive: true
		},
		lazyload: {
			class: "js-lazyload",
		}
	};
	var fn = {
		isInViewport: function(element){
			var box = element.getBoundingClientRect();
			var isInViewportT = 0 <= box.top && box.top <= innerHeight;
			var isInViewportB = 0 <= box.bottom && box.bottom <= innerHeight;
			return isInViewportT || isInViewportB;
		},
		showImage: function($image){
			var covered = $image.is("." + opts.cover.class) ? $image.is(".covered") : true;
			var loaded = $image.is("." + opts.lazyload.class) ? $image.is(".loaded") : true;
			if(covered && loaded)
				$image.animate({
					opacity: 1
				}, 500)
		},
		onLoadCover: function(idx){
			if(!this.width && !this.height)
				return;
			var $image = $(this);
			var $parent = $image.parent();
			$image.addClass("center");
			$parent.addClass("crop");
			var dimens = {
				parent: {
					x: $parent.outerWidth(),
					y: $parent.outerHeight(),
				},
				image: {
					x: $image.width(),
					y: $image.height(),
				}
			}
			var ratio = {
				parent: dimens.parent.x / dimens.parent.y,
				image: dimens.image.x / dimens.image.y
			}
			$image.data("ratio", ratio.image);
			fn.setCover($image, ratio);
			$image.addClass("covered");
			fn.showImage($image);
		},
		onWindowScroll: function($e){
			var $list = $("img." + opts.lazyload.class + ":not(.loaded)");
			var $bgList = $("." + opts.lazyload.class + ":not(img):not(.loaded)");
			$list.each(function(){
				if(fn.isInViewport(this)){
					var $image = $(this);
					$image.attr("src", $image.data("src"));
				}
			});
			$bgList.each(function(){
				if(fn.isInViewport(this)){
					var $this = $(this);
					$this.css("background-image", "url(" + $this.data("bg-src") + ")").addClass("loaded");
				}
			});
		},
		onLoadLazy: function($e){
			var $image = $(this);
			$image.addClass("loaded");
			fn.showImage($image);
		},
		coverOnResize: function(i){
			var $image = $(this);
			var $parent = $image.parent();
			var imageRatio = $image.data("ratio");
			ratio = {
				parent: $parent.outerWidth() / $parent.outerHeight(),
				image: imageRatio
			}
			fn.setCover($image, ratio);
		},
		setCover: function($image, ratio){
			if(ratio.parent > ratio.image){
				$image.css({
					width: "100%",
					height: "auto"
				});
			} else {
				$image.css({
					width: "auto",
					height: "100%"
				});
			}
		}
	};
	$.lz = function(options){
		opts = $.extend(opts, options);
		var $window = $(window);
		$("img." + opts.cover.class).on("load", fn.onLoadCover);
		$("img." + opts.cover.class).trigger("load");
		$("img." + opts.lazyload.class).on("load", fn.onLoadLazy);
		$window.on("scroll load", fn.onWindowScroll);
		if(opts.cover.adaptive){
			$window.resize(function(){
				$("img." + opts.cover.class).each(fn.coverOnResize);
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
* @version 1.5
**/
function $_COOKIE(key, value, path, lifetime){
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
			var result = "";
			for(var i = 0; i < cookie.length; i++){
				var pair = cookie[i].trim().split("=");
				keyName = pair[0];
				if(keyName === key){
					return pair[1];
				}
			}
			return undefined;
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

function $_GET(){
	if(arguments.length){
		// Установить массив значений/очистить строку / вернуть одно значение
		if(arguments.length === 1){
			// Вернуть одно значение
			if(typeof arguments[0] === "string"){
				return $_GET()[arguments[0]];
			// Установить массив значений/очистить строку
			} else {
				var params = arguments[0];
				var queryString = $_GET.toQueryString(params);
				var url = location.protocol + "//" + location.host + location.pathname;
				if(queryString)
					url += "?" + queryString;
				if(location.hash)
					url += location.hash;
				history.pushState({
					path: url
				}, "", url);
			}
		// Установить/удалить пару ключ/значение
		} else {
			var key = arguments[0];
			var value = arguments[1];
			var queryParams = $_GET();
			if(value)
				queryParams[key] = value;
			else
				delete queryParams[key];
			$_GET(queryParams);
		}
	// Вернуть все значения query-строки
	} else {
		return $_GET.fromQueryString();
	}
}

$_GET.toQueryString = function(obj){
	var result = [];
	var keyPath = arguments[1] || [];
	var keyPrefix = "";
	if(keyPath.length){
		keyPrefix = keyPath[0];
		if((keyPath.length - 1) > 0)
			keyPrefix += "[" + keyPath.slice(1).join("][") + "]";
	}
	for(var key in obj){
		var value = obj[key];
		if(typeof value === "object"){
			result.push($_GET.toQueryString(value, keyPath.concat(key)));
		} else {
			if(keyPrefix){
				if(Array.isArray(obj)){
					result.push(keyPrefix + "[]=" + value);
				} else {
					result.push(keyPrefix + "[" + key + "]=" + value);
				}
			} else {
				result.push(key + "=" + value);
			}
		}
	}
	return result.join("&");
}

$_GET.fromQueryString = function(str){
	var queryString = "";
	if(str)
		queryString = str;
	else
		queryString = location.search ? location.search.split("?")[1] : "";
	if(!queryString)
		return {};
	var result = {};
	var queryParts = queryString.split("&");
	for(var i in queryParts){
		var parts = queryParts[i].split("=");
		var keyParts = parts[0].split(/[\[\]]{1,2}/);
		var value = parts[1];
		if(keyParts.length > 2)
			keyParts.pop();
		var parentObj = result;
		for(var j = 0; j < keyParts.length; j++){
			var key = keyParts[j];
			var nextKey = keyParts[j + 1];
			if(!parentObj[key]){
				if(nextKey === undefined){
					if(Array.isArray(parentObj)){
						parentObj.push(value);
					} else {
						parentObj[key] = value;
					}
				} else if(nextKey === "" || nextKey.search(/^\d+$/) >= 0) {
					parentObj[key] = [];
				} else {
					parentObj[key] = {};
				}
			}
			var parentObj = parentObj[key];
		}
	}
	return result;
}

$(main);