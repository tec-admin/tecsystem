		<div class="fix-wrap">
			<header>
				<h1>
					<a class="logo" href="{$baseurl}/{if $controllerName == 'tecfolio/student'}labo/index{else}{$controllerName}/file{/if}"></a>
				</h1>
				<nav>
					<ul{if $locale != 'ja'} class="eng"{/if}>
						<li class="nav1" id="top_file">
							<a href="{$baseurl}/{$controllerName}/file{if $actionName != 'file' && !empty($selected->id) && empty($mentorid)}/id/{$selected->id}{/if}" class="replace{if $actionName == 'file'} active{/if}">{t}ファイル置場{/t}</a>
						</li>
						<li class="nav3" id="top_portfolio">
							<a href="{$baseurl}/{$controllerName}/portfolio{if $actionName != 'portfolio' && !empty($selected->id) && empty($mentorid)}/id/{$selected->id}{/if}" class="replace{if $actionName == 'portfolio'} active{/if}">{t}ポートフォリオ{/t}</a>
						</li>
						<li class="nav5" id="top_rubric">
							<a href="{$baseurl}/{$controllerName}/rubric{if $actionName != 'rubric' && !empty($selected->id) && empty($mentorid)}/id/{$selected->id}{/if}" class="replace{if $actionName == 'rubric'} active{/if}">{t}ルーブリック{/t}</a>
						</li>
					</ul>
				</nav>
			</header>
			{include file="../../common/head_lang.tpl"}
			{if $controllerName === 'tecfolio/professor'}
			<div id="info">
				<span id="info_mark" class="mark{if !empty($new_info_flg)} colored{/if}">
					{if !empty($new_info_flg)}<span id="info_num">{if $new_info_flg <= 99}{$new_info_flg}{else}99{/if}</span>{/if}
				</span>
			</div>
			{/if}
			<div id="user">
				<figure>
					<span class="photo"><img src="{if !empty($profile->input_name)}{$profile->input_name}{else}/images/userStudent.png{/if}" height="40" width="40" alt=""></span>
					<a class="name" href="#" id="loginStatusTrigger"><i></i>{$member->name_jp}</a>
					<ul class="droplist" id="loginStatus">
						<li><a href="{$baseurl}/{$controllerName}/profile">{t}個人設定{/t}</a></li>
						{if $controllerName === 'tecfolio/professor'}
						<li><a href="{$baseurl}/{$controllerName}/setting">{t}授業科目設定{/t}</a></li>
						{/if}
						<li><a href="{$baseurl}/auth/logout">{t}ログアウト{/t}</a></li>
					</ul>
					<!--<span class="help"><a href="/pdf/簡易マニュアル(学生).pdf" target="_blank"><img src="/images/question.gif" height="16" width="16" alt="ヘルプ"></a></span>-->
				</figure>
			</div>
		</div>