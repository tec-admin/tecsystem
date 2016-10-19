<!doctype html>
<html lang="ja">
<head>
	{include file='tecfolio/shared/common_head.tpl'}
	{assign var='subject_flg' value={$controllerName == 'tecfolio/professor' && !empty($subjectid)} nocache}
	{assign var='common_flg' value={!empty($mentorid) || !empty($subject_flg)} nocache}
	{assign var='mentor_flg' value={!empty($mentorid)} nocache}
	
	<script src="/js/tecfolio.portfolio.js?{($smarty.now|date_format:"%d%H%M%S")}" type="text/javascript" id="portfoliojs" data-subject_flg="{$subject_flg}" data-common_flg="{$common_flg}" data-mentor_flg="{$mentor_flg}"></script>
	
	<script>
		// ポートフォリオ追加
		function submitInsertPortfolio()
		{
			// タイトルが入力されているか
			if($('#pftitle').val() != '')
			{
				$('#insertPortfolioForm').submit();
			}
			else
			{
				$('#insertPortfolioTitleFailDialog').bPopup();
			}
		}
		
		// ポートフォリオ削除
		function submitDeletePortfolio()
		{
			$('#deletePortfolioForm').submit();
		}
		
		// ポートフォリオ選択
		function submitGetPortfolioDetail(id)
		{
			// 各ダイアログ内のポートフォリオID(hidden値)を更新する
			$('.t_portfolio_id').each(function(){
				$(this).prop('value', id);
			});
			
			$('#getPortfolioDetailForm').submit();
		}
		
		// タイトル更新
		function submitUpdateTitle()
		{
			if($('#update_title').val() != '')
				$('#updateTitleForm').submit();
			else
				$('#updateTitleNoValueDialog').bPopup();
		}
		
		// コンテンツ追加
		function submitAddContentsToPortfolio()
		{
			// 最低一つのチェックボックスにチェックが必要
			var flg = 0;
			$('#dialogAddContentsInner .contentCheck').each(function()
			{
				if($(this).prop('checked'))
				{
					flg = 1;
					return true;
				}
			});
			
			// 一つでもチェックがされているか
			if(flg != 0)
			{
				$('#addContentsToPortfolioForm').submit();
			}
			else
			{
				$('#addContentsCheckFailDialog').bPopup();
			}
		}
		
		// コンテンツ解除
		function submitDeletePFC()
		{
			$('#deletePFCForm').submit();
		}
		
		// ルーブリック選択
		function submitSelectRubric()
		{
			var selected = $('#selectrubric').val();
			
			if(selected == undefined || selected == '0')
				$('#selectRubricNoTargetDialog').bPopup();
			else if(!$('#edit_rubric').hasClass('noRubric'))
				$('#selectRubricConfirmDialog').bPopup();
			else
				submitSelectRubricConfirm();
		}
		
		// ルーブリック確認
		function submitSelectRubricConfirm()
		{
			$('#updatePortfolioRubricForm').submit();
		}
		
		// ルーブリック解除
		function submitDeleteSelectedRubric()
		{
			$('#deleteSelectedRubricForm').submit();
		}
		
		// 自己評価
		function submitRating()
		{
			{if empty($common_flg)}
				$('#upsertSelfRatingForm').submit();
			{else}
				$('#upsertMentorRatingForm').submit();
			{/if}
		}
		
		// メンター検索
		function submitSearchMentor()
		{
			$('#searchMentorForm').submit();
		}
		
		// メンター依頼
		function submitRequestMentor()
		{
			if($('#mentor_selected_id').val() == '')
				$('#searchMentorNoValueFailDialog').bPopup();
			else
				$('#requestMentorForm').submit();
		}
		
		// メンター相談
		function submitInsertChatMentor()
		{
			if($('#chat_body').val() == '')
				$('#insertChatMentorFailDialog').bPopup();
			else
				$('#insertChatMentorForm').submit();
		}
	</script>
</head>

