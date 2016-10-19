<!doctype html>
<html lang="ja">
<head>
<!--
	{t}%1さんの相談一覧{/t}
	{t}予定{/t}
	{t}履歴{/t}
	{t}%1の相談一覧{/t}
	{t}%1さんの担当一覧{/t}
	{t}すべての相談一覧{/t}
	{t}未確定{/t}
	{t}担当者情報が更新されました。ページの更新を行ってください{/t}
-->
{include file='staff/header.tpl'}
<script src="/js/base.reserve.js" type="text/javascript"></script>
<script src="/js/jquery.repAttach.js" type="text/javascript"></script>
<script>

	window.onload = function()
	{
		// 初期値設定
		{if !empty($reserve)}
			var shift_code	= String.fromCharCode(65 + ({$shift->dayno}-1));
			var shift_string = toHM('{$shift->m_timetables_starttime}') + '-' + toHM('{$shift->m_timetables_endtime}');
			var dates		='{$vDate->dateFormat($reserve->reservationdate, 'Y/m/d(wj)', false, true, true)}';
			var date_string = dates + ' ' + shift_code + ' ' + shift_string;

			document.getElementById('v-basic-date').appendChild(document.createTextNode(date_string));
		{/if}

		createConsulList('{$baseurl}/{$controllerName}', {if empty($reserveid)}'0'{else}'{$reserveid}'{/if}, {if empty($page)}1{else}{$page}{/if}, {if empty($reserver)}'0'{else}'{$reserver}'{/if}, {if empty($subjectid)}'0'{else}'{$subjectid}'{/if}, {if empty($chargeid)}'0'{else}'{$chargeid}'{/if});
	}
	
	function submitTmp()
	{
		$('#tmpsendflg').attr('value', '1');
		$('#newadvice').submit();
	}

	function submitData()
	{
		$('#newadvice').submit();
	}
	
	function submitComment()
	{
		$('#newcomment').submit();
	}

</script>
</head>

