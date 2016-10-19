<!--
	{t}同名のテーマが既に登録されています{/t}
	{t}テーマ名称を入力してください{/t}
-->
<div id="wrapMenu">
	<ul id="menu">
		<li class="commons hidden">
			<div>{t}TECコモンズ{/t}</div>
		</li>
		<li id="mytheme">
			<div class="title mytheme" id="tMytheme">
				{t}Myテーマ{/t}
			</div>
			<div class="menuBar minus"></div>
			<div class="menuBar plus hidden"></div>
			<div class="menuDummyRight" title="{t}テーマを作成します{/t}"></div>
			{foreach from=$mythemes item=mytheme name=mytheme}
			<div class="wrapTheme">
				<a href="{$baseurl}/{$controllerName}/{$actionName}/id/{$mytheme->id}" id="{$mytheme->id}" {if !empty($selected->id) && $selected->id == $mytheme->id}class="active"{/if}>{$mytheme->name}</a>
				<div class="targetdrop miniMenu"></div>
				<div class="wrapdrop">
					<ul class="droplist">
						<li class="mythemeUp{if $smarty.foreach.mytheme.first} hidden{/if}"><i class="up"></i><a class="up hasMenu" data-id="{$mytheme->id}" data-name="{$mytheme->name}">{t}上へ{/t}</a></li>
						<li class="mythemeDown{if $smarty.foreach.mytheme.last} hidden{/if}"><i class="down"></i><a class="down hasMenu" data-id="{$mytheme->id}" data-name="{$mytheme->name}">{t}下へ{/t}</a></li>
						<li class="mythemeEdit"><i class="edit"></i><a class="edit hasMenu" data-id="{$mytheme->id}" data-name="{$mytheme->name}">{t}名称編集{/t}</a></li>
						<li class="mythemeDisable"><i class="etc"></i><a class="etc hasMenu" data-id="{$mytheme->id}" data-name="{$mytheme->name}">{t}利用しない{/t}</a></li>
						<li class="mythemeDelete"><i class="delete"></i><a class="delete hasMenu" data-id="{$mytheme->id}" data-name="{$mytheme->name}">{t}テーマ削除{/t}</a></li>
					</ul>
				</div>
			</div>
			{/foreach}
			{if count($disabled_mythemes) > 0}
			<div class="wrapTheme">
				<a class="disabled">{t}<利用しないテーマ>{/t}</a>
				<div class="targetdrop miniMenu"></div>
				<div class="wrapdrop">
					<ul class="droplist">
					{foreach from=$disabled_mythemes item=disabled_mytheme name=disabled_mytheme}
						<li class="mythemeEnable"><i class="up"></i><a class="up hasMenu" data-id="{$disabled_mytheme->id}" data-name="{$disabled_mytheme->name}">{$disabled_mytheme->name}</a></li>
					{/foreach}
					</ul>
				</div>
			</div>
			{/if}
		</li>
		<!--
		<li id="activity">
			<div class="title activity" id="tActivity">
				課外活動
			</div>
			<a>オーケストラ</a>
		</li>
		-->
		<li id="subject">
			<div class="title subject" id="tSubject">
				{t}授業科目{/t}
			</div>
			<div class="menuBar minus"></div>
			<div class="menuBar plus hidden"></div>
			<div class="selectNendo">
				<form method="POST" action="{$baseurl}/{$controllerName}/getsubject" name="getSubjectForm" id="getSubjectForm" enctype="multipart/form-data">
					<select id="selectYear" name="selectYear">
						{section name=i loop=$nendo+1 max=$nendo+1-2015 step=-1}
						<option value="{$smarty.section.i.index}"{if (empty($currentNendo) && $smarty.section.i.index == $nendo) || $smarty.section.i.index == $currentNendo} selected="selected"{/if}>{$smarty.section.i.index}年度</option>
						{/section}
					</select>
					<select id="selectTerm" name="selectTerm">
						<option value="0"{if $currentGakki == 0} selected="selected"{/if}>全期間</option>
						<option value="1"{if $currentGakki == 1} selected="selected"{/if}>春学期</option>
						<option value="2"{if $currentGakki == 2} selected="selected"{/if}>秋学期</option>
					</select>
				</form>
			</div>
			<div class="wrapTheme">
			{foreach from=$subjects item=subject name=subject}
				<a href="{$baseurl}/{$controllerName}/{$actionName}/id/{$subject->id}" {if !empty($subjectid) && $subjectid == $subject->id}class="active"{/if}><span class="head">{$subject->yogen}　</span>{$subject->class_subject}</a>
			{/foreach}
			</div>
		</li>
		{if $controllerName == 'tecfolio/student'}
		<li id="facility">
			<div class="title facility" id="tFacility">
				{t}学内施設{/t}
			</div>
			<div class="menuBar minus"></div>
			<div class="menuBar plus hidden"></div>
			<div class="wrapTheme">
				<a href="{$baseurl}/{$controllerName}/{$actionName}/id/LABO_{$member->id}" {if !empty($laboid)}class="active"{/if}>{t}ライティングラボ{/t}</a>
			</div>
		</li>
		{/if}
		{if !empty($mentors) && count($mentors) > 0}
		<li id="mentor">
			<div class="title mentor" id="tMentor">
				{t}メンター依頼者{/t}
			</div>
			<div class="menuBar minus"></div>
			<div class="menuBar plus hidden"></div>
			<div class="wrapTheme">
			{foreach from=$mentors item=mentor name=mentor key=k}
				<a href="{$baseurl}/{$controllerName}/{$actionName}/id/{$mentor->id}" {if !empty($selected->t_mentors_id) && $selected->t_mentors_id == $mentor->id}class="active"{/if}>{$mentor->requester_name_jp} ({$mentor->mytheme_name})</a>
			{/foreach}
			</div>
		</li>
		{/if}
	</ul>
</div>