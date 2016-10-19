<!doctype html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="description" content="">
	<meta name="format-detection" content="telephone=no"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<title>ログイン | TECsystem</title>
	<link href="/css/base.css" type="text/css" rel="stylesheet" />
	<link rel="stylesheet" href="/css/jquery-ui-1.10.4.custom.min.css" type="text/css" />
	<script src="/js/jquery-1.11.0.min.js" type="text/javascript"></script>
	<script src="/js/jquery-ui-1.10.4.custom.min.js" type="text/javascript"></script>
	<script src="/js/jquery.skOuterClick.js" type="text/javascript"></script>
	<script src="/js/jquery.bpopup.min.js" type="text/javascript"></script>
	<script src="/js/jquery.modalDialog.js" type="text/javascript"></script>
	<script type="text/javascript" src="/js/footerFixed.js"></script>

</head>
<body class="login">
	<article id="loginForm">
		<form method="POST" action="{$baseurl}/auth/orgprocess" name="frm">
			<h1><span>TECsystem</span>（学外関係者専用）</h1>
			<ul>
				<li>
					<label for="userid">利用者ID</label><input type="text" text="" id="account" name="account">
				</li>
				<li>
					<label for="password">パスワード</label><input type="password" text="" id="password" name="password">
				</li>
				{if !empty($error)}<li><span style="color:red;">{$error}</span></li>{/if}
			</ul>

			<div id="pageControl">
				<button class="finish" onclick="document.frm.submit();" >ログイン</button>
				<span class="back"><input type="reset" value="クリア"></span>
			</div>
		</form>
	</article>
	{include file="../common/foot_v2.php"}

	<script>
		$("form").submit(function() {
		  var self = this;
		  $(":submit", self).prop("disabled", true);
		  setTimeout(function() {
		    $(":submit", self).prop("disabled", false);
		  }, 5000);
		});
	</script>

</body>
</html>