function getLang(){
	return $('#commonjs').data('lang');
}
function getServerUrl(){
	return $('#commonjs').data('server');
}
function getBaseUrl(){
	return $('#commonjs').data('url');
}
function getActionName(){
	return $('#commonjs').data('action');
}

// tiny sprintf ---
if (!String.prototype.sprintf) {
  String.prototype.sprintf = function(args___) {
    var rv = [], i = 0, v, width, precision, sign, idx, argv = arguments, next = 0;
    // 第一引数で配列を扱えるように変更
    if(Array.isArray(argv[0]))
    	argv = argv[0];
    // 変数部分を置換し、Smarty Gettextとの互換をもたせる 例) %1→%1$s
    var s = this.replace(/(\%\d)(?!\$[a-z])/g, "$1\$s");
    var unsign = function(val) { return (val >= 0) ? val : val % 0x100000000 + 0x100000000; };
    var getArg = function() { return argv[idx ? idx - 1 : next++]; };

    for (; i < s.length; ++i) {
      if (s[i] !== "%") { rv.push(s[i]); continue; }

      ++i, idx = 0, precision = undefined;

      // arg-index-specifier
      if (!isNaN(parseInt(s[i])) && s[i + 1] === "$") { idx = parseInt(s[i]); i += 2; }
      // sign-specifier
      sign = (s[i] !== "#") ? false : ++i, true;
      // width-specifier
      width = (isNaN(parseInt(s[i]))) ? 0 : parseInt(s[i++]);
      // precision-specifier
      if (s[i] === "." && !isNaN(parseInt(s[i + 1]))) { precision = parseInt(s[i + 1]); i += 2; }

      switch (s[i]) {
      	case "d": v = parseInt(getArg()).toString(); break;
      	case "u": v = parseInt(getArg()); if (!isNaN(v)) { v = unsign(v).toString(); } break;
      	case "o": v = parseInt(getArg()); if (!isNaN(v)) { v = (sign ? "0"  : "") + unsign(v).toString(8); } break;
      	case "x": v = parseInt(getArg()); if (!isNaN(v)) { v = (sign ? "0x" : "") + unsign(v).toString(16); } break;
      	case "X": v = parseInt(getArg()); if (!isNaN(v)) { v = (sign ? "0X" : "") + unsign(v).toString(16).toUpperCase(); } break;
      	case "f": v = parseFloat(getArg()).toFixed(precision); break;
      	case "c": width = 0; v = getArg(); v = (typeof v === "number") ? String.fromCharCode(v) : NaN; break;
      	case "s": width = 0; v = getArg().toString(); if (precision) { v = v.substring(0, precision); } break;
      	case "%": width = 0; v = s[i]; break; 
      	default:  width = 0; v = "%" + ((width) ? width.toString() : "") + s[i].toString(); break;
      }
      if (isNaN(v)) { v = v.toString(); }
      (v.length < width) ? rv.push(" ".repeat(width - v.length), v) : rv.push(v);
    }
    return rv.join("");
  };
}
if (!String.prototype.repeat) {
  String.prototype.repeat = function(n) {
    var rv = [], i = 0, sz = n || 1, s = this.toString();
    for (; i < sz; ++i) { rv.push(s); }
    return rv.join("");
  };
}
// --- tiny sprintf

