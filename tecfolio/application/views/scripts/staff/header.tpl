	<meta charset="UTF-8">
	<meta name="description" content="">
	<meta name="format-detection" content="telephone=no"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<title>{t}{$subtitle}{/t} | TECsystem{if !empty($title)} {$title}{/if}</title>
	<link href="/css/base.css?{($smarty.now|date_format:"%d%H%M%S")}" type="text/css" rel="stylesheet" />
	<link href="/css/jquery-ui-1.10.4.custom.min.css?{($smarty.now|date_format:"%d%H%M%S")}" type="text/css" rel="stylesheet" />
	<link href="/css/languages.css?{($smarty.now|date_format:"%d%H%M%S")}" type="text/css" rel="stylesheet" />
	<!-- iPhone（width: 〜480pxの端末） -->
	<link rel=”stylesheet” media=”screen and (max-device-width: 480px)” href=”iphone.css”>
	<!--iPad縦（width: 481〜1024の端末が縦（portrait）のとき）-->
	<link rel=”stylesheet” media=”screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait)” href=”ipad-portrait.css”>
	<!--iPad横（width: 481〜1024の端末が横（landscape）のとき）-->
	<link rel=”stylesheet” media=”screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape)” href=”ipad-landscape.css”>
	<script type="text/javascript" src="/js/footerFixed.js"></script>
	
	<script src="/js/jquery-1.11.0.min.js" type="text/javascript"></script>
	<script src="/js/jquery-ui-1.10.4.custom.min.js" type="text/javascript"></script>
	<script src="/js/jquery.localize.js" type="text/javascript"></script>
	<script src="/js/jquery.skOuterClick.js" type="text/javascript"></script>
	<script src="/js/jquery.bpopup.min.js" type="text/javascript"></script>
	<script src="/js/jquery.modalDialog.js" type="text/javascript"></script>
	<script src="/js/jquery.selectMirror.js" type="text/javascript"></script>
	<script src="/js/jquery.textAreaCD.js" type="text/javascript"></script>
	<script src="/js/jquery.replaceButton.js" type="text/javascript"></script>
	<script src="/js/jquery.miniMenu.js" type="text/javascript"></script>
	<script src="/js/jquery.MultiFile.pack.js" type="text/javascript"></script>
	<script src="/js/jquery.ui.datepicker-ja.js" type="text/javascript"></script>
	<script src="/js/tecsystem.common.js" type="text/javascript" id="commonjs" data-lang="{$locale}" data-server="{$serverurl}" data-url="{$baseurl}/{$controllerName}" data-action="{$actionName}"></script>
	<script src="/js/tecfolio.util.js" type="text/javascript"></script>
	<script src="/js/tecbook.common.js" type="text/javascript"></script>
	<script src="/js/base.share.js" type="text/javascript"></script>