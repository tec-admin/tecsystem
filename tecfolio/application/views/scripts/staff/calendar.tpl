<!doctype html>
<html lang="ja">
<head>
<!--
	{t}%1人{/t}
	{t}%1の予約 %2件{/t}
	{t}相談者を指定してください{/t}
	{t}文書の種類を選択してください{/t}
	{t}相談場所を選択してください{/t}
	{t}既に同じ相談者の予約が登録されています{/t}
-->
{include file='staff/header.tpl'}
<script src="/js/base.reserve.js" type="text/javascript"></script>
<script src="/js/jquery.tableAction.js" type="text/javascript"></script>
<script>
	window.onload = function()
	{
		var baseurl = '{$baseurl}/{$controllerName}';

		createShiftHead(baseurl, {if empty($campusid)}1{else}'{$campusid}'{/if});
		createShiftCalender(baseurl, {if empty($campusid)}1{else}'{$campusid}'{/if});
		createTodayReserve(baseurl, {if empty($campusid)}1{else}'{$campusid}'{/if});
		activcheck();
		
		jqTranslate();
	}

	function createShiftHead(baseurl, campusid)
	{
		var tr = document.getElementById('shifthead');
		while (tr.firstChild)
			tr.removeChild(tr.firstChild);

		var th = document.createElement('th');
		th.setAttribute('class', 'blank');
		tr.appendChild(th);

		var $dir = location.href.split("/");
	    var dir = $dir[$dir.length -1];


		{foreach from=$weeks item=week name=weeks }
			var index = {$smarty.foreach.weeks.index};

			var todayFull	= '{$vDate->dateFormat($week, 'Ymd', true)}';
			var weekMd		= '{$vDate->dateFormat($week, 'm/d', true)}';
			var todayMd		= '{$vDate->dateFormat($ymd, 'm/d', true)}';
			var weekMdwj	= '{$vDate->dateFormat($week, 'm/d (wj)')}'
			
			var th = document.createElement('th');
			var link = '{$baseurl}/{$controllerName}/{$actionName}/shiftclass/' + campusid + '/ymd/{$week}';
			th.setAttribute('onclick', 'location.href="' + link + '";');
			
			var span = document.createElement('span');
			span.appendChild(document.createTextNode(weekMdwj));
				
			if(myymd == todayFull && weekMd == todayMd){
				th.setAttribute('class', 'today');
				span.setAttribute('class', 'day3');
			}
			else if(myymd == todayFull && myymd != todayMd ){
				th.setAttribute('class', 'today2');
				span.setAttribute('class', 'day4');
			}
			else if(weekMd == todayMd){
				th.setAttribute('class', 'day2');
				span.setAttribute('class', 'day');
			}
			else{
				span.setAttribute('class', 'day');
			}
			th.appendChild(span);
			

			tr.appendChild(th);
		{/foreach}
	}

	function selectCampus(campusid)
	{
		var old = document.getElementById('facility').value;
		if (old != campusid)
		{
			var link = '{$baseurl}/{$controllerName}/{$actionName}/shiftclass/' + campusid {if !empty($ymd)} + '/ymd/{$ymd}' {/if};
			document.location = link;
		}
	}

	function createShiftCalender(baseurl, campusid)
	{
		deleteShiftCalender();

		var tbody = document.getElementById('shiftinput');

		// シフト入力表を作成
		var arShifts = [];
		{foreach from=$shifts item=shift name=shifts}
			arShifts[arShifts.length] = {$shift->dayno};
			var tr = document.createElement('tr');
			var th = document.createElement('th');
			var shiftdata = toAlpha({$smarty.foreach.shifts.index}) + ' {$vDate->dateFormat($shift->m_timetables_starttime, 'H:i')}-{$vDate->dateFormat($shift->m_timetables_endtime, 'H:i')}';
			th.appendChild(document.createTextNode(shiftdata))
			tr.appendChild(th);
			{foreach from=$weeks item=week name=weeks}
				var dow = {$vDate->dateFormat($week, 'w')};

				var td = document.createElement('td');
				var id = dow + '_{$shift->dayno}';	// 曜日 + 連番
				td.setAttribute('id', id);
				td.setAttribute('data-dayno', '{$shift->dayno}');
				td.setAttribute('data-dow', dow);
				td.setAttribute('reserve', 0);
				td.setAttribute('date', '{$week}');
				td.setAttribute('datejp', '{$vDate->dateFormat($week, 'Y/m/d(wj)')}');
				td.setAttribute('starttime', '{$shift->m_timetables_starttime}');
				td.setAttribute('endtime', '{$shift->m_timetables_endtime}');

				td.setAttribute('onclick', "createShiftDetail('" + baseurl + "','" + campusid + "','" + id + "');");

				td.appendChild(document.createTextNode(' '));

				tr.appendChild(td);
			{/foreach}

			tbody.appendChild(tr);
		{/foreach}


		// スタッフのシフトを取得し、入力表へ設定
		var request = createXMLHttpRequest();
		var scripturl = baseurl + "/getshiftinput/actionname/calendar/shiftclass/" + campusid {if !empty($weektop)} + '/ymd/{$weektop}' {/if};
		request.open("POST", scripturl , false);
		request.send(null);
		var json = request.responseText;
		var staffshift = JSON.parse(json);

		for (var dow in staffshift['shiftinput'])
		{
			for (var dayno in staffshift['shiftinput'][dow])
			{
				var td = document.getElementById(dow + '_' + dayno);
				td.setAttribute('class', staffshift['shiftinput'][dow][dayno]['class']);
			}
		}

		// 今週の予約を取得
		var request = createXMLHttpRequest();
		var scripturl = baseurl + "/getweekreserve/shiftclass/" + campusid {if !empty($weektop)} + '/ymd/{$weektop}' {/if};
		request.open("POST", scripturl , false);
		request.send(null);
		var json = request.responseText;
		var weekreserve = JSON.parse(json);
		for (var i = 0; i < weekreserve.length; i++)
		{
			var dayno = weekreserve[i]['m_shifts_dayno'];
			var dow = weekreserve[i]['dow'];

			// 予約数を増やす
			var id = dow + '_' + dayno;
			var td = document.getElementById(id);
			var reservecount = td.getAttribute('reserve');
			reservecount++;

			td.setAttribute('reserve', reservecount);
			td.setAttribute('title', '予約数:' + reservecount + '件');

			if(baseurl == "/kwl/staff")
			{
				var person = document.getElementById('person_' + id);
				if (person == null)
				{
					person = document.createElement('i');
					person.setAttribute('id', 'person_' + id);
					person.setAttribute('class', 'person');
					td.appendChild(person);
				}
			}
		}

		// JQueryのイベントをバインド
		$("#shiftCalendar").find("table").each(function(){
			$(this).tableAction();
			$(this).tooltip({
				position: {
					my: 'right top', at: 'right bottom-5', collision: 'none'
				}
			});
		});
	}

	function deleteShiftCalender()
	{
		var list = document.getElementById('shiftinput');
		while (list.firstChild)
			list.removeChild(list.firstChild);
	}

	// 本日の予約一覧
	function createTodayReserve(baseurl, campusid)
	{

		var sidebar = deleteSidebar();
		var title = document.createElement('h1');

		var $dir = location.href.split("/");
	    var dir = $dir[$dir.length -1];

		if(dir.indexOf("week") == 0){
		}
		else{
			var todayAll = document.createElement('ul');
			todayAll.setAttribute('class', 'todayAll');

			// 本日の予約を取得
			var request = createXMLHttpRequest();
			var scripturl = baseurl + "/gettodayreserve/shiftclass/" + campusid {if !empty($ymd)} + '/ymd/{$ymd}' {/if};
			request.open("POST", scripturl , false);
			request.send(null);
			var json = request.responseText;
			var reservelist = JSON.parse(json);
			var dayno = 0;
			var li, ul;

			//title.appendChild(document.createTextNode('{$vDate->dateFormat($ymd, 'Y年m月d日(wj)')}の予約 ' + reservelist.length + '件'));
			title.setAttribute('data-localize', '%1の予約 %2件');
			title.setAttribute('data-arg1', "{$vDate->dateFormat($ymd, 'Y年m月d日(wj)')}");
			title.setAttribute('data-arg2', reservelist.length);
			sidebar.appendChild(title);

			for (var i = 0; i < reservelist.length; i++)
			{
				if (dayno != reservelist[i]['m_shifts_dayno'])
				{
					if (dayno != 0)
					{
						li.appendChild(ul);
						todayAll.appendChild(li);
					}

					li = document.createElement('li');
					var tagtime = document.createElement('time');
					var alpha = toAlpha(reservelist[i]['m_shifts_dayno'] - {$shifts[0]->dayno});
					tagtime.setAttribute('class', 'sub');
					tagtime.appendChild(document.createTextNode(alpha + '. ' + toHM(reservelist[i]['m_timetables_starttime']) + '-' + toHM(reservelist[i]['m_timetables_endtime'])));
					li.appendChild(tagtime);
					ul = document.createElement('ul');

					dayno = reservelist[i]['m_shifts_dayno'];
				}

				var reserveli = document.createElement('li');
				var a = document.createElement('a');
				a.setAttribute('href', baseurl + '/advice/reserveid/' + reservelist[i]['id']);

				var index = document.createElement('i');
				index.setAttribute('class', 'index');
				//index.appendChild(document.createTextNode((i+1) + '.'));
				a.appendChild(index);

				var name = document.createElement('span');
				name.setAttribute('class', 'name');
				name.appendChild(document.createTextNode(reservelist[i]['name_jp']));
				a.appendChild(name);

				var place = document.createElement('place');
				place.setAttribute('class', 'facility');
				place.appendChild(document.createTextNode('(' + reservelist[i]['m_places_consul_place'] + ')'));
				a.appendChild(place);

				var staff = document.createElement('span');
				staff.setAttribute('class', 'staff');
				if (reservelist[i]['charge_name_jp'] != undefined)
					staff.appendChild(document.createTextNode(reservelist[i]['charge_name_jp']));
				a.appendChild(staff);

				reserveli.appendChild(a);
				ul.appendChild(reserveli);

				if (i == reservelist.length - 1)
				{
					li.appendChild(ul);
					todayAll.appendChild(li);
				}
			}
			sidebar.appendChild(todayAll);
		}
	}

	// シフト詳細作成
	function createShiftDetail(baseurl, campusid, id)
	{
		var sidebar = deleteSidebar();

		// セルから情報を取得
		var cel = document.getElementById(id);
		var dayno = cel.getAttribute('data-dayno');
		var alpha = toAlpha(Number(dayno) - {$shifts[0]->dayno});
		var dow = cel.getAttribute('data-dow');
		var reservecount = cel.getAttribute('reserve');
		var reservationdate = cel.getAttribute('date');
		var datejp = cel.getAttribute('datejp');
		var starttime = cel.getAttribute('starttime');
		var endtime = cel.getAttribute('endtime');
		
		var startStr = reservationdate.split('-').join('/') + ' ' + starttime;

		var shiftstr = alpha + ':' + toHM(starttime) + '-' + toHM(endtime);

		// 駆け込み予約の日付とシフトを設定
		document.getElementById('reservationdate').value = reservationdate;
		document.getElementById('dayno').value = dayno;

		// 駆け込み予約の表示用日付とシフトを設定
		var vreservationdate = document.getElementById('vreservationdate');
		var vshift = document.getElementById('vshift');
		vreservationdate.innerHTML = datejp;
		vshift.innerHTML = shiftstr;

		// 本日のスタッフ
		var request = createXMLHttpRequest();
		var scripturl = baseurl + '/gettodaystaff/shiftclass/' + campusid + '/ymd/' + reservationdate + '/dow/' + dow + '/dayno/' + dayno;
		request.open("POST", scripturl , false);
		request.send(null);
		var json = request.responseText;
		var stafflist = JSON.parse(json);

		// シフト詳細
		var title = document.createElement('h1');
		title.appendChild(document.createTextNode("{t}シフト詳細{/t}"));
		sidebar.appendChild(title);

		var detail = document.createElement('dl');
		detail.setAttribute('class', 'detail');

		createTagText('dt', "{t}年月日{/t}", detail);
		createTagText('dd', datejp, detail);
		createTagText('dt', "{t}シフト{/t}", detail);
		createTagText('dd', shiftstr, detail);
		createTagText('dt', "{t}予約{/t}", detail);
		//createTagNum('dd', reservecount, '%1人', '%1人(複数)', detail);
		createTagAttr('dd', '%1人', reservecount, detail);
		createTagText('dt', "{t}TA{/t}", detail);
		//createTagNum('dd', stafflist['count'], '%1人', '%1人(複数)', detail);
		createTagAttr('dd', '%1人', stafflist['count'], detail);

		sidebar.appendChild(detail);


		// 相談（指定日の予約一覧）
		var request = createXMLHttpRequest();
		//var scripturl = baseurl + '/gettodayreserve/shiftclass/' + campusid + '/ymd/' + reservationdate;
		var scripturl = baseurl + '/getshiftreserve/shiftclass/' + campusid + '/ymd/' + reservationdate + '/dayno/' + dayno;
		request.open("POST", scripturl , false);
		request.send(null);
		var json = request.responseText;
		var reservelist = JSON.parse(json);

		var reserve = document.createElement('div');

		var title = document.createElement('div');
		title.setAttribute('class', 'sub');
		title.appendChild(document.createTextNode("{t}相談{/t}"));
		reserve.appendChild(title);

		if (reservelist.length > 0)
		{
			var table = document.createElement('table');
			table.setAttribute('class', 'appointment');

			var thead = document.createElement('thead');

			createTagText('th', "{t}氏名{/t}", thead);
			createTagText('th', "{t}担当{/t}", thead);
			createTagText('th', "{t}種類{/t}", thead);

			table.appendChild(thead);

			var tbody = document.createElement('tbody');
			for (var i = 0; i < reservelist.length; i++)
			{
					var tr = document.createElement('tr');

					var td = document.createElement('td');
					var a = document.createElement('a');
					a.setAttribute('href', baseurl + '/advice/reserveid/' + reservelist[i]['id'] + '/reserver/' + reservelist[i]['m_member_id_reserver']);
					a.appendChild(document.createTextNode(reservelist[i]['name_jp']));
					td.appendChild(a);
					tr.appendChild(td);

					td = document.createElement('td');
					if (reservelist[i]['charge_name_jp'] != undefined)
					{
						a = document.createElement('a');
						a.setAttribute('href', baseurl + '/advice/reserveid/' + reservelist[i]['id'] + '/reserver/' + reservelist[i]['m_member_id_reserver']);
						a.appendChild(document.createTextNode(reservelist[i]['charge_name_jp']));
						td.appendChild(a);
					}
					tr.appendChild(td);

					td = document.createElement('td');
					if(baseurl == '/kwl/staff'){
						td.appendChild(document.createTextNode(reservelist[i]['m_dockinds_document_category']));
					}
					else
					{
						td.appendChild(document.createTextNode(reservelist[i]['m_dockinds_clipped_form']));
					}
					tr.appendChild(td);

					tbody.appendChild(tr);
			}
			table.appendChild(tbody);

			reserve.appendChild(table);
			sidebar.appendChild(reserve);
		}

		// 関大では過去の日付に対し駆け込み予約ボタンは表示させない
		// 津田塾大学では駆け込み予約ボタンが必要ない
		// 駆け込み予約ボタン
		
		// 駆け込み予約は現在時刻以前(当該コマを含む)のコマで可能
		var tempLow = Date.parse(new Date()) - 86400000;
		var tempHigh = Date.parse(new Date());
		var minutes =  new String((new Date()).getMinutes());
		if(parseInt(minutes) < 10){
			minutes = new String(0) + minutes;
		}
		var nowTime = new String((new Date).getHours()) + minutes;
		var startTime = starttime.replace(":","").substring(0,4);
		var endTime = endtime.replace(":","").substring(0,4);
		
		if(Date.parse(startStr) <= tempHigh)
		{
			//if(parseInt(nowTime) >= parseInt(startTime) && parseInt(endTime) >= parseInt(nowTime)){
				
				var button = document.createElement('button');
				button.setAttribute('id', 'addReserveButton');
				button.appendChild(document.createElement('i'));
				button.appendChild(document.createTextNode("{t}駆け込み予約{/t}"));
				reserve.appendChild(button);
				sidebar.appendChild(reserve);
		}

		// スタッフ
		if (stafflist['count'] > 0)
		{
			var staff = document.createElement('div');

			var title = document.createElement('div');
			title.setAttribute('class', 'sub');
			title.appendChild(document.createTextNode("{t}スタッフ{/t}"));
			staff.appendChild(title);

			var ul = document.createElement('ul');
			ul.setAttribute('class', 'staff');

			for (var shiftstaff in stafflist['staffs'])
			{
				var li = document.createElement('li');
				if (stafflist['staffs'][shiftstaff]['id'] == '{$member->id}')
					li.setAttribute('class', 'you');

				var figure = document.createElement('figure');
				var span = document.createElement('span');
				span.setAttribute('class', 'photo');

				var img = document.createElement('img');
				img.setAttribute('src', '/images/userStaff.png');

				span.appendChild(img);
				figure.appendChild(span);
				figure.appendChild(document.createTextNode(stafflist['staffs'][shiftstaff]['m_members_name_jp']));

				li.appendChild(figure);
				ul.appendChild(li);
			}

			staff.appendChild(ul);

			sidebar.appendChild(staff);
		}



		$("#addReserveButton").addRunReserveDialog($("#addReserve"));
		$("#addReserve .calendarSet").each(function(){
			var v = $(this).find(".view");
			v.datepicker({
				showOn:"button",
				buttonText: "{t}▼ 選んでください{/t}",
				beforeShow: function(input, inst) {
					var calendar = inst.dpDiv;
					setTimeout(function() {
						calendar.position({
							my: 'right top',
							at: 'right bottom',
							collision: 'none',
							of: ".ui-datepicker-trigger"
						});
					}, 1);
				}
			});
		});
		
		jqTranslate();
	}

	function createTagText(tag, text, parent)
	{
		var element = document.createElement(tag);
		element.appendChild(document.createTextNode(text));
		parent.appendChild(element);
	}
	
	function createTagAttr(tag, text, arg, parent)
	{
		var element = document.createElement(tag);
		element.setAttribute('data-localize', text);
		element.setAttribute('data-arg1', arg);
		parent.appendChild(element);
	}
	
	function createTagNum(tag, count, single, plural, parent)
	{
		var element = document.createElement(tag);
		element.setAttribute('data-localize', single);
		element.setAttribute('data-arg1', count);
		element.setAttribute('data-plural', plural);
		element.setAttribute('data-count', count);
		parent.appendChild(element);
	}

	function deleteSidebar()
	{
		var sidebar = document.getElementById('sidebar');
		while (sidebar.firstChild)
			sidebar.removeChild(sidebar.firstChild);
		return sidebar;
	}
	
	function changeSubmitdate()
	{
		var submitdate = document.getElementById('item5').value;
		submitdate = submitdate.split("/").join("-");
		document.getElementById('submitdate').value = submitdate;
		
		document.getElementById('item5').value = dateFormat(submitdate, 'Y/m/d(wj)', false, true);
	}

	function addReserve()
	{
		$('#newreserve').submit();
	}

	function searchUser()
	{
		var studentid = document.getElementById('userSearch').value;

		var request = createXMLHttpRequest();
		var scripturl = '{$baseurl}/{$controllerName}/searchuser/studentid/' + studentid;
		request.open("POST", scripturl , false);
		request.send(null);
		var json = request.responseText;
		//alert(json);
		var user = JSON.parse(json);
		if (user['result'] == 'success')
		{
			document.getElementById('username').innerHTML = user['member']['student_id'] + ' ' + user['member']['name_jp']; //+ user['member']['department_id']
			document.getElementById('reserver').value = user['member']['id'];
			$('#subjects').removeClass('inactive');
			var opt = $('#subjects').siblings('.options');
			opt.children().remove();
			
			for(var i in user['subject'])
			{
				var li = document.createElement('li');
				li.setAttribute('data-value', user['subject'][i]['jwaricd']);
				li.innerHTML = '<span class="head">' + user['subject'][i]['yogen'] + '　</span>' + user['subject'][i]['class_subject'];
				
				opt.append($(li));
			}
			$('#subjects').unbind();
			opt.parent().parent().selectMirror();
		}
		else
		{
			document.getElementById('username').innerHTML = "{t}ユーザーが見つかりません{/t}";
			document.getElementById('reserver').value = null;
			$('#subjects').addClass('inactive');
			var opt = $('#subjects').siblings('.options');
			opt.children().remove();
		}
		$('#item4').prop('value', '');
		opt.parent().siblings('.selected').html('');
	}
	//20140827 kowia アクティブな日付チェック
	function activcheck()
	{
		//押下したセル
		if($.inArray("月", $(".day2").html()) !== -1){
			for(i=1 ; i <= $("#shiftinput").children().length ; i++){
				$("#1_" + i).addClass("colHover");
			}
		}
		if($.inArray("火", $(".day2").html()) !== -1){
			for(i=1 ; i <= $("#shiftinput").children().length ; i++){
				$("#2_" + i).addClass("colHover");
			}
		}
		if($.inArray("水", $(".day2").html()) !== -1){
			for(i=1 ; i <= $("#shiftinput").children().length ; i++){
				$("#3_" + i).addClass("colHover");
			}
		}
		if($.inArray("木", $(".day2").html()) !== -1){
			for(i=1 ; i <= $("#shiftinput").children().length ; i++){
				$("#4_" + i).addClass("colHover");
			}
		}
		if($.inArray("金", $(".day2").html()) !== -1){
			for(i=1 ; i <= $("#shiftinput").children().length ; i++){
				$("#5_" + i).addClass("colHover");
			}
		}
		//当日の曜日
		if($.inArray("月", $(".today").html()) !== -1){
			for(i=1 ; i <= $("#shiftinput").children().length ; i++){
				$("#1_" + i).addClass("colHover");
			}
		}
		if($.inArray("火", $(".today").html()) !== -1){
			for(i=1 ; i <= $("#shiftinput").children().length ; i++){
				$("#2_" + i).addClass("colHover");
			}
		}
		if($.inArray("水", $(".today").html()) !== -1){
			for(i=1 ; i <= $("#shiftinput").children().length ; i++){
				$("#3_" + i).addClass("colHover");
			}
		}
		if($.inArray("木", $(".today").html()) !== -1){
			for(i=1 ; i <= $("#shiftinput").children().length ; i++){
				$("#4_" + i).addClass("colHover");
			}
		}
		if($.inArray("金", $(".today").html()) !== -1){
			for(i=1 ; i <= $("#shiftinput").children().length ; i++){
				$("#5_" + i).addClass("colHover");
			}
		}
	}

