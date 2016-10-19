<!doctype html>
<html lang="ja">
<head>
<!--
	{t}トップページ{/t}
	{t}予約状況{/t}
	{t}勤務/業務管理{/t}
	{t}学期前シフト管理{/t}
	{t}閉室日設定{/t}
	{t}全指導履歴{/t}
	{t}新規お知らせ登録{/t}
	{t}お知らせ詳細{/t}
	{t}稼働率{/t}
	{t}予約形態別利用状況{/t}
	{t}学部・学年別利用状況{/t}
	{t}学期/シフト入力許可設定{/t}
	{t}文書種類設定{/t}
	{t}相談場所設定{/t}
	{t}利用規約設定{/t}
	{t}ユーザー権限設定{/t}
	{t}ユーザー登録{/t}
-->
{include file='admin/header.tpl'}

<script type="text/javascript" src="/js/index.js" id="indexjs" data-weektop="{$vDate->dateFormat($weektop, 'Y/m/d')}" ></script>

<script>
	var mintime	= {$mintime|date_format:'%-H'};
	var maxtime = {$maxtime|date_format:'%-H'} + 1;
	
	window.onload = function()
	{
		/*
		// カレンダー設定
		{if !empty($datelist)}
			
			// 本日の日付と曜日
			var myTbl = new Array("日","月","火","水","木","金","土");
			var myD = new Date();
			var myMonth = myD.getMonth() + 1;
			var myDate = myD.getDate() ;
			var myDay = myD.getDay();
			var myMess  = "本日は" + myMonth + "月" + myDate + "日" + "(" + myTbl[myDay] + ")"  ;
			if(myMonth < 10)
				myMonth = "0" + myMonth ;
			if(myDate < 10)
				myDate = "0" + myDate ;
			var Today = myMonth + "/" + myDate + " (" + myTbl[myDay] + ")" ;
			document.getElementById('today').appendChild(document.createTextNode(myMess));
			
			// 日付部
			{foreach from=$datelist item=day name=datelist}
				var ymd		= '{$day['ymd']}';
				var holiday	= '{$day['holiday']}';
				var name	= '{$day['name']}';
				var index	= {$smarty.foreach.datelist.index};

				var vdate	= '{$vDate->dateFormat($day['ymd'], 'm/d (wj)')}';
				var w		= Number('{$vDate->dateFormat($day['ymd'], 'w')}');

				var tab = 1
				var id = 't' + tab + '_ch' + index;
				var td = document.getElementById(id);
				while (td.firstChild)
					td.removeChild(td.firstChild);

				td.removeAttribute('class');
				if (w == 0)
					td.setAttribute('class', 'sunday');
				else if (w == 6)
					td.setAttribute('class', 'saturday');
				else if (holiday == 1)
					td.setAttribute('class', 'holiday');
				var span = document.createElement('span');
				span.setAttribute('class', 'day');
				if(Today == vdate){
					if (w == 0)
						td.setAttribute('class', 'tosunday');
					else if (w == 6)
						td.setAttribute('class', 'tosaturday');
					else if (holiday == 1)
						td.setAttribute('class', 'toholiday');
					else
						td.setAttribute('class', 'today');
				}
				span.appendChild(document.createTextNode(vdate));
				td.appendChild(span);
			{/foreach}
		{/if}
		
		{if !empty($calinfo)}
			var tabnum	= 1;
			
			{foreach from=$calinfo item=info name=calinfo}
				var infoid		= '{$info['id']}';
				var info		= '{$info['subtitle']}';
				
				var startdate	= '{$info['startdate']}';
				var enddate		= '{$info['enddate']}';
				
				var flg 		= {$info['allday_flag']};
				
				insideForeach(tabnum, infoid, info, startdate, enddate, flg);
			{/foreach}
			
			prepareFormat(tabnum);
		{/if}
		
		
		// 以下整形処理
		
		$('.scheduleTable tbody tr td').each(function()
		{
			if($(this).parent().hasClass('all')) return true;		// continue
			
			$(this).find('.item').each(function()
			{
				var num = $(this).attr('data-num');
				var max = Number($(this).attr('data-max')) + 1;
				
				$(this).css('width', 98/max + '%');
				$(this).css('margin-left', num * 100/max + '%');
				
				num++;
			});
		});
		
		$('.scheduleTable tr.all td').each(function()
		{
			var max = 0;
			$(this).find('.item').each(function()
			{
				max++
			});
			
			var num = 0;
			$(this).find('.item').each(function()
			{
				$(this).css('width', 98/max + '%');
				$(this).css('margin-left', num * 100/max + '%');
				num++;
			});
		});
		*/
	}

