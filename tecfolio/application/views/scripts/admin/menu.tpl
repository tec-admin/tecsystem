<div id="topbar">
	<div class="fix-wrap">
		<header>
			<h1><a href="{$baseurl}/{$controllerName}/index">{t}TECsystem{/t}</a></h1>
			<nav>
				<ul{if $locale != 'ja'} class="eng"{/if}>
					<!--<li class="nav1"><span class="replace">{t}TECfolio{/t}</span></li>-->
					<li class="nav2"><a href="{$baseurl}/{$controllerName}/index" class="replace">{t}ライティングラボ{/t}</a></li>
					<!--
					<li class="nav3"><span class="replace">文章作成ガイド</span></li>
					<li class="nav4"><span class="replace">キャリア支援</span></li>
					-->
				</ul>
			</nav>
		</header>
		{include file="../common/head_lang.tpl"}
		<div id="user">
			<figure>
				<span class="photo"><img src="/images/userAdmin.png" height="38" width="38" alt=""></span>
				<a class="name" href="#" id="loginStatusTrigger"><i></i>{$member->name_jp}</a>
				<ul class="droplist" id="loginStatus">
					<li><a href="{$baseurl}/auth/logout">{t}ログアウト{/t}</a></li>
				</ul>
			</figure>
		</div>
	</div>
</div>
<div id="contents" class="fix-wrap">
	<nav id="menu">
		<ul>
			<li class="reserve{if $actionName=='reservestatus'} active{else} inactive{/if}"><a href="{$baseurl}/{$controllerName}/reservestatus">{t}予約状況{/t}</a></li>
			<li class="shift{if $actionName=='workmanagement'} active{else} inactive{/if}"><a href="{$baseurl}/{$controllerName}/workmanagement">{t}シフト調整{/t}</a></li>
			<li class="shift{if $actionName=='shiftmanagement'} active{else} inactive{/if}"><a href="{$baseurl}/{$controllerName}/shiftmanagement">{t}シフト作成{/t}</a></li>
			<li class="shift{if $actionName=='closuredate'} active{else} inactive{/if}"><a href="{$baseurl}/{$controllerName}/closuredate">{t}閉室日設定{/t}</a></li>
			<li class="reserve{if $actionName=='allhistory'} active{else} inactive{/if}"><a href="{$baseurl}/{$controllerName}/allhistory">{t}全指導履歴{/t}</a></li>
			<li class="edit{if $actionName=='editinformation' || $actionName=='information'} active{else} inactive{/if}"><a href="{$baseurl}/{$controllerName}/editinformation">{t}お知らせ管理{/t}</a></li>
			<li class="view{if $actionName=='utilization' || $actionName=='byreserveform' || $actionName=='byfacultyandclass'} active{else} inactive{/if}"><a href="{$baseurl}/{$controllerName}/utilization">{t}利用統計{/t}</a></li>
			<li class="edit{if $actionName=='termandshift'} active{else} inactive{/if}"><a href="{$baseurl}/{$controllerName}/termandshift">{t}学期/シフト入力許可設定{/t}</a></li>
			<li class="edit{if $actionName=='dockind'} active{else} inactive{/if}"><a href="{$baseurl}/{$controllerName}/dockind">{t}文書種類設定{/t}</a></li>
			<li class="edit{if $actionName=='place'} active{else} inactive{/if}"><a href="{$baseurl}/{$controllerName}/place">{t}相談場所設定{/t}</a></li>
			<li class="edit{if $actionName=='agreement'} active{else} inactive{/if}"><a href="{$baseurl}/{$controllerName}/agreement">{t}利用規約設定{/t}</a></li>
			<li class="edit{if $actionName=='user'} active{else} inactive{/if}"><a href="{$baseurl}/{$controllerName}/user">{t}ユーザー権限設定{/t}</a></li>
			<li class="edit{if $actionName=='usermake'} active{else} inactive{/if}"><a href="{$baseurl}/{$controllerName}/makeuserid">{t}ユーザー作成{/t}</a></li>
		</ul>
	</nav>