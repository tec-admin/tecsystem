<!doctype html>
<html lang="ja">
<head>

{include file='staff/header.tpl'}

</head>

<body class="staff">
	{include file='staff/menu.tpl'}
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
	</body>
</html>