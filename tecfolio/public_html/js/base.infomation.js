// おしらせを作成
function createInfomationList(baseurl, infomationid, page)
{
	deleteInfomationList();

	if (page === undefined)
		page = 1;

	var request = createXMLHttpRequest();
	var scripturl = baseurl + "/getinfomationlist/page/" + page;
	request.open("POST", scripturl , false);
	request.send(null);

	var json = request.responseText;
	var infomationlist = JSON.parse(json);
	var ul = document.getElementById("infomationlist");
	for (var i = 0; i < infomationlist['data'].length; i++)
	{
		var link = baseurl + "/information/informationid/" + infomationlist['data'][i]['id'] + "/page/" + page;
		var li = document.createElement("li");
		if (infomationid == infomationlist['data'][i]['id'])
			li.setAttribute('class', 'open');

		var createdate = infomationlist['data'][i]['createdate'];
		createdate = createdate.split("-").join("/");

		var rDate = new Date(createdate);
    	//var w = ["日", "月", "火", "水", "木", "金", "土"];
		//var w = getDowArray();
		//var rdate = rDate.getFullYear() + '/' + (rDate.getMonth() + 1) + '/' + rDate.getDate() + '(' + w[rDate.getDay()] + ')&nbsp;' + toDD(rDate.getHours()) + ':' + toDD(rDate.getMinutes());
		var rdate = dateFormat(rDate, 'Y/m/d(wj)');
		var title = infomationlist['data'][i]['title'];

		li.innerHTML = "<a href=\"" + link + "\"><span class=\"detail\">" + title + "</span><time data-localize=\"掲示：%1\" data-arg1=\""+ rdate + "\"></time></a>";
		ul.appendChild(li);
	}

	if (infomationlist['pages']['pageCount'] > 0)
	{
		var pager = document.getElementById('infomationpager');
		while (pager.firstChild)
			pager.removeChild(pager.firstChild);

		var prev = document.createElement("a");
		if (infomationlist['pages']['previous'] > 0)
		{
			prev.setAttribute('class', 'prev');
			prev.setAttribute('href', "javascript:void(0);");
			prev.setAttribute('onclick', "createInfomationList('" + baseurl + "'," + infomationid + "," + infomationlist['pages']['previous'] + ");");
		}
		else
		{
			prev.setAttribute('class', 'prev inactive');
			prev.setAttribute('href', "#");
		}
		//prev.appendChild(document.createTextNode("前の10件"));
		prev.setAttribute('data-localize', '前の10件');
		pager.appendChild(prev);

		var next = document.createElement("a");
		if (infomationlist['pages']['next'] > 0)
		{
			next.setAttribute('class', 'next');
			next.setAttribute('href', "javascript:void(0);");
			next.setAttribute('onclick', "createInfomationList('" + baseurl + "'," + infomationid + "," + infomationlist['pages']['next'] + ");");
		}
		else
		{
			next.setAttribute('class', 'next inactive');
			next.setAttribute('href', "#");
		}
		//next.appendChild(document.createTextNode("次の10件"));
		next.setAttribute('data-localize', '次の10件');
		pager.appendChild(next);
	}
	
	jqTranslate();
}

function deleteInfomationList()
{
	var list = document.getElementById('infomationlist');
	while (list.firstChild)
		list.removeChild(list.firstChild);
}
