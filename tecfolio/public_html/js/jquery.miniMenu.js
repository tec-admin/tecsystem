(function( $ ) {
	$.fn.miniMenu = function(item) {
		this.click(function(e){
			e.stopPropagation();
			item.animate({opacity: "toggle",height: "toggle"},0);
		});
		item.skOuterClick(function() {
		    item.fadeOut(300);
		});
	}
})( jQuery );