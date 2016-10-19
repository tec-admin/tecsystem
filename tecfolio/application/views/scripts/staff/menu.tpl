<div id="topbar">
		<div class="fix-wrap">
			<header>
				<h1><a href="{$baseurl}/{$controllerName}/index">{t}TECsystem{/t}</a></h1>
				<nav>
					<ul{if $locale != 'ja'} class="eng"{/if}>
						<!--<li class="nav1"><span class="replace">{t}TECfolio{/t}</span></li>-->
						<li class="nav2"><a href="{$baseurl}/{$controllerName}/calendar" class="replace">{t}ライティングラボ{/t}</a></li>
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
					<span class="photo"><img src="/images/userStaff.png" height="38" width="38" alt=""></span>
					<a class="name" href="#" id="loginStatusTrigger"><i></i>{$member->name_jp}</a>
					<ul class="droplist" id="loginStatus">
						<li><a href="{$baseurl}/auth/logout">{t}ログアウト{/t}</a></li>
					</ul>
					<span class="help"><a href="/pdf/簡易マニュアル(スタッフ).pdf" target="_blank"><img src="/images/question.gif" height="16" width="16" alt="{t}ヘルプ{/t}"></a></span>
				</figure>
			</div>
		</div>
	</div>
	<div id="contents" class="fix-wrap">
		<nav id="menu">
			<ul>
				<li id ="staffCalendar"{if $actionName=='calendar'} class="active"{/if}><a href="{$baseurl}/{$controllerName}/calendar">{t}シフトカレンダー{/t}</a></li>
				<li id="staffAdvice"{if $actionName=='advice'} class="active"{/if}><a href="{$baseurl}/{$controllerName}/advice">{t}予定/指導履歴{/t}</a></li>
				<li id="staffHistory"{if $actionName=='allhistory'} class="active"{/if}><a href="{$baseurl}/{$controllerName}/allhistory">{t}全指導履歴{/t}</a></li>
				<li id="staffShift"{if $actionName=='shift'} class="active"{/if}><a href="{$baseurl}/{$controllerName}/shift">{t}シフト入力{/t}</a></li>
				<li id="staffNotice"{if $actionName=='information'} class="active"{/if}><a href="{$baseurl}/{$controllerName}/information">{t}お知らせ{/t}</a></li>
			</ul>
		</nav>