<!doctype html>
<html lang="ja">
<head>
<!--
	{t}トップページ{/t}
	{t}新規予約{/t}
	{t}予約詳細・変更{/t}
	{t}履歴詳細{/t}
	{t}お知らせ詳細{/t}
-->
{include file='labo/header.tpl'}

<script type="text/javascript" src="/js/index.js" id="indexjs" data-weektop="{$vDate->dateFormat($weektop, 'Y/m/d')}" data-active="{$active + 1}"></script>

<script>
	var mintime	= {$mintime|date_format:'%-H'};
	var maxtime = {$maxtime|date_format:'%-H'} + 1;
	
	window.onload = function()
	{
		// カレンダー設定
		{if !empty($datelist)}
			// 本日の日付と曜日
			//var myTbl = new Array("日","月","火","水","木","金","土");
			//var myTbl = getDowArray();
			
			var myD = new Date();
// 			var myMonth = myD.getMonth() + 1;
// 			var myDate = myD.getDate() ;
// 			var myDay = myD.getDay();
			//var myMess  = "本日は" + myMonth + "月" + myDate + "日" + "(" + myTbl[myDay] + ")" ;
			var myMess = "{t 1=$vDate->dateFormat($nowdate, 'm月d日(wj)', false, true)}本日は%1{/t}";
			
// 			if(myMonth < 10)
// 				myMonth = "0" + myMonth ;
// 			if(myDate < 10)
// 				myDate = "0" + myDate ;
// 			var Today = myMonth + "/" + myDate + " (" + myTbl[myDay] + ")";
			var Today = dateFormat(myD, 'm/d (wj)')
			
			var todayElm = document.getElementById('today');
			todayElm.innerHTML = myMess;

			// 日付部と提出日
			{foreach from=$datelist item=day name=datelist}
				var ymd		= '{$day['ymd']}';
				var holiday	= '{$day['holiday']}';
				var name	= '{$day['name']}';
				var submit	= {$day['submit']};
				var index	= {$smarty.foreach.datelist.index};

				var vdate	= '{$vDate->dateFormat($day['ymd'], 'm/d (wj)')}';
				//var wj		= '{$vDate->dateFormat($day['ymd'], '')}';
				var w		= Number('{$vDate->dateFormat($day['ymd'], 'w')}');

				for (var tab = 1; tab <= 3; tab++)
				{
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
					{
						td.setAttribute('class', 'holiday');
						td.setAttribute('title', name);
					}
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
					//td.appendChild(document.createTextNode(wj));

					// 提出日
					var csid = 't' + tab + '_cs' + index;
					var cstd = document.getElementById(csid);
					while (cstd.firstChild)
						cstd.removeChild(cstd.firstChild);
					if (submit > 0)
						cstd.appendChild(document.createTextNode('○'));
				}
			{/foreach}
		{/if}
		// 予定ごと
		{if !empty($calreserves)}
			// リストクリア
			for (var cl = 0; cl <= 6; cl++)
			{
				for (var hour = {$mintime|date_format:'%-H'}; hour <= {$maxtime|date_format:'%-H'}; hour++)
				{
					for (var tab = 1; tab <= 3; tab++)
					{
						var id = 't' + tab + '_cl' + cl + '_' + hour;
						var td = document.getElementById(id);
						while (td.firstChild)
							td.removeChild(td.firstChild);
					}
				}
			}


			{foreach from=$calreserves item=reserve name=calreserves}
				var ymd		= '{$reserve['reservationdate']}';
				var start	= '{$reserve['m_timetables_starttime']}';
				var end		= '{$reserve['m_timetables_endtime']}';
				var place	= '{$reserve['m_places_consul_place']}';
				var place2;
				// カレンダーに表示される文字の長さを調整
				if(place.length >= 15){
					place2 = place.substr(0,12);
					place2 += "...";
				}else{
					place2 = place;
				}
				var w		= Number('{$vDate->dateFormat($reserve['reservationdate'], 'w')}');		// 曜日

				var index	= {$smarty.foreach.calreserves.index};

				var vdate	= '{$vDate->dateFormat($reserve['reservationdate'], 'y/m/d(wj)')}';
				var vstime	= '{$vDate->dateFormat($reserve['m_timetables_starttime'], 'H:i')}';
				var vetime	= '{$vDate->dateFormat($reserve['m_timetables_endtime'], 'H:i')}';
				var shour	= Number('{$vDate->dateFormat($reserve['m_timetables_starttime'], 'H')}');	// 開始時
				var smin	= Number('{$vDate->dateFormat($reserve['m_timetables_starttime'], 'i')}');	// 開始分
				var ehour	= Number('{$vDate->dateFormat($reserve['m_timetables_endtime'], 'H')}');	// 開始時
				var emin	= Number('{$vDate->dateFormat($reserve['m_timetables_endtime'], 'i')}');	// 開始分
				var duration = (ehour * 60 + emin) - (shour * 60 + smin);							// 全体の時間（分）

				// 表示処理
				var tab = 2;
				var id = 't' + tab + '_cl' + w + '_' + shour;
				
				var td = document.getElementById(id);

				//var a = document.createElement('a');
				//a.setAttribute('href', '{$baseurl}/{$controllerName}/editreserve/reserveid/' + '{$reserve['id']}');
				var a = document.createElement('div');
				a.setAttribute('class', 'item');
				
				smin = smin * 0.675;
				a.setAttribute('style', 'top: ' + smin + 'px;');	// 分px

				var span1 = document.createElement('span');
				span1.setAttribute('id', 'cover{$smarty.foreach.calreserves.iteration}');
				span1.setAttribute('class', 'cover2');
				duration = duration * 0.675;
				
				span1.setAttribute('style', 'height: ' + duration + 'px;'); // 分px
				span1.setAttribute('title', vdate + ' / ' + vstime + '-' + vetime + ' / ' + place);

				var stime = document.createElement('time');

				var span2 = document.createElement('span');
				span2.setAttribute('class', 'place2');
				span2.appendChild(document.createTextNode(place2));
				span1.appendChild(span2);

				a.appendChild(span1);
				td.appendChild(a);

				$('#Schedule #cover{$smarty.foreach.calreserves.iteration}').tooltip();
			{/foreach}
			
		{/if}
		
		{if !empty($subjects)}
			var tabnum	= 1;
			
			$("#schedule.hasTabs").tabs("option", "active", tabnum - 1);
			{foreach from=$subjects item=info name=subject}
				var infoid		= '{$info['jwaricd']}';
				var info		= '{$info['class_subject']}';
				
				var startdate	= '{$vDate->timeAdd($vDate->dateAdd($weektop, $info['yobi'] ,'Y/m/d'), $info['starttime'] ,'Y/m/d H:i')}';
				var enddate		= '{$vDate->timeAdd($vDate->dateAdd($weektop, $info['yobi'] ,'Y/m/d'), $info['endtime'] ,'Y/m/d H:i')}';
				
				var flg 		= 0;
				
				insideForeach(tabnum, infoid, info, startdate, enddate, flg, '{$locale}');
			{/foreach}
			$("#schedule.hasTabs").tabs("option", "active", {$active});
			
			prepareFormat(tabnum);
		{/if}
		
		{if !empty($calinfo)}
			var tabnum	= 3;
			
			$("#schedule.hasTabs").tabs("option", "active", tabnum - 1);
			{foreach from=$calinfo item=info name=calinfo}
				var infoid		= '{$info['id']}';
				var info		= '{$info['subtitle']}';
				
				var startdate	= '{$info['startdate']}';
				var enddate		= '{$info['enddate']}';
				
				var flg 		= {$info['allday_flag']};
				
				insideForeach(tabnum, infoid, info, startdate, enddate, flg, '{$locale}');
			{/foreach}
			$("#schedule.hasTabs").tabs("option", "active", {$active});
			
			prepareFormat(tabnum);
		{/if}
		
		
		/***** 以下整形処理 *****/
		
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
	}
	
	
