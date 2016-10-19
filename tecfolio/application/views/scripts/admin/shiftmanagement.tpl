<!doctype html>
<html lang="ja">
<head>

{include file='admin/header.tpl'}

<script src="/js/base.reserve.js" type="text/javascript"></script>
<script>
	var flag = true;
	var shiftarray = [];

	window.onload = function()
	{
		{if $termid != null}
		createShiftInput({if empty($campusid)}1{else}'{$campusid}'{/if}, {if empty($termid)}1{else}{$termid}{/if});
		setDialogNum();
		{/if}
		
		jqTranslate();
	}

	function selectCampus(campusid)
	{
		var link = '{$baseurl}/{$controllerName}/{$actionName}/shiftclass/' + campusid {if !empty($termid)} + '/termid/{$termid}'{/if}{if !empty($memberid)} + "/memberid/{$memberid}" {/if};
		document.location = link;
	}

	function selectTerm(termid)
	{
		var link = '{$baseurl}/{$controllerName}/{$actionName}/'{if !empty($campusid)} + 'shiftclass/{$campusid}/'{/if} + 'termid/' + termid{if !empty($memberid)} + "/memberid/{$memberid}" {/if};
		document.location = link;
	}

	// 受入数設定ダイアログの数値設定
	function setDialogNum()
	{
		var num = 1;
		for (var i in shiftarray)
		{
			var label = document.getElementsByClassName('label' + num);
			var text = shiftarray[i];

			for (var j = 0; j < label.length; j++)
			{
				// 時間帯毎にシフト時間の文字列挿入
				label[j].innerHTML = "";
				label[j].appendChild(document.createTextNode(text));

				// 表から【受入数(スタッフ数)】を取得
				var th = document.getElementById((j+1) + '_' + num);
				var limitandstaff = document.getElementById('limitandstaff' + (j+1) + '_' + num);
				limitandstaff.innerHTML = "";
				limitandstaff.appendChild(document.createTextNode(th.innerHTML));

				// 受入数とスタッフ数を別々に取得
				var head = th.innerHTML.indexOf("(");
				var back = th.innerHTML.indexOf(")");

				var tmplimit = th.innerHTML.slice(0,head);
				var tmpstaff = th.innerHTML.substr(head+1,back-head-1);

				var limit = document.getElementById('limit' + (j+1) + '_' + num);

				$('#limit' + (j+1) + '_' + num).children().remove();

				for (var k = 0; k <= tmpstaff; k++)
				{
					var option = document.createElement("option");
					option.value = tmpstaff - k;

					var opttext = "-" + k;

					if (opttext == 0)
					{
						option.text = "±0";
					}
					else
					{
						option.text = opttext;
					}

					if (option.value == tmplimit)
					{
						option.selected = "selected";
					}

					limit.appendChild(option);
				}
			}

			num++;
		}
	}

	function createShiftInput(campusid, termid)
	{
		deleteShiftInput();

		var baseurl = '{$baseurl}/{$controllerName}';

		var tbody = document.getElementById('shiftinput');

		// シフト入力表を作成
		var arShifts = [];
		{if $termid != null}
		{foreach from=$shifts item=shift name=shifts}
			arShifts[arShifts.length] = {$shift->dayno};
			var tr = document.createElement('tr');
			var th = document.createElement('th');
			var shiftdata = toAlpha({$smarty.foreach.shifts.index}) + ' {$vDate->dateFormat($shift->m_timetables_starttime, 'H:i')}-{$vDate->dateFormat($shift->m_timetables_endtime, 'H:i')}';
			shiftarray.push(shiftdata);
			th.setAttribute('id', 'shift' + toAlpha({$smarty.foreach.shifts.index}));
			th.appendChild(document.createTextNode(shiftdata));
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
		{/if}

		// スタッフのシフトを取得し、入力表へ設定
		var request = createXMLHttpRequest();
		var scripturl = baseurl + "/getshiftinput/actionname/shiftmanagement/shiftclass/" + campusid + "/termid/" + termid {if !empty($memberid)} + "/memberid/{$memberid}" {/if};
		request.open("POST", scripturl , false);
		request.send(null);
		var json = request.responseText;
		var staffshift = JSON.parse(json);

		for (var dow in staffshift['shiftinput'])
		{
			for (var dayno in staffshift['shiftinput'][dow])
			{
				var td = document.getElementById(dow + '_' + dayno);

				if(staffshift['memberinput'][dow] != undefined && staffshift['memberinput'][dow][dayno] != undefined)
				{
					td.setAttribute('class', staffshift['memberinput'][dow][dayno]['class']);
				}

				var limit = staffshift['shiftinput'][dow][dayno]['limit'];
				var count = staffshift['shiftinput'][dow][dayno]['count'];

				if(staffshift['shiftinput'][dow][dayno]['limit'] != undefined)
				{
					td.innerHTML = limit;
				}
				else
				{
					td.innerHTML = '0';
				}

				if(staffshift['shiftinput'][dow][dayno]['count'] != undefined)
				{
					td.innerHTML += '(' + count + ')';
				}
				else
				{
					td.innerHTML += '(0)';
				}

				// $memberidが空なら受入数によってセルの色を変える
				{if empty($memberid)}
					if (limit == 1)
					{
						//td.style.backgroundColor = '#d5e5f4';
						td.className += ' receive1';
					}
					else if (limit >= 2 && 3 >= limit)
					{
						//td.style.backgroundColor = '#b0ceea';
						td.className += ' receive2';
					}
					else if (limit >= 4 && 5 >= limit)
					{
						//td.style.backgroundColor = '#8cb8e1';
						td.className += ' receive3';
					}
					else if (limit >= 6 && 7 >= limit)
					{
						//td.style.backgroundColor = '#68a1d7';
						td.className += ' receive4';
					}
					else if (limit >= 8)
					{
						//td.style.backgroundColor = '#448bce';
						td.className += ' receive5';
					}
					else
					{
						//td.style.backgroundColor = '#ffffff';
						td.className += ' receive0';
					}
				{else}
					// $memberidに一致するスタッフのシフトを一色で表す
						//td.style.backgroundColor = '#ffffff';
						td.className += ' notattach';

					if (staffshift['memberinput'][dow] != undefined && staffshift['memberinput'][dow][dayno] != undefined && staffshift['memberinput'][dow][dayno]['m_member_id'] != undefined)
					{
						//td.style.backgroundColor = '#d5e5f4';
					}
				{/if}
			}
		}

		// JQueryのイベントをバインド(関大用)
		{if !empty($memberid)}
			$("#shift").each(function(){
				var shiftAdd = $("#shiftAttachDialog"),
					shiftAddFail = $("#shiftFailDialog"),
					shiftRemove = $("#shiftRemoveDialog"),
					shiftRemoveFail = $("#shiftRemoveFailDialog");
					shiftOverFail = $("#shiftOverFailDialog");
				if (baseurl == "/kwl/admin"){
					$(this).shiftDialog_kandai(shiftAdd,shiftAddFail,shiftRemove,shiftRemoveFail,shiftOverFail);
				}else{
					$(this).shiftDialog(shiftAdd,shiftAddFail,shiftRemove,shiftRemoveFail,shiftOverFail);
				}
			});
		{/if}
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
		var memberid = {if !empty($memberid)}'{$memberid}'{else}'0'{/if};
		var campusid = document.getElementById('facility').value;
		var placeid = {if !empty($campusid)}{$campusid}{else}0{/if};	// 受入数設定用に、場所IDを取得する
		var termid = document.getElementById('selectedTerm').value;
		var dow = document.getElementById(action + 'dow').value;
		var dayno = document.getElementById(action + 'dayno').value;

		// 登録
		var scripturl = '{$baseurl}/{$controllerName}' + "/" + action + "shiftinput/actionname/shiftmanagement/memberid/" + memberid + "/shiftclass/" + campusid + "/placeid/" + placeid + "/termid/" + termid + "/dow/" + dow + "/dayno/" + dayno;
		var request = createXMLHttpRequest();
		request.open("POST", scripturl , false);
		request.send(null);
		var json = request.responseText;
		var shiftinput = JSON.parse(json);

		// 再読み込み
		var link = '{$baseurl}/{$controllerName}/{$actionName}/memberid/' + memberid + '/shiftclass/' + '{$campusid}' + "/termid/" + termid;
		document.location = link;
	}

	// シフト詳細作成
	function createRightDetail(baseurl, campusid, termid, dow)
	{
		// 鉛筆との同時クリック防止
		if(!flag)
		{
			flag = true;
			return false;
		}

		// 本日のスタッフ
		var request = createXMLHttpRequest();
		var scripturl = baseurl + '/gettodaystaff/actionname/shiftmanagement/campusid/' + campusid + '/termid/' + termid + '/dow/' + dow;
		request.open("POST", scripturl , false);
		request.send(null);
		var json = request.responseText;
		var stafflist = JSON.parse(json);

		//var dowj = [ '月', '火', '水', '木', '金'];
		var dowj = getWeekdayArray();

		{foreach from=$dowarray key=i item=dow name=downame}
			$('#dow'+ {$smarty.foreach.downame.iteration}).css('background-color','');
			if('{$dow}' == dowj[dow-1]){
				$('#dow'+ dow).css('background-color','#fef8a7');
			}
		{/foreach}

		// これ以前に処理が走っていた場合、それらを削除
		var mClassName = document.getElementsByClassName('n');

		if (mClassName != undefined)
		{
			for (var i = 0; i < mClassName.length; i++)
			{
				var alphaElm = mClassName[i].getElementsByTagName('i');
				for (var j = alphaElm[0].childNodes.length-1; j >= 0; j--)
				{
					alphaElm[0].removeChild(alphaElm[0].childNodes[j]);
				}
				// 'added'削除
				alphaElm[0].className = alphaElm[0].className.replace(/added/g, '');
			}

			while(mClassName.length > 0)
			{
				// 'n'削除
				mClassName[0].className = mClassName[0].className.replace(/\sn/g, '');
			}
		}

		// スタッフ
		if (stafflist['count'] > 0)
		{
			// シフト時間帯の数だけループ
			for (var dayno in stafflist['staffs'])
			{
				for (var staffid in stafflist['staffs'][dayno])
				{
					var shiftTimeList = document.getElementById('shiftTimeList' + stafflist['staffs'][dayno][staffid]['m_member_id']);

					if(shiftTimeList != undefined)
					{
						// 一つでもシフトが存在していれば、クラス名を与えて区別し、また背景色を変更させる
						if($('#shiftTimeList' + stafflist['staffs'][dayno][staffid]['m_member_id']).hasClass('added'))
						{
							shiftTimeList.appendChild(document.createTextNode('/'));
						}
						else
						{
							shiftTimeList.setAttribute('class', 'added');
							var memberRow = document.getElementById('memberRow' + stafflist['staffs'][dayno][staffid]['m_member_id']);
							memberRow.className += ' n';
						}
						shiftTimeList.appendChild(document.createTextNode(toAlpha(dayno-1)));
					}
				}
			}
		}
		else
		{
			stafflist['count'] = 0;
		}

		// スタッフ数変更
		var staffnum = document.getElementById('staffNum');
		for (var j = staffnum.childNodes.length-1; j >= 0; j--)
		{
			staffnum.removeChild(staffnum.childNodes[j]);
		}
		//staffnum.appendChild(document.createTextNode("当日スタッフ：" + stafflist['count'] + "名　(全:{count($members)}名)"));
		staffnum.setAttribute('data-localize', '当日スタッフ：%1名 (全：%2名)');
		staffnum.setAttribute('data-arg1', stafflist['count']);
		staffnum.setAttribute('data-arg2',{count($members)});
		
		jqTranslate();
	}

	{for $i=1 to 5}
	function submitData{$i}()
	{
		$('#setlimit{$i}').submit();
	}
	{/for}

