function main() {
	setScalable(); // Remove zoom from smartphones
	$(".js-slider").each(initSlick);
	$(".js-anchor").click(anchorView);
	$(".js-accordion-button").click(toggleAccordion);
	$("#burger").click(onBurgerClick);
	$.lz();
}

function initSlick() {
	var $this = $(this);
	var settings = {
		infinite: false,
		swipeToSlide: true
	};
	if($this.data("slick"))
		settings = $.extend(settings, $this.data("slick"));
	$wrapper = $this.closest(".slider-wrapper");
	$btnswrapper = $wrapper.find(".slider-btns");
	$dotswrapper = $wrapper.find(".slider-dots");
	if($btnswrapper.length)
		$.extend(settings, {
			appendArrows: $btnswrapper
		});
	if($dotswrapper.length)
		$.extend(settings, {
			appendDots: $dotswrapper
		});
	$this.slick(settings);
}

function anchorView($e) {
	$e.preventDefault();
	var $this = $(this);
	$("html,body").animate({
		scrollTop: $($this.attr("href")).offset().top - $header.height()
	});
}

function toggleAccordion($e) {
	$e.preventDefault();
	var $item = $(this).closest(".js-accordion-item");
	var bodies = $item.find(".js-accordion-body").toArray();
	var $body = $(bodies[0]);
	for(var i = 1; i < bodies.length; i++){
		var $tmpBody = $(bodies[i]);
		if($tmpBody.parents().length < $body.parents().length)
			$body = $tmpBody;
	}
	if ($body.is(":animated"))
		return;
	$body.slideToggle();
	var $wrapper = $item.parents(".js-accordion").first();
	if($wrapper.hasClass("singlemode") && !$item.hasClass("expanded")){
		var expanded = $wrapper.find(".js-accordion-item.expanded").toArray();
		var $expanded = $(expanded[0]);
		for(var i = 1; i < expanded.length; i++){
			var $tmpExpanded = $(expanded[i]);
			if($tmpExpanded.parents().length < $expanded.parents().length)
				$expanded = $tmpExpanded;
		}
		$expanded.removeClass("expanded").addClass("collapsed");
		bodies = $expanded.find(".js-accordion-body");
		$body = $(bodies[0]);
		for(var i = 1; i < bodies.length; i++){
			var $tmpBody = $(bodies[i]);
			if($tmpBody.parents().length < $body.parents().length)
				$body = $tmpBody;
		}
		$body.slideUp();
	}
	$item.toggleClass("expanded").toggleClass("collapsed");
}

function onBurgerClick($e) {
	$(this).toggleClass("expanded").toggleClass("collapsed");
	var $nav = $("nav");
	$nav.toggleClass("expanded").toggleClass("collapsed");
	$body.toggleClass("fixed");
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

$(main);