if (!String.prototype.htmlspecialchars) {
	String.prototype.htmlspecialchars = function() {
		var str = this;
		str = str.replace(/&/g,"&amp;");
		str = str.replace(/"/g,"&quot;");
		str = str.replace(/'/g,"&#039;");
		str = str.replace(/</g,"&lt;");
		str = str.replace(/>/g,"&gt;");
		
		return str;
	}
}

function jqTranslate()
{
	var loc_opts = {
			language: getLang(),
			pathPrefix: getServerUrl() + '/lang'
	};
	$('[data-localize]').localize('lang', loc_opts);
}

function getDowArray(formal)
{
	if(getLang() == 'ja') {
		var arrDow = new Array("日","月","火","水","木","金","土");
	}
	else {
		if(formal == undefined || formal == false)
			var arrDow = new Array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
		else
			var arrDow = new Array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
	}
	return arrDow;
}

function getWeekdayArray(formal)
{
	if(getLang() == 'ja') {
		var arrDow = new Array("月","火","水","木","金");
	}
	else {
		if(formal == undefined || formal == false)
			var arrDow = new Array("Mon","Tue","Wed","Thu","Fri");
		else
			var arrDow = new Array("Monday","Tuesday","Wednesday","Thursday","Friday");
	}
	return arrDow;
}

function getMonthArray(formal)
{
	if(formal == undefined || formal == false)
		return new Array("", "Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
	else
		return new Array("", "January","February","March","April","May","June","July","August","September","October","November","December");
}

function fillZero(str)
{
	return ("0" + str).slice(-2);
}

// @param date		文字列(例: '2016-01-01') もしくは Date Object
// @param format	例: 'Y/m/d(wj) H:i' -> views/helpers/DateOut.php で扱う形式に合わせた
// @param $force	trueなら強制的に日本語表記にする
// @param title		見出し用など: trueなら曜日・月を略称表記しない
// @param shift		trueなら英語の場合に末尾のカンマ等を消さない(この次にA 11:30-12:10のようなシフトの表示が続く)
function dateFormat(date, format, force, title, shift)
{
	// dateが空文字ならそのまま空文字を返す
	if(date == undefined || date == '') return '';
	if(force == undefined) force = false;

	if(typeof date !== 'object' || !date instanceof Date) {
		date	= date.split('-').join('/');
		var d	= new Date(date);
	}
	else {
		var d = date;
	}
	var w	= getDowArray();
	var dow	= getDowArray(true);
	
	if(getLang() == 'ja' || force == true) {
		if(format == undefined) format = 'Y/m/d(wj) H:i';
		
		//var str = d.getFullYear() + '/' + fillZero(d.getMonth() + 1) + '/' + fillZero(d.getDate()) + '(' + dow[d.getDay()] + ') ' + fillZero(d.getHours()) + ':' + fillZero(d.getMinutes());
		format = format.replace(/Y/g, d.getFullYear());
		format = format.replace(/m/g, fillZero(d.getMonth() + 1));
		format = format.replace(/d/g, fillZero(d.getDate()));
		format = format.replace(/wj/g, w[d.getDay()]);
		format = format.replace(/H/g, fillZero(d.getHours()));
		format = format.replace(/i/g, fillZero(d.getMinutes()));
	}
	else{
		if(format == undefined) format = 'wj, m d, Y H:i';
		
		var tmp = '';
		if(format.match(/wj/g))		tmp += 'wj, ';
		if(format.match(/m/g))		tmp += 'm ';
		if(format.match(/d/g))		tmp += 'd, ';
		if(format.match(/Y/g))		tmp += 'Y, ';
		if(format.match(/H:i/g))	tmp += 'H:i';
		if(shift == undefined || shift == false)
			tmp = tmp.replace(/,\s$|\s$/, '');	// 末尾にスペース OR カンマ+スペースなら消す
		if(tmp !== '')
			format = tmp;
		
		var month	= getMonthArray(true);
		var m		= getMonthArray(false);
		//var str =  dow[d.getDay()] + ', ' + months[d.getMonth() + 1] + ' ' + fillZero(d.getDate()) + ', ' + d.getFullYear() + ' ' + fillZero(d.getHours()) + ':' + fillZero(d.getMinutes());
		format = format.replace(/H/g, fillZero(d.getHours()));
		format = format.replace(/i/g, fillZero(d.getMinutes()));
		
		format = format.replace(/Y/g, d.getFullYear());
		if(title == undefined || title == false)
			format = format.replace(/m/g, m[d.getMonth() + 1]);
		else
			format = format.replace(/m/g, month[d.getMonth() + 1]);
		format = format.replace(/d/g, fillZero(d.getDate()));
		if(title == undefined || title == false)
			format = format.replace(/wj/g, w[d.getDay()]);
		else
			format = format.replace(/wj/g, dow[d.getDay()]);
	}
	return format;
}

$(function(){
	if(getLang() == 'ja') {
		$.datepicker.setDefaults($.datepicker.regional['ja']);
	}
	else {
		$.datepicker.setDefaults($.datepicker.regional['en']);
	}
	
	$('.lang_values').each(function(){
		$(this).click(function(){
			var tmp = getBaseUrl() + '/updatelanguages/language/' + $(this).attr('data-value');
			ajaxSubmitUrl(tmp, function(response){
				location.reload(true);
			});
		});
	});
});
