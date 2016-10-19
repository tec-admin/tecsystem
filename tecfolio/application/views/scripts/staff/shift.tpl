<!doctype html>
<html lang="ja">
<head>

{include file='staff/header.tpl'}
<script src="/js/base.reserve.js" type="text/javascript"></script>
<script src="/js/jquery.tableAction.js" type="text/javascript"></script>
<script>
	window.onload = function()
	{
		createShiftInput({if empty($campusid)}1{else}{$campusid}{/if}, {if empty($termid)}1{else}{$termid}{/if});
	}

	function selectCampus(campusid)
	{
		var termid = document.getElementById('selectedTerm').value;

		createShiftInput(campusid, termid);

		document.getElementById('facility').value = campusid;
	}

	function selectTerm(termid)
	{
		var campusid = document.getElementById('facility').value;

		createShiftInput(campusid, termid);

		document.getElementById('selectedTerm').value = termid;
	}

	function createShiftInput(campusid, termid)
	{
		deleteShiftInput();

		var baseurl = '{$baseurl}/{$controllerName}';

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

			var dowj = ["{t}日曜日{/t}", "{t}月曜日{/t}", "{t}火曜日{/t}", "{t}水曜日{/t}", "{t}木曜日{/t}", "{t}金曜日{/t}", "{t}土曜日{/t}"];
			for (var i = 1; i <= 5; i++)
			{
				var td = document.createElement('td');
				td.setAttribute('id', i + '_{$shift->dayno}');	// 曜日 + 連番
				td.setAttribute('data-shift', dowj[i] + ' '+ shiftdata);
				td.setAttribute('data-dayno', '{$shift->dayno}');
				td.setAttribute('data-dow', i);
				td.appendChild(document.createTextNode(' '));
				tr.appendChild(td);
			}

			tbody.appendChild(tr);
		{/foreach}

		// スタッフのシフトを取得し、入力表へ設定
		var request = createXMLHttpRequest();
		var scripturl = baseurl + "/getshiftinput/shiftclass/" + campusid + "/termid/" + termid {if !empty($ymd)} + '/ymd/{$ymd}' {/if};
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

		// 一覧作成
		for (var dow in staffshift['list'])
		{
			var td = document.getElementById('list_' + dow);
			if (td != null)
				td.innerHTML = '<time>' + staffshift['list'][dow] + '</time>';
		}

		// JQueryのイベントをバインド(関大用)
		$("#shiftCalendar").each(function(){
			var shiftAdd = $("#shiftAttachDialog"),
				shiftAddFail = $("#shiftFailDialog"),
				shiftRemove = $("#shiftRemoveDialog"),
				shiftRemoveFail = $("#shiftRemoveFailDialog"),
				shiftOverFail = $("#shiftOverFailDialog"),
				shiftExpired = $("#shiftExpiredDialog");
			$(this).shiftDialog_kandai(shiftAdd,shiftAddFail,shiftRemove,shiftRemoveFail,shiftOverFail,shiftExpired);
		});

		// 今学期のシフト数が0件、かつ前学期のシフト数が1件以上なら
		// staffshift['button_flg']=='show'、そうでなければstaffshift['button_flg']=='hide'
		// 前の学期のシフトを引き継ぐボタンを表示する
		if(staffshift['button_flg'] == 'show')
		{
			var target = document.getElementById('copy');
			target.removeAttribute('style');

			$(".calendar").find(".finish").decisionDialog($("#finishDialog"));
		}
		else
		{
			var target = document.getElementById('copy');
			target.setAttribute('style', 'display:none;');
		}

		//$("p#msg_expired").text(staffshift['termdata']['name'] + " のシフト入力許可期間は " + staffshift['termdata']['shift_startdate'] + " ～ " + staffshift['termdata']['shift_enddate'] + " です。");
		$("p#msg_expired").attr('data-localize', '%1 のシフト入力許可期間は %2 ～ %3 です。');
		$("p#msg_expired").attr('data-arg1', staffshift['termdata']['name']);
		$("p#msg_expired").attr('data-arg2', dateFormat(staffshift['termdata']['shift_startdate'], 'Y/m/d'));
		$("p#msg_expired").attr('data-arg3', dateFormat(staffshift['termdata']['shift_enddate'], 'Y/m/d'));
		
		jqTranslate();
	}

	// 前学期のシフトを今学期へ複製
	function copyPreviousShifts()
	{
		var termid = document.getElementById('selectedTerm').value;
		var campusid = document.getElementById('facility').value;

		var baseurl = '{$baseurl}/{$controllerName}';
		var request = createXMLHttpRequest();
		var scripturl = baseurl + "/copypreviousshifts/shiftclass/" + campusid + "/termid/" + termid;
		request.open("POST", scripturl , false);
		request.send(null);

		createShiftInput(campusid, termid);
	}

	function deleteShiftInput()
	{
		var list = document.getElementById('shiftinput');
		while (list.firstChild)
			list.removeChild(list.firstChild);
	}

	function setcharge()
	{
		chargeFunction('set');
	}

	function deletecharge()
	{
		chargeFunction('delete');
	}

	function chargeFunction(action)
	{
		var campusid = document.getElementById('facility').value;
		var termid = document.getElementById('selectedTerm').value;
		var dow = document.getElementById(action + 'dow').value;
		var dayno = document.getElementById(action + 'dayno').value;

		// 登録
		var scripturl = '{$baseurl}/{$controllerName}' + "/" + action + "shiftinput/shiftclass/" + campusid + "/termid/" + termid + "/dow/" + dow + "/dayno/" + dayno {if !empty($ymd)} + '/ymd/{$ymd}'{/if};
		var request = createXMLHttpRequest();
		request.open("POST", scripturl , false);
		request.send(null);
		var json = request.responseText;
		var shiftinput = JSON.parse(json);

		// 再読み込み
		var link = '{$baseurl}/{$controllerName}/{$actionName}/shiftclass/' + campusid + "/termid/" + termid {if !empty($ymd)} + '/ymd/{$ymd}' {/if};
		document.location = link;
	}