</script>
</head>

<body class="admin">
	{include file='admin/menu.tpl'}
		<div id="main">
			<article class="calendar">
				<h1>{t}シフト作成{/t}：{t}受入数{/t}/{t}(スタッフ数){/t}</h1>
				<div id="shift">
					<div class="container">
					{if $termid != null}
						<div class="facility">
							<div class="bezel selectMirror">
								<span class="selected" id="selectedcampusname"><span class="sel_before">{t}利用施設：{/t}</span>{if !empty($campusname)}{$campusname}{/if}</span>
								<div class="control">
									<input type="button" value="{t}▼ 選んでください{/t}" class="select">
									<ul class="options">
										{foreach from=$campuses item=campus name=campuses}
											<li data-value="{$campus->id}" onclick="selectCampus({if $baseurl == "/kwl"}{$campus->id}{else}'{$campus->m_shiftclass_id}'{/if})">{if $baseurl == "/kwl"}{$campus->consul_place}{else}{$campus->l_class_name}{/if}</li>
										{/foreach}
									</ul>
									<input type="hidden" name="facility" id="facility" class="valueInput" value="{if $baseurl == "/kwl"}{if empty($lclass)}1{else}{$lclass}{/if}{else}{if empty($campusid)}1{else}{$campusid}{/if}{/if}">
								</div>
							</div>
						</div>

						<div class="pag">
							<div class="bezelk selectMirror">
								<span class="season selected">{t 1=$term->year 2=$term->name}%1年度 %2{/t}</span>
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
				<!--事前予約表-->
					<table>
						<thead>
							<tr>
								<th class="blank"></th>
								{foreach from=$dowarray key=i item=dow name=downame}
									<th class="blank" id="dow{$smarty.foreach.downame.iteration}" onclick="createRightDetail('/kwl/admin', {$lclass}, {$termid}, {$i+1});">{$dow}
								{if empty($memberid)}
									<div style="position: relative; top:0; right:0;">
										<div id="pageControl{$i+1}">
											<img src="/image/index.png" align="right" class="finish">
											<div id="finishDialog{$i+1}" class="dialog">
												<i class="closeButton cancel"></i>
												<div class="sub">{t}受入数設定(差分){/t}</div>
												<form method="POST" action="{$baseurl}/{$controllerName}/setlimit/actionname/shiftmanagement/dow/{$i+1}/termid/{$termid}/campusid/{$campusid}" name="setlimit{$i+1}" id="setlimit{$i+1}" enctype="multipart/form-data">
												<ul class="formSet">
												<div class="outer">
													<div class="title_shift">
														{t}シフト時間{/t}
													</div>
													<div class="title_setlimit">
														{t}受入数設定{/t}
													</div>
													<div class="title_limitandstaff" style="padding: 10px 0 0 0;">
														{t}受入数{/t}/{t}(スタッフ数){/t}
													</div>
												</div>
													{for $j=1 to $countDayno}
													<li class="limitline">
														<label for="shiftlabel{$j}" class="label{$j} limitlabel"></label>
														<div id="set{$j}" class="dl">
															<div class="control">
																<div class="shift" style="display: inline-block;">
																	<select name="limit{$i+1}_{$j}" id="limit{$i+1}_{$j}">
																	</select>
																</div>
																<div class="count" style="display: inline-block;">
																	<div id="limitandstaff{$i+1}_{$j}" style="text-align: center; display: inline-block; width: 160px; font-size: 17px; position: relative;">　</div>
																</div>
															</div>
														</div>
													</li>
													{/for}
												</ul>
												</form>
												<div class="buttonSet dubble">
													<a href="#" onclick="submitData{$i+1}();" class="affirm">{t}設定する{/t}</a>
													<a href="#" class="cancel">{t}キャンセル{/t}</a>
												</div>
											</div>
											<div id="compDialog{$i+1}" class="dialog">
												<div class="cmpsub">{t}受入数設定が完了しました。{/t}</div>
												<div class="buttonSet single" id="complocation{$i+1}">
													<!--<a href="#" class="affirm">OK</a>-->
												</div>
											</div>
										</div>
									</div>
								{/if}
								</th>
								{/foreach}
							</tr>
						</thead>
						<tbody id="shiftinput">
						</tbody>
					</table>
					{if empty($memberid)}
					<table>
						<tbody>
							<tr>
								<th>{t}受入数{/t}</th>
								<th class="ao1">1</th>
								<th class="ao2">2～3</th>
								<th class="ao3">4～5</th>
								<th class="ao4">6～7</th>
								<th class="ao5">8～</th>
							</tr>
						</tbody>
					</table>
					{else}
					<div class="legend">
						<span class="note">{t}※セルをクリックして選択してください。{/t}</span>
						<div class="attached"><i></i>{t}：自分の担当シフト{/t}</div>
					</div>
					{/if}
				{else}
					<p style="line-height: 20px;">
						来年度のデータが未入力のため、画面を表示できません。<br>
						まず<a href="{$baseurl}/{$controllerName}/termandshift">学期/シフト入力許可設定画面</a>で、年度追加処理を実行してください。
					</p>
				{/if}
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
			</article>
		</div>
		{if $termid != null}
		<aside id="sidebar">
			<h1>{t}スタッフ一覧{/t}</h1>
			<nav id="rire">
				<ul class="admin">
					<li id ="staffCalendar"><a href="{$baseurl}/{$controllerName}/{$actionName}/{if !empty($campusid)}shiftclass/{$campusid}/{/if}{if !empty($termid)}termid/{$termid}/{/if}">{t}運営管理者{/t}</a><i>　</i></li>
				</ul>
					<p class="sut" id="staffNum">{t 1='-' 2=count($members)}当日スタッフ：%1名 (全：%2名){/t}</p>
				<ul>
					{foreach from=$members key=k item=v}
						<li id="memberRow{$v->id}"><a href="{$baseurl}/{$controllerName}/{$actionName}/{if !empty($campusid)}shiftclass/{$campusid}/{/if}{if !empty($termid)}termid/{$termid}/{/if}memberid/{$v->id}">{$v->name_jp}</a><i id="shiftTimeList{$v->id}"></i></li>
					{/foreach}
				</ul>
			</nav>
		</aside>
		{/if}
	 </div>
		{include file="../common/foot_v2.php"}
		
		<script>
			$(function(){
				$("#loginStatusTrigger").miniMenu($("#loginStatus"));

				$(".selectMirror").each(function(){
					$(this).selectMirror();
				});

				$(".finish").click(function(){
					flag = false;
				});

				{if empty($memberid)}
			    	$("li#staffCalendar").addClass("active");
			    {else}
			    {foreach from=$members key=k item=v}
					{if {$v->id} === {$memberid}}
						$("li#memberRow{$v->id}").addClass("active");
					{/if}
				{/foreach}
				{/if}

				{for $i=1 to 5}
				$("#pageControl{$i}").find(".finish").decisionDialog($("#finishDialog{$i}"),true);

				$('#setlimit{$i}').submit(function(event) {
					event.preventDefault();	// 本来のsubmit処理をキャンセル

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
								//alert('論理エラー');
								alert(response['error']);
							}
							else
							{	// 成功
								// 完了後の飛び先を設定
								var link = '{$baseurl}/{$controllerName}/{$actionName}/'{if !empty($campusid)} + 'shiftclass/{$campusid}/'{/if} {if !empty($termid)} + 'termid/{$termid}'{/if}{if !empty($memberid)} + "/memberid/{$memberid}" {/if};
								
								// 完了ダイアログ
								$("#finishDialog{$i}").find(".affirm").decisionDialog($("#compDialog{$i}"));
								$(this).delay(2200).queue(function() {
									window.location.href=link;
									$(this).dequeue();
								});
							}
						},
						error: function(jqXHR, textSatus, errorThrown) {
							alert("error");
						},
						complete: function(jqXHR, textStatus) {
						},
					});

				});
				{/for}
			});
		</script>
	<!--[if lte IE 9]>
	<script src="/js/flexie.min.js" type="text/javascript"></script>
	<![endif]-->
	</body>
</html>