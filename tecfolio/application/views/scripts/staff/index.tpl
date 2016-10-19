<!doctype html>
<html lang="ja">
<head>
<!--
	{t}トップページ{/t}
	{t}シフトカレンダー{/t}
	{t}予定/指導履歴{/t}
	{t}全指導履歴{/t}
	{t}学期単位のシフト入力{/t}
	{t}お知らせ詳細{/t}
-->
{include file='staff/header.tpl'}

</head>

<body class="staff top">
	<div id="topbar">
		<div class="fix-wrap">
			<header>
				<h1><a href="{$baseurl}/{$controllerName}/index">{t}TECsystem{/t}</a></h1>
			</header>
			{include file="../common/head_lang.tpl"}
			<div id="user">
				<figure>
					<span class="photo"><img src="/images/userStaff.png" height="38" width="38" alt=""></span>
					<a class="name" href="#" id="loginStatusTrigger"><i></i>{$member->name_jp}</a>
					<ul class="droplist" id="loginStatus">
						<li><a href="{$baseurl}/auth/logout">{t}ログアウト{/t}</a></li>
					</ul>
					<span class="help"><a href="/pdf/簡易マニュアル(スタッフ).pdf" target="_blank"><img src="/images/question.gif" height="16" width="16" alt="{t}ヘルプ{/t}"></a></span>
				</figure>
			</div>
		</div>
	</div>
	<div id="contents" class="fix-wrap">
		<div id="toppageNavigation">
			<nav id="modeMenu">
				<ul>
					<li id="myHistory" class="inactive"><a href="#"><i></i>{t}TECfolio{/t}</a></li>
					<li id="laboCenter"><a href="{$baseurl}/{$controllerName}/calendar"><i></i>{t}ライティングラボ{/t}</a></li>
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
									<a href="{$baseurl}/{$controllerName}/advice/reserveid/{$info['t_reserve_id']}">
									{* 新規予約 *}
									{t 1=$info['name_jp'] 2=$vDate->dateFormat($info['reservationdate'], 'Y/m/d(wj)')}【予約】%1さん：%2{/t}
									{$vDate->dateFormat($info['m_timetables_starttime'], 'H:i')}-{$vDate->dateFormat($info['m_timetables_endtime'], 'H:i')}
									</a>
								{elseif $info['staffnews_type'] == 'update'}
									<a href="{$baseurl}/{$controllerName}/advice/reserveid/{$info['t_reserve_id']}">
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
									<a href="{$baseurl}/{$controllerName}/advice/reserveid/{$info['t_reserve_id']}">
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
										<a href="{$baseurl}/{$controllerName}/advice/reserveid/{$info['t_reserve_id']}">
										{* 新規予約 *}
										{t 1=$info['name_jp'] 2=$vDate->dateFormat($info['reservationdate'], 'Y/m/d(wj)')}【予約】%1さん：%2{/t}
										{$vDate->dateFormat($info['m_timetables_starttime'], 'H:i')}-{$vDate->dateFormat($info['m_timetables_endtime'], 'H:i')}
										</a>
									{elseif $info['staffnews_type'] == 'update'}
										<a href="{$baseurl}/{$controllerName}/advice/reserveid/{$info['t_reserve_id']}">
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
										<a href="{$baseurl}/{$controllerName}/advice/reserveid/{$info['t_reserve_id']}">
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
					{/if}
				</ul>
			</div>
		</div>
		<div id="reserveList">
				{assign var="shiftno" value=0}
				{foreach from=$nowdatereserves item=reserve name=nowdatereserves }
					{if $smarty.foreach.nowdatereserves.iteration == 1 }
						<div class="sub">{t 1=$vDate->dateFormat($nowdate, 'Y/m/d(wj)')}本日%1の予約{/t}<i id="nowdatereserves" class="nowdatereserves"></i></div>
						<ul class="todayAll">
					{/if}
					{if $shiftno != $reserve->m_timetables_starttime}
						{if $shiftno != 0}</ul></li>{/if}
						<li><time class="sub"><script>document.write(toAlpha({$reserve->m_shifts_dayno - 1}));</script>.&nbsp;{$vDate->dateFormat($reserve->m_timetables_starttime, 'H:i')}-{$vDate->dateFormat($reserve->m_timetables_endtime, 'H:i')}</time>
						<ul>
						{assign var="shiftno" value=$reserve->m_timetables_starttime}
					{/if}
					<li><a href="{$baseurl}/{$controllerName}/advice/reserveid/{$reserve->id}"><span class="name">{$reserve->name_jp}</span><span class="facility">({$reserve->m_places_consul_place})</span>{if !empty($reserve->charge_name_jp)}<span class="staff">{$reserve->charge_name_jp}</span>{/if}</a></li>
					{if $smarty.foreach.nowdatereserves.last == TRUE}</ul></li>{/if}
				{foreachelse}
				<div class="sub">{t 1=$vDate->dateFormat($nowdate, 'Y/m/d(wj)')}本日%1の予約{/t}<i class="nowdatereserves">{t 1=0}全%1件{/t}</i></div>
					<ul class="todayAll">
					<span class="nothing">{t}本日の予約はありません{/t}</span>
				{/foreach}
				<div id="sub" class="dn">{t 1={$smarty.foreach.nowdatereserves.index + 1}}全%1件{/t}</div>
			</ul>
		</div>
	</div>
	{include file="../common/foot_v2.php"}
	
	<script>
		$(function(){
			$("#loginStatusTrigger").miniMenu($("#loginStatus"));
			
			$("#morenews").hide();
		});
		$(window).load(function () {
			if({$smarty.foreach.nowdatereserves.index + 1} >= 1){
				var html = document.getElementById('sub').innerHTML;
				document.getElementById('nowdatereserves').innerHTML = html;
			}
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