</script>
</head>

<body class="staff">
	{include file='staff/menu.tpl'}
		<div id="main">
			<article class="calendar">
				<h1 id="shifttitle">
					{t}学期単位のシフト入力{/t}
				</h1>
				<div id="shiftCalendar"><div class="container">

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

					<div class="pag">
						<div class="bezelk selectMirror">
							<span class="season selected">{t 1=$term['year'] 2=$term['name']}%1年度 %2{/t}</span>
							<div class="control">
								<input type="button" value="{t}▼ 選んでください{/t}" class="select">
								<ul class="options">
									{foreach from=$allterm item=singleterm name=allterm}
										<li data-value="{$singleterm->id}" onclick="selectTerm({$singleterm->id})">{t 1=$singleterm->year 2=$singleterm->name}%1年度 %2{/t}</li>
									{/foreach}
								</ul>
								<input type="hidden" name="selectedTerm" id="selectedTerm" class="valueInput" value="{if empty($termid)}1{else}{$termid}{/if}">
							</div>
						</div>
					</div>
					<table>
						<thead>
							<tr>
								<th class="blank" id="cal_top_left">
									<div id="shiftPageControl">
										<button id="copy" class="finish" style="display:none;">{t}シフトの引き継ぎ{/t}</button>
										<div id="finishDialog" class="dialog">
											<i class="closeButton cancel"></i>
											<div class="sub">{t}前学期のシフトを引き継ぎますか？{/t}</div>
											<div class="buttonSet dubble">
												<a href="#" onclick="copyPreviousShifts();" class="affirm">{t}OK{/t}</a>
												<a href="#" class="cancel">{t}キャンセル{/t}</a>
											</div>
										</div>
									</div>
								</th>
								{foreach from=$dowarray key=i item=dow name=downame}
									<th class="blank">{$dow}</th>
								{/foreach}
							</tr>
						</thead>
						<tbody id="shiftinput">
						</tbody>
					</table>
					<div class="legend">
						<span class="note">{t}※セルをクリックして選択してください。{/t}</span>
						<div class="attached"><i></i>{t}：自分の担当シフト{/t}</div>
					</div>
				</div>
			</div>
			<div id="shiftAttachDialog" class="dialog">
				<i class="closeButton cancel"></i>
				<div class="sub">{t}シフトを担当する{/t}</div>
				<form action="./index.html">
					<p>{t}このシフトを担当しますか？{/t}</p>
					<time class="shiftData">---</time>
					<input type="hidden" class="dayno" id="setdayno">
					<input type="hidden" class="dow" id="setdow">
					<div class="buttonSet dubble">
						<a onclick="setcharge();" class="affirm">{t}担当する{/t}</a>
						<a href="#" class="cancel">{t}キャンセル{/t}</a>
					</div>
				</form>
			</div>
			<div id="shiftRemoveDialog" class="dialog">
				<i class="closeButton cancel"></i>
				<div class="sub">{t}シフトを削除する{/t}</div>
				<form action="">
					<p>{t}このシフトを削除しますか？{/t}</p>
					<time class="shiftData">---</time>
					<input type="hidden" class="dayno" id="deletedayno">
					<input type="hidden" class="dow" id="deletedow">
					<div class="buttonSet dubble">
						<a onclick="deletecharge();" class="deleted"><i></i>{t}削除する{/t}</a>
						<a href="#" class="cancel">{t}キャンセル{/t}</a>
					</div>
				</form>
			</div>
			
			<!-- 4コマ制限撤廃につき翻訳対象外 -->
			<div id="shiftFailDialog" class="dialog">
				<i class="closeButton cancel"></i>
				<div class="sub">このシフトは指定できません</div>
				<p>同じ曜日にはひとつの連続したシフトしか入力できません。<br>すでに入力されたシフトに隣接している時間帯だけが指定できます。</p>
			</div>
			<div id="shiftRemoveFailDialog" class="dialog">
				<i class="closeButton cancel"></i>
				<div class="sub">中間のシフトは削除できません</div>
				<p>同じ曜日にはひとつの連続したシフトしか設定できません。<br>勤務開始のシフト、もしくは終了のシフトのみがいますぐ削除できます。</p>
			</div>
			<div id="shiftOverFailDialog" class="dialog">
				<i class="closeButton cancel"></i>
				<div class="sub">シフトは{$dowmax}枠以上設定できません</div>
				<p>同じ曜日に{$dowmax}枠以上のシフトに入ることはできません。</p>
			</div>
			
			<div id="shiftExpiredDialog" class="dialog">
				<i class="closeButton cancel"></i>
				<div class="sub">{t}シフト入力許可期間の対象外です{/t}</div>
				<p id="msg_expired">{t 1="`$term->year`年度 `$term->name`" 2=$term->shift_startdate 3=$term->shift_enddate}%1 のシフト入力許可期間は %2 ～ %3 です。{/t}</p>
			</div>
		</article>
		<!--/#main--></div>
		<aside id="sidebar">
			<h1>{t}シフト時間一覧{/t}</h1>
			<table class="officeHours">
				<thead>
					<tr>
						<th>{t}曜日{/t}</th>
						<th class="time">{t}シフト時間{/t}</th>
					</tr>
				</thead>
				<tbody>
				{foreach from=$dowarray key=i item=dow name=downame}
					<tr>
						<td>{$dow}</td>
						<td id="list_{$i+1}"></td>
					</tr>
				{/foreach}
				</tbody>
			</table>
		</aside>
		<!--/#contents--></div>
		{include file="../common/foot_v2.php"}

		<script>
			$(function(){
				$("#loginStatusTrigger").miniMenu($("#loginStatus"));
				$(".selectMirror").each(function(){
					$(this).selectMirror();
				});
			});
		</script>
	<!--[if lte IE 9]>
	<script src="/js/flexie.min.js" type="text/javascript"></script>
	<![endif]-->
</body>
</html>