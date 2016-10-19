//	buttonReplace ver 0.1
//	written by Shota Kusama 
//

(function( $ ) {
	$.fn.replaceButton = function(options) {
		var defaults = {
			"v"	: "view",
			"s" : "substance" 
		};
		var init = $.extend(defaults, options);
		var vObj = this.find("."+init["v"]),
			sObj = this.find("."+init["s"]);
		sObj.hover(
			function(){
				vObj.addClass("hover");
			},
			function(){
				vObj.removeClass("hover");
			}
		);
 	};
})( jQuery );