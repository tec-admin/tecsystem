var toDD = function(num)
{
	num += "";
	if (num.length === 1)
		num = "0" + num;
	return num;
}

var toHM = function(timestring)
{
	var rTime = new Date(Date.parse("2014/01/01 " + timestring));
	return toDD(rTime.getHours()) + ':' + toDD(rTime.getMinutes());
}

var toAlpha = function(num)
{
	if (typeof num != 'number')
		num = Number(num);
	return String.fromCharCode(65 + num);
}

function createXMLHttpRequest()
{
	// XMLHttpRequest オブジェクトを作成する 
	if(window.addEventListener)
	{ // Firefox 用
		return new XMLHttpRequest();
	}
	else
	{	// IE 用
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
}