</script>
</head>

<body class="staff">
	{include file='staff/menu.tpl'}
		<div id="main">
			<article class="calendar">
				<h1>{t}シフトカレンダー{/t}</h1>
				<div id="shiftCalendar">
					<div class="container">
					<div class="facility">
						<div class="bezel selectMirror">
							<span class="selected"><span class="sel_before">{t}利用施設：{/t}</span>{if !empty($campusname)}{$campusname}{/if}</span>
							<div class="control">
								<input type="button" value="{t}▼ 選んでください{/t}" class="select">
								<ul class="options">
									{foreach from=$campuses item=campus name=campuses}
										<li data-value="{$campus->id}" onclick="selectCampus({$campus->id})">{$campus->campus_name}</li>
									{/foreach}
								</ul>
							<input type="hidden" name="facility" id="facility" class="valueInput" value="{if empty($campusid)}1{else}{$campusid}{/if}">
							</div>
						</div>
					</div>

					<div class="pager">
						<span class="date"><a href="{$baseurl}/{$controllerName}/{$actionName}{if !empty($campusid)}/shiftclass/{$campusid}{/if}{if !empty($lastweek)}/ymd/{$lastweek}/week{/if}" class="prev">previous</a>{$vDate->dateFormat($weektop, 'Y')} {$vDate->dateFormat($weektop, 'm/d', false, true)} - {$vDate->dateFormat($weekend, 'm/d', false, true)}<a href="{$baseurl}/{$controllerName}/{$actionName}{if !empty($campusid)}/shiftclass/{$campusid}{/if}{if !empty($nextweek)}/ymd/{$nextweek}/week{/if}" class="next">next</a></span>
					</div>
					<table class="staff">
						<thead>
							<tr id="shifthead">
							</tr>
						</thead>
						<tbody id="shiftinput">
						</tbody>
					</table>
					<div class="legend">
						<div class="person"><i></i>{t}：予約あり{/t}</div>
						<div class="attached"><i></i>{t}：担当シフト{/t}</div>
					</div>
				</div>
			</div>
			<input type="hidden" name="basic-date" id="basic-date" value="">
		</article>
		<!--/#main--></div>

		<aside id="sidebar">
		</aside>

		{* 駆け込み予約フォーム *}
		<div id="addReserve" class="dialog">
			<i class="closeButton cancel"></i>
			<div class="sub">{t}駆け込み予約・相談者の追加{/t}</div>
			<form method="POST" action="{$baseurl}/{$controllerName}/newreserve" name="newreserve" id="newreserve" enctype="multipart/form-data">
				<input type="hidden" name="reservationdate" id="reservationdate">
				<input type="hidden" name="dayno" id="dayno">
				<dl class="detail">
					<dt class="nen">{t}年月日{/t}</dt><dd id="vreservationdate">2014/04/01(火)</dd>
					<dt class="shif">{t}シフト{/t}</dt><dd id="vshift">C:13:00-13:40</dd>
				</dl>
				<div class="user"><p class="gaku">{t}相談者{/t}</p>
					<div class="bezel">
						<span class="selected" id="username">{t}例）文14-1234{/t}</span>
						<div class="control"><input class="userSearch" type="text" value="{t}こちらに記入{/t}" id="userSearch" onfocus="if (this.value == "{t}こちらに記入{/t}") this.value = '';" onblur="if (this.value == '') this.value = "{t}こちらに記入{/t}";if(this.value != '' && this.value != "{t}こちらに記入{/t}")style='color:#000';"></input><input type="button" value="{t}学籍番号で検索{/t}" id="userSearchButton" onclick="searchUser();"></div>
						<input type="hidden" name="reserver" id="reserver">
					</div>
				</div>
				<ul class="formSet">
					<li>
						<label for="item1">{t}文書の種類{/t}</label>
						<div class="bezel selectMirror">
							<span class="selected"></span>
							<div class="control">
								<input type="button" value="{t}▼ 選んでください{/t}" class="select">
								<ul class="options">
									{foreach from=$dockinds item=dockinditem name=dockinds}
										<li data-value="{$dockinditem['id']}">{$dockinditem['document_category']}</li>
									{/foreach}
								</ul>
								<input type="hidden" name="item1" id="item1" class="valueInput" value="">
							</div>
						</div>
					</li>
					<li>
						<label for="item2">{t}相談場所{/t}</label>
						<div class="bezel selectMirror">
							<span class="selected"></span>
							<div class="control">
								<input type="button" value="{t}▼ 選んでください{/t}" class="select">
								<ul class="options">
									{foreach from=$places item=placeitem name=places}
										<li data-value="{$placeitem['id']}">{$placeitem['consul_place']}</li>
									{/foreach}
								</ul>
								<input type="hidden" name="item2" id="item2" class="valueInput" value="">
							</div>
						</div>
					</li>
				</ul>

				<ul class="formSet">
					<li>
						<label for="item4">{t}授業科目{/t}</label>
						<div class="bezel selectMirror">
							<span class="selected"></span>
							<div class="control">
								<input type="button" value="{t}▼ 選んでください{/t}" class="select inactive" id="subjects">
								<ul class="options">
								
								</ul>
								<input type="hidden" name="item4" id="item4" class="valueInput" value="">
							</div>
						</div>
					</li>
					<li>
						<label for="item5">{t}提出日{/t}</label>
						<div class="bezel">
							<div class="control calendarSet">
								<input type="text" id="item5" class="view" readonly="readonly" onchange="changeSubmitdate()">
							</div>
						</div>
						<input type="hidden" name="submitdate" id="submitdate">
					</li>
					<li>
						<label for="item6">{t}進行状況{/t}</label>
						<div class="bezel selectMirror">
							<span class="selected"></span>
							<div class="control">
								<input type="button" value="{t}▼ 選んでください{/t}" class="select">
								<ul class="options">
									{foreach from=$progresssal key=k item=v name=progresssal}
									<li data-value="{$k}">{$v}</li>
									{/foreach}
								</ul>
								<input type="hidden" name="item6" id="item6" class="valueInput">
							</div>
						</div>
					</li>
					<li>
						<label for="item7">{t}添付ファイル{/t}</label>
						<div class="bezel fileup">
							<div class="replaceButton">
								<!--.replaceButton クラスでは.viewを透明化した.substanceで覆って入力を実現する-->
								<i class="view">{t}ファイルを選択{/t}</i><input type="file" class="multi substance" id="item7" name="item7[]">
							</div>
						</div>
					</li>
				</ul>
				<div class="freetext">
					<label for="item8">{t}相談内容{/t}</label>
					<div class="countText">
						<span><textarea class="sou" name="item8" id="item8" cols="42" rows="5" maxlength="250" onfocus="if (this.value == "{t}例）レポートの書き方がわからないので、基本的なことを教えてほしい。{/t}") this.value = '';" onblur="if (this.value == '') this.value = "{t}例）レポートの書き方がわからないので、基本的なことを教えてほしい。{/t}";if(this.value != '' && this.value != "{t}例）レポートの書き方がわからないので、基本的なことを教えてほしい。{/t}")style='color:#000';">{t}例）レポートの書き方がわからないので、基本的なことを教えてほしい。{/t}</textarea> </span>
						<div class="counter"><span id="counter">0</span>/250</div>
					</div>
				</div>
				<div class="buttonSet dubble">
					<a onclick="addReserve();" class="affirm">{t}追加する{/t}</a>
					<a href="#" class="cancel">{t}キャンセル{/t}</a>
				</div>
			</form>
		</div>

		<!--/#contents--></div>
		{include file="../common/foot_v2.php"}
		
		<script>
			$(function(){
				$("#loginStatusTrigger").miniMenu($("#loginStatus"));
				// 本日の日付と年
				//myTbl = new Array("日","月","火","水","木","金","土");
				myTbl = getDowArray();
				myD = new Date();
				y = myD.getFullYear();
				m = myD.getMonth() + 1;
				d = myD.getDate();
				myDay = myD.getDay();
				myDay = myTbl[myDay];

				if ( m < 10 ) {
						m = '0' + m;
					}
				if ( d < 10 ) {
						d = '0' + d;
					}
				myymd = y + "" + m + ""+ d;
				$(".selectMirror").each(function(){
					$(this).selectMirror();
				});

				// 残り文字数の表示の仕方変更
				$('#item8').bind('keyup',function(){
					var tnum  = $(this).val().length;
					$('#counter').text(tnum);
					var tmax = 250 - tnum //250文字制限
					if(tmax > 0){
						$('#txtmax').text(tmax);
					}else{
						$('#counter').text("250");//文字数オーバーの際の挙動

					}
				});

				$('#newreserve').submit(function(event) {
					event.preventDefault();	// 本来のsubmit処理をキャンセル

					// 2014/08/11
					// placeholder対策
					var tempText = document.getElementById('item8').value;
					if(document.getElementById('item8').value == '例）レポートの書き方がわからないので、基本的なことを教えてほしい。'){
						document.getElementById('item8').value = '';
					}
					var $form = $(this);
					var fd = new FormData($form[0]);

					$.ajax({
						async: false,				// 同期通信
						url: $form.attr('action'),
						type: $form.attr('method'),
						timeout: 600000,

						// 以下、ファイルアップロードに必須
						data: fd,
						processData: false,
						contentType: false,

						// 各種処理
						beforeSend: function(xhr, settings) {
						},
						success: function(data, textStatus, jqXHR) {
							//alert(data);
							var response = JSON.parse(data);
							if (response['error'] !== undefined)
							{	// 論理エラー
								alert(response['error']);
							}
							else
							{	// 成功
								var link = '{$baseurl}/{$controllerName}/{$actionName}/shiftclass/' + {if empty($campusid)}'1'{else}'{$campusid}'{/if} {if !empty($ymd)} + '/ymd/{$ymd}' {/if};
								document.location = link;
							}
						},
						error: function(jqXHR, textSatus, errorThrown) {
							alert(textSatus);
							alert(errorThrown);
						},
						complete: function(jqXHR, textStatus) {
						},
					});

				});
			});
		</script>
	<!--[if lte IE 9]>
	<script src="/js/flexie.min.js" type="text/javascript"></script>
	<![endif]-->
</body>
</html>