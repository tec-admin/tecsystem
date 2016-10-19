<!doctype html>
<html lang="ja">
<head>

{include file='admin/header.tpl'}

</head>

<body class="admin">
	{include file='admin/menu.tpl'}
		<div id="maind">
			<article>
				{$message}
			</article>
		</div>
	</div>
	{include file="../common/foot_v2.php"}
	<script src="/js/jquery-1.11.0.min.js" type="text/javascript"></script>
	<script src="/js/jquery-ui-1.10.4.custom.min.js" type="text/javascript"></script>
	<script src="/js/jquery.miniMenu.js" type="text/javascript"></script>
	<script src="/js/jquery.skOuterClick.js" type="text/javascript"></script>
	<script src="/js/jquery.ui.datepicker-ja.js" type="text/javascript"></script>
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