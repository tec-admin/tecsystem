// おしらせを作成
function createHistoryList(baseurl, reserveid, page, today)
{
	deleteHistoryList();

	if (page === undefined)
		page = 1;

	today = today.split("-").join("/");
	var tDate = new Date(today);

	var request = createXMLHttpRequest();
	var scripturl = baseurl + "/labo/gethistorylist/page/" + page;
	request.open("POST", scripturl , false);
	request.send(null);

	var json = request.responseText;
	var historylist = JSON.parse(json);
	var ul = document.getElementById("historylist");
	for (var i = 0; i < historylist['data'].length; i++)
	{
		if(historylist['data'][i+1] == undefined || (historylist['data'][i]['id'] != historylist['data'][i+1]['id']))
		{
			var link = baseurl + "/labo/history/reserveid/" + historylist['data'][i]['id'] + "/page/" + page;
	
			var reservationdate = historylist['data'][i]['reservationdate'];
			reservationdate = reservationdate.split("-").join("/");
			var rDate = new Date(reservationdate);
	
			li = document.createElement("li");
			if (reserveid == historylist['data'][i]['id'])
				li.setAttribute('class', 'open');
			else if (tDate.getFullYear() == rDate.getFullYear() && tDate.getMonth() == rDate.getMonth() && tDate.getDate() == rDate.getDate())
				li.setAttribute('class', 'today');
	
	    	//var w = ["日", "月", "火", "水", "木", "金", "土"];
			//var w = getDowArray();
			//var rdate = rDate.getFullYear() + '/' + (rDate.getMonth() + 1) + '/' + rDate.getDate() + '(' + w[rDate.getDay()] + ')&nbsp;' + toHM(historylist['data'][i]['m_timetables_starttime']) + '-' + toHM(historylist['data'][i]['m_timetables_endtime']);
			var rdate = dateFormat(rDate, 'Y/m/d(wj)', false, true, true) + '&nbsp;' + String.fromCharCode(65 + (historylist['data'][i]['m_shifts_dayno']-1)) + '&nbsp;' + toHM(historylist['data'][i]['m_timetables_starttime']) + '-' + toHM(historylist['data'][i]['m_timetables_endtime']);
			
			if(document.URL.match('/kwl/'))
			{
				var title_first = historylist['data'][i]['m_dockinds_document_category'];
			}
			else
			{
				var title_first = historylist['data'][i]['m_dockinds_clipped_form'];
			}
			
			var title = title_first + ' / ' + historylist['data'][i]['m_places_consul_place'];
	
			li.innerHTML = "<a href=\"" + link + "\"><time>" + rdate + "</time><span class=\"detail\">" + title + "</span></a>";
			ul.appendChild(li);
		}
	}
	
	if (historylist['data'].length == 0)
	{
		li = document.createElement("li");
		li.setAttribute('class', 'norire');
		ul.appendChild(li);
	}
	if (historylist['pages']['pageCount'] > 0)
	{
		var pager = document.getElementById('historypager');
		while (pager.firstChild)
			pager.removeChild(pager.firstChild);

		var prev = document.createElement("a");
		if (historylist['pages']['previous'] > 0)
		{
			prev.setAttribute('class', 'prev');
			prev.setAttribute('href', "javascript:void(0);");
			prev.setAttribute('onclick', "createHistoryList('" + baseurl + "','" + reserveid + "'," + historylist['pages']['previous'] + ",'" + today  +"');");
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
		if (historylist['pages']['next'] > 0)
		{
			next.setAttribute('class', 'next');
			next.setAttribute('href', "javascript:void(0);");
			next.setAttribute('onclick', "createHistoryList('" + baseurl + "','" + reserveid + "'," + historylist['pages']['next'] + ",'" + today  +"');");
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

function deleteHistoryList()
{
	var list = document.getElementById('historylist');
	while (list.firstChild)
		list.removeChild(list.firstChild);
}
