//	SelectMirror ver1.0
//	written by Shota Kusama

(function( $ ) {
	$.fn.selectMirror = function() {
		var t = this,
		c = t.find(".select"),
		o = t.find(".options"),
		s = t.find(".selected"),
		sl = t.find(".selecd"),
		v = t.find(".valueInput"),
		m = $(this);
		c.click(function(e) {
			e.stopPropagation();
			$(".selectMirror").each(function(){
				if(this != t[0])
					$(this).find(".options").fadeOut(150);
			});
			o.animate({opacity: "toggle",height: "toggle"},300);
		});
		o.children("li").click(function(){
			var selected = $(this).clone();
			
			if(!$(this).hasClass('li_dialog'))
			{
				selected.find('.head').remove();
				s.html(selected.text());
				v.attr("value",selected.data("value"));
				$(".finish").removeClass("inactive");
			}
			else
			{
				sl.html(selected.text());
				v.attr("value",selected.data("value"));
			}
			o.fadeOut(150);
		});
			//Close the lists if clicked outside of active one.
			o.skOuterClick(function() {
				o.fadeOut(400);
			});
		};
	})( jQuery );