</script>

</head>

<body class="admin top">
	<div id="topbar">
		<div class="fix-wrap">
			<header>
				<h1><a href="{$baseurl}/{$controllerName}/index">{t}TECsystem{/t}</a></h1>
			</header>
			{include file="../common/head_lang.tpl"}
			<div id="user">
				<figure>
					<span class="photo"><img src="/images/userAdmin.png" height="38" width="38" alt=""></span>
					<a class="name" href="#" id="loginStatusTrigger"><i></i>{$member->name_jp}</a>
					<ul class="droplist" id="loginStatus">
						<li><a href="{$baseurl}/auth/logout">{t}ログアウト{/t}</a></li>
					</ul>
				</figure>
			</div>
		</div>
	</div>
	<div id="contents" class="fix-wrap">
		<div id="toppageNavigation">
			<nav id="modeMenu">
				<ul>
					<li id="myHistory" class="inactive"><a href="#"><i></i>{t}TECfolio{/t}</a></li>
					<li id="laboCenter"><a href="{$baseurl}/{$controllerName}/reservestatus"><i></i>{t}ライティングラボ{/t}</a></li>
					<!--
					<li id="guide" class="inactive"><a href="#"><i></i>文章作成ガイド</a></li>
					<li id="carrier" class="inactive"><a href="#"><i></i>キャリア支援</a></li>
					-->
				</ul>
			</nav>
			<div id="latestInfo" class="notice">
				<div class="sub">{t}新着情報{/t}</div>
				<ul>
					{if !empty($news) }
						{foreach from=$news item=info name=news}
							<li>
								<time>{$vDate->dateFormat($info['staffnews_date'], 'Y/m/d(wj) H:i')}</time>
								{if $info['staffnews_type'] == 'insert'}
									<a href="{$baseurl}/{$controllerName}/reservestatus{if $baseurl == "/kwl"}/campusid/{$info['m_places_m_campus_id']}{/if}/ymd/{$vDate->dateFormat($info['reservationdate'], 'Y-m-d')}/reserveid/{$info['t_reserve_id']}">
									{* 新規予約 *}
									{t 1=$info['name_jp'] 2=$vDate->dateFormat($info['reservationdate'], 'Y/m/d(wj)')}【予約】%1さん：%2{/t}
									{$vDate->dateFormat($info['m_timetables_starttime'], 'H:i')}-{$vDate->dateFormat($info['m_timetables_endtime'], 'H:i')}
									</a>
								{elseif $info['staffnews_type'] == 'update'}
									<a href="{$baseurl}/{$controllerName}/reservestatus{if $baseurl == "/kwl"}/campusid/{$info['m_places_m_campus_id']}{/if}/ymd/{$vDate->dateFormat($info['reservationdate'], 'Y-m-d')}/reserveid/{$info['t_reserve_id']}">
									{* 変更 *}
									{t 1=$info['name_jp'] 2=$vDate->dateFormat($info['reservationdate'], 'Y/m/d(wj)')}【変更】%1さん：%2{/t}
									{$vDate->dateFormat($info['m_timetables_starttime'], 'H:i')}-{$vDate->dateFormat($info['m_timetables_endtime'], 'H:i')}
									</a>
								{elseif $info['staffnews_type'] == 'delete'}
									{* 削除 *}
									<a class="can">
									{t 1=$info['name_jp'] 2=$vDate->dateFormat($info['reservationdate'], 'Y/m/d(wj)')}【キャンセル】%1さん：%2{/t}
									{$vDate->dateFormat($info['m_timetables_starttime'], 'H:i')}-{$vDate->dateFormat($info['m_timetables_endtime'], 'H:i')}
									</a>
								{else}
									<a href="{$baseurl}/{$controllerName}/reservestatus{if $baseurl == "/kwl"}/campusid/{$info['m_places_m_campus_id']}{/if}/ymd/{$vDate->dateFormat($info['reservationdate'], 'Y-m-d')}/reserveid/{$info['t_reserve_id']}">
									{* コメント *}
									{t 1=$info['name_jp'] 2=$vDate->dateFormat($info['reservationdate'], 'Y/m/d(wj)')}【コメント】%1さん：%2{/t}
									{$vDate->dateFormat($info['m_timetables_starttime'], 'H:i')}-{$vDate->dateFormat($info['m_timetables_endtime'], 'H:i')}
									</a>
								{/if}
							</li>
						{/foreach}
						<span id="morenews">
							{foreach from=$morenews item=info name=morenews}
								<li>
									<time>{$vDate->dateFormat($info['staffnews_date'], 'Y/m/d(wj) H:i')}</time>
									{if $info['staffnews_type'] == 'insert'}
										<a href="{$baseurl}/{$controllerName}/reservestatus{if $baseurl == "/kwl"}/campusid/{$info['m_places_m_campus_id']}{/if}/ymd/{$vDate->dateFormat($info['reservationdate'], 'Y-m-d')}/reserveid/{$info['t_reserve_id']}">
										{* 新規予約 *}
										{t 1=$info['name_jp'] 2=$vDate->dateFormat($info['reservationdate'], 'Y/m/d(wj)')}【予約】%1さん：%2{/t}
										{$vDate->dateFormat($info['m_timetables_starttime'], 'H:i')}-{$vDate->dateFormat($info['m_timetables_endtime'], 'H:i')}
										</a>
									{elseif $info['staffnews_type'] == 'update'}
										<a href="{$baseurl}/{$controllerName}/reservestatus{if $baseurl == "/kwl"}/campusid/{$info['m_places_m_campus_id']}{/if}/ymd/{$vDate->dateFormat($info['reservationdate'], 'Y-m-d')}/reserveid/{$info['t_reserve_id']}">
										{* 変更 *}
										{t 1=$info['name_jp'] 2=$vDate->dateFormat($info['reservationdate'], 'Y/m/d(wj)')}【変更】%1さん：%2{/t}
										{$vDate->dateFormat($info['m_timetables_starttime'], 'H:i')}-{$vDate->dateFormat($info['m_timetables_endtime'], 'H:i')}
										</a>
									{elseif $info['staffnews_type'] == 'delete'}
										{* 削除 *}
										<a class="can">
										{t 1=$info['name_jp'] 2=$vDate->dateFormat($info['reservationdate'], 'Y/m/d(wj)')}【キャンセル】%1さん：%2{/t}
										{$vDate->dateFormat($info['m_timetables_starttime'], 'H:i')}-{$vDate->dateFormat($info['m_timetables_endtime'], 'H:i')}
										</a>
									{else}
										<a href="{$baseurl}/{$controllerName}/reservestatus{if $baseurl == "/kwl"}/campusid/{$info['m_places_m_campus_id']}{/if}/ymd/{$vDate->dateFormat($info['reservationdate'], 'Y-m-d')}/reserveid/{$info['t_reserve_id']}">
										{* コメント *}
										{t 1=$info['name_jp'] 2=$vDate->dateFormat($info['reservationdate'], 'Y/m/d(wj)')}【コメント】%1さん：%2{/t}
										{$vDate->dateFormat($info['m_timetables_starttime'], 'H:i')}-{$vDate->dateFormat($info['m_timetables_endtime'], 'H:i')}
										</a>
									{/if}
								</li>
							{/foreach}
						</span>
						{if {$smarty.foreach.morenews.index + 1} >= 1 && {$smarty.foreach.news.index + 1}== 5}
							<a id="more" class="more">{t}さらに表示{/t}</a>
						{/if}
					{else}
						<li>{t}新着なし{/t}</li>
					{/if}
				</ul>
			</div>
			<div id="adminNotice" class="notice">
				<div class="sub">{t}管理者からのお知らせ{/t}</div>
				<ul>
					{if !empty($infomations) }
						{foreach from=$infomations item=info name=infomations}
							<li>
								<time>{$vDate->dateFormat($info['createdate'], 'Y/m/d(wj) H:i')}</time>
								<a href="{$baseurl}/{$controllerName}/information/informationid/{$info['id']}">
									{$info['title']}
								</a>
							</li>
						{/foreach}
					{else}
						<li>{t}お知らせなし{/t}</li>
					{/if}
				</ul>
			</div>
		</div>
		<!--
		<div id="schedule" class="hasTabs">
			<ul class="tabs">
				<li><a href="#eventSchedule">{t}イベント{/t}</a></li>
				<p class="ty" id="today"></p>
			</ul>
			<div class="container" id="eventSchedule">
				<div class="pager">
					<span class="date">
						<a href="{$baseurl}/{$controllerName}/{$actionName}/ymd/{$lastweek}" class="prev">previous</a>
							{$vDate->dateFormat($weektop, 'Y m/d')} - {$vDate->dateFormat($weekend, 'm/d')}
						<a href="{$baseurl}/{$controllerName}/{$actionName}/ymd/{$nextweek}" class="next">next</a>
					</span>
				</div>
				<table class="scheduleTable">
					<thead>
						<tr>
							<th></th>
							<th id="t1_ch0" class="sunday"></th>
							<th id="t1_ch1"></th>
							<th id="t1_ch2"></th>
							<th id="t1_ch3"></th>
							<th id="t1_ch4"></th>
							<th id="t1_ch5"></th>
							<th id="t1_ch6" class="saturday"></th>
						</tr>
					</thead>
					<tbody>
						<tr class="all">
							<th><i class="alltime">{t}終日{/t}</i></th>
							<td id="t1_cl0_all"></td>
							<td id="t1_cl1_all"></td>
							<td id="t1_cl2_all"></td>
							<td id="t1_cl3_all"></td>
							<td id="t1_cl4_all"></td>
							<td id="t1_cl5_all"></td>
							<td id="t1_cl6_all" class="last"></td>
						</tr>
						<tr class="blank">
							<th></th>
							<td id="t1_cl0_all"></td>
							<td id="t1_cl1_all"></td>
							<td id="t1_cl2_all"></td>
							<td id="t1_cl3_all"></td>
							<td id="t1_cl4_all"></td>
							<td id="t1_cl5_all"></td>
							<td id="t1_cl6_all" class="last"></td>
						</tr>
						{for $v={$mintime|date_format:'%-H'} to {$maxtime|date_format:'%-H'} + 1}
						<tr class="hour{$v|string_format:"%02d"}{if $v == {$maxtime|date_format:'%-H'} + 1} lasted{/if}">
							<th {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if}><i class="timeup">{$v}:00</i></th>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t1_cl0_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t1_cl1_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t1_cl2_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t1_cl3_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t1_cl4_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t1_cl5_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t1_cl6_{$v}" class="last"></td>
						</tr>
						{/for}
					</tbody>
				</table>
			</div>
		</div>
		-->
	</div>
	{include file="../common/foot_v2.php"}
	
	<script>
		$(function(){
			$("#loginStatusTrigger").miniMenu($("#loginStatus"));
			$("#schedule.hasTabs").tabs();
			
			$("#morenews").hide();
		});
		
		$("#more").click(function(){
			$("#morenews").show();
			$("#more").hide();
		});
	</script>
	<!--[if lte IE 9]>
	<script src="/js/flexie.min.js" type="text/javascript"></script>
	<![endif]-->
</body>
</html>