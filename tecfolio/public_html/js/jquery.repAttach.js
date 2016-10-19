(function( $ ) {
	$.fn.repAttachToggle = function(name,href,hider) {
		var t = this.find("button.rep"),
			s = this.find("#repName"),
			h = hider;
		t.click(function(e){
			e.stopPropagation();
			if(!t.hasClass('release')){
				t.addClass('release').html("<i></i>担当解除");
				s.text(name).attr("href",href).removeClass("inactive");
				h.slideDown("400");
			}else{
				t.removeClass('release').html("<i></i>担当する");
				s.text("未設定").attr("href","#").addClass("inactive");
				h.fadeOut("400");
			}
			return false;

		});
	}
})( jQuery );