<body class="commons">
<div>
	<div id="topbar">
		{include file='tecfolio/shared/common_top.tpl'}
	</div>
	<div id="contents" class="fix-wrap">
		{include file='tecfolio/shared/common_menu.tpl'}
		<div id="main">
			<article>
				<div id="wrapcontent" class="noTabs">
				{if !empty($selected) && empty($error)}
					<div class="head">
						<h1 id="content_name">◆{$selected->name}</h1>
						{if !empty($selected->createdate) && empty($laboid)}<div id="starting_period">{t 1={$vDate->dateFormat($selected->createdate, 'Y/m/d')}}取り組み期間：%1～{/t}</div>{/if}
					</div>
					<div class="contents container portfolio" id="activeContents">
						{if empty($common_flg)}
						<div class="icons">
							<div class="wrapicon">
								<div class="connect noMenu" title="{t}選択したユニットをポートフォリオから解除します{/t}"><i class="connect"></i></div>
							</div>
							
							<div class="wrapicon">
								<form method="POST" action="{$baseurl}/{$controllerName}/insertContents/id/{$selected->id}" name="insertContentsForm" id="insertContentsForm" enctype="multipart/form-data">
								<div class="add noMenu" title="{t}ユニットを追加します{/t}"><i class="add"></i></div>
								<input type="file" name="addbypc" id="addbypc" style="display:none;" />
								</form>
								<!--
								<div class="add miniMenu" title="{t}ユニットを追加します{/t}"><i class="add"></i></div>
								<div class="wrapdrop">
									<ul class="add droplist">
										<form method="POST" action="{$baseurl}/{$controllerName}/insertContents/id/{$selected->id}" name="insertContentsForm" id="insertContentsForm" enctype="multipart/form-data">
										<li class="mainMenu list_folder"><i class="folder"></i><a class="folder">{t}ファイル置場{/t}</a></li>
										<input type="file" name="addbypc" id="addbypc" style="display:none;" />
										</form>
									</ul>
								</div>
								-->
							</div>
							
							<span style="clear: both;"></span>
						</div>
						{else}
						<div class="requester">
						{if !empty($selected->t_profiles_input_name)}
							<img class="selectedImg" src="{$selected->t_profiles_input_name}" />
						{else}
							<img class="selectedImg noImg" id="studentImg" />
						{/if}
							<div>
							{if !empty($selected->name_jp)}
								<a id="memberDetail" class="memberDetail">{$selected->name_jp}</a>
							{else}
								<form method="POST" action="{$baseurl}/{$controllerName}/getsubjectportfolio/id/{$selected->id}" name="getMembersPortfolioForm" id="getMembersPortfolioForm" enctype="multipart/form-data">
									<select id="memberSelect" name="memberid">
										<option value="0" default>{t}すべて{/t}</option>
								{if count($class_members) > 0}
									{foreach from=$portfolio_members item=member name=member}
										<option value="{$member->m_member_id}">{$member->student_id_jp}　{$member->name_jp}</option>
									{/foreach}
								{/if}
									</select>
									<a id="memberDetail" class="memberDetail subject hidden">{t}詳細{/t}</a>
								</form>
							{/if}
							</div>
							<br class="clear" />
						</div>
						{/if}
						<form method="POST" action="{$baseurl}/{$controllerName}/getportfoliodetail" name="getPortfolioDetailForm" id="getPortfolioDetailForm" enctype="multipart/form-data">
							<input type="hidden" id="t_portfolio_id" name="t_portfolio_id" class="t_portfolio_id" />
						</form>
						
						<div class="wrapOuterLeft">
							<div class="wrapContentTop">
								<div class="wrapContentTopTable">
									<form method="POST" action="{$baseurl}/{$controllerName}/deleteportfolio" name="deletePortfolioForm" id="deletePortfolioForm" enctype="multipart/form-data">
									<div class="wrapTableHead tblPortfolio">
										<table class="contentsTbl">
											<thead>
												<tr>
													{if empty($common_flg)}<th class="w1 main"><input type="checkbox" id="contentCheckAll" /></th>{/if}
													<th class="w1 main hasOrder" data-id="portfolio_num">{t}No.{/t}</th>
													<th class="main hasOrder" data-id="portfolio_title">{t}ユニットタイトル{/t}</th>
													<th class="w8 purple hasOrder" data-id="portfolio_rubric">{t}ルーブリック{/t}</th>
													<th class="rate purple hasOrder" data-id="portfolio_self_rate">{t}自己評価{/t}</th>
													<th class="rate purple hasOrder" data-id="portfolio_mentor_rate">{t}メンター評価{/t}</th>
												</tr>
											</thead>
										</table>
									</div>
									
									<div class="wrapTableBody tblPortfolio">
										<table class="contentsTbl" id="contentsTbl">
											<thead id="contentsInnerHead" style="display:none;">
												<tr>
													{if empty($common_flg)}<th class="w1 main"></th>{/if}
													<th class="w1 main" id="portfolio_num">{t}No.{/t}</th>
													<th class="main" id="portfolio_title">{t}ユニットタイトル{/t}</th>
													<th class="w8 purple" id="portfolio_rubric">{t}ルーブリック{/t}</th>
													<th class="rate purple" id="portfolio_self_rate">{t}自己評価{/t}</th>
													<th class="rate purple" id="portfolio_mentor_rate">{t}メンター評価{/t}</th>
												</tr>
											</thead>
											<tbody id="contentsInner">
											</tbody>
										</table>
									</div>
									</form>
									{include file='tecfolio/shared/pager.tpl' pagerId="pager"}
								</div>
							</div>
							
							<div class="wrapContentBottom">
								<form method="POST" action="{$baseurl}/{$controllerName}/updateportfolio{if !empty($common_flg)}formentor{/if}" name="updatePortfolioForm" id="updatePortfolioForm" enctype="multipart/form-data">
								<input type="hidden" id="edit_portfolio_id" name="id" class="t_portfolio_id" />
								<div class="contentBottomLeft">
									<div class="edit noborder">
										<div class="loading hidden">
											<img src="/images/loading.gif" />
										</div>
										<div class="desc hidden"><i>{t}編集{/t}</i></div>
										<div class="editWrap hidden">
											<div class="editContent">
												<div class="editLeft">
													<div class="editRow title">
														<div class="editRowLeft">
															<div class="label">{t}◆ユニット{/t}</div>
														</div>
														<div class="editRowRight" id="wrap_edit_title">
															<div class="content" id="edit_title"></div>
															<span id="icon_edit_title" style="display:none;">　</span>
														</div>
													</div>
													<div class="editRow contents">
														<div class="editRowLeft">
															<div class="label">{t}◆コンテンツ{/t}<br /><input type="button" id="contentsButton" value="{t}追加{/t}" /></div>
														</div>
														<div class="editRowRight">
															<div class="content"  id="edit_contents">
															</div>
														</div>
													</div>
													<div class="editRow rubric">
														<div class="editRowLeft">
															<div class="label">{t}◆ルーブリック{/t}<br /><input type="button" id="rubricButton" value="{t}選択{/t}" /></div>
														</div>
														<div class="editRowRight">
															<div class="content" id="edit_rubric">{t}選択してください{/t}</div>
														</div>
													</div>
												</div>
												<div class="editRight">
													<div class="editRow self_rating">
														<div class="editRowLeft">
															<div class="label">{t}◆自己評価{/t}</div>
														</div>
														<div class="editRowRight">
															<div class="content" id="edit_self_rating">{t}未評価{/t}</div>
															<span id="edit_rate_stars" style="display:none;">　</span>
														</div>
													</div>
													<div class="editRow self_comment">
														<div class="editRowLeft">
															<div class="label">{t}コメント{/t}</div>
														</div>
														<div class="editRowRight">
															<div class="wrapComment">
																<div class="content" id="edit_self_comment">{t}未記入{/t}</div>
															</div>
														</div>
													</div>
													<div class="editRow mentor_rating">
														<div class="editRowLeft">
															<div class="label">{t}◆メンター評価{/t}</div>
														</div>
														<div class="editRowRight">
															<div class="content" id="edit_mentor_rating">{t}未評価{/t}</div>
															<span id="mentor_rate_stars" style="display:none;">　</span>
														</div>
													</div>
													<div class="editRow mentor_comment">
														<div class="editRowLeft">
															<div class="label">{t}コメント{/t}</div>
														</div>
														<div class="editRowRight">
															<div class="wrapComment">
																<div class="content" id="edit_mentor_comment">{t}未記入{/t}</div>
															</div>
														</div>
													</div>
												</div>
												<!--
												<div class="editRight editBottom">
													<div class="editRow showcase">
														<div class="editRowLeft">
															<div class="label">{if empty($common_flg)}{t}◆ショーケース{/t}{/if}</div>
														</div>
														<div class="editRowRight">
															<div class="content">
																{if empty($common_flg)}<input type="checkbox" name="showcase_flag" id="showcase_flag" value="1" />{t}使用する{/t}{/if}
															</div>
														</div>
													</div>
												</div>
												-->
											</div>
										</div>
									</div>
								</div>
								</form>
							</div>
						</div>
						<div class="wrapOuterRight" id="wrapOuterRight">
							<form method="POST" action="{$baseurl}/{$controllerName}/insertchatmentor" name="insertChatMentorForm" id="insertChatMentorForm" enctype="multipart/form-data">
							<input type="hidden" name="chat_mytheme_id" value="{$selected->id}">
							<input type="hidden" name="chat_mentor_id" id="chat_mentor_id">
							<input type="hidden" name="chat_tgt_id" id="chat_tgt_id">
							<div class="contentBottomRight" style="display:none;">
								<div class="mentor">
									<div class="desc"><i>{if !empty($common_flg)}{t}学習者と相談する{/t}{else}{t}メンターと相談する{/t}{/if}</i></div>
									<div class="mentorWrap">
										<div class="mentorContent">
											<div class="mentorTop{if !empty($common_flg)} hidden{/if}">
												<div class="person">
													<div class="selectedMentor">{t}メンター{/t}</div>
													<img class="selectedImg notYet" id="mentorImg" />
													<div class="selectedText notYet" id="mentorText">{t}未選択{/t}{t}(承認待ち){/t}</div>
												</div>
												<div class="search">
													<a class="submit orange" id="searchMentorButton">{t}検索{/t}</a>
												</div>
											</div>
											<div class="mentorBottom">
												<div class="mentorBottomTitle">
													<div class="label"><div class="contentLabel">{t}タイトル：{/t}</div></div>
													<div class="inputText">
														<div class="wrapText">
															<input type="text" name="chat_title" id="chat_title" />
														</div>
													</div>
												</div>
												<div class="mentorBottomContent">
													<div class="label"><div class="contentLabel">{t}本文：{/t}</div></div>
													<div class="inputText">
														<div class="wrapText">
															<textarea rows="3" name="chat_body" id="chat_body" ></textarea>
														</div>
													</div>
												</div>
											</div>
											<div class="wrapMentorButton">
												<a onclick="submitInsertChatMentor();" class="submit orange">{t}送信{/t}</a>
											</div>
											<div class="wrapChat mentor" id="wrapChatMentor">
											</div>
										</div>
									</div>
								</div>
							</div>
							</form>
						</div>
					</div>
				{elseif !empty($error)}
				<p class="empty">{t}存在しないか、アクセス許可のないIDです。{/t}</p>
				{else}
				<p class="empty">{t}左のメニューからテーマを選択してください。{/t}</p>
				{/if}
				</div>
			</article>
			
			<!-- 新規追加 -->
			<div id="insertPortfolioDialog" class="dialog large">
				<form method="POST" action="{$baseurl}/{$controllerName}/getcontents" name="getContentsForm" id="getContentsForm" enctype="multipart/form-data">
					<input type="hidden" name="id" id="dm_mytheme_id" />
					<input type="hidden" name="action_name" id="dm_action_name" value="portfolio" />
				</form>
				<form method="POST" action="{$baseurl}/{$controllerName}/insertportfolio" name="insertPortfolioForm" id="insertPortfolioForm" enctype="multipart/form-data">
				<input type="hidden" name="portfolio_mytheme_id" id="portfolio_mytheme_id" value="{$selected->id}" />
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}ファイル置場から追加{/t}</div>
				<div class="dialogBot">
					<p class="dialogBotFirst">
						{t}◆Step1：ユニットタイトルを入力してください{/t}
					</p>
					<input type="text" id="pftitle" name="title" class="long" />
				</div>
				<div class="dialogTop reversed">
					<p class="dialogTopFirst">
						{t}◆Step2：コンテンツを選択してください{/t}
					</p>
					<div class="dialogSelectContents">
						<div class="dialogLeft">
							<ul id="dialogMenu">
								<li id="dmMythemeInsert">
									<div class="dmTitle dmMytheme">{t}Myテーマ{/t}</div>
									{foreach from=$mythemes item=mytheme}
									<div class="dmList dmMythemeList dmInsertList" id="dm_{$mytheme->id}" data-id="{$mytheme->id}">{$mytheme->name}</div>
									{/foreach}
									<div class="dmTitle dmSubject">{t}授業科目{/t}</div>
									{foreach from=$subjects item=subject}
									<div class="dmList dmSubjectList dmInsertList" id="dm_{$subject->id}" data-id="{$subject->id}"><span class="head">{$subject->yogen}　</span>{$subject->class_subject}</div>
									{/foreach}
									<div class="dmTitle dmFacility">{t}学内施設{/t}</div>
									<div class="dmList dmFacilityList dmInsertList" id="dm_LABO_{$member->id}" data-id="LABO_{$member->id}">{t}ライティングラボ{/t}</div>
								</li>
							</ul>
						</div>
						<div class="dialogRight">
							<div id="wrapDialogTable" class="wrapDialogTable" style="display:none;">
								<div class="wrapTableHead">
									<table class="dialogContents dialogContentsHead">
										<thead>
											<tr>
												<th class="w1"><input type="checkbox" id="pfCheckAll" /></th>
												<th class="w1">{t}No.{/t}</th>
												<th class="w1">{t}類{/t}</th>
												<th>{t}コンテンツ{/t}</th>
											</tr>
										</thead>
									</table>
								</div>
								<div class="wrapTableBody">
									<table id="dialogContents" class="dialogContents dialogContentsBody">
										<tbody id="dialogContentsInner">
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
					<br class="clear" />
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitInsertPortfolio();" class="affirm">{t}追加する{/t}</a>
				</div>
				</form>
			</div>
			
			<!-- コンテンツのみ追加 -->
			<div id="addContentsToPortfolioDialog" class="dialog large">
				<form method="POST" action="{$baseurl}/{$controllerName}/getavailablecontents" name="getAvailableContentsForm" id="getAvailableContentsForm" enctype="multipart/form-data">
					<input type="hidden" name="id" id="dm_add_mytheme_id" />
					<input type="hidden" name="t_portfolio_id" class="t_portfolio_id" />
				</form>
				<form method="POST" action="{$baseurl}/{$controllerName}/addcontentstoportfolio" name="addContentsToPortfolioForm" id="addContentsToPortfolioForm" enctype="multipart/form-data">
				<input type="hidden" name="t_portfolio_id" class="t_portfolio_id" />
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}コンテンツを追加する{/t}</div>
				<div class="dialogTop">
					<p class="dialogTopFirst">
						{t}◆コンテンツを選択してください{/t}
					</p>
					<div class="dialogSelectContents dialogAddContents">
						<div class="dialogLeft">
							<ul id="dialogMenu">
								<li id="dmMythemeAdd">
									<div class="dmTitle dmMytheme">{t}Myテーマ{/t}</div>
									{foreach from=$mythemes item=mytheme}
									<div class="dmList dmMythemeList dmAddList" id="dm_add_{$mytheme->id}" data-id="{$mytheme->id}">{$mytheme->name}</div>
									{/foreach}
									<div class="dmTitle dmSubject">{t}授業科目{/t}</div>
									{foreach from=$subjects item=subject}
									<div class="dmList dmSubjectList dmAddList" id="dm_add_{$subject->id}" data-id="{$subject->id}"><span class="head">{$subject->yogen}　</span>{$subject->class_subject}</div>
									{/foreach}
									<div class="dmTitle dmFacility">{t}学内施設{/t}</div>
									<div class="dmList dmFacilityList dmAddList" id="dm_add_LABO_{$member->id}" data-id="LABO_{$member->id}">{t}ライティングラボ{/t}</div>
								</li>
							</ul>
						</div>
						<div class="dialogRight">
							<div id="wrapDialogAddTable" class="wrapDialogTable" style="display:block;">
								<div class="wrapTableHead">
									<table class="dialogContents dialogContentsHead">
										<thead>
											<tr>
												<th class="w1"><input type="checkbox" id="addPfCheckAll" /></th>
												<th class="w1">{t}No.{/t}</th>
												<th class="w1">{t}類{/t}</th>
												<th>{t}コンテンツ{/t}</th>
											</tr>
										</thead>
									</table>
								</div>
								<div class="wrapTableBody">
									<table class="dialogContents">
										<tbody id="dialogAddContentsInner">
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
					<br class="clear" />
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitAddContentsToPortfolio();" class="affirm">{t}追加する{/t}</a>
				</div>
				</form>
			</div>
			
			<div id="addContentsToPortfolioCompDialog" class="dialog">
				<div class="cmpsub">{t}コンテンツを追加しました{/t}</div>
			</div>
			
			<div id="addContentsCheckFailDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}コンテンツを追加する{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}コンテンツにチェックを入れてください{/t}
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="insertPortfolioTitleFailDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}ファイル置場から追加{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}タイトルを入力してください{/t}
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="insertPortfolioCompDialog" class="dialog">
				<div class="cmpsub">{t}ポートフォリオにユニットを追加しました{/t}</div>
			</div>
			
			<div id="deletePortfolioDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}ユニットの解除{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}選択したユニットをポートフォリオから解除します。ルーブリックとの関連/自己評価/メンター評価は解除されますが、コンテンツはファイル置場に残ります。よろしいですか？{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitDeletePortfolio();" class="affirm">{t}解除する{/t}</a>
				</div>
			</div>
			<div id="deletePortfolioCompDialog" class="dialog">
				<div class="cmpsub">{t}ユニットを解除しました{/t}</div>
			</div>
			
			<div id="deletePortfolioFailDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}ユニットの解除{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}解除するコンテンツにチェックを入れてください{/t}
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="updatePortfolioCompDialog" class="dialog">
				<div class="cmpsub">{t}変更内容を保存しました{/t}</div>
			</div>
			
			<div id="waitDialog" class="dialog">
				<div class="cmpsub">
					<img id="loading" src="/images/loading.gif" />
				</div>
			</div>
			
			<div id="updateTitleDialog" class="dialog">
				<form method="POST" action="{$baseurl}/{$controllerName}/updatetitle" name="updateTitleForm" id="updateTitleForm" enctype="multipart/form-data">
				<input type="hidden" name="id" class="t_portfolio_id" />
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}タイトルを変更する{/t}</div>
				<div class="dialogWrap">
					<div class="dialogWrapTop">
						<p class="dialogTitleLabel">
							{t}◆タイトルを入力してください{/t}
						</p>
						<input type="text" id="update_title" name="title" class="long" />
					</div>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitUpdateTitle();" class="affirm">{t}変更する{/t}</a>
				</div>
				</form>
			</div>
			<div id="updateTitleCompDialog" class="dialog">
				<div class="cmpsub">{t}タイトルを変更しました{/t}</div>
			</div>
			<div id="updateTitleNoValueDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}タイトルを変更する{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}タイトルを入力してください{/t}
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="deletePFCDialog" class="dialog medium">
				<form method="POST" action="{$baseurl}/{$controllerName}/deletepfc" name="deletePFCForm" id="deletePFCForm" enctype="multipart/form-data">
				<input type="hidden" name="id" id="pfc_portfolio_id" class="t_portfolio_id" />
				<input type="hidden" name="pfc_id" id="pfc_id" />
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}コンテンツを解除する{/t}</div>
				<div class="dialogWrap">
					<p class="dialogWrapTop">
						{t 1='<span id="pfc_name" class="bold"></span>'}コンテンツ「%1」をユニットへの引用から解除します。コンテンツはファイル置場に残ります。よろしいですか？{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitDeletePFC();" class="affirm">{t}解除する{/t}</a>
				</div>
				</form>
			</div>
			<div id="deletePFCCompDialog" class="dialog">
				<div class="cmpsub">{t}コンテンツを解除しました{/t}</div>
			</div>
			
			<div id="selectRubricDialog" class="dialog extra">
				<form method="POST" action="{$baseurl}/{$controllerName}/updateportfoliorubric" name="updatePortfolioRubricForm" id="updatePortfolioRubricForm" enctype="multipart/form-data">
				<input type="hidden" name="id" id="rubric_portfolio_id" class="t_portfolio_id" />
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}ルーブリックを選択する{/t}</div>
				<div class="dialogWrap">
					<p class="dialogWrapTop">
						<select id="selectrubric" name="selectrubric">
							<option value="0" selected="selected">{t}ルーブリックを選択{/t}</option>
						</select>
					</p>
					<div class="dialogMatrix" id="dialogMatrix" style="display:none; ">
						<table class="rubricMatrix" id="selectedMatrix">
						</table>
					</div>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitSelectRubric();" class="affirm">{t}選択する{/t}</a>
				</div>
				</form>
			</div>
			<div id="selectRubricConfirmDialog" class="dialog medium">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}ルーブリックを選択する{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}選択中のユニットにルーブリックを設定します。自己評価/メンター評価と各コメントが入力されている場合、全て削除されます。よろしいですか？{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitSelectRubricConfirm();" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			<div id="selectRubricCompDialog" class="dialog">
				<div class="cmpsub">ルーブリックを設定しました</div>
			</div>
			
			<div id="deleteSelectedRubricDialog" class="dialog medium">
				<form method="POST" action="{$baseurl}/{$controllerName}/deleteselectedrubric" name="deleteSelectedRubricForm" id="deleteSelectedRubricForm" enctype="multipart/form-data">
					<input type="hidden" name="id" class="t_portfolio_id" />
					<i class="sCloseButton cancel" onclick="cancel(this);"></i>
					<div class="sub">{t}ルーブリックの解除{/t}</div>
					<div class="mythemeWrap">
						<p class="mythemeDesc">
							{t}選択中のユニットからルーブリックを解除します。自己評価/メンター評価と各コメントが削除されます。これらは元に戻せません。よろしいですか？{/t}<br />
						</p>
					</div>
					<div class="buttonSet dubble">
						<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
						<a onclick="submitDeleteSelectedRubric();" class="affirm">{t}解除する{/t}</a>
					</div>
				</form>
			</div>
			<div id="deleteSelectedRubricCompDialog" class="dialog">
				<div class="cmpsub">{t}ルーブリックを解除しました{/t}</div>
			</div>
			
			<div id="selectRubricNoTargetDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}ルーブリックを選択する{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}ルーブリックを選択してください{/t}
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="ratingDialog" class="dialog extra{if empty($common_flg)} active{/if}">
				{if empty($common_flg)}
				<form method="POST" action="{$baseurl}/{$controllerName}/upsertselfrating" name="upsertSelfRatingForm" id="upsertSelfRatingForm" enctype="multipart/form-data">
				<input type="hidden" name="id" id="rating_portfolio_id" class="t_portfolio_id" />
				{/if}
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{if empty($common_flg)}{t}ルーブリックで評価する{/t}{else}{t}自己評価の詳細{/t}{/if}</div>
				<div class="dialogWrap">
					<div class="dialogWrapTop dialogMatrix">
						<table class="rubricMatrix{if empty($common_flg)} editable{/if}" id="rubricMatrix">
						</table>
					</div>
					<div class="dialogWrapBottom">
						{t 1='<div class="rubric_avg" id="rubric_avg"></div>'}平均：%1{/t}
					</div>
					{if empty($common_flg)}
					<div class="dialogInner">
						<div class="dialogTextArea dialogLeft">
							<div>{t}コメント{/t}</div>
						</div>
						<div class="dialogTextArea dialogRight">
							<textarea id="input_self_comment" name="input_self_comment" placeholder="{t}未記入{/t}" {if !empty($common_flg)}readonly="readonly"{/if}></textarea>
						</div>
					</div>
					{/if}
				</div>
				{if empty($common_flg)}
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="ratingAfter affirm hidden">{t}OK{/t}</a>
					<a onclick="cancel(this);" class="ratingBefore cancel">{t}キャンセル{/t}</a>
					<a onclick="submitRating();" class="ratingBefore affirm">{t}保存する{/t}</a>
				</div>
				</form>
				{else}
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
				{/if}
			</div>
			<div id="ratingCompDialog" class="dialog">
				<div class="cmpsub">{t}自己評価を設定しました{/t}</div>
			</div>
			
			<div id="ratingMentorDialog" class="dialog extra{if !empty($common_flg)} active{/if}">
				{if !empty($common_flg)}
				<form method="POST" action="{$baseurl}/{$controllerName}/upsertmentorrating" name="upsertMentorRatingForm" id="upsertMentorRatingForm" enctype="multipart/form-data">
				<input type="hidden" name="id" id="rating_portfolio_id" class="t_portfolio_id" />
				{/if}
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{if !empty($common_flg)}{t}ルーブリックで評価する{/t}{else}{t}メンター評価の詳細{/t}{/if}</div>
				<div class="dialogWrap">
					<div class="dialogWrapTop dialogMatrix">
						<table class="rubricMatrix{if !empty($common_flg)} editable{/if}" id="rubricMatrixMentor">
						</table>
					</div>
					<div class="dialogWrapBottom">
						{t 1='<div class="rubric_avg" id="rubric_avg_mentor"></div>'}平均：%1{/t}
					</div>
					{if !empty($common_flg)}
					<div class="dialogInner">
						<div class="dialogTextArea dialogLeft">
							<div>{t}コメント{/t}</div>
						</div>
						<div class="dialogTextArea dialogRight">
							<textarea id="input_mentor_comment" name="input_mentor_comment" placeholder="{t}未記入{/t}" {if empty($common_flg)}readonly="readonlyv"{/if}></textarea>
						</div>
					</div>
					{/if}
				</div>
				{if !empty($common_flg)}
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitRating();" class="affirm">{t}保存する{/t}</a>
				</div>
				</form>
				{else}
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
				{/if}
			</div>
			<div id="ratingMentorCompDialog" class="dialog">
				<div class="cmpsub">{t}メンター評価を設定しました{/t}</div>
			</div>
			
			<div id="searchMentorDialog" class="dialog large">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}メンターを探す{/t}</div>
				<div class="wrapDialog">
					<form method="POST" action="{$baseurl}/{$controllerName}/searchmentor" name="searchMentorForm" id="searchMentorForm" enctype="multipart/form-data">
					<div class="dialogTop">
						<p class="dialogTopFirst">
							{t}◆氏名を入力してください{/t}
						</p>
						<div class="wrapSearch">
							<div class="dialogLeft">
								<div class="title">{t}氏名{/t}</div>
							</div>
							<div class="dialogRight">
								<div class="content">
									<input type="text" id="mentor_search_input" name="mentor_search_input" />
								</div>
								<div class="button">
									<input type="button" id="mentor_search_submit" name="mentor_search_submit" value="{t}検索{/t}" onclick="submitSearchMentor();" />
								</div>
							</div>
						</div>
					</div>
					</form>
					<form method="POST" action="{$baseurl}/{$controllerName}/requestmentor" name="requestMentorForm" id="requestMentorForm" enctype="multipart/form-data">
					<input type="hidden" name="mytheme_id" id="mentor_search_mytheme_id" value="{$selected->id}" />
					<input type="hidden" name="mentor_num" id="mentor_num" value="1" />
					<div class="dialogMid">
						<p class="dialogMidFirst">
							{t}◆検索結果{/t}
						</p>
						<div class="dialogLeft resultSet">
							<div class="wrapResult">
								<ul class="mentorResult" id="mentorResult">
									<li class="notyet">{t}検索していません{/t}</li>
								</ul>
							</div>
						</div>
						<div class="dialogCenter resultSet">
							<img src="/images/right.png" class="mentorArrow" />
						</div>
						<div class="dialogRight resultSet">
							<div class="selectedHead">{t}選択中のユーザー{/t}</div>
							<img alt="{t}プロフィール画像{/t}" src="/images/userStudent.png" class="hidden" id="mentorImage" />
							<div id="mentorSelected"><div class="notyet">{t}選択されていません{/t}</div></div>
							<input type="hidden" name="mentor_selected_id" id="mentor_selected_id" />
							<input type="hidden" name="mentor_selected_name" id="mentor_selected_name" />
							<input type="hidden" name="mentor_selected_syzkcd" id="mentor_selected_syzkcd" />
						</div>
					</div>
					<br class="clear" />
					<div class="buttonSet dubble">
						<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
						<a onclick="submitRequestMentor();" class="affirm">{t}依頼する{/t}</a>
					</div>
					</form>
				</div>
			</div>
			
			<div id="searchMentorTooManyFailDialog" class="dialog">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}メンターを探す{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}条件に一致するユーザーが多すぎます。条件を変更して再度検索してください{/t}
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="searchMentorNoResultFailDialog" class="dialog">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}メンターを探す{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}条件に一致するユーザーが見つかりませんでした{/t}
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="searchMentorNoValueFailDialog" class="dialog">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}メンターを探す{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}メンターを依頼するユーザーを選択してください{/t}
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="insertChatMentorFailDialog" class="dialog">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}メンターと相談する{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}本文を入力してください{/t}
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="searchMentorCompDialog" class="dialog">
				<div class="cmpsub">{t}メンターを依頼しました{/t}</div>
			</div>
			
			<div id="insertChatMentorCompDialog" class="dialog">
				<div class="cmpsub">{t}メッセージを送信しました{/t}</div>
			</div>
			
			<div id="memberDetailDialog" class="dialog medium">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}メンバーの詳細{/t}</div>
				<div class="mythemeWrap">
					<div class="mythemeDesc">
						<div class="diag_img_outer">
							<div class="diag_img_inner">
								<img id="diag_input_name" class="diag_val" {if !empty($selected->t_profiles_input_name)}src="{$selected->t_profiles_input_name}"{else}src="/images/userStudent.png"{/if}>
							</div>
						</div>
						<table class="memberDetail">
							<thead>
							</thead>
							<tbody>
								<tr>
									<th>{t}学籍番号{/t}</th>
									<td id="diag_student_id_jp" class="diag_val">{if !empty($selected->m_members_student_id_jp)}{$selected->m_members_student_id_jp}{/if}</td>
								</tr>
								<tr>
									<th>{t}氏名{/t}</th>
									<td id="diag_name_jp" class="diag_val">{if !empty($selected->name_jp)}{$selected->name_jp}{/if}</td>
								</tr>
								<tr>
									<th>{t}言語{/t}</th>
									<td id="diag_languages" class="diag_val">{if !empty($selected->t_profiles_m_member_id)}日本語{/if}</td>
								</tr>
								<tr>
									<th>{t}メールアドレス 1{/t}</th>
									<td id="diag_email" class="diag_val">{if !empty($selected->m_members_email)}{$selected->m_members_email}{/if}</td>
								</tr>
								<tr>
									<th>{t}メールアドレス 2{/t}</th>
									<td id="diag_email2" class="diag_val">{if !empty($selected->t_profiles_email_2)}{$selected->t_profiles_email_2}{/if}</td>
								</tr>
								<tr>
									<th>{t}メールアドレス 3{/t}</th>
									<td id="diag_email3" class="diag_val">{if !empty($selected->t_profiles_email_3)}{$selected->t_profiles_email_3}{/if}</td>
								</tr>
								{if !empty($subject_flag)}
								<tr>
									<th>{t}学部{/t}</th>
									<td id="diag_syozoku1_szknam_c" class="diag_val"></td>
								</tr>
								<tr>
									<th>{t}学科{/t}</th>
									<td id="diag_syozoku2_szknam_c" class="diag_val"></td>
								</tr>
								{else}
								<tr>
									<th>{t}所属{/t}</th>
									<td id="diag_szkcd_c" class="diag_val">{if !empty($selected->syzkcd_c)}{$selected->syzkcd_c}{/if}</td>
								</tr>
								{/if}
								<tr>
									<th>{t}専攻/専修/コース{/t}</th>
									<td id="diag_speciality" class="diag_val">{if !empty($selected->t_profiles_speciality)}{$selected->t_profiles_speciality}{/if}</td>
								</tr>
								<tr>
									<th>{t}ゼミ{/t}</th>
									<td id="diag_seminar" class="diag_val">{if !empty($selected->t_profiles_seminar)}{$selected->t_profiles_seminar}{/if}</td>
								</tr>
								<tr>
									<th>{t}卒業した高校{/t}</th>
									<td id="diag_highschool" class="diag_val">{if !empty($selected->t_profiles_highschool)}{$selected->t_profiles_highschool}{/if}</td>
								</tr>
								<tr>
									<th>{t}誕生日{/t}</th>
									<td id="diag_birthday" class="diag_val">{if !empty($selected->t_profiles_birthday)}{$selected->t_profiles_birthday}{/if}</td>
								</tr>
								<tr>
									<th>{t}性別{/t}</th>
									<td id="diag_sex" class="diag_val">{if !empty($selected->t_profiles_sex)}{$selected->t_profiles_sex}{/if}</td>
								</tr>
								<tr>
									<th>{t}出身地{/t}</th>
									<td id="diag_birthplace" class="diag_val">{if !empty($selected->t_profiles_birthplace)}{$selected->t_profiles_birthplace}{/if}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">OK</a>
				</div>
			</div>
			
			
			{include file='tecfolio/shared/common_dialog.tpl'}
		</div>
	</div>
	{include file="../../common/foot_v2.php"}
	{if !empty($selected)}
	<script>
		function callInsertPortfolio(response)
		{
			$('#insertPortfolioCompDialog').bPopup();
			$('#insertPortfolioDialog').bPopup().close();
			
			drawPortfolio('{$selected->id}');
			
			// 20160202 ダイアログを初期化
			$('#pftitle').prop('value', '');					// タイトル
			$('#dm_mytheme_id').prop('value', '');				// コンテンツ取得元テーマ
			$('#wrapDialogTable').css('display', 'none');		// コンテンツ取得テーブル
			$('#dialogContentsInner').children().remove();		// 同上
			$('#dmMythemeInsert > .dmList').each(function(){	// コンテンツ取得元テーマメニュー
				$(this).removeClass('active');
			});
		}
		
		function callDeletePortfolio(response)
		{
			$('#deletePortfolioCompDialog').bPopup();
			$('#deletePortfolioDialog').bPopup().close();
			
			// 解除されたポートフォリオが選択状態であった場合、編集ペインを非表示にする
			var curId = $('#t_portfolio_id').val();
			for(var i in response['id'])
			{
				if(response['id'][i] == curId)
				{
					$('.contentBottomLeft .edit').addClass('noborder');
					$('.contentBottomLeft .edit .desc').addClass('hidden');
					$('.contentBottomLeft .edit .editWrap').addClass('hidden');
				}
			}
			
			drawPortfolio('{$selected->id}');
		}
		
		// ショーケース
		function callUpdatePortfolio(response)
		{
			$('#updatePortfolioCompDialog').bPopup();
			$('#waitDialog').bPopup().close();
			
			//drawPortfolio('{$selected->id}');
		}
		
		function callGetPortfolioDetail(response)
		{
			$('.contentBottomLeft .edit').addClass('noborder');
			$('.contentBottomLeft .edit .desc').addClass('hidden');
			$('.contentBottomLeft .edit .editWrap').addClass('hidden');
			$('.contentBottomLeft .edit .loading').removeClass('hidden');
			
			createEditPortfolio(response, {if empty($common_flg)}0{else}1{/if});
			
			$('.contentBottomLeft .edit').removeClass('noborder');
			$('.contentBottomLeft .edit .desc').removeClass('hidden');
			$('.contentBottomLeft .edit .editWrap').removeClass('hidden');
			$('.contentBottomLeft .edit .loading').addClass('hidden');
			//$('.contentBottomLeft .edit').hide();
			//$('.contentBottomLeft .edit').animate( { opacity: 'show',}, { duration: 500, easing: 'swing', } );
		}
		function callUpdatePortfolioRubric(response)
		{
			// メインテーブル再描画
			drawPortfolio('{$selected->id}');
			
			// ルーブリック編集ブロックを再描画
			submitGetPortfolioDetail(response['id']);
			
			$('#selectRubricCompDialog').bPopup();
			$('#selectRubricConfirmDialog').bPopup().close();
			$('#selectRubricDialog').bPopup().close();
		}
		function callUpdatePortfolioNewRubric(response)
		{
			// メインテーブル再描画
			drawPortfolio('{$selected->id}');
			
			// ルーブリック編集ブロックを再描画
			submitGetPortfolioDetail(response['id']);
			
			$('#selectRubricCompDialog').bPopup();
			$('#selectRubricDialog').bPopup().close();
		}
		function callUpsertRating(response)
		{
			// メインテーブル再描画
			drawPortfolio('{$selected->id}');
			
			// ルーブリック編集ブロックを再描画
			submitGetPortfolioDetail(response['id']);
			
			$('#ratingCompDialog').bPopup();
			$('#ratingDialog').bPopup().close();
		}
		
		function callUpsertMentorRating(response)
		{
			// メインテーブル再描画
			drawPortfolio('{$selected->id}');
			
			// ルーブリック編集ブロックを再描画
			submitGetPortfolioDetail(response['id']);
			
			$('#ratingMentorCompDialog').bPopup();
			$('#ratingMentorDialog').bPopup().close();
		}
		
		function callSearchMentor(response)
		{
			$('#mentorResult > li').each(function(){
				$(this).remove();
			});
			
			$('#mentorImage').addClass('hidden');
			$('#mentorSelected').html('<div class="notyet">{t}選択されていません{/t}</div>');
			$('#mentor_selected_id').val('');
			$('#mentor_selected_name').val('');
			$('#mentor_selected_syzkcd').val('');
			
			if(response['count'] >= 100)
			{
				$('#searchMentorTooManyFailDialog').bPopup();
				$('#mentorResult').html('<li class="notyet">{t}検索していません{/t}</li>');
				return;
			}
			else if(response['count'] == 0)
			{
				$('#searchMentorNoResultFailDialog').bPopup();
				$('#mentorResult').html('<li class="notyet">{t}検索していません{/t}</li>');
				return;
			}
			
			var result = document.getElementById('mentorResult');
			
			for(var i in response['members'])
			{
				var li = document.createElement('li');
				li.setAttribute('data-id', response['members'][i]['id']);
				li.setAttribute('data-name_jp', response['members'][i]['name_jp']);
				li.setAttribute('data-syzkcd_c', response['members'][i]['syzkcd_c']);
				li.setAttribute('data-input_name', response['members'][i]['input_name']);
				li.innerHTML = response['members'][i]['name_jp'] + '（' + response['members'][i]['syzkcd_c'] + ')';
				result.appendChild(li);
			}
			
			$('#mentorResult > li').each(function(){
				$(this).click(function(){
					$('#mentorImage').removeClass('hidden');
					$('#mentor_selected_id').prop('value', $(this).data('id'));
					$('#mentor_selected_name').prop('value', $(this).data('name_jp'));
					$('#mentor_selected_syzkcd').prop('value', $(this).data('syzkcd_c'));
					$('#mentorSelected').html('<div class="syzkcd_c">' + $(this).data('syzkcd_c') + '</div><div>' + $(this).data('name_jp') + '</div>');
					if($(this).data('input_name') != undefined)
					{
						$('#mentorImage').attr('src', $(this).data('input_name'));
					}
					else
					{
						$('#mentorImage').attr('src', '/images/userStudent.png');
					}
				});
			});
		}
		
		function callRequestMentor(response)
		{
			// 全体を再描画
			drawPortfolio('{$selected->id}');
			
			$('#searchMentorCompDialog').bPopup();
			$('#searchMentorDialog').bPopup().close();
		}
		
		function callInsertChatMentor(response)
		{
			// 全体を再描画
			drawPortfolio('{$selected->id}');
			
			$('#insertChatMentorCompDialog').bPopup();
		}
		
		// ルーブリック選択時とポートフォリオ詳細部分の描画で共用
		// 　ルーブリック選択時の下部テーブルを作成後、
		// 　それを詳細部分にクローンするかどうかだけ分岐する
		function callGetRubric(response, afterMentorRate)
		{
			// テーブル要素削除
			$('#selectedMatrix > *').each(function(){
				$(this).remove();
			});
			
			var res = response['matrix'];
			
			// 各要素の準備と固定項目の挿入
			var target	= document.getElementById('selectedMatrix');			// ルーブリック選択時
			
			var thead	= document.createElement('thead');
				var trh		= document.createElement('tr');
					var th		= document.createElement('th');
					th.innerHTML = "{t}評価の観点{/t}";
					trh.appendChild(th);
					
					var th		= document.createElement('th');
					th.innerHTML = "{t}評価の観点の説明{/t}";
					trh.appendChild(th);
			
			var tbody	= document.createElement('tbody');
			
			var max_rank	= 0;
			var max_v		= 0;
			
			// 前提として、縦・横の順に整列したデータを取得している
			for(var i = 0; i < res.length; i++)
			{
				if(res[i]['rank'] != undefined && res[i]['rank'] > max_rank)
					max_rank	= res[i]['rank'];
				
				if(res[i]['vertical'] != undefined && res[i]['vertical'] > max_v)
					max_v		= res[i]['vertical'];
				
				// 1行目
				if(res[i]['vertical'] == 0)
				{
					var th			= document.createElement('th');
					th.innerHTML	= res[i]['description'];
					th.setAttribute('class', 'rate');
					trh.appendChild(th);
				}
				else
				{
					// 1列目
					if(res[i]['horizontal'] == 0)
					{
						// 評価の観点
						var tr			= document.createElement('tr');
						
						var th			= document.createElement('th');
						th.innerHTML	= '(' + res[i]['vertical'] + ')<br />' + res[i]['description'];
						tr.appendChild(th);
					}
					else if(res[i]['horizontal'] == 1)
					{
						// 評価の観点の説明
						var td			= document.createElement('td');
						td.innerHTML	= res[i]['description'];
						tr.appendChild(td);
					}
					else
					{
						var td			= document.createElement('td');
						td.innerHTML	= res[i]['description'];
						// row=縦位置, col=横位置, rank=評価値
						td.setAttribute('class', 'rating row' + res[i]['vertical'] + ' col' + res[i]['horizontal'] + ' rank' + res[i]['rank']);
						td.setAttribute('data-num', res[i]['vertical']);
						td.setAttribute('data-val', res[i]['rank']);
						tr.appendChild(td);
					}
					
					if(res[i+1] == undefined || res[i+1]['horizontal'] == 0)
						tbody.appendChild(tr);
				}
			}
			
			thead.appendChild(trh);
			
			target.appendChild(thead);
			target.appendChild(tbody);
			
			// ポートフォリオ詳細部分の描画用に呼び出された場合
			if(response['portfolio'] != undefined)
			{
				// テーブル要素削除
				$('#rubricMatrix > *, #rubricMatrixMentor > *').each(function(){
					$(this).remove();
				});
				
				$('#selectedMatrix thead, #selectedMatrix tbody').clone(false).appendTo('#rubricMatrix');
				$('#selectedMatrix thead, #selectedMatrix tbody').clone(false).appendTo('#rubricMatrixMentor');
				
				{if empty($common_flg)}
					var tableId		= '#rubricMatrix';
					var dialogId	= '#ratingDialog';
					var flg 		= '0';
				{else}
					var tableId		= '#rubricMatrixMentor';
					var dialogId	= '#ratingMentorDialog';
					var flg 		= '1';
				{/if}
				
				// 平均値
				if($('#rubric_max_vals').get(0))
					$('#rubric_max_vals').remove();
				$(tableId).after('<div id="rubric_max_vals" data-v="' + max_v + '" data-rank="' + max_rank + '"></div>');
				
				// 評価値格納用のinput削除と生成
				$('.input_rating').each(function(){
					$(this).remove();
				});
				for(var n = max_v; n > 0; n--)
				{
					$(tableId).after('<input type="hidden" name="input_rating[]" id="input_rating' + n + '" class="input_rating" value="0" />');
				}
				
				// 各セルクリック時イベント
				// 20151215 メンター評価後は各セルを選択不可とする
				
				if(afterMentorRate == undefined)
				{
					$(dialogId + ' .dialogMatrix td.rating').each(function() {
						$(this).click(function(){
							var num = $(this).data('num');
							var val = $(this).data('val');
							var max = $('#rubric_max_vals').data('rank');
							
							// 1行全ての色をリセット
							$('.row' + num).each(function() {
								$(this).removeClass('active');
								$("#input_rating" + num).val('0');
							});
							
							// 背景色・内部値変更
							if(!$(this).hasClass('active'))
							{
								$(this).addClass('active');
								$("#input_rating" + num).val(val);
							}
							
							// 平均値の再算出
							var sum = 0;
							var cnt = 0;
							$(dialogId + ' .input_rating').each(function() {
								sum += Number($(this).val());
								cnt++;
							});
							
							$(dialogId + ' .rubric_avg').html(getStars(sum, cnt, max, flg));
						});
					});
				}
				
				// 既存の選択値をアクティブにする
				if(response['portfolio']['m_rubric_id'] != undefined)
				{
					$('#selectrubric').val(response['portfolio']['m_rubric_id']).trigger('change');
					$('#dialogMatrix').css('display', 'block');
				}
				else
				{
					$('#selectrubric').val('0');
					$('#dialogMatrix').css('display', 'none');
				}
			}
		}
		
		function clearThemeList()
		{
			$('.dmList').each(function(){
				$(this).remove();
			});
		}
		
		function createThemeList(array)
		{
			var targetInsert 	= document.getElementById('dmMythemeInsert');
			var targetAdd 		= document.getElementById('dmMythemeAdd');
			
			$(targetInsert).children('.dmSubject').remove();
			$(targetAdd).children('.dmSubject').remove();
			
			$(targetInsert).children('.dmFacility').remove();
			$(targetAdd).children('.dmFacility').remove();
			
			var arr_mytheme = array['mytheme'];
			var arr_subject = array['subject'];
			
			// Myテーマ
			for(var i in arr_mytheme)
			{
				var div = document.createElement('div');
				div.setAttribute('class', 'dmList dmMythemeList dmInsertList');
				div.setAttribute('id', 'dm_' + arr_mytheme[i]['id']);
				div.setAttribute('data-id', arr_mytheme[i]['id']);
				div.innerHTML = arr_mytheme[i]['name'];
				
				targetInsert.appendChild(div);
				
				var div = document.createElement('div');
				div.setAttribute('class', 'dmList dmMythemeList dmAddList');
				div.setAttribute('id', 'dm_add_' + arr_mytheme[i]['id']);
				div.setAttribute('data-id', arr_mytheme[i]['id']);
				div.innerHTML = arr_mytheme[i]['name'];
				
				targetAdd.appendChild(div);
			}
			
			// 授業科目
			var div = document.createElement('div');
			div.setAttribute('class', 'dmTitle dmSubject');
			div.innerHTML = "{t}授業科目{/t}";
			
			targetInsert.appendChild(div);
			
			var div = document.createElement('div');
			div.setAttribute('class', 'dmTitle dmSubject');
			div.innerHTML = "{t}授業科目{/t}";
			
			targetAdd.appendChild(div);
			
			for(var i in arr_subject)
			{
				var div = document.createElement('div');
				div.setAttribute('class', 'dmList dmSubjectList dmInsertList');
				div.setAttribute('id', 'dm_' + arr_subject[i]['id']);
				div.setAttribute('data-id', arr_subject[i]['id']);
				div.innerHTML = '<span class="head">' + arr_subject[i]['yogen'] + '　</span>' + arr_subject[i]['class_subject'];
				
				targetInsert.appendChild(div);
				
				var div = document.createElement('div');
				div.setAttribute('class', 'dmList dmSubjectList dmAddList');
				div.setAttribute('id', 'dm_add_' + arr_subject[i]['id']);
				div.setAttribute('data-id', arr_subject[i]['id']);
				div.innerHTML = '<span class="head">' + arr_subject[i]['yogen'] + '　</span>' + arr_subject[i]['class_subject'];
				targetAdd.appendChild(div);
			}
			
			// 学内施設
			var div = document.createElement('div');
			div.setAttribute('class', 'dmTitle dmFacility');
			div.innerHTML = "{t}学内施設{/t}";
			
			targetInsert.appendChild(div);
			
			var div = document.createElement('div');
			div.setAttribute('class', 'dmList dmFacilityList dmInsertList');
			div.setAttribute('id', 'dm_LABO_{$member->id}');
			div.setAttribute('data-id', 'LABO_{$member->id}');
			div.innerHTML = "{t}ライティングラボ{/t}";
			
			targetInsert.appendChild(div);
			
			
			var div = document.createElement('div');
			div.setAttribute('class', 'dmTitle dmFacility');
			div.innerHTML = "{t}学内施設{/t}";
			
			targetAdd.appendChild(div);
			
			var div = document.createElement('div');
			div.setAttribute('class', 'dmList dmFacilityList dmAddList');
			div.setAttribute('id', 'dm_add_LABO_{$member->id}');
			div.setAttribute('data-id', 'LABO_{$member->id}');
			div.innerHTML = "{t}ライティングラボ{/t}";
			
			targetAdd.appendChild(div);
		}

		// Myテーマを引用する部分の再描画
		function drawThemeList(response)
		{
			clearThemeList();
			
			// Myテーマ(など)の描画
			createThemeList(response);
			
			setListEvent();
		}
		
		function setListEvent()
		{
			// ファイル置場から追加
			$(".dmInsertList").each(function() {
				$(this).off('click');
				$(this).on('click', function() {
					$(".dmInsertList").each(function() {
						$(this).removeClass('active');
					});
					
					$(this).addClass('active');
					$('#wrapDialogTable').css('display', 'none');
					$("#pfCheckAll").prop('checked', false);
					$("#dm_mytheme_id").val($(this).data('id'));
					submitGetContents();
					$('#wrapDialogTable').css('display', 'table');
				});
			});
			
			// コンテンツ追加
			$(".dmAddList").each(function() {
				$(this).off('click');
				$(this).on('click', function() {
					
					// コンテンツが一つでも存在している場合
					if( $('#edit_contents').children().length > 0 )
					{
						$(".dmAddList").each(function() {
							$(this).removeClass('active');
							$(this).addClass('hidden');
						});
						// タイトル部分(Myテーマ/授業科目/学内施設)で選択されていないものは隠す
						$("#dmMythemeAdd > .dmTitle").each(function() {
							$(this).addClass('hidden');
						});
						
						if($(this).hasClass('dmMythemeList'))
							$('#dmMythemeAdd > .dmMytheme').removeClass('hidden');
						else if($(this).hasClass('dmSubjectList'))
							$('#dmMythemeAdd > .dmSubject').removeClass('hidden');
						else if($(this).hasClass('dmFacilityList'))
							$('#dmMythemeAdd > .dmFacility').removeClass('hidden');
					}
					else
					{
						$(".dmAddList").each(function() {
							$(this).removeClass('active');
						});
					}
					
					$(this).addClass('active');
					$(this).removeClass('hidden');
					$("#addPfCheckAll").prop('checked', false);
					$("#dm_add_mytheme_id").val($(this).data('id'));
					submitGetAvailableContents();
				});
			});
		}
		
		$(function(){
			{if !empty($subject_flg)}
			
			$('#wrapOuterRight').addClass('hidden');
			
			$('#getMembersPortfolioForm').submit(function(event) {
				ajaxSubmit(this, event, function(response){
					var tmp = $('#t_portfolio_id').val();
					var flg = 0;
					
					proceedDrawPortfio(response);
					
					if($('#memberSelect').val() !== '0')
						$('#memberDetail').removeClass('hidden');
					else
						$('#memberDetail').addClass('hidden');
					
					var prof = response['profile'];
					
					// メンバー詳細ダイアログ上の値を設定
					// 画像
					if(prof['input_name'] != undefined)
					{
						$('#diag_input_name').attr('src', prof['input_name']);
						$('#studentImg').removeClass('noImg');
						$('#studentImg').attr('src', prof['input_name']);
					}
					else
					{
						$('#diag_input_name').attr('src', '/images/userStudent.png');
						$('#studentImg').addClass('noImg');
						$('#studentImg').removeAttr('src');
					}
						
					// 画像以外
					$('#diag_student_id_jp').html(prof['m_members_student_id_jp']);
					$('#diag_name_jp').html(prof['m_members_name_jp']);
					if(prof['languages'] != undefined)
						$('#diag_languages').html('日本語');
					else
						$('#diag_languages').html('');
					$('#diag_email').html(prof['m_members_email']);
					$('#diag_email2').html(prof['email_2']);
					$('#diag_email3').html(prof['email_3']);
					$('#diag_syozoku1_szknam_c').html(prof['syozoku1_szknam_c']);
					$('#diag_syozoku2_szknam_c').html(prof['syozoku2_szknam_c']);
					$('#diag_speciality').html(prof['speciality']);
					$('#diag_seminar').html(prof['seminar']);
					$('#diag_highschool').html(prof['highschool']);
					$('#diag_birthday').html(prof['birthday']);
					$('#diag_sex').html(prof['sex']);
					$('#diag_birthplace').html(prof['birthplace']);
					
					
					// tablesorterの初期化
					$('.hasOrder, #contentsInnerHead th').each(function(){
						$(this).removeClass('headerSortUp');
						$(this).removeClass('headerSortDown');
					});
					
					// mentorペインの準備
					if(Object.keys(response['mentors']).length > 0)
					{
						$('#chat_tgt_id').val($('#memberSelect').val());
						$('#wrapOuterRight').removeClass('hidden');
					}
					else
					{
						$('#chat_tgt_id').val('');
						$('#wrapOuterRight').addClass('hidden');
					}
					
					// ページ下部に表示しているポートフォリオが含まれないメンバーに切り替わった場合
					if(tmp != undefined && tmp != '')
					{
						for(var i in response['portfolio'])
						{
							// responseのidと保存しているidが一致した
							if(tmp == response['portfolio'][i]['id'])
							{
								flg = 1;
								break;
							}
						}
						
						if(!flg)
						{
							$('.contentBottomLeft .edit').addClass('noborder');
							$('.contentBottomLeft .edit .desc').addClass('hidden');
							$('.contentBottomLeft .edit .editWrap').addClass('hidden');
							
							$('#t_portfolio_id').val('')
						}
					}
				});
			});
			
			// メンバー選択時	※教員の授業科目選択時のみ
			$('#memberSelect').on('change',function(){
				$('#getMembersPortfolioForm').submit();
			});
			
			{/if}
			
			$('#memberDetail').click(function(){
				$('#memberDetailDialog').bPopup();
			});
			
			drawPortfolio('{$selected->id}');
			$('.contentBottomRight').css('display', 'block');
			
			// タイトル追加ボタン押下時
			$("#activeContents .add").click(function() {
				$('#insertPortfolioDialog').bPopup();
			});
			
			setListEvent();
			
			// メイン：チェックボックスによる全選択
			$('#contentCheckAll').change(function() {
				var v 	= $(this).prop('checked');
				$('#contentsInner .contentCheck').each(function() {
					$(this).prop('checked', v);
				});
			});
			
			// ポートフォリオ追加ダイアログ：チェックボックスによる全選択
			$("#pfCheckAll").change(function() {
				var v 	= $(this).prop('checked');
				$("#dialogContentsInner .contentCheck").each(function() {
					$(this).prop('checked', v);
				});
			});
			
			// コンテンツ追加ダイアログ：チェックボックスによる全選択
			$("#addPfCheckAll").change(function() {
				var v 	= $(this).prop('checked');
				$("#dialogAddContentsInner .contentCheck").each(function() {
					$(this).prop('checked', v);
				});
			});
			
			// 解除ボタン押下時
			$('#activeContents .connect').click(function() {
				var flg = 0;
				$('#contentsInner .contentCheck').each(function(){
					if($(this).prop('checked')){
						flg = 1;
						return true;
					}
				});
				
				if(flg)
					$('#deletePortfolioDialog').bPopup();
				else
					$('#deletePortfolioFailDialog').bPopup();
			});
			
			// ルーブリック選択セレクトボックス変更時
			$('#selectrubric').change(function() {
				if($(this).val() != '0')
				{
					ajaxSubmitUrl(baseUrl + '/getrubricmatrix/id/' + $(this).val(), callGetRubric);
					$('#dialogMatrix').css('display', 'block');
				}
				else
				{
					$('#dialogMatrix').css('display', 'none');
				}
			});
			
			// メンター検索ボタン押下時
			$('#searchMentorButton').click(function() {
				$('#searchMentorDialog').bPopup();
			});
			
			// ショーケースチェックボックス変更時（即保存）
			$('#showcase_flag').change(function() {
				$('#waitDialog').bPopup({
						modalClose: false
				});
				
				setTimeout(function(){
					$('#updatePortfolioForm').submit();
				},100);
			});
			
			$('#insertPortfolioForm').submit(function(event) {
				ajaxSubmit(this, event, callInsertPortfolio);
			});
			$('#deletePortfolioForm').submit(function(event) {
				ajaxSubmit(this, event, callDeletePortfolio);
			});
			$('#updatePortfolioForm').submit(function(event) {
				ajaxSubmitEx(this, event, true, function(){}, callUpdatePortfolio);
			});
			$('#getPortfolioDetailForm').submit(function(event) {
				ajaxSubmit(this, event, callGetPortfolioDetail, undefined, true);
			});
			$('#addContentsToPortfolioForm').submit(function(event) {
				ajaxSubmit(this, event, function(response){
					// ルーブリック編集ブロックを再描画
					submitGetPortfolioDetail(response['id']);
			
					$('#addContentsToPortfolioCompDialog').bPopup();
					$('#addContentsToPortfolioDialog').bPopup().close();
				});
			});
			$('#deletePFCForm').submit(function(event) {
				ajaxSubmit(this, event, function(response){
					// ルーブリック編集ブロックを再描画
					submitGetPortfolioDetail(response['id']);
			
					$('#deletePFCCompDialog').bPopup();
					$('#deletePFCDialog').bPopup().close();
				});
			});
			$('#updateTitleForm').submit(function(event) {
				ajaxSubmit(this, event, function(response){
					// メインペイン再描画
					drawPortfolio('{$selected->id}');
					// ルーブリック編集ブロックを再描画
					submitGetPortfolioDetail(response['id']);
					
					$('#updateTitleCompDialog').bPopup();
					$('#updateTitleDialog').bPopup().close();
				});
			});
			$('#updatePortfolioRubricForm').submit(function(event) {
				// 20160202 ルーブリック選択済みの場合、確認ダイアログを閉じるため分岐
				if(!$('#edit_rubric').hasClass('noRubric'))
					ajaxSubmit(this, event, callUpdatePortfolioRubric);
				else
					ajaxSubmit(this, event, callUpdatePortfolioNewRubric);
			});
			$('#deleteSelectedRubricForm').submit(function(event) {
				ajaxSubmit(this, event, function(response){
					// メインペイン再描画
					drawPortfolio('{$selected->id}');
					// ルーブリック編集ブロックを再描画
					submitGetPortfolioDetail(response['id']);
					
					$('#deleteSelectedRubricCompDialog').bPopup();
					$('#deleteSelectedRubricDialog').bPopup().close();
				});
			});
			$('#upsertSelfRatingForm').submit(function(event) {
				ajaxSubmit(this, event, callUpsertRating);
			});
			$('#upsertMentorRatingForm').submit(function(event) {
				ajaxSubmit(this, event, callUpsertMentorRating);
			});
			
			$('#searchMentorForm').submit(function(event) {
				ajaxSubmit(this, event, callSearchMentor);
			});
			$('#requestMentorForm').submit(function(event) {
				ajaxSubmit(this, event, callRequestMentor);
			});
			
			$('#insertChatMentorForm').submit(function(event) {
				ajaxSubmit(this, event, callInsertChatMentor);
			});
		});
	</script>
	{/if}
</div>
</body>
</html>