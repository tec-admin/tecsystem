(function( $ ) {
 	$.fn.singleAccordion = function(h) {
 		var header = this.find(h);
 		this.addClass("singleAccordionBody");
 		header.next().addClass("singleAccordionWrap");
	}
})( jQuery );