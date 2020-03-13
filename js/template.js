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

$(main);
