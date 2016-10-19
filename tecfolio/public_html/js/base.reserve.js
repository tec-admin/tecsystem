// 予約リストを作成
function createReserveList(baseurl, reserveid, page)
{
	deleteReserveList();

	if (page === undefined)
		page = 1;

	var request = createXMLHttpRequest();
	var scripturl = baseurl + "/getreservelist/page/" + page;
	request.open("POST", scripturl , false);
	request.send(null);

	var json = request.responseText;

	var reservelist = JSON.parse(json);
	var ul = document.getElementById("reservelist");

	for (var i = 0; i < reservelist['data'].length; i++)
	{
		if(reservelist['data'][i+1] == undefined || (reservelist['data'][i]['id'] != reservelist['data'][i+1]['id']))
		{
			if (reservelist['data'][i]['history'] == true)
				var link = baseurl + "/history/reserveid/" + reservelist['data'][i]['id'] + "/page/" + page;
			else
				var link = baseurl + "/editreserve/reserveid/" + reservelist['data'][i]['id'] + "/page/" + page;

			li = document.createElement("li");
			if (reserveid == reservelist['data'][i]['id'])
				li.setAttribute('class', 'open');

			var rDate = new Date(reservelist['data'][i]['reservationdate']);
	    	//var w = ["日", "月", "火", "水", "木", "金", "土"];
			//var w = getDowArray();

			//var rdate = rDate.getFullYear() + '/' + (rDate.getMonth() + 1) + '/' + rDate.getDate() + '(' + w[rDate.getDay()] + ')';
			var rdate = dateFormat(rDate, 'Y/m/d(wj)', false, true, true);
			var rtime = toHM(reservelist['data'][i]['m_timetables_starttime']) + '-' + toHM(reservelist['data'][i]['m_timetables_endtime']);
			
			if(document.URL.match('/kwl/'))
			{
				var title_first = reservelist['data'][i]['m_dockinds_document_category'];
			}
			else
			{
				var title_first = reservelist['data'][i]['m_dockinds_clipped_form'];
			}
			
			var title = title_first + '/' + reservelist['data'][i]['m_places_consul_place'];

			li.innerHTML = "<a href=\"" + link + "\"><time>" + rdate + '&nbsp;' + rtime + "</time><span class=\"detail\">" + title +"</span></a>";

			ul.appendChild(li);
		}
	}

	if (reservelist['pages']['pageCount'] > 0)
	{
		var pager = document.getElementById('reservepager');
		while (pager.firstChild)
			pager.removeChild(pager.firstChild);

		var prev = document.createElement("a");
		if (reservelist['pages']['previous'] > 0)
		{
			prev.setAttribute('class', 'prev');
			prev.setAttribute('href', "javascript:void(0);");
			prev.setAttribute('onclick', "createReserveList('" + baseurl + "','" + reserveid + "'," + reservelist['pages']['previous'] + ");");
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
		if (reservelist['pages']['next'] > 0)
		{
			next.setAttribute('class', 'next');
			next.setAttribute('href', "javascript:void(0);");
			next.setAttribute('onclick', "createReserveList('" + baseurl + "','" + reserveid + "'," + reservelist['pages']['next'] + ");");
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

function deleteReserveList()
{
	var list = document.getElementById('reservelist');
	if(list != null)
		while (list.firstChild)
			list.removeChild(list.firstChild);
}

// 指定ユーザの予約一覧を作成
function createUserReserveList(baseurl, reserverid, page)
{
	deleteSidebarlList();

	if (page === undefined)
		page = 1;

	var request = createXMLHttpRequest();
	var scripturl = baseurl + "/getreservelist/reserverid/" + reserverid + "/page/" + page;
	request.open("POST", scripturl , false);
	request.send(null);

	var json = request.responseText;
	var reservelist = JSON.parse(json);

	var sidebar = document.getElementById("sidebar");
	var h1 = document.createElement('h1');
	
	h1.setAttribute('data-localize', '%1さんの相談一覧');
	if (reservelist['history'][0] !== undefined)
	{
		//h1.appendChild(document.createTextNode( reservelist['history'][0]['reserver_name_jp'] + 'さんの相談一覧'));
		h1.setAttribute('data-arg1', reservelist['history'][0]['reserver_name_jp']);
	}
	else
	{
		//h1.appendChild(document.createTextNode( reservelist['schedule'][0]['reserver_name_jp'] + 'さんの相談一覧'));
		h1.setAttribute('data-arg1', reservelist['schedule'][0]['reserver_name_jp']);
	}
	sidebar.appendChild(h1);

	var reservename = ['予定', '履歴'];
	var reservetype = ['schedule', 'history'];
	for (var type = 0; type < reservetype.length; type++)
	{
		if (reservelist[reservetype[type]].length > 0)
		{
			var div = document.createElement('div');
			//div.appendChild(document.createTextNode(reservename[type]));
			div.setAttribute('data-localize', reservename[type]);
			div.setAttribute('class', 'sub');
			sidebar.appendChild(div);

			var ul = document.createElement('ul');
			ul.setAttribute('class', 'booked');
			for (var i = 0; i < reservelist[reservetype[type]].length; i++)
			{
				var link = baseurl + "/advice/reserveid/" + reservelist[reservetype[type]][i]['id'] + "/page/" + page;

				var li = document.createElement("li");
				if (reserverid == reservelist[reservetype[type]][i]['id'])
					li.setAttribute('class', 'open');

				var rDate = new Date(reservelist[reservetype[type]][i]['reservationdate']);
		    	//var w = ["日", "月", "火", "水", "木", "金", "土"];
				//var w = getDowArray();

				//var rdate = rDate.getFullYear() + '/' + (rDate.getMonth() + 1) + '/' + rDate.getDate() + '(' + w[rDate.getDay()] + ')';
				var rdate = dateFormat(rDate, 'Y/m/d(wj)', false, true, true);
				var rtime = toHM(reservelist[reservetype[type]][i]['m_timetables_starttime']) + '-' + toHM(reservelist[reservetype[type]][i]['m_timetables_endtime']);
				
				if(document.URL.match('/kwl/'))
				{
					var title_first = reservelist['data'][i]['m_dockinds_document_category'];
				}
				else
				{
					var title_first = reservelist['data'][i]['m_dockinds_clipped_form'];
				}
				
				var title = title_first + '/' + reservelist[reservetype[type]][i]['m_places_consul_place'];

				li.innerHTML = "<a href=\"" + link + "\"><time>" + rdate + '&nbsp;' + rtime + "</time><span class=\"detail\">" + title +"</span></a>";

				ul.appendChild(li);
			}
			sidebar.appendChild(ul);
		}
	}

	if (reservelist['pages']['pageCount'] > 0)
	{
		var pager = document.createElement('div');
		pager.setAttribute('class', 'pager');

		var prev = document.createElement("a");
		if (reservelist['pages']['previous'] > 0)
		{
			prev.setAttribute('class', 'prev');
			prev.setAttribute('href', "javascript:void(0);");
			prev.setAttribute('onclick', "createConsulList('" + baseurl + "','" + reserveid + "'," + reservelist['pages']['previous'] + ");");
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
		if (reservelist['pages']['next'] > 0)
		{
			next.setAttribute('class', 'next');
			next.setAttribute('href', "javascript:void(0);");
			next.setAttribute('onclick', "createConsulList('" + baseurl + "','" + reserverid + "'," + reservelist['pages']['next'] + ");");
		}
		else
		{
			next.setAttribute('class', 'next inactive');
			next.setAttribute('href', "#");
		}
		//next.appendChild(document.createTextNode("次の10件"));
		next.setAttribute('data-localize', '次の10件');
		pager.appendChild(next);

		sidebar.appendChild(pager);
	}
	
	jqTranslate();
}

function deleteSidebarlList()
{
	var list = document.getElementById('sidebar');
	if(list != null)
		while (list.firstChild)
			list.removeChild(list.firstChild);
}


// 相談一覧を作成
function createConsulList(baseurl, reserveid, page, reserver, subjectid, chargeid)
{
	deleteConsulList();

	if (page === undefined)
		page = 1;

	if (reserver === undefined)
		reserver = '0';

	if (subjectid === undefined)
		subjectid = '0';

	if (chargeid === undefined)
		chargeid = '0';

	var request = createXMLHttpRequest();
	var scripturl = baseurl + "/getreservelist/page/" + page + "/reserver/" + reserver + "/subjectid/" + subjectid + "/chargeid/" + chargeid;
	request.open("POST", scripturl , false);
	request.send(null);

	var json = request.responseText;
	var reservelist = JSON.parse(json);

	var sidebar = document.getElementById("sidebar");
	var h1 = document.createElement('h1');
	if(reserveid == '0' && reserver == '0' && subjectid == '0' && chargeid == '0')
	{
		if(reservelist['schedule'].length == "0" && reservelist['history'].length == "0" && sidebar != undefined){
			sidebar.remove(0);
			h1.remove(0);
		}else{
			//h1.appendChild(document.createTextNode( $('#loginStatusTrigger').text() + 'さんの相談一覧'));
			h1.setAttribute('data-localize', '%1さんの相談一覧');
			h1.setAttribute('data-arg1', $('#loginStatusTrigger').text());
		}
	}
	else if(reserver == '0' && subjectid == '0' && chargeid == '0')
	{
		//h1.appendChild(document.createTextNode( $('#loginStatusTrigger').text() + 'さんの相談一覧'));
		h1.setAttribute('data-localize', '%1さんの相談一覧');
		h1.setAttribute('data-arg1', $('#loginStatusTrigger').text());
	}
	else if(reserver !== '0')
	{
		h1.setAttribute('data-localize', '%1さんの相談一覧');
		if (reservelist['history'][0] !== undefined)
		{
			//h1.appendChild(document.createTextNode( reservelist['history'][0]['name_jp'] + 'さんの相談一覧'));
			h1.setAttribute('data-arg1', reservelist['history'][0]['name_jp']);
		}
		else
		{
			//h1.appendChild(document.createTextNode( reservelist['schedule'][0]['name_jp'] + 'さんの相談一覧'));
			h1.setAttribute('data-arg1', reservelist['schedule'][0]['name_jp']);
		}
	}
	else if(subjectid !== '0')
	{
		h1.setAttribute('data-localize', '%1の相談一覧');
		if (reservelist['history'][0] !== undefined)
		{
			//h1.appendChild(document.createTextNode( reservelist['history'][0]['class_subject'] + 'の相談一覧'));
			h1.setAttribute('data-arg1', reservelist['history'][0]['class_subject']);
		}
		else
		{
			//h1.appendChild(document.createTextNode( reservelist['schedule'][0]['class_subject'] + 'の相談一覧'));
			h1.setAttribute('data-arg1', reservelist['schedule'][0]['class_subject']);
		}
	}
	else if(chargeid !== '0')
	{
		h1.setAttribute('data-localize', '%1さんの担当一覧');
		if (reservelist['history'][0] !== undefined)
		{
			//h1.appendChild(document.createTextNode( reservelist['history'][0]['t_leadings_name_jp'] + 'さんの担当一覧'));
			h1.setAttribute('data-arg1', reservelist['history'][0]['t_leadings_name_jp']);
		}
		else
		{
			//h1.appendChild(document.createTextNode( reservelist['schedule'][0]['t_leadings_name_jp'] + 'さんの担当一覧'));
			h1.setAttribute('data-arg1', reservelist['schedule'][0]['t_leadings_name_jp']);
		}
	}
	else
	{
		//h1.appendChild(document.createTextNode('すべての相談一覧'));
		h1.setAttribute('data-localize', 'すべての相談一覧');
	}
	sidebar.appendChild(h1);

	if (reservelist['schedule'].length > 0)
	{
		var div = document.createElement('div');
		//div.appendChild(document.createTextNode('予定'));
		div.setAttribute('data-localize', '予定');
		div.setAttribute('class', 'sub');
		sidebar.appendChild(div);

		var ul = document.createElement('ul');
		ul.setAttribute('class', 'booked');
		for (var i = 0; i < reservelist['schedule'].length; i++)
		{
			var link = baseurl + "/advice/reserveid/" + reservelist['schedule'][i]['id'] + "/page/" + page + "/reserver/" + reserver + "/subjectid/" + subjectid + "/chargeid/" + chargeid;

			var li = document.createElement("li");
			if (reserveid == reservelist['schedule'][i]['id'])
			{
				li.setAttribute('class', 'open');
			}

			var rDate = new Date(reservelist['schedule'][i]['reservationdate']);
			var rstaff = reservelist['schedule'][i]['t_leadings_name_jp'];
			var rstudent = reservelist['schedule'][i]['name_jp'];
			var rdate = dateFormat(rDate, 'm/d(wj)', false, true, true);
			var rtime = toHM(reservelist['schedule'][i]['m_timetables_starttime']) + '～'<!-- + toHM(reservelist['schedule'][i]['m_timetables_endtime'])-->;
			
			var title = reservelist['schedule'][i]['m_dockinds_document_category'];

			if(rstaff == null){
				var rstaff = "<span data-localize=\"未確定\"></span>";
			}

			li.innerHTML = "<a href=\"" + link + "\"><time>" + rdate + '&nbsp;' + rtime + '&nbsp;' + rstudent + "</time><span class=\"detail\">" + title + "</span>" + rstaff + "</a>";

			ul.appendChild(li);
		}
		sidebar.appendChild(ul);
	}

	if (reservelist['history'].length > 0)
	{
		var div = document.createElement('div');
		div.setAttribute('data-localize', '履歴');
		div.setAttribute('class', 'sub');
		sidebar.appendChild(div);

		var ul = document.createElement('ul');
		for (var i = 0; i < reservelist['history'].length; i++)
		{
			var link = baseurl + "/advice/reserveid/" + reservelist['history'][i]['id'] + "/page/" + page + "/reserver/" + reserver + "/subjectid/" + subjectid + "/chargeid/" + chargeid;

			var li = document.createElement("li");
			if (reserveid == reservelist['history'][i]['id'])
				li.setAttribute('class', 'open');

			var rDate = new Date(reservelist['history'][i]['reservationdate']);
			var w = getDowArray();

			var rstaff = reservelist['history'][i]['charge_name_jp'];
			var rstudent = reservelist['history'][i]['name_jp'];
			var rdate = dateFormat(rDate, 'm/d(wj)', false, true, true);
			var rtime = toHM(reservelist['history'][i]['m_timetables_starttime']) + '～'<!-- + toHM(reservelist['history'][i]['m_timetables_endtime'])-->;
			
			var title = reservelist['history'][i]['m_dockinds_document_category'];

			if(rstaff == null){
				var rstaff = "<span data-localize=\"未確定\"></span>";
			}

			li.innerHTML = "<a href=\"" + link + "\"><time>" + rdate + '&nbsp;' + rtime + '&nbsp;' + rstudent + "</time><span class=\"detail\">" + title + "</span>" + rstaff + "</a>";

			ul.appendChild(li);
		}
		sidebar.appendChild(ul);
	}

	if (reservelist['pages']['pageCount'] > 0)
	{
		var pager = document.createElement('div');
		pager.setAttribute('class', 'pager');

		var prev = document.createElement("a");
		if (reservelist['pages']['previous'] > 0)
		{
			prev.setAttribute('class', 'prev');
			prev.setAttribute('href', "javascript:void(0);");
			prev.setAttribute('onclick', "createConsulList('" + baseurl + "','" + reserveid + "'," + reservelist['pages']['previous'] + ",'" + reserver + "','" + subjectid + "','" + chargeid + "');");
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
		if (reservelist['pages']['next'] > 0)
		{
			next.setAttribute('class', 'next');
			next.setAttribute('href', "javascript:void(0);");
			next.setAttribute('onclick', "createConsulList('" + baseurl + "','" + reserveid + "'," + reservelist['pages']['next'] + ",'" + reserver + "','" + subjectid + "','" + chargeid + "');");
		}
		else
		{
			next.setAttribute('class', 'next inactive');
			next.setAttribute('href', "#");
		}
		//next.appendChild(document.createTextNode("次の10件"));
		next.setAttribute('data-localize', '次の10件');
		pager.appendChild(next);

		sidebar.appendChild(pager);
	}
	
	jqTranslate();
}

function deleteConsulList()
{
	var list = document.getElementById('sidebar');
	if(list !== null){
		while (list.firstChild)
			list.removeChild(list.firstChild);
	}
}
