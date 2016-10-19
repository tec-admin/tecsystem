<!doctype html>
<html lang="ja">
<head>

<!--
	{t}スタッフを選択してください{/t}
-->

{include file='admin/header.tpl'}

<script src="/js/base.reserve.js" type="text/javascript"></script>
	<script>
	window.onload = function()
	{
		var baseurl = '{$baseurl}/{$controllerName}';
		createShiftHead();
		createShiftCalender(baseurl,{if empty($campusid)}1{else}{$campusid}{/if});

		var reserveid = {if !empty($reserveid)}'{$reserveid}'{else}0{/if};
		if(reserveid != 0)
		{
			for(var i = 1; i <= 3; i++)
			{
				var elm = document.getElementById(reserveid + '_' + i);
				if(elm != undefined)
					elm.click();
			}
		}
	}

	//tablehead作成
	function createShiftHead()
	{
		//thead出力
		var thead = document.getElementById('shifthead');
		var tr1 = document.createElement('tr');
		var tr2 = document.createElement('tr');
		var th = document.createElement('th');
		th.setAttribute('class', 'blank');
		th.setAttribute("rowSpan", "2")
		tr1.appendChild(th);

		{foreach from=$consul_places item=consul_place name=consul_places}
			var index = {$smarty.foreach.consul_places.index};
			var thPlace = document.createElement('th');

			thPlace.appendChild(document.createTextNode('{$consul_place}'));
			thPlace.setAttribute('colSpan', '2');
			if(index % 2 == 0){
				thPlace.setAttribute('class', 'day1');
			}else{
				thPlace.setAttribute('class', 'day3');
			}
			tr1.appendChild(thPlace);
		{/foreach}
		thead.appendChild(tr1);
		if({$smarty.foreach.consul_places.iteration}==2)
			$(".shifttable").css('width','71.5%');

		if({$smarty.foreach.consul_places.iteration}==1)
			$(".shifttable").css('width','43%');

		{foreach from=$places item=consul_place name=places}
			var thPlace = document.createElement('th');
			var thReserve = document.createElement('th');
			var thStaff = document.createElement('th');
			thReserve.appendChild(document.createTextNode("{t}学生{/t}"));
			thStaff.appendChild(document.createTextNode("{t}スタッフ{/t}"));
			thReserve.setAttribute('class', 'day');
			thStaff.setAttribute('class', 'day');
			tr2.appendChild(thReserve);
			tr2.appendChild(thStaff);
		{/foreach}
		thead.appendChild(tr2);
	}

	//tablebody作成
	function createShiftCalender(baseurl,campusid)
	{
		// スタッフのシフトを取得し、入力表へ設定
		var request = createXMLHttpRequest();
		var scripturl = baseurl + "/getreserveinput/campusid/" + campusid {if !empty($ymd)} + '/ymd/{$ymd}' {/if};

		request.open("POST", scripturl , false);
		request.send(null);
		var json = request.responseText;
		var reserverlist = JSON.parse(json);
		
		//tbody出力
		var tbody = document.getElementById('shiftinput');
		tbody.innerHTML="";
		var tr = document.createElement('tr');
		var th = document.createElement('th');
		var td = document.createElement('td');
		//テーブルのベースを作成
		{foreach from=$shifts item=shift name=shifts}
			var index = {$smarty.foreach.shifts.index};
			for(var k=0 ;k<reserverlist['reservercnt'][index] ;k++){
				var tr = document.createElement('tr');
				if(k==0){
					var th = document.createElement('th');
					tr.setAttribute('class', 'sen');
					th.setAttribute('class', 'day');
					var shiftdata = toAlpha({$smarty.foreach.shifts.index}) + ' {$vDate->dateFormat($shift->m_timetables_starttime, 'H:i')}-{$vDate->dateFormat($shift->m_timetables_endtime, 'H:i')}';
					th.appendChild(document.createTextNode(shiftdata));
					th.setAttribute('rowSpan', reserverlist['reservercnt'][index]);
					tr.appendChild(th);
				}
				{foreach from=$places item=place name=places}
					var indexj = {$place->id};
					var td = document.createElement('td');
					var td1 = document.createElement('td');
					if(reserverlist['reserverlist'][index][indexj][k]!=null){
						if(reserverlist['reserverlist'][index][indexj][k]["reserver_name_jp"]!=""){
							var ele = document.createElement("a");
							var str = document.createTextNode(reserverlist['reserverlist'][index][indexj][k]["reserver_name_jp"]);
							ele.appendChild(str);
							td.appendChild(ele);
							td.setAttribute('class', 'ao');
							td.setAttribute('id', reserverlist['reserverlist'][index][indexj][k]['id']+'_1');

							td.setAttribute('data-dayno',  toAlpha({$smarty.foreach.shifts.index}));
							td.setAttribute('starttime', '{$vDate->dateFormat($shift->m_timetables_starttime, 'H:i')}');
							td.setAttribute('endtime', '{$vDate->dateFormat($shift->m_timetables_endtime, 'H:i')}');

							td.setAttribute('onclick', "disp('"+baseurl+"','"+reserverlist['reserverlist'][index][indexj][k]['id']+ "','1');");
						}
						if(reserverlist['reserverlist'][index][indexj][k]["charge_name_jp"]!=null){
							//担当あり
							if(reserverlist['reserverlist'][index][indexj][k]["reserver_name_jp"]!=""){
								td1.appendChild(document.createTextNode(reserverlist['reserverlist'][index][indexj][k]["charge_name_jp"]));
								td1.setAttribute('class', 'ao');
								td1.setAttribute('id', reserverlist['reserverlist'][index][indexj][k]['id']+'_2');
								td1.setAttribute('data-dayno',  toAlpha({$smarty.foreach.shifts.index}));
								td1.setAttribute('starttime', '{$vDate->dateFormat($shift->m_timetables_starttime, 'H:i')}');
								td1.setAttribute('endtime', '{$vDate->dateFormat($shift->m_timetables_endtime, 'H:i')}');
								td1.setAttribute('onclick', "disp('"+baseurl+"','"+reserverlist['reserverlist'][index][indexj][k]['id']+ "','2');");
								td.setAttribute('onclick', "disp('"+baseurl+"','"+reserverlist['reserverlist'][index][indexj][k]['id']+ "','2');");
							}else{
								td1.appendChild(document.createTextNode(reserverlist['reserverlist'][index][indexj][k]["charge_name_jp"]));
							}
						}else{
							td1.appendChild(document.createTextNode(''));
						}
					}
					tr.appendChild(td);
					tr.appendChild(td1);
				{/foreach}
				tbody.appendChild(tr);
			}
		{/foreach}
	}

	//リンク選択時、サイドバー表示
	function disp(baseurl,reserveid,idflg){
		//active解除
		if(document.newreservestatus.reserveid.value!=""){
			var td = document.getElementById(document.newreservestatus.reserveid.value +"_1");
			td.setAttribute('class','ao');
			if(document.newreservestatus.idflg.value=='2'){
				var td1 = document.getElementById(document.newreservestatus.reserveid.value+"_2");
				td1.setAttribute('class','ao');
			}
		}
		document.newreservestatus.reserveid.value=reserveid;
		document.newreservestatus.idflg.value=idflg;
		//active設定
		var td = document.getElementById(reserveid+"_1");
		td.setAttribute('class','active');
		if(idflg=='2'){
			var td1 = document.getElementById(reserveid+"_2");
			td1.setAttribute('class','active');
		}

		// 本日のスタッフ
	    var select = document.getElementById('sl');
	    var options = document.getElementById('sl').options;
	    var campusid = options.item(select.selectedIndex).value;

		var request = createXMLHttpRequest();
		var scripturl = baseurl + '/getreserve/campusid/' + campusid +'/reserveid/' + reserveid {if !empty($ymd)} + '/ymd/{$ymd}' {/if};
		request.open("POST", scripturl , false);
		request.send(null);
		var json = request.responseText;
		var reserver = JSON.parse(json);

		// セルから情報を取得
		var cel = document.getElementById(reserveid+"_1");
		var alpha = cel.getAttribute('data-dayno');
		var starttime = cel.getAttribute('starttime');
		var endtime = cel.getAttribute('endtime');

		// スタッフの選択画面設定
		var vreservationdate = document.getElementById('vreservationdate');
		vreservationdate.innerHTML = '{$vDate->dateFormat($ymd, 'Y/m/d(wj)')}';
		
		var vshift = document.getElementById('vshift');
		vshift.innerHTML = alpha + '.' + starttime + '-' + endtime;
		
		var vname = document.getElementById('vname');
		vname.innerHTML = reserver['reserverdetail']['reserver_name_jp'];
		
		var vstudent_id = document.getElementById('vstudent_id');
		vstudent_id.innerHTML = reserver['reserverdetail']['reserver_student_id'];

		var sidebar = deleteSidebar();
		//予約詳細
		var title = document.createElement('h1');
		title.appendChild(document.createTextNode("{t}予約詳細{/t}"));
		sidebar.appendChild(title);

		var nav = document.createElement('div');
		nav.setAttribute('id', 'yoya');

		var detail = document.createElement('ul');

		createTagText('li', "{t}年月日{/t}", '{$vDate->dateFormat($ymd, 'Y/m/d(wj)')}', detail);
		createTagText('li', "{t}シフト{/t}",  alpha+' '+starttime.substr(0, 5)+'-'+endtime.substr(0, 5) , detail);
		createTagText('li', "{t}学籍番号{/t}", reserver['reserverdetail']['reserver_student_id'], detail);
		createTagText('li', "{t}氏名{/t}", reserver['reserverdetail']['reserver_name_jp'], detail);

		var element = document.createElement('li');
		var element1 = document.createElement('li');
		var span = document.createElement('span');
		span.appendChild(document.createTextNode("{t}スタッフ{/t}"));
		element.setAttribute('id','staffCalendar');
		element.appendChild(span);
		detail.appendChild(element);

		var atag = document.createElement('a');
		atag.appendChild(document.createTextNode(reserver['reserverdetail']['charge_name_jp']));
		element1.setAttribute('id','basicSettingButton');
		element1.appendChild(atag);
		detail.appendChild(element1);

		nav.appendChild(detail);
		sidebar.appendChild(nav);

		//相談内容
		var title = document.createElement('h1');
		title.appendChild(document.createTextNode("{t}相談内容{/t}"));
		sidebar.appendChild(title);

		var nav = document.createElement('div');
		nav.setAttribute('id', 'yoya');

		var files_namelist = reserver['reserverdetail']['reservefiles'];

		var detail = document.createElement('ul');
		createTagText('li', "{t}文書の種類{/t}", reserver['reserverdetail']['m_dockinds_document_category'], detail);
		createTagText('li', "{t}相談場所{/t}", reserver['reserverdetail']['m_places_consul_place'], detail);
		createTagText('li', "{t}進行状況{/t}", reserver['reserverdetail']['progress'], detail);
		if(reserver['reserverdetail']['submitdate'] != "1970/01/01(木)")
			createTagText('li', "{t}提出日{/t}", reserver['reserverdetail']['submitdate'], detail);
		else
			createTagText('li', "{t}提出日{/t}", '', detail);
		createTagText('li', "{t}授業科目{/t}", reserver['reserverdetail']['m_subjects_class_subject'], detail);
		createTagText('li', "{t}担当教員{/t}", reserver['reserverdetail']['kyoin'], detail);

		var element = document.createElement('li');
		var element1 = document.createElement('li');
		var em = document.createElement('i');
		var span = document.createElement('span');
		span.appendChild(document.createTextNode("{t}添付ファイル{/t}"));
		element.setAttribute('id','staffCalendar');
		element.appendChild(span);
		detail.appendChild(element);

		for(var i=0;i<files_namelist.length;i++){
			var element2 = document.createElement('p');
			element2.appendChild(document.createTextNode(files_namelist[i]));
			em.appendChild(element2);
		}

		element1.setAttribute('id','staffCalendar');
		element1.appendChild(em);
		detail.appendChild(element1);


		createTagText('li', "{t}相談したいこと{/t}", reserver['reserverdetail']['question'], detail);
		nav.appendChild(detail);
		sidebar.appendChild(nav);
		
		//連絡先
		var title = document.createElement('h1');
		title.appendChild(document.createTextNode("{t}学生の連絡先{/t}"));
		sidebar.appendChild(title);

		var nav = document.createElement('div');
		nav.setAttribute('id', 'yoya');
		
		var detail = document.createElement('ul');
		createTagText2('li', '{t}メール{/t}', reserver['reserverdetail']['reserver_email'], detail);
		
		nav.appendChild(detail);
		sidebar.appendChild(nav);
		
		// キャンセルボタン
		var nav = document.createElement('div');
		nav.setAttribute('id', 'yoya');
		
		var detail = document.createElement('ul');
		var li = document.createElement('li');
		var a = document.createElement('a');
		a.appendChild(document.createTextNode("{t}キャンセル{/t}"));
		a.setAttribute('class', 'delete');
		a.setAttribute('value', 'deldel');
		li.setAttribute('id','sidebarCancel');
		li.appendChild(a);
		detail.appendChild(li);
		
		nav.appendChild(detail);
		sidebar.appendChild(nav);
		
		$("#sidebarCancel").find(".delete").decisionDialog($("#finishDialog"));		// 確認ダイアログ表示設定
		document.cancelreservestatus.reserveid.value=reserveid;

		//select作成
		var select = document.getElementById('basic-doctype2');
		//リセット
		select.innerHTML = "";
		for(var i = 0; i < reserver['staffselectlist'].length; i++){
			var option = document.createElement('option');
			option.setAttribute('value', reserver['staffselectlist'][i]['m_member_id']);
			option.innerHTML = reserver['staffselectlist'][i]['m_members_name_jp'];
			select.appendChild(option);
		}
		if(idflg=='2'){
			var option = document.createElement('option');
			option.setAttribute('value', '000');
			option.innerHTML = "{t}解除する{/t}";
			select.appendChild(option);
		}

		var finish = $("div#pageControl > button.finish");
		if($("select#basic-doctype2").children("option").val() == undefined)
		{
			var option = document.createElement('option');
			option.innerHTML = "{t}担当可能なスタッフがいません{/t}";
			select.appendChild(option);

			finish.removeAttr('onclick');
			finish.prop('disabled', 'disabled');
		}
		else
		{
			finish.attr('onclick', 'changestaff()');
			finish.removeAttr('disabled');
		}

		$("#basicSettingButton").basicSetting();

		{literal}
		$('html, body').animate({scrollTop:0}, 600);
		{/literal}
	}

	//サイドバー項目追加
	function createTagText(tag, title, text, parent)
	{
		var element = document.createElement(tag);
		var element1 = document.createElement(tag);

		var span = document.createElement('span');
		span.appendChild(document.createTextNode(title));
		element.setAttribute('id','staffCalendar');
		element.appendChild(span);
		parent.appendChild(element);

		if(text == undefined || text == '')
			text = ' ';
		var em = document.createElement('i');
		em.appendChild(document.createTextNode(text));
		element1.setAttribute('id','staffCalendar');
		element1.appendChild(em);
		parent.appendChild(element1);
	}
	
	//サイドバー項目追加2
	function createTagText2(tag, title, text, parent)
	{
		var element = document.createElement(tag);
		var element1 = document.createElement(tag);

		var span = document.createElement('span');
		span.appendChild(document.createTextNode(title));
		element.setAttribute('id','staffCalendar');
		element.appendChild(span);
		parent.appendChild(element);
		
		// 空ならリンクしない
		if(text == undefined || text == '')
		{
			text = ' ';
			var em = document.createElement('i');
			em.appendChild(document.createTextNode(text));
		}
		else
		{
			var em = document.createElement('a');
			em.appendChild(document.createTextNode(text));
			em.setAttribute('href', 'mailto:'+text);
			em.setAttribute('class', 'mail');
		}
		element1.setAttribute('id','staffCalendar');
		element1.appendChild(em);
		parent.appendChild(element1);
	}


	function deleteSidebar()
	{
		var sidebar = document.getElementById('sidebar');
		while (sidebar.firstChild)
			sidebar.removeChild(sidebar.firstChild);
		return sidebar;
	}


	//相談場所変更
	function selectCampus(){
	    var select = document.getElementById('sl');
	    var options = document.getElementById('sl').options;
	    var value = options.item(select.selectedIndex).value;

		var link = '{$baseurl}/{$controllerName}/{$actionName}/campusid/' + value {if !empty($reserveid)} + '/reserveid/' + '{$reserveid}'{/if}{if !empty($ymd)} + '/ymd/{$ymd}' {/if};
		document.location = link;
	}

	<!--ダイアログ-->
	//キャンセル
	function cancel()
	{
		$("#basicSettingDialog").bPopup().close();
	}
	//スタッフ選択時
	function changestaff()
	{
		var request = createXMLHttpRequest();
		var scripturl = '{$baseurl}/{$controllerName}' + '/changestaff/reserveid/' + document.newreservestatus.reserveid.value + '/chargeid/' + document.newreservestatus.chargeid.value;

		request.open("POST", scripturl , false);
		request.send(null);
		var json = request.responseText;
		$("#basicSettingDialog").bPopup().close();

		//解除時のリセット処理
		createShiftCalender('{$baseurl}/{$controllerName}',{if empty($campusid)}1{else}{$campusid}{/if});
		if(document.newreservestatus.chargeid.value=='000'){
			document.newreservestatus.idflg.value=1;
			var td = document.getElementById(document.newreservestatus.reserveid.value+"_1");
			td.setAttribute('class','active');
			//スタッフ
			disp('{$baseurl}/{$controllerName}',document.newreservestatus.reserveid.value,1);
		}else{
			//active設定
			var td = document.getElementById(document.newreservestatus.reserveid.value+"_1");
			td.setAttribute('class','active');
			var td1 = document.getElementById(document.newreservestatus.reserveid.value+"_2");
			td1.setAttribute('class','active');
			document.newreservestatus.idflg.value=2;
			disp('{$baseurl}/{$controllerName}',document.newreservestatus.reserveid.value,2);
		}
	}

	<!--相談場所表示切替-->
	function changeSelect(){
		var s1 = document.getElementById('sl').value;
		var id = s1;
		var html = document.getElementById(id).innerHTML;
		document.getElementById('table').innerHTML = html;
	}
	
	//スタッフ選択時
	function cancelReserve()
	{
		var request = createXMLHttpRequest();
		var scripturl = '{$baseurl}/{$controllerName}' + '/cancelreserve/reserveid/' + document.cancelreservestatus.reserveid.value;

		request.open("POST", scripturl , false);
		request.send(null);
		var json = request.responseText;
		$("#finishDialog").bPopup().close();

		//解除時のリセット処理
		document.newreservestatus.reserveid.value="";
		createShiftCalender('{$baseurl}/{$controllerName}',{if empty($campusid)}1{else}{$campusid}{/if});
		deleteSidebar();
	}
