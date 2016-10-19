<!doctype html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="description" content="">
	<title>TECsystem トップページ</title>
	<!-- Flexie -->
	<!--[if lte IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
</head>
<script>
	window.onload = function()
	{
		{if $member->roles|mb_strpos:',' !== FALSE}
			{* 複数権限 *}
			var link = '{$baseurl}/seltop/index';
		{else}
			{* 個別権限 *}
			{if $member->roles|mb_strpos:'Student' !== FALSE}
				var link = '{$baseurl}/labo/index';
			{else if $member->roles|mb_strpos:'Staff' !== FALSE}
				var link = '{$baseurl}/staff/index';
			{else if $member->roles|mb_strpos:'Administrator' !== FALSE}
				var link = '{$baseurl}/admin/index';
			{else if $member->roles|mb_strpos:'Professor' !== FALSE}
				var link = '{$baseurl}/tecfolio/professor/file';
			{/if}
		{/if}

		document.location = link;
	}
</script>
<body>
</body>
</html>