<body class="staff">
	{include file='staff/menu.tpl'}
		{if !empty($reserve)}
		<div id="main">
			<article class="advice">
				<h1>{t}相談予定{/t}</h1>
				<section>
					<h1>{t}相談内容{/t}</h1>
					<ul class="formSet inactive">
						<li>
							<span class="label">{t}学籍番号・氏名{/t}</span>
							<div class="bezels"><a href="{$baseurl}/{$controllerName}/{$actionName}/reserveid/{$reserve->id}/reserver/{$reserve->m_member_id_reserver}" title="{t 1={$reserve->name_jp}}%1さんの相談一覧を表示{/t}">{$reserve->student_id} {$reserve->name_jp}</a>　</div>

						</li>
						<li>
							<span class="label">{t}相談日時{/t}</span>
							<div class="bezels"><time id="v-basic-date"></time></div>
						</li>
						<li>
							<span class="label">{t}相談場所{/t}</span>
							<div class="bezels">{$reserve->m_places_consul_place}</div>
						</li>
						<li>
							<span class="label">{t}文書の種類{/t}</span>
							<div class="bezels">{$reserve->m_dockinds_document_category}</div>
						</li>
					</ul>
					<ul class="formSet inactive">
						<li>
							<span class="label">{t}提出日{/t}</span>
							<div class="bezels"><time>{if ($vDate->dateFormat($reserve->submitdate, 'Y/m/d(wj)')) == null }　{elseif ($vDate->dateFormat($reserve->submitdate, 'Y/m/d(wj)')) == '1970/01/01(木)'}　{elseif ($vDate->dateFormat($reserve->submitdate, 'Y/m/d(wj)')) != '1980/01/01(火)'}{$vDate->dateFormat($reserve->submitdate, 'Y/m/d(wj)', false, true)}{else}　{/if}</time></div>
						</li>
						<li>
							<span class="label">{t}進行状況{/t}</span>
							<div class="bezels">{if $reserve->progress > 0}{$progresssal[$reserve->progress]}{else}　{/if}</div>
						</li>
						<li>
							<span class="label">{t}授業科目{/t}</span>
							<div class="bezels">{if empty($reserve->jwaricd)}　{else}<a href="{$baseurl}/{$controllerName}/advice/reserveid/{$reserve->id}/subjectid/{$reserve->jwaricd}" title="{t 1={$reserve->class_subject}}%1での相談一覧を表示{/t}">{$reserve->class_subject}</a>{/if}</div>
						</li>
						<li>
							<span class="label">{t}添付ファイル{/t}</span>
							<div class="bezels fileup">
								<ul class="MultiFile-existing">
									{if !empty($reservefiles) && count($reservefiles) > 0}
										{foreach from=$reservefiles item=reservefile name=reservefiles}
											<li class="MultiFile-label" style="margin: 0;"><i class="file"></i><a href="{$baseurl}/{$controllerName}/download/id/{$reservefile['t_files_id']}" class="MultiFile-title">{$vhHtmlOut->escape($reservefile['t_files_name'])}</a></li>
										{/foreach}
									{/if}
									{if {$smarty.foreach.reservefiles.iteration} == 0}
									　
									{/if}
								</ul>
							</div>
						</li>
						<li>
							<span class="label">{t}相談したいこと{/t}</span>
							<div id="comment" class="bezels"><p>{if empty($reserve->question)}　{/if}{$vhHtmlOut->escape($reserve->question)}</p></div>
						</li>
					</ul>
				</section>

				{* 担当スタッフ未決定、もしくは決定済みだが自身ではない *}
				<div id="advice-reserve">
					{if empty($leadings->submit_flag) && empty($charge)}
						<section>
							<h1>{t}指導内容{/t}</h1>
							<ul class="formSet">
								<li>
									<label for="item1">{t}担当スタッフ{/t}</label>
									<div class="bezels rep" id="repAttach">
										{* 指導が存在しない場合 *}
										{if empty($leadings)}
											{* 自分のシフト外である場合は 未決定 の表示のみ *}
											<span class="selected"><a href="#" id="repName" class="inactive">{t}未決定{/t}</a></span>
											{* 自分のシフト内である場合 *}
											{if !empty($staffshift)}
											<div class="note">{t}※担当スタッフの設定は確認表示なしで保存されます{/t}</div>
											<div class="control">
												<button class="rep"><i></i>{t}担当する{/t}</button>
											</div>
											{/if}
										{* 指導が存在する場合 *}
										{else if !empty($leadings)}
											<span class="selected">
												<a href="{$baseurl}/{$controllerName}/{$actionName}/reserveid/{$reserve->id}/chargeid/{$leadings->m_members_id}" id="repName">
													{$leadings->m_members_name_jp}
												</a>
											</span>
											{* 相談時間開始前である場合 *}
											{if $reservetype == 0}
											<div class="note">{t}※担当スタッフの設定は確認表示なしで保存されます{/t}</div>
											<div class="control">
												<button class="rep release"><i></i>{t}担当解除{/t}</button>
											</div>
											{/if}
										{/if}
									</div>
								</li>
							</ul>
							<div class="hideRep" {if empty($leadings)}style="display: none;"{/if}>
							<form method="POST" action="{$baseurl}/{$controllerName}/newadvice" name="newadvice" id="newadvice" enctype="multipart/form-data">
							<input type="hidden" name="reserveid" value="{$reserve->id}">
								<ul class="formSet">
									<li>
										<label for="cancel_flag">{t}ドタキャンフラグ{/t}</label>
										{if !empty($cancel_flag)}
											{html_checkboxes name='cancel_flag' options=$cancel_flag selected=$canceled}
										{else}
											<input type="checkbox" name="cancel_flag[]" id="cancel_flag" value="canceled" />
										{/if}
									</li>
									<li>
										<label for="item1">{t}相談内容{/t}</label>
										<textarea name="item1" id="item1" cols="42" rows="5">{if !empty($leadings)}{$leadings->counsel}{/if}</textarea>
									</li>
									<li>
										<label for="item2">{t}指導内容{/t}</label>
										<textarea name="item2" id="item2" cols="42" rows="5">{if !empty($leadings)}{$leadings->teaching}{/if}</textarea>
									</li>
									<li>
										<label for="item3">{t}所感{/t}</label>
										<textarea name="item3" id="item3" cols="42" rows="5">{if !empty($leadings)}{$leadings->remark}{/if}</textarea>
									</li>
									<li>
										<label for="item4">{t}備考{/t}</label>
										<textarea name="item4" id="item4" cols="42" rows="3">{if !empty($leadings)}{$leadings->summary}{/if}</textarea>
									</li>
								</ul>
								<div id="pageControl" style="margin-left: 65px;">
									<div class="buttonSet dubble">
										<input type="hidden" value="0" name="tmpsendflg" id="tmpsendflg" />
										<a class="delete" onclick="submitTmp();" style="width: 150px; padding: 10px 20px; ">{t}一時保存する{/t}</a>
										<a class="finish" style="width: 150px; padding: 10px 20px; ">{t}確定する{/t}</a>
									</div>
									<div id="finishDialog" class="dialog">
										<i class="closeButton cancel"></i>
										<div class="sub">{t}指導内容を確定します。よろしいですか？{/t}</div>
										<div class="buttonSet dubble">
											<a href="#" onclick="submitData();" class="affirm">{t}OK{/t}</a>
											<a href="#" class="cancel">{t}キャンセル{/t}</a>
										</div>
									</div>
									<div id="tmpDialog" class="dialog">
										<div class="cmpsub">{t}指導内容を保存しました。次回はこの続きからの入力が可能になります。{/t}</div>
										<div class="buttonSet single">
										</div>
									</div>
									<div id="regDialog" class="dialog">
										<div class="regsub">{t}指導内容を確定しました。右の一覧で指導履歴として確認できます。{/t}</div>
										<div class="buttonSet single">
										</div>
									</div>
								</div>
							</form>
							{if !empty($staffshift) && $reservetype != 0 && empty($leadings->leading_comment)}
							<form method="POST" action="{$baseurl}/{$controllerName}/newcomment" name="newcomment" id="newcomment" enctype="multipart/form-data">
							<input type="hidden" name="reserveid" value="{$reserve->id}">
								<div id="comments">
									<div class="staff">
										<figure><span class="photo"><img src="/images/userStaff.png" height="38" width="38" alt=""></span></figure>
										<div class="content">
											<div class="sub">
												<div class="title">{t}相談者へのコメント{/t}</div>
											</div>
											<textarea name="comment" id="comment" cols="42" rows="5">{if !empty($leadings)}{$leadings->leading_comment}{/if}</textarea>
											<div id="pageControlSub">
												<div class="buttonSet">
													<a class="finish" style="width: 150px; padding: 10px 20px;">{t}送信する{/t}</a>
												</div>
												<div id="finishDialogSub" class="dialog">
													<i class="closeButton cancel"></i>
													<div class="sub">{t}コメントを送信します。よろしいですか？{/t}</div>
													<div class="buttonSet dubble">
														<a href="#" onclick="submitComment();" class="affirm">{t}OK{/t}</a>
														<a href="#" class="cancel">{t}キャンセル{/t}</a>
													</div>
												</div>
												<div id="compDialog" class="dialog">
													<div class="cmpsub">{t}コメントを送信しました。右の一覧で指導履歴として確認できます。{/t}</div>
													<div class="buttonSet single">
														<!-- <a href="/staff/advice-by-student-sent.html" class="affirm" id="complocation">OK</a> -->
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</form>
							{/if}
							</div>
						</section>
					{else if !empty($charge)}
						<section>
						<h1>{t}指導内容{/t}</h1>
						<ul class="formSet">
							<li>
								<label>{t}担当スタッフ{/t}</label>
								<div class="bezels">
									<span class="selected"><a href="{$baseurl}/{$controllerName}/advice/reserveid/{$reserve->id}/chargeid/{$charge->m_members_id}">{$charge->m_members_name_jp}</a></span>
								</div>
							</li>
						</ul>
						<ul class="formSet">
							{if $charge->cancel_flag == 1}
							<li>
								<label for="item1">{t}ドタキャン{/t}</label>
								<div class="bezels"><p>{t}時間通りに来ませんでした{/t}</p></div>
							</li>
							{/if}
							<li>
								<label for="item1">{t}相談内容{/t}</a></label>
								<div class="bezels" id="comment"><p>{$charge->counsel}　</p></div>
							</li>
							<li>
								<label for="item2">{t}指導内容{/t}</label>
								<div class="bezels" id="comment"><p>{$charge->teaching}　</p></div>
							</li>
							<li>
								<label for="item3">{t}所感{/t}</label>
								<div class="bezels" id="comment"><p>{$charge->remark}　</p></div>
							</li>
							<li>
								<label for="item4">{t}備考{/t}</label>
								<div class="bezels" id="comment"><p>{$charge->summary}　</p></div>
							</li>
						</ul>

						</section>
					{else}
						<section>
						<h1>{t}指導内容{/t}</h1>
						<ul class="formSet">
							<li>
								<label>{t}担当スタッフ{/t}</label>
								<div class="bezels">
									<span class="selected"><a href="{$baseurl}/{$controllerName}/advice/reserveid/{$reserve->id}/chargeid/{$leadings->m_members_id}">{$leadings->m_members_name_jp}</a></span>
								</div>
							</li>
						</ul>
						<ul class="formSet">
							{if $leadings->cancel_flag == 1}
							<li>
								<label for="item1">{t}ドタキャン{/t}</label>
								<div class="bezels"><p>{t}時間通りに来ませんでした{/t}</p></div>
							</li>
							{/if}
							<li>
								<label for="item1">{t}相談内容{/t}</a></label>
								<div class="bezels" id="comment"><p>{$leadings->counsel}　</p></div>
							</li>
							<li>
								<label for="item2">{t}指導内容{/t}</label>
								<div class="bezels" id="comment"><p>{$leadings->teaching}　</p></div>
							</li>
							<li>
								<label for="item3">{t}所感{/t}</label>
								<div class="bezels" id="comment"><p>{$leadings->remark}　</p></div>
							</li>
							<li>
								<label for="item4">{t}備考{/t}</label>
								<div class="bezels" id="comment"><p>{$leadings->summary}　</p></div>
							</li>
						</ul>

						</section>
					{/if}
							{if !empty($leadings) && $reservetype != 0 && empty($leadings->leading_comment)}
							<section>
							<form method="POST" action="{$baseurl}/{$controllerName}/newcomment" name="newcomment" id="newcomment" enctype="multipart/form-data">
							<input type="hidden" name="reserveid" value="{$reserve->id}">
								<div id="comments">
									<div class="staff">
										<figure><span class="photo"><img src="/images/userStaff.png" height="38" width="38" alt=""></span></figure>
										<div class="content">
											<div class="sub">
												<div class="title">{t}相談者へのコメント{/t}</div>
											</div>
											<textarea name="comment" id="comment" cols="42" rows="5">{if !empty($leadings)}{$leadings->leading_comment}{/if}</textarea>
											<div id="pageControlSub">
												<div class="buttonSet">
													<a class="finish" style="width: 150px; padding: 10px 20px;">{t}送信する{/t}</a>
												</div>
												<div id="finishDialogSub" class="dialog">
													<i class="closeButton cancel"></i>
													<div class="sub">{t}送信しますか？{/t}</div>
													<div class="buttonSet dubble">
														<a href="#" onclick="submitComment();" class="affirm">{t}OK{/t}</a>
														<a href="#" class="cancel">{t}キャンセル{/t}</a>
													</div>
												</div>
												<div id="compDialog" class="dialog">
													<div class="cmpsub">{t}送信完了しました。右の一覧で指導履歴を確認できます。{/t}</div>
													<div class="buttonSet single">
														<!-- <a href="/staff/advice-by-student-sent.html" class="affirm" id="complocation">OK</a> -->
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</form>
							</section>
							{else if !empty($leadings->leading_comment) || !empty($charge->leading_comment)}
							<div id="comments">
								<div class="staff">
									<figure><span class="photo"><img src="/images/userStaff.png" height="38" width="38" alt=""></span></figure>
									<div class="content">
										<div class="sub">
											<div class="title">{if !empty($leadings->leading_comment)}{t}あなたからのコメント{/t}{else}{t 1={$charge->m_members_name_jp}}%1さんからのコメント{/t}{/if}</div>
											<time>{if !empty($leadings->lastupdate)}{$vDate->dateFormat($leadings->lastupdate, 'Y/m/d(wj) H:i')}{else}{$vDate->dateFormat($charge->lastupdate, 'Y/m/d(wj) H:i')}{/if}</time>
										</div>
										<p>{if !empty($leadings->leading_comment)}{$leadings->leading_comment}{else}{$charge->leading_comment}{/if}</p>
									</div>
								</div>
								<div class="you">
									<figure><span class="photo"><img src="/images/userStudent.png" height="38" width="38" alt=""></span></figure>
									<div class="content">
										{if empty($reservecomments)}
										<div class="sub_before">
											<div class="title">{t 1={$reserve->reserver_name_jp}}%1さんからのコメントはまだありません{/t}</div>
										</div>
										{else}
										<div class="sub">
											<div class="title">{t 1={$reserve->reserver_name_jp}}%1さんからのコメント{/t}</div>
											<time>{$vDate->dateFormat($reservecomments->lastupdate, 'Y/m/d(wj) H:i')}</time>
										</div>
										<p>{$reservecomments->reservecomment}</p>
										<div class="files">
											<div class="sub">
												<div class="title">{t}提出したファイル{/t}</div>
											</div>
											<div class="bezels fileup">
												<ul class="MultiFile-existing">
													{if !empty($reservecommentfiles) && count($reservecommentfiles) > 0}
														{foreach from=$reservecommentfiles item=reservefile name=reservefiles}
															<li class="MultiFile-label" style="margin: 0;">
																<i class="file" style="top: 0;"></i>
																	<a href="{$baseurl}/{$controllerName}/download/id/{$reservefile['t_files_id']}" class="MultiFile-title">{$vhHtmlOut->escape($reservefile['t_files_name'])}</a>
															</li>
														{/foreach}
													{/if}
												</ul>
											</div>
										</div>
										{/if}
									</div>
								</div>
							</div>
							{/if}
					
				</div>
			</article>
		{else}
			{if $indexflg == 1}
				<div id="main">
					{t}右の一覧から予定/指導履歴を選んでください{/t}
			{else}
				<div id="maind">
					{t}予定/指導履歴が存在しません{/t}
			{/if}
		{/if}
		</div><!--/#main-->
		<aside id="sidebar">
		</aside>
		<!--/#contents--></div>
		{include file="../common/foot_v2.php"}
		
		<script>
			$(function(){
				{if !empty($reserve)}
					var loginName = '{$member->name_jp}';
					var loginHref = '{$baseurl}/{$controllerName}/{$actionName}/reserveid/{$reserve->id}/chargeid/{$member->id}';
					//$("#repAttach").repAttachToggle(loginName,loginHref,$(".hideRep"));
					$('button.rep').click(function(e)
					{
						e.preventDefault();
						
						// 担当・解除を行う
						// flg=0:担当、flg=1:解除
						if($(this).hasClass('release'))
							var flg = 1;
						else
							var flg = 0;
						
						$.ajax({
							async: false,				// 同期通信
							url: '{$baseurl}/{$controllerName}' + "/changecharge/reserveid/" + {if empty($reserveid)}'0'{else}'{$reserveid}'{/if} + '/flg/' + flg,
							type: 'POST',
							timeout: 600000,

							data: '',
							processData: false,
							contentType: false,

							// 各種処理
							beforeSend: function(xhr, settings) {
							},
							success: function(data, textStatus, jqXHR) {
								//alert(data);
								var response = JSON.parse(data);
								if (response['error'] !== undefined)
								{	// 論理エラー
									alert(response['error']);
								}
								else
								{	// 成功
									var r = $("#repAttach");
									var t = r.find("button.rep");
									var s = r.find("#repName");
									var h = $(".hideRep");
									
									if(!t.hasClass('release')){
										t.addClass('release').html("<i></i>{t}担当解除{/t}");
										s.text(loginName).attr("href",loginHref).removeClass("inactive");
										h.slideDown("400");
									}else{
										t.removeClass('release').html("<i></i>{t}担当する{/t}");
										s.text("未設定").attr("href","#").addClass("inactive");
										h.fadeOut("400");
									}
									
									createConsulList('{$baseurl}/{$controllerName}', {if empty($reserveid)}'0'{else}'{$reserveid}'{/if}, {if empty($page)}1{else}{$page}{/if}, {if empty($reserver)}'0'{else}'{$reserver}'{/if}, {if empty($subjectid)}'0'{else}'{$subjectid}'{/if}, {if empty($chargeid)}'0'{else}'{$chargeid}'{/if});
								}
							},
							error: function(jqXHR, textSatus, errorThrown) {
								alert("error");
							},
							complete: function(jqXHR, textStatus) {
							},
						});
					});
				{/if}
				$("#loginStatusTrigger").miniMenu($("#loginStatus"));
				
				$("#pageControlSub").find(".finish").decisionDialog($("#finishDialogSub"));
				$("#pageControl").find(".delete").decisionDialog($("#tmpDialog"));
				$("#pageControl").find(".finish").decisionDialog($("#finishDialog"));
				
				
				
				$('#newadvice').submit(function(event) {
					event.preventDefault();	// 本来のsubmit処理をキャンセル

					var $form = $(this);
					var fd = new FormData($form[0]);

					$.ajax({
						async: false,				// 同期通信
						url: $form.attr('action'),
						type: $form.attr('method'),
						timeout: 600000,

						// 以下、ファイルアップロードに必須
						data: fd,
						processData: false,
						contentType: false,

						// 各種処理
						beforeSend: function(xhr, settings) {
						},
						success: function(data, textStatus, jqXHR) {
							//alert(data);
							var response = JSON.parse(data);
							if (response['error'] !== undefined)
							{	// 論理エラー
								//alert('論理エラー');
								alert(response['error']);
							}
							else
							{	// 成功

								// 完了後の飛び先を設定
								var link = '{$baseurl}/{$controllerName}/{$actionName}/reserveid/' + response['success'];
								//var comp = document.getElementById('complocation');
								//comp.setAttribute('href', link);
								// 完了ダイアログ
								if($("#tmpsendflg").val() == 1)
									$("#finishDialog").find(".delete").decisionDialog($("#tmpDialog"));
								else
									$("#finishDialog").find(".affirm").decisionDialog($("#regDialog"));
								
								$(this).delay(3000).queue(function() {
									window.location.href=link;
									$(this).dequeue();
								});
							}
						},
						error: function(jqXHR, textSatus, errorThrown) {
							alert("error");
						},
						complete: function(jqXHR, textStatus) {
						},
					});

				});
				
				
				$('#newcomment').submit(function(event) {
					event.preventDefault();	// 本来のsubmit処理をキャンセル

					var $form = $(this);
					var fd = new FormData($form[0]);

					$.ajax({
						async: false,				// 同期通信
						url: $form.attr('action'),
						type: $form.attr('method'),
						timeout: 600000,

						// 以下、ファイルアップロードに必須
						data: fd,
						processData: false,
						contentType: false,

						// 各種処理
						beforeSend: function(xhr, settings) {
						},
						success: function(data, textStatus, jqXHR) {
							//alert(data);
							var response = JSON.parse(data);
							if (response['error'] !== undefined)
							{	// 論理エラー
								//alert('論理エラー');
								alert(response['error']);
							}
							else
							{	// 成功

								// 完了後の飛び先を設定
								var link = '{$baseurl}/{$controllerName}/{$actionName}/reserveid/' + response['success'];
								//var comp = document.getElementById('complocation');
								//comp.setAttribute('href', link);
								// 完了ダイアログ
								$("#finishDialogSub").find(".affirm").decisionDialog($("#compDialog"));
								
								$(this).delay(3000).queue(function() {
									window.location.href=link;
									$(this).dequeue();
								});
							}
						},
						error: function(jqXHR, textSatus, errorThrown) {
							alert("error");
						},
						complete: function(jqXHR, textStatus) {
						},
					});

				});


			});
		</script>
	<!--[if lte IE 9]>
	<script src="/js/flexie.min.js" type="text/javascript"></script>
	<![endif]-->
</body>
</html>