</script>
</head>

<body class="admin">
	{include file='admin/menu.tpl'}
		<div id="main">
			<article class="calendar">
				<h1>{t}予約状況{/t}</h1>
				<div id="yoyaku">
					<div class="container">
						<div class="facility">
							<div class="pager">
								<span class="date">
									<a  class="prev" href="{$baseurl}/{$controllerName}/{$actionName}{if !empty($campusid)}/campusid/{$campusid}{/if}{if !empty($beforeday)}/ymd/{$beforeday}{/if}">＜＜</a>
									　　{t 1='<input type="text" name="dispdate" size="14" id="dispdate" readonly="readonly" value="">'}%1の予約状況{/t}　　
									<a class="next" href="{$baseurl}/{$controllerName}/{$actionName}{if !empty($campusid)}/campusid/{$campusid}{/if}{if !empty($nextday)}/ymd/{$nextday}{/if}">＞＞</a>
								</span>
							</div>
						</div>
						<div align="right">{t}相談場所：{/t}
						<select id="sl" onChange="selectCampus()">
							{foreach from=$campuses item=campus name=campuses}
								<option value="{$campus->id}" {if $campusid == $campus->id}selected{/if}>{$campus->campus_name}</li>
							{/foreach}
						</select>
						</div>
						<!--予約表-->
							<table class="shifttable">
							<thead id="shifthead">
							</thead>
							<tbody id="shiftinput">
							</tbody>
						</table>
					</div>
			</div>
			</article>
		<!--/#main--></div>



		<aside id="sidebar">
			<h1>{t}予約学生を選択してください{/t}</h1>
		<div id="yoya">
		</div>
		</aside>


		<div id="pageControl" style="margin: 0;">
			<div id="basicSettingDialog" class="dialog" style="width:480px;">
				<i class="closeButton cancel"></i>
				<div class="sub">{t}スタッフを選択してください{/t}</div>
				<form method="POST" action="{$baseurl}/{$controllerName}/newreservestatus" name="newreservestatus" id="" enctype="multipart/form-data">
				<input type="hidden" name="reserveid" value="">
				<input type="hidden" name="idflg" value="">

				<ul class="formSet">
					<li>
						<label id="treservationdate" class="dialogleft">{t}年月日：{/t}</label>
						<label class="dialogright" id="vreservationdate">
						</label>
					</li>
					<li>
						<label id="tshift" class="dialogleft">{t}シフト：{/t}</label>
						<label class="dialogright" id="vshift">
						</label>
					</li>
					<li>
						<label id="tstudent_id" class="dialogleft">{t}学籍番号：{/t}</label>
						<label class="dialogright" id="vstudent_id">
						</label>
					</li>
					<li>
						<label id="basic_doctype1" class="dialogleft">{t}氏名：{/t}</label>
						<label class="dialogright" id="vname">
						</label>
					</li>
					<li>
						<label for="basic-doctype2" class="dialogleft">{t}スタッフ：{/t}</label>
						<div class="control dialogright">
							<select name="chargeid" id="basic-doctype2">
							</select>
						</div>
					</li>
				</ul>
				<div class="buttonSet dubble">
					<a class="delete" style="width: 100px; padding: 10px 20px;" onClick="cancel()">{t}キャンセル{/t}</a>
					<a class="affirm" style="width: 100px; padding: 10px 20px;" onClick="changestaff()">{t}決定{/t}</a>
				</div>
				</form>
			</div>
			
			<form method="POST" action="{$baseurl}/{$controllerName}/cancelreservestatus" name="cancelreservestatus" id="" enctype="multipart/form-data">
			<input type="hidden" name="reserveid" value="">
			<div id="finishDialog" class="dialog">
				<i class="closeButton cancel"></i>
				<div class="sub">{t}この予約を取り消しますか？{/t}</div>
				<div class="buttonSet dubble">
					<a href="#" onclick="cancelReserve()" class="affirm">{t}OK{/t}</a>
					<a href="#" class="cancel">{t}キャンセル{/t}</a>
				</div>
			</div>
			</form>
		</div>
			<!--/#contents--></div>
			{include file="../common/foot_v2.php"}
			
			<script>
				$(function(){
					$('#dispdate').val('{$vDate->dateFormat($ymd, 'Y/m/d(wj)')}');
					//var w = ["日","月","火","水","木","金","土"];
					var w = getDowArray();

					var mindate = new Date({$mindate});
					mindate = "{$mindate}" + "(" + w[mindate.getDay()] + ")";

					var maxdate = new Date({$maxdate});
					maxdate = "{$maxdate}" + "(" + w[maxdate.getDay()] + ")";

					$('#dispdate').datepicker({
						dateFormat: 'yy-mm-dd(D)',		//年月日の並びを変更
						minDate: mindate,				//dateFormatと表記を統一しなければいけない点に注意
						maxDate: maxdate,
						changeMonth: true,				//月をドロップボックスで選択化
						onSelect: function(dateText, inst){
							//var tmpDate = dateText.slice(0,-3);
							var tmpDate = dateText.replace(/\(.*\)/, '');
							$('#dispdate').val(dateFormat(tmpDate, 'Y/m/d(wj)'));
							var link = '{$baseurl}/{$controllerName}/{$actionName}/campusid/' + {$campusid} + "/ymd/" + tmpDate;
							document.location = link;
						}
					});

					$("#loginStatusTrigger").miniMenu($("#loginStatus"));
				});
			</script>
	<!--[if lte IE 9]>
	<script src="/js/flexie.min.js" type="text/javascript"></script>
	<![endif]-->
</body>
</html>