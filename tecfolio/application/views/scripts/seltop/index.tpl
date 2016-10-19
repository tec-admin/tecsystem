<!doctype html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="description" content="">
	<meta name="format-detection" content="telephone=no"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<title>{t}ログイン{/t} | TECsystem</title>
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
		<form action="">
			<h1><span>TECsystem</span></h1>
			<ul class="inactive">
				<li>
					<label for="userid">{t}利用者ID{/t}</label><input type="text" value="{$member->id}" id="userid" name="account" readonly>
				</li>
				<li>
					<label for="password">{t}パスワード{/t}</label><input type="password" value="123456" id="password" name="password" readonly>
				</li>
			</ul>
			<p>{t 1=$member->name_jp}ようこそ、%1さん。ログインするモードを選択してください{/t}</p>

			<div id="pageControl">
				<div class="buttonSet vertical">
					{if $member->roles|mb_strpos:'Student' !== FALSE}
						<a class="finish student" href="{$baseurl}/labo/">{t}学生{/t}<span class="note">{t}(自分の相談予約をする){/t}</span></a>
					{/if}

					{if $member->roles|mb_strpos:'Staff' !== FALSE}
						<a class="finish staff" href="{$baseurl}/staff/">{t}スタッフ{/t}<span class="note">{t}(相談業務を行う){/t}</span></a>
					{/if}

					{if $member->roles|mb_strpos:'Administrator' !== FALSE}
						<a class="finish admin" href="{$baseurl}/admin/">{t}運営管理者{/t}<span class="note">{t}(ラボの運営管理を行う){/t}</span></a>
					{/if}
					
					{if $member->roles|mb_strpos:'Professor' !== FALSE}
						<a class="finish prof" href="{$baseurl}/tecfolio/professor/file">{t}教員{/t}<span class="note">{t}(授業管理を行う){/t}</span></a>
					{/if}
				</div>
				<span class="back"><a href="{$baseurl}/auth/logout">{t}ログインをキャンセル{/t}</a></span>
			</div>
		</form>
	</article>
	{include file="../common/foot_v2.php"}
</body>
</html>