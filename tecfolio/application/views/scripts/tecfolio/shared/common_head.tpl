	<meta charset="UTF-8">
	<meta name="description" content="">
	<meta name="format-detection" content="telephone=no"/>
	
	<link href="/css/commons.css?{($smarty.now|date_format:"%d%H%M%S")}" type="text/css" rel="stylesheet" />
	<link href="/css/jquery-ui-1.10.4.custom.min.css" type="text/css" rel="stylesheet" />
	<link href="/css/languages.css?{($smarty.now|date_format:"%d%H%M%S")}" type="text/css" rel="stylesheet" />
	
	<script src="/js/footerFixed.js" type="text/javascript"></script>
	<script src="/js/jquery-1.11.0.min.js" type="text/javascript"></script>
	<script src="/js/jquery-ui-1.10.4.custom.min.js" type="text/javascript"></script>
	<script src="/js/jquery.localize.js" type="text/javascript"></script>
	<script src="/js/jquery.skOuterClick.js" type="text/javascript"></script>
	<script src="/js/jquery.miniMenu.js" type="text/javascript"></script>
	<script src="/js/jquery.bpopup.min.js" type="text/javascript"></script>
	<script src="/js/tecfolio.util.js" type="text/javascript"></script>
	<script src="/js/tecsystem.common.js" type="text/javascript"></script>
	<script src="/js/tecfolio.common.js" type="text/javascript" id="commonjs" data-lang="{$locale}" data-server="{$serverurl}" data-url="{$baseurl}/{$controllerName}" data-action="{$actionName}" data-selected="{if !empty($selected->id)}{$selected->id}{/if}" data-memberid="{$member->id}"></script>
	<script src="/js/jquery.fileUploader.js" type="text/javascript"></script>
	
	<script src="/js/tecfolio.api.js" type="text/javascript"></script>
	<script src="/js/jquery.tablesorter.js" type="text/javascript"></script>
	<script src="/js/jquery.tablesorter.pager.js" type="text/javascript"></script>
	<script src="/js/jquery.metadata.js" type="text/javascript"></script>
	<link href="/css/jquery.tablesorter.pager.css" type="text/css" rel="stylesheet" />
	
	<title>{t}{$subtitle}{/t} | TECfolio{if !empty($title)} {t}{$title}{/t}{/if}</title>