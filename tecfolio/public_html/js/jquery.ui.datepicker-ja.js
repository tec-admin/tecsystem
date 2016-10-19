/* Japanese initialisation for the jQuery UI date picker plugin. */
/* Written by Kentaro SATO (kentaro@ranvis.com). */
jQuery(function($){
	$.datepicker.regional['ja'] = {
			closeText: '閉じる',
			prevText: '&#x3C;前へ',
			nextText: '次&#x3E;',
			currentText: '今日',
			monthNames: ['1月','2月','3月','4月','5月','6月',
			             '7月','8月','9月','10月','11月','12月'],
			monthNamesShort: ['1月','2月','3月','4月','5月','6月',
			                  '7月','8月','9月','10月','11月','12月'],
			dayNames: ['日曜日','月曜日','火曜日','水曜日','木曜日','金曜日','土曜日'],
			dayNamesShort: ['日','月','火','水','木','金','土'],
			dayNamesMin: ['日','月','火','水','木','金','土'],
			weekHeader: '週',
			dateFormat: 'yy/mm/dd',
			firstDay: 0,
			isRTL: false,
			showMonthAfterYear: true,
			yearSuffix: '年'};
	
	$.datepicker.regional[ "en" ] = {
			closeText: "Done",
			prevText: "Prev",
			nextText: "Next",
			currentText: "Today",
			monthNames: [ "January","February","March","April","May","June",
			              "July","August","September","October","November","December" ],
			monthNamesShort: [ "Jan", "Feb", "Mar", "Apr", "May", "Jun",
			                   "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ],
			dayNames: [ "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" ],
			dayNamesShort: [ "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" ],
			dayNamesMin: [ "Su","Mo","Tu","We","Th","Fr","Sa" ],
			weekHeader: "Wk",
			//dateFormat: "mm/dd/yy",
			dateFormat: "yy/mm/dd",
			firstDay: 0,
			isRTL: false,
			showMonthAfterYear: false,
			yearSuffix: "" };
	
	//$.datepicker.setDefaults($.datepicker.regional['ja']);
});
