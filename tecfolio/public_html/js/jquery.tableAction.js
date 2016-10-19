//	TextAreaCountDown ver1.0
//	originated in BlackFlag
//	mod by Shota Kusama
//

(function( $ ) {
	$.fn.tableAction = function(options) {
		var body = this,
			thead = body.find("thead"),
			tbody = body.find("tbody");

		thead.delegate("th","click", function(e) {
			var d = $(this).data("date");
			body.find(".activeCell").removeClass("activeCell");
			$(this).addClass("activeCell");
			//ここに日付セルクリック時の動作を記述してください。現状ではURLクエリにdata-dateの値を読み込みます
			//location.href = "?"+d;
		});
		tbody.delegate("td","click", function(e) {
			var d = $(this).data("date");
			body.find(".activeCell").removeClass("activeCell");
			$(this).addClass("activeCell");
			for(i=1 ; i < 9 ; i++ ){
				for(j=1 ; j < 9 ; j++ ){
					$('#'+ i +'_' + j).removeClass("colHover");
				}
			}
			$(".day2").addClass("day");
			$(".day2").removeClass("day2");
			$(".today").addClass("today2");
			$(".today").removeClass("today");
			$(".day3").addClass("day4");
			$(".day3").removeClass("day3");
			//ここに各シフトセルクリック時の動作を記述してください。現状ではURLクエリにdata-dateの値を読み込みます
			//location.href = "?"+d;
		});
 	};
})( jQuery );