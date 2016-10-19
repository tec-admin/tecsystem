<!doctype html>
<html lang="ja">
<head>
<!--
	{t}予約が存在しません。既に削除された可能性があります{/t}
-->
{include file='labo/header.tpl'}
<script src="/js/base.share.js" type="text/javascript"></script>
</head>

<body class="student">
	{include file='labo/menu.tpl'}
		<div id="maind">
			<article>
				{$message}
			</article>
		</div>
	</div>
	{include file="../common/foot_v2.php"}
	
	<script>
		$(function(){
			$("#loginStatusTrigger").miniMenu($("#loginStatus"));
		});
	</script>
	<!--[if lte IE 9]>
	<script src="/js/flexie.min.js" type="text/javascript"></script>
	<![endif]-->
	</body>
</html>