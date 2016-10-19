<!doctype html>
<html lang="ja">
<head>

{include file='staff/header.tpl'}
<script src="/js/base.infomation.js" type="text/javascript"></script>
<script>

	window.onload = function()
	{
		createInfomationList('{$baseurl}/{$controllerName}', {if empty($infomationid)}0{else}{$infomationid}{/if}, {if empty($page)}1{else}{$page}{/if});
	}

</script>
</head>

<body class="staff">
	{include file='staff/menu.tpl'}
		<div id="main">
			<article class="noticeinfo">
				<h1>{t}お知らせ詳細{/t}</h1>
				{if !empty($infomation)}
				<table class="back">
					<thead>
						<tr>
							<th class="tit">{$infomation['title']}</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{t 1={$vDate->dateFormat($infomation['createdate'], 'Y/m/d(wj) H:i', false, true)}}掲示：%1{/t}</td>
						</tr>
						<tr>
							<td class="txt">{$infomation['body']}</td>
						</tr>
					</tbody>
				</table>
				{/if}
			</article>
		<!--/#main--></div>
		<aside id="sidebar">
			<h1>{t}履歴一覧{/t}</h1>
			<ul id="infomationlist">
			</ul>
			<div class="pager" id="infomationpager">
			</div>
		</aside>
		<!--/#contents--></div>
		{include file="../common/foot_v2.php"}
		
		<script>
			$(function(){
				$("#commentControl").find(".finish").decisionDialog($("#commentDialog"));
				$("#commentDialog").find(".affirm").decisionDialog($("#compDialog"));
				$(".countText").each(function(){
					$(this).textAreaCD();
				});
				$(".replaceButton").each(function(){
					$(this).replaceButton();
				});
				$("#loginStatusTrigger").miniMenu($("#loginStatus"));
			});
		</script>
	<!--[if lte IE 9]>
	<script src="/js/flexie.min.js" type="text/javascript"></script>
	<![endif]-->
</body>
</html>