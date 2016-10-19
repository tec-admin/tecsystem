//	TextAreaCountDown ver1.0
//	originated in BlackFlag
//	mod by Shota Kusama 
//

(function( $ ) {
	$.fn.textAreaCD = function(options) {
		var defaults = {
			"zeroCheck":false,
			"max"	:"250",
			"fin"	:$("#pageControl .finish")
		};
		var init = $.extend(defaults, options);

		var t = this,
			area = t.find("textarea"),
			counter = t.find(".counter"),
			fin = init["fin"];

		function getCD (t,max){
			var cd = max - area.val().length;
			counter.html(cd);
			if (cd < 0){
				counter.addClass("over");
				fin.addClass("overText");
			}else {
				counter.removeClass("over");
				fin.removeClass("overText");
				if(init["zeroCheck"]){
					if (cd-max >= 0){
						fin.addClass("overText");
					}
				}
			}
			return cd;
		};

		counter.html(getCD(t,init["max"]));
		area.bind("keydown keyup keypress change",function(){
			getCD(t,init["max"]);
		});
 	};
})( jQuery );