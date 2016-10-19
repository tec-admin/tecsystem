// 指導履歴一覧を作成
function createLeadingList(baseurl, chargeid, chargename, reserveid, page)
{
	deleteSidebarlList();

	if (page === undefined)
		page = 1;

	var request = createXMLHttpRequest();
	var scripturl = baseurl + "/gethistorylist/charge/" + chargeid + "/page/" + page;
	request.open("POST", scripturl , false);
	request.send(null);

	var json = request.responseText;
	var reservelist = JSON.parse(json);

	var sidebar = document.getElementById("sidebar");
	var h1 = document.createElement('h1');
	h1.appendChild(document.createTextNode(chargename + 'さんの指導履歴'));
	sidebar.appendChild(h1);

	var reservetype = ['schedule', 'history'];
	for (var type = 0; type < reservetype.length; type++)
	{
		if (reservelist[reservetype[type]].length > 0)
		{
			var ul = document.createElement('ul');
			ul.setAttribute('class', 'booked');
			for (var i = 0; i < reservelist[reservetype[type]].length; i++)
			{
				var link = baseurl + "/advice/reserveid/" + reservelist[reservetype[type]][i]['id'] + "/charge/" + chargeid + "/page/" + page;

				var li = document.createElement("li");
				if (reserveid == reservelist[reservetype[type]][i]['id'])
					li.setAttribute('class', 'open');

				var rDate = new Date(reservelist[reservetype[type]][i]['reservationdate']);
		    	var w = ["日", "月", "火", "水", "木", "金", "土"];

				var rdate = rDate.getFullYear() + '/' + (rDate.getMonth() + 1) + '/' + rDate.getDate() + '(' + w[rDate.getDay()] + ')';
				var rtime = toHM(reservelist[reservetype[type]][i]['m_timetables_starttime']) + '-' + toHM(reservelist[reservetype[type]][i]['m_timetables_endtime']);

				var name = reservelist[reservetype[type]][i]['reserver_name_jp'];

				li.innerHTML = "<a href=\"" + link + "\"><time>" + rdate + '&nbsp;' + rtime + "</time><span class=\"name\"><i><img src=\"/images/userStudent.png\" height=\"19\" width=\"19\" alt=\"\"></i>" + name +"</span></a>";

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
			prev.setAttribute('onclick', "createConsulList('" + baseurl + "'," + reserveid + "," + reservelist['pages']['previous'] + ");");
		}
		else
		{
			prev.setAttribute('class', 'prev inactive');
			prev.setAttribute('href', "#");
		}
		prev.appendChild(document.createTextNode("前の10件"));
		pager.appendChild(prev);

		var next = document.createElement("a");
		if (reservelist['pages']['next'] > 0)
		{
			next.setAttribute('class', 'next');
			next.setAttribute('href', "javascript:void(0);");
			next.setAttribute('onclick', "createConsulList('" + baseurl + "'," + reserveid + "," + reservelist['pages']['next'] + ");");
		}
		else
		{
			next.setAttribute('class', 'next inactive');
			next.setAttribute('href', "#");
		}
		next.appendChild(document.createTextNode("次の10件"));
		pager.appendChild(next);

		sidebar.appendChild(pager);
	}
}

// 指定ユーザの予約一覧を作成
function createReserveList(baseurl, reserverid, reservername, reserveid, page)
{
	deleteSidebarlList();

	if (page === undefined)
		page = 1;

	var request = createXMLHttpRequest();
	var scripturl = baseurl + "/getreservelist/reserver/" + reserverid + "/page/" + page;
	request.open("POST", scripturl , false);
	request.send(null);

	var json = request.responseText;
	var reservelist = JSON.parse(json);

	var sidebar = document.getElementById("sidebar");
	var h1 = document.createElement('h1');
	h1.appendChild(document.createTextNode(reservername + 'さんの相談一覧'));
	sidebar.appendChild(h1);

	var reservename = ['予定', '履歴'];
	var reservetype = ['schedule', 'history'];
	for (var type = 0; type < reservetype.length; type++)
	{
		if (reservelist[reservetype[type]].length > 0)
		{
			var div = document.createElement('div');
			div.appendChild(document.createTextNode(reservename[type]));
			div.setAttribute('class', 'sub');
			sidebar.appendChild(div);

			var ul = document.createElement('ul');
			ul.setAttribute('class', 'booked');
			for (var i = 0; i < reservelist[reservetype[type]].length; i++)
			{
				var link = baseurl + "/advice/reserveid/" + reservelist[reservetype[type]][i]['id'] + "/reserver/" + reserverid + "/page/" + page;

				var li = document.createElement("li");
				if (reserveid == reservelist[reservetype[type]][i]['id'])
					li.setAttribute('class', 'open');

				var rDate = new Date(reservelist[reservetype[type]][i]['reservationdate']);
		    	var w = ["日", "月", "火", "水", "木", "金", "土"];

				var rdate = rDate.getFullYear() + '/' + (rDate.getMonth() + 1) + '/' + rDate.getDate() + '(' + w[rDate.getDay()] + ')';
				var rtime = toHM(reservelist[reservetype[type]][i]['m_timetables_starttime']) + '-' + toHM(reservelist[reservetype[type]][i]['m_timetables_endtime']);
				var title = reservelist[reservetype[type]][i]['m_dockinds_document_category'] + '/' + reservelist[reservetype[type]][i]['m_places_consul_place'];

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
			prev.setAttribute('onclick', "createConsulList('" + baseurl + "'," + reserveid + "," + reservelist['pages']['previous'] + ");");
		}
		else
		{
			prev.setAttribute('class', 'prev inactive');
			prev.setAttribute('href', "#");
		}
		prev.appendChild(document.createTextNode("前の10件"));
		pager.appendChild(prev);

		var next = document.createElement("a");
		if (reservelist['pages']['next'] > 0)
		{
			next.setAttribute('class', 'next');
			next.setAttribute('href', "javascript:void(0);");
			next.setAttribute('onclick', "createConsulList('" + baseurl + "'," + reserveid + "," + reservelist['pages']['next'] + ");");
		}
		else
		{
			next.setAttribute('class', 'next inactive');
			next.setAttribute('href', "#");
		}
		next.appendChild(document.createTextNode("次の10件"));
		pager.appendChild(next);

		sidebar.appendChild(pager);
	}
}


// 授業科目の相談一覧
function createSubjectList(baseurl, subjectid, subjectname, reserveid, page)
{
	deleteSidebarlList();

	if (page === undefined)
		page = 1;

	var request = createXMLHttpRequest();
	var scripturl = baseurl + "/getreservelist/subject/" + subjectid + "/page/" + page;
	request.open("POST", scripturl , false);
	request.send(null);

	var json = request.responseText;
	var reservelist = JSON.parse(json);

	var sidebar = document.getElementById("sidebar");
	var h1 = document.createElement('h1');
	h1.appendChild(document.createTextNode(subjectname + 'の相談一覧'));
	sidebar.appendChild(h1);

	var reservename = ['予定', '履歴'];
	var reservetype = ['schedule', 'history'];
	for (var type = 0; type < reservetype.length; type++)
	{
		if (reservelist[reservetype[type]].length > 0)
		{
			var div = document.createElement('div');
			div.appendChild(document.createTextNode(reservename[type]));
			div.setAttribute('class', 'sub');
			sidebar.appendChild(div);

			var ul = document.createElement('ul');
			if (type == 0)
				ul.setAttribute('class', 'booked');

			for (var i = 0; i < reservelist[reservetype[type]].length; i++)
			{
				var link = baseurl + "/advice/reserveid/" + reservelist[reservetype[type]][i]['id'] + "/subject/" + subjectid + "/page/" + page;

				var li = document.createElement("li");
				if (reserveid == reservelist[reservetype[type]][i]['id'])
					li.setAttribute('class', 'open');

				var rDate = new Date(reservelist[reservetype[type]][i]['reservationdate']);
		    	var w = ["日", "月", "火", "水", "木", "金", "土"];

				var rdate = rDate.getFullYear() + '/' + (rDate.getMonth() + 1) + '/' + rDate.getDate() + '(' + w[rDate.getDay()] + ')';
				var rtime = toHM(reservelist[reservetype[type]][i]['m_timetables_starttime']) + '-' + toHM(reservelist[reservetype[type]][i]['m_timetables_endtime']);

				var name = reservelist[reservetype[type]][i]['reserver_name_jp'];

				li.innerHTML = "<a href=\"" + link + "\"><time>" + rdate + '&nbsp;' + rtime + "</time><span class=\"name\"><i><img src=\"/images/userStudent.png\" height=\"19\" width=\"19\" alt=\"\"></i>" + name +"</span></a>";

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
			prev.setAttribute('onclick', "createConsulList('" + baseurl + "'," + reserveid + "," + reservelist['pages']['previous'] + ");");
		}
		else
		{
			prev.setAttribute('class', 'prev inactive');
			prev.setAttribute('href', "#");
		}
		prev.appendChild(document.createTextNode("前の10件"));
		pager.appendChild(prev);

		var next = document.createElement("a");
		if (reservelist['pages']['next'] > 0)
		{
			next.setAttribute('class', 'next');
			next.setAttribute('href', "javascript:void(0);");
			next.setAttribute('onclick', "createConsulList('" + baseurl + "'," + reserveid + "," + reservelist['pages']['next'] + ");");
		}
		else
		{
			next.setAttribute('class', 'next inactive');
			next.setAttribute('href', "#");
		}
		next.appendChild(document.createTextNode("次の10件"));
		pager.appendChild(next);

		sidebar.appendChild(pager);
	}
}


function deleteSidebarlList()
{
	var list = document.getElementById('sidebar');
	while (list.firstChild)
		list.removeChild(list.firstChild);
}