</script>
</head>

<body class="student top">
	<div id="topbar">
		<div class="fix-wrap">
			<header>
				<h1><a href="{$baseurl}/{$controllerName}/index">{t}TECsystem{/t}</a></h1>
			</header>
			{include file="../common/head_lang.tpl"}
			<div id="user">
				<figure>
					<span class="photo"><img src="/images/userStudent.png" height="38" width="38" alt=""></span>
					<a class="name" href="#" id="loginStatusTrigger"><i></i>{$member->name_jp}</a>
					<ul class="droplist" id="loginStatus">
						<li><a href="{$baseurl}/auth/logout">{t}ログアウト{/t}</a></li>
					</ul>
					<span class="help"><a href="/pdf/簡易マニュアル(学生).pdf" target="_blank"><img src="/images/question.gif" height="16" width="16" alt="{t}ヘルプ{/t}"></a></span>
				</figure>
			</div>
		</div>
	</div>
	<div id="contents" class="fix-wrap">
		<div id="toppageNavigation">
			<nav id="modeMenu">
				<ul>
					<li id="myHistory"><a href="{$baseurl}/tecfolio/student/file"><i></i>{t}TECfolio{/t}</a></li>
					<li id="laboCenter"><a href="{$baseurl}/{$controllerName}/reserve"><i></i>{t}ライティングラボ{/t}</a></li>
					<!--
					<li id="guide" class="inactive"><a href="#"><i></i>文章作成ガイド</a></li>
					<li id="carrier" class="inactive"><a href="#"><i></i>キャリア支援</a></li>
					-->
				</ul>
			</nav>
			<div id="latestInfo" class="notice">
				<div class="sub">{t}新着情報{/t}</div>
				<form method="POST" action="{$baseurl}/tecfolio/student/updatementor" name="updateMentorForm" id="updateMentorForm" enctype="multipart/form-data">
				<input type="hidden" name="mentor_id" id="mentor_id" />
				<input type="hidden" name="mentor_flag" id="mentor_flag" />
				<ul>
					{if !empty($news)}
						{foreach from=$news item=info name=news}
							{if !empty($info['t_mentors_lastupdate']) && $info['MyID'] == $info['t_mentors_m_member_id'] && $info['t_mentors_agreement_flag'] == 0}
								<li>
									<time class="new">{$vDate->dateFormat($info['studentnews_date'], 'Y/m/d(wj) H:i')}</time><span class="accept" data-id="{$info['t_mentors_id']}">{t}承諾{/t}</span><span class="reject" data-id="{$info['t_mentors_id']}">{t}拒否{/t}</span>
									<a class="mentor">{t 1={$info['requester_name_jp']}}%1さんからメンターの依頼が来ています。{/t}</a>
								</li>
							{elseif !empty($info['t_mentors_lastupdate']) && $info['MyID'] == $info['t_mentors_m_member_id'] && $info['t_mentors_agreement_flag'] != 0}
								<li>
									<time>{$vDate->dateFormat($info['studentnews_date'], 'Y/m/d(wj) H:i')}</time>
									{if $info['t_mentors_agreement_flag'] == 1}
									<a class="mentor">{t 1={$info['requester_name_jp']}}%1さんからのメンターの依頼を承諾しました。{/t}</a>
									{elseif $info['t_mentors_agreement_flag'] == 2}
									<a class="mentor">{t 1={$info['requester_name_jp']}}%1さんからのメンターの依頼を拒否しました。{/t}</a>
									{/if}
								</li>
							{elseif !empty($info['t_mentors_lastupdate']) && $info['MyID'] == $info['requester_id'] && $info['t_mentors_agreement_flag'] == 0}
								<li>
									<time>{$vDate->dateFormat($info['studentnews_date'], 'Y/m/d(wj) H:i')}</time>
									<a class="mentor">{t 1={$info['t_mentors_name_jp']}}%1さんにメンターの依頼をしています。{/t}</a>
								</li>
							{elseif !empty($info['t_mentors_lastupdate']) && $info['MyID'] == $info['requester_id'] && $info['t_mentors_agreement_flag'] != 0}
								<li>
									<time>{$vDate->dateFormat($info['studentnews_date'], 'Y/m/d(wj) H:i')}</time>
									{if $info['t_mentors_agreement_flag'] == 1}
									<a class="mentor">{t 1={$info['t_mentors_name_jp']}}%1さんへのメンターの依頼が承諾されました。{/t}</a>
									{elseif $info['t_mentors_agreement_flag'] == 2}
									<a class="mentor">{t 1={$info['t_mentors_name_jp']}}%1さんへのメンターの依頼が承諾されませんでした。{/t}</a>
									{/if}
								</li>
							{elseif empty($info['t_leadings_submit_flag'])}
								<li>
									<time>{$vDate->dateFormat($info['studentnews_date'], 'Y/m/d(wj) H:i')}</time>
									{* 予約 *}
									{if $info['type'] == 0}
										<a href="{$baseurl}/{$controllerName}/editreserve/reserveid/{$info['id']}">
									{elseif $info['type'] == 1}
										<a href="{$baseurl}/{$controllerName}/history/reserveid/{$info['id']}">
									{else}
										<a href="{$baseurl}/{$controllerName}/viewreserve/reserveid/{$info['id']}">
									{/if}
										{t 1=$vDate->dateFormat($info['reservationdate'], 'Y/m/d(wj)') 2=$vDate->dateFormat($info['m_timetables_starttime'], 'H:i') 3=$vDate->dateFormat($info['m_timetables_endtime'], 'H:i')}%1 %2-%3 に相談予約{/t}
									</a>
								</li>
							{else}
								{* 指導 *}
								{if $info['t_leadings_submit_flag'] != '0' && ($info['type'] == 1 || $info['type'] == 2)}
									<li>
										<time>{$vDate->dateFormat($info['studentnews_date'], 'Y/m/d(wj) H:i')}</time>
										<a href="{$baseurl}/{$controllerName}/history/reserveid/{$info['id']}">
											{t 1=$info['charge_name_jp'] 2=$vDate->dateFormat($info['reservationdate'], 'Y/m/d(wj)')}%1さんが %2 の相談にコメント{/t}
										</a>
									</li>
								{/if}
							{/if}
						{foreachelse}
						<li>{t}新着なし{/t}</li>
						{/foreach}
						<span id="morenews">
							{foreach from=$morenews item=info name=morenews}
								{if !empty($info['t_mentors_lastupdate']) && $info['MyID'] == $info['t_mentors_m_member_id'] && $info['t_mentors_agreement_flag'] == 0}
									<li>
										<time class="new">{$vDate->dateFormat($info['studentnews_date'], 'Y/m/d(wj) H:i')}</time><span class="accept" data-id="{$info['t_mentors_id']}">{t}承諾{/t}</span><span class="reject" data-id="{$info['t_mentors_id']}">{t}拒否{/t}</span>
										<a class="mentor">{t 1={$info['requester_name_jp']}}%1さんからメンターの依頼が来ています。{/t}</a>
									</li>
								{elseif !empty($info['t_mentors_lastupdate']) && $info['MyID'] == $info['t_mentors_m_member_id'] && $info['t_mentors_agreement_flag'] != 0}
									<li>
										<time>{$vDate->dateFormat($info['studentnews_date'], 'Y/m/d(wj) H:i')}</time>
										{if $info['t_mentors_agreement_flag'] == 1}
										<a class="mentor">{t 1={$info['requester_name_jp']}}%1さんからのメンターの依頼を承諾しました。{/t}</a>
										{elseif $info['t_mentors_agreement_flag'] == 2}
										<a class="mentor">{t 1={$info['requester_name_jp']}}%1さんからのメンターの依頼を拒否しました。{/t}</a>
										{/if}
									</li>
								{elseif !empty($info['t_mentors_lastupdate']) && $info['MyID'] == $info['requester_id'] && $info['t_mentors_agreement_flag'] == 0}
									<li>
										<time>{$vDate->dateFormat($info['studentnews_date'], 'Y/m/d(wj) H:i')}</time>
										<a class="mentor">{t 1={$info['t_mentors_name_jp']}}%1さんにメンターの依頼をしています。{/t}</a>
									</li>
								{elseif !empty($info['t_mentors_lastupdate']) && $info['MyID'] == $info['requester_id'] && $info['t_mentors_agreement_flag'] != 0}
									<li>
										<time>{$vDate->dateFormat($info['studentnews_date'], 'Y/m/d(wj) H:i')}</time>
										{if $info['t_mentors_agreement_flag'] == 1}
										<a class="mentor">{t 1={$info['t_mentors_name_jp']}}%1さんへのメンターの依頼が承諾されました。{/t}</a>
										{elseif $info['t_mentors_agreement_flag'] == 2}
										<a class="mentor">{t 1={$info['t_mentors_name_jp']}}%1さんへのメンターの依頼が承諾されませんでした。{/t}</a>
										{/if}
									</li>
								{elseif empty($info['t_leadings_submit_flag'])}
									<li>
										<time>{$vDate->dateFormat($info['studentnews_date'], 'Y/m/d(wj) H:i')}</time>
										{* 予約 *}
										{if $info['type'] == 0}
											<a href="{$baseurl}/{$controllerName}/editreserve/reserveid/{$info['id']}">
										{elseif $info['type'] == 1}
											<a href="{$baseurl}/{$controllerName}/history/reserveid/{$info['id']}">
										{else}
											<a href="{$baseurl}/{$controllerName}/viewreserve/reserveid/{$info['id']}">
										{/if}
											{t 1=$vDate->dateFormat($info['reservationdate'], 'Y/m/d(wj)') 2=$vDate->dateFormat($info['m_timetables_starttime'], 'H:i') 3=$vDate->dateFormat($info['m_timetables_endtime'], 'H:i')}%1 %2-%3 に相談予約{/t}
										</a>
									</li>
								{else}
									{* 指導 *}
									{if $info['t_leadings_submit_flag'] != '0' && ($info['type'] == 1 || $info['type'] == 2)}
										<li>
											<time>{$vDate->dateFormat($info['studentnews_date'], 'Y/m/d(wj) H:i')}</time>
											<a href="{$baseurl}/{$controllerName}/history/reserveid/{$info['id']}">
												{t 1=$info['charge_name_jp'] 2=$vDate->dateFormat($info['reservationdate'], 'Y/m/d(wj)')}%1さんが %2 の相談にコメント{/t}
											</a>
										</li>
									{/if}
								{/if}
							{/foreach}
						</span>
						{if {$smarty.foreach.morenews.index + 1} >= 1 && {$smarty.foreach.news.index + 1}== 5}
							<a id="more" class="more">{t}さらに表示{/t}</a>
						{/if}
					{else}
						<li>{t}新着なし{/t}</li>
					{/if}
				</ul>
				</form>
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
					{/if}
				</ul>
			</div>
		</div>
		{if $baseurl == "/kwl"}
		<div id="schedule" class="hasTabs">
			<ul class="tabs">
				<li><a href="#studentsSchedule">{t}時間割{/t}</a></li>
				<li><a href="#Schedule">{t}ラボ予約{/t}</a></li>
				<li><a href="#eventSchedule">{t}ラボイベント{/t}</a></li>
				<p class="ty" id="today"></p>
			</ul>
			<div class="container" id="studentsSchedule">
				<div class="pager">
					<span class="date">
						<a href="{$baseurl}/{$controllerName}/{$actionName}/ymd/{$lastweek}" class="prev">previous</a>
							{$vDate->dateFormat($weektop, 'Y')} {$vDate->dateFormat($weektop, 'm/d', false, true)} - {$vDate->dateFormat($weekend, 'm/d', false, true)}
						<a href="{$baseurl}/{$controllerName}/{$actionName}/ymd/{$nextweek}" class="next">next</a>
					</span>
				</div>
				<div class="attention">
					{t}この時間割は、休講や補講等の情報は更新されていません。正しい情報は、インフォメーションシステムで確認してください。{/t}
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
						<tr class="deadline">
							<th class="produce">{t}提出日{/t}</th>
							<td id="t1_cs0"></td>
							<td id="t1_cs1"></td>
							<td id="t1_cs2"></td>
							<td id="t1_cs3"></td>
							<td id="t1_cs4"></td>
							<td id="t1_cs5"></td>
							<td id="t1_cs6" class="last"></td>
						</tr>
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
			<div class="container" id="Schedule">
				<div class="pager">
					<span class="date">
						<a href="{$baseurl}/{$controllerName}/{$actionName}/ymd/{$lastweek}/active/1" class="prev">previous</a>
							{$vDate->dateFormat($weektop, 'Y')} {$vDate->dateFormat($weektop, 'm/d', false, true)} - {$vDate->dateFormat($weekend, 'm/d', false, true)}
						<a href="{$baseurl}/{$controllerName}/{$actionName}/ymd/{$nextweek}/active/1" class="next">next</a>
					</span>
				</div>
				<table class="scheduleTable">
					<thead>
						<tr>
							<th></th>
							<th id="t2_ch0" class="sunday"></th>
							<th id="t2_ch1"></th>
							<th id="t2_ch2"></th>
							<th id="t2_ch3"></th>
							<th id="t2_ch4"></th>
							<th id="t2_ch5"></th>
							<th id="t2_ch6" class="saturday"></th>
						</tr>
					</thead>
					<tbody>
						<tr class="deadline">
							<th class="produce">{t}提出日{/t}</th>
							<td id="t2_cs0"></td>
							<td id="t2_cs1"></td>
							<td id="t2_cs2"></td>
							<td id="t2_cs3"></td>
							<td id="t2_cs4"></td>
							<td id="t2_cs5"></td>
							<td id="t2_cs6" class="last"></td>
						</tr>
						<tr class="all">
							<th><i class="alltime">{t}終日{/t}</i></th>
							<td id="t2_cl0_all"></td>
							<td id="t2_cl1_all"></td>
							<td id="t2_cl2_all"></td>
							<td id="t2_cl3_all"></td>
							<td id="t2_cl4_all"></td>
							<td id="t2_cl5_all"></td>
							<td id="t2_cl6_all" class="last"></td>
						</tr>
						<tr class="blank">
							<th></th>
							<td id="t2_cl0_all"></td>
							<td id="t2_cl1_all"></td>
							<td id="t2_cl2_all"></td>
							<td id="t2_cl3_all"></td>
							<td id="t2_cl4_all"></td>
							<td id="t2_cl5_all"></td>
							<td id="t2_cl6_all" class="last"></td>
						</tr>
						{for $v={$mintime|date_format:'%-H'} to {$maxtime|date_format:'%-H'} + 1}
						<tr class="hour{$v|string_format:"%02d"}{if $v == {$maxtime|date_format:'%-H'} + 1} lasted{/if}">
							<th {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if}><i class="timeup">{$v}:00</i></th>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t2_cl0_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t2_cl1_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t2_cl2_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t2_cl3_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t2_cl4_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t2_cl5_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t2_cl6_{$v}" class="last"></td>
						</tr>
						{/for}
					</tbody>
				</table>
			<!--/schedule--></div>

			<div class="container" id="eventSchedule">
				<div class="pager">
					<span class="date">
						<a href="{$baseurl}/{$controllerName}/{$actionName}/ymd/{$lastweek}/active/2" class="prev">previous</a>
							{$vDate->dateFormat($weektop, 'Y')} {$vDate->dateFormat($weektop, 'm/d', false, true)} - {$vDate->dateFormat($weekend, 'm/d', false, true)}
						<a href="{$baseurl}/{$controllerName}/{$actionName}/ymd/{$nextweek}/active/2" class="next">next</a>
					</span>
				</div>
				<table class="scheduleTable">
					<thead>
						<tr>
							<th></th>
							<th id="t3_ch0" class="sunday"></th>
							<th id="t3_ch1"></th>
							<th id="t3_ch2"></th>
							<th id="t3_ch3"></th>
							<th id="t3_ch4"></th>
							<th id="t3_ch5"></th>
							<th id="t3_ch6" class="saturday"></th>
						</tr>
					</thead>
					<tbody>
						<tr class="deadline">
							<th class="produce">{t}提出日{/t}</th>
							<td id="t3_cs0"></td>
							<td id="t3_cs1"></td>
							<td id="t3_cs2"></td>
							<td id="t3_cs3"></td>
							<td id="t3_cs4"></td>
							<td id="t3_cs5"></td>
							<td id="t3_cs6" class="last"></td>
						</tr>
						<tr class="all">
							<th><i class="alltime">{t}終日{/t}</i></th>
							<td id="t3_cl0_all"></td>
							<td id="t3_cl1_all"></td>
							<td id="t3_cl2_all"></td>
							<td id="t3_cl3_all"></td>
							<td id="t3_cl4_all"></td>
							<td id="t3_cl5_all"></td>
							<td id="t3_cl6_all" class="last"></td>
						</tr>
						<tr class="blank">
							<th></th>
							<td id="t3_cl0_all"></td>
							<td id="t3_cl1_all"></td>
							<td id="t3_cl2_all"></td>
							<td id="t3_cl3_all"></td>
							<td id="t3_cl4_all"></td>
							<td id="t3_cl5_all"></td>
							<td id="t3_cl6_all" class="last"></td>
						</tr>
						{for $v={$mintime|date_format:'%-H'} to {$maxtime|date_format:'%-H'} + 1}
						<tr class="hour{$v|string_format:"%02d"}{if $v == {$maxtime|date_format:'%-H'} + 1} lasted{/if}">
							<th {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if}><i class="timeup">{$v}:00</i></th>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t3_cl0_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t3_cl1_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t3_cl2_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t3_cl3_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t3_cl4_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t3_cl5_{$v}"></td>
							<td {if $v == {$maxtime|date_format:'%-H'} + 1}class="lasted"{/if} id="t3_cl6_{$v}" class="last"></td>
						</tr>
						{/for}
					</tbody>
				</table>
			<!--/event.schedule--></div>
		</div>
		{/if}
	</div>
	<div id="pageControl">
		<div id="acceptedDialog" class="dialog">
			<div class="cmpsub">{t}メンターの依頼を承諾しました。{/t}</div>
		</div>
		<div id="rejectedDialog" class="dialog">
			<div class="cmpsub">{t}メンターの依頼を拒否しました。{/t}</div>
		</div>
	</div>
	
	{include file="../common/foot_v2.php"}
	
	<script>
		function callUpdateMentor()
		{
			var val = $('#mentor_flag').prop('value');
			if(val == 1)
				$('#acceptedDialog').bPopup();
			else
				$('#rejectedDialog').bPopup();
			
			$(this).delay(1000).queue(function()
			{
				//window.location.href=link;
				window.location.reload();
				$(this).dequeue();
			});
		}
		
		$(function(){
			$('.accept').each(function(){
				$(this).click(function(){
					$('#mentor_id').prop('value', $(this).data('id'));
					$('#mentor_flag').prop('value', '1');
					$('#updateMentorForm').submit();
				});
			});
			
			$('.reject').each(function(){
				$(this).click(function(){
					$('#mentor_id').prop('value', $(this).data('id'));
					$('#mentor_flag').prop('value', '2');
					$('#updateMentorForm').submit();
				});
			});
			
			$("#loginStatusTrigger").miniMenu($("#loginStatus"));
			$("#schedule.hasTabs").tabs();
			{if !empty($active)}
			$("#schedule.hasTabs").tabs("option", "active", {$active});
			{/if}
			
			$("#morenews").hide();
			
			$('#updateMentorForm').submit(function(event) {
				ajaxSubmit(this, event, callUpdateMentor);
			});
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
