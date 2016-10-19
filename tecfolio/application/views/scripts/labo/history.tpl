<!doctype html>
<html lang="ja">
<head>
{include file='labo/header.tpl'}
<script src="/js/base.share.js?{($smarty.now|date_format:"%d%H%M%S")}" type="text/javascript"></script>
<script src="/js/base.history.js?{($smarty.now|date_format:"%d%H%M%S")}" type="text/javascript"></script>
<script>

	window.onload = function()
	{
		// 初期値設定
		{if !empty($reserve)}

			// コメント欄の有無によって切り分ける必要があるものは先に設定
			{if !empty($leadings)}
				{if empty($disableComment)}
					{if empty($reservecomments)}
						enableEdit(true);
					{else}
						enableEdit(false);
					{/if}
				{/if}
			{/if}

			var shift_code	= String.fromCharCode(65 + ({$shift->dayno}-1));
			var shift_string = toHM('{$shift->m_timetables_starttime}') + '-' + toHM('{$shift->m_timetables_endtime}');
			var dates		='{$vDate->dateFormat($reserve->reservationdate, 'Y/m/d(wj)', false, true, true)}';
			var date_string = dates + ' ' + shift_code + ' ' + shift_string;

			document.getElementById('v-basic-date').appendChild(document.createTextNode(date_string));

			var submitdate = '{$reserve->submitdate}';
			//document.getElementById('submitdate').appendChild(document.createTextNode(submitdate.split("-").join("/")));

		{/if}

		createHistoryList('{$baseurl}', {if empty($reserveid)}'0'{else}'{$reserveid}'{/if}, {if empty($page)}1{else}{$page}{/if}, '{$nowdate}');
	}

	function enableEdit(enabled)
	{
		if (enabled == true)
		{
			document.getElementById('editcomment').style.display="block";
			document.getElementById('readcomment').style.display="none";
		}
		else
		{
			document.getElementById('editcomment').style.display="none";
			document.getElementById('readcomment').style.display="block";
		}
	}

	function removeFile(commentfileid)
	{
		var ul = document.getElementById('existingfiles');
		var li = document.getElementById('commentfile' + commentfileid);
		ul.removeChild(li);
	}

	function submitComment(obj)
	{
		$(obj).addClass('disabled');
		$('#newcomment').submit();
	}

</script>
</head>

<body class="student">
	{include file='labo/menu.tpl'}
		<div id="main">
			{if empty($reserve) }
				<span class="a">{t}履歴がありません{/t}</span>
			{/if}
			{if !empty($reserve) }
			<article class="history">
				<h1>{t}履歴詳細{/t}</h1>
				<div id="basicSet">
					<ul class="formSet">
						<li><label>{t}相談した日時{/t}</label><div class="bezel"><span class="selected fromInput" data-source="basic-date" id="v-basic-date"></span></div></li>
						<li><label>{t}文書の種類{/t}</label><div class="bezel"><span class="selected fromSelector" data-source="basic-doctype" id="v-basic-doctype">{$doctype}</span></div></li>
						<li><label>{t}相談場所{/t}</label><div class="bezel"><span class="selected fromSelector" data-source="basic-place" id="v-basic-place">{$place}</span></div></li>
					</ul>
				</div>
				<div>
					<ul class="formSet">
						<li>
							<label>{t}授業科目{/t}</label>
							<div class="bezel">
								<span class="selected" id="subject">{if !empty($subject)}{$subject}{/if}　</span>
							</div>
						</li>
						<li>
							<label>{t}提出日{/t}</label>
							<div class="bezel">
								<span class="selected" id="submitdate">{if ($vDate->dateFormat($reserve->submitdate, 'Y/m/d(wj)')) == null}　{elseif ($vDate->dateFormat($reserve->submitdate, 'Y/m/d(wj)')) == '1970/01/01(木)'}　{elseif ($vDate->dateFormat($reserve->submitdate, 'Y/m/d(wj)')) != '1980/01/01(火)'}{$vDate->dateFormat($reserve->submitdate, 'Y/m/d(wj)', false, true)}{else}　{/if}</span>
							</div>
						</li>
						<li>
							<label>{t}進行状況{/t}</label>
							<div class="bezel selectMirror">
								<span class="selected" id="progress">{if !empty($progresssal[$reserve->progress])}{$progresssal[$reserve->progress]}{/if}</span>
							</div>
						</li>
						<li>
							<label>{t}添付したファイル{/t}</label>
							<div class="bezel fileup">
								<ul class="MultiFile-existing">
									{if !empty($reservefiles) && count($reservefiles) > 0}
										{foreach from=$reservefiles item=reservefile name=reservefiles}
											<li class="MultiFile-label"><i class="file"></i><a href="{$baseurl}/{$controllerName}/download/id/{$reservefile['t_files_id']}" class="MultiFile-title">{$vhHtmlOut->escape($reservefile['t_files_name'])}</a></li>
										{/foreach}
									{/if}
								</ul>
							</div>
						</li>
					</ul>
				</div>
				<div class="freetext">
					<label>{t}相談したいこと{/t}</label>
					<p id="question">{$vhHtmlOut->escape($reserve->question)}</p>
				</div>
					<div id="comments">

					{* スタッフ指導 start*}
					{if !empty($leadings)}
						<div class="staff">
							<figure><span class="photo"><img src="/images/userStaff.png" height="38" width="38" alt=""></span><span class="name">{$reserve->charge_name_jp}</span></figure>
							<div class="content">
								<div class="sub">
									<div class="title">{t}スタッフからのコメント{/t}</div>
									<time>{$vDate->dateFormat($leadings->lastupdate, 'Y/m/d(wj) H:i')}</time>
								</div>
								<p>{$leadings->leading_comment}</p>
							</div>
						</div>

						{* コメント start *}
						{if empty($disableComment)}

							{* コメント編集可能 *}
							{* 新規・編集共用 *}
							<div id="editcomment">
								<form method="POST" action="{$baseurl}/labo/newcomment" name="newcomment" id="newcomment" enctype="multipart/form-data">
									<input type="hidden" name="reserveid" id="reserveid" value="{$reserveid}">
									<input type="hidden" name="reservecommentid" id="reservecommentid" value="{if empty($reservecomments)}0{else}{$reservecomments->id}{/if}">
									<div class="you">
										<figure><span class="photo"><img src="/images/userStudent.png" height="38" width="38" alt=""></span></figure>
										<div class="content">
											<div class="countText">
												<textarea name="comment" id="comment" cols="42" rows="5">{if empty($reservecomments)}{else}{$reservecomments->reservecomment}{/if}</textarea>
												<span class="counter">250</span>
												<span class="note">{t}送信後も再編集可能です{/t}</span>
											</div>
											<div class="files">
												<div class="sub">
													<div class="title">{t}添付ファイル{/t}</div>
													<p class="note">{t}※提出できましたか？先生に提出したレポートやレジュメなどの原稿をラボにも送ってくださいね。{/t}</p>
												</div>
												<div class="bezel fileup">
													<div class="replaceButton">
														<!--.replaceButton クラスでは.viewを透明化した.substanceで覆って入力を実現する-->
														<i class="view">{t}ファイルを選択{/t}</i><input type="file" class="multi substance" id="attach" name="attach[]">
													</div>
													{if !empty($reservecommentfiles) && count($reservecommentfiles) > 0}
													<ul class="MultiFile-existing" id="existingfiles">
														{foreach from=$reservecommentfiles item=commentfile name=reservecommentfiles}
															<li class="MultiFile-label" id="commentfile{$commentfile['id']}">
																<a class="MultiFile-remove" href="javascript:void(0);" onclick="removeFile({$commentfile['id']});">x</a>
																<a href="{$baseurl}/{$controllerName}/download/id/{$commentfile['t_files_id']}" class="MultiFile-title">{$vhHtmlOut->escape($commentfile['t_files_name'])}</a>
																<input type="hidden" name="keepfile[]" value="{$commentfile['id']}">
															</li>
														{/foreach}
													</ul>
													{/if}
												</div>
											</div>
											<div id="pageControl">
												<div class="buttonSet single">
													<button class="finish">{t}送信する{/t}</button>
												</div>
											</div>
										</div>

									</div>

									<div id="commentDialog" class="dialog">
										<i class="closeButton cancel"></i>
										<div class="sub">{t}コメントを送信しますか？{/t}</div>

										<div class="buttonSet dubble">
											<a href="#" onclick="submitComment(this);" class="affirm">{t}OK{/t}</a>
											<a href="#" class="cancel">{t}キャンセル{/t}</a>
										</div>
									</div>
									<div id="compDialog" class="dialog">
										<div class="cmpsub">{t}コメントの送信が完了しました。右の履歴一覧で確認できます。{/t}</div>
										<div class="buttonSet single" id="complocation">
											<!-- <a href="history.html" class="affirm">OK</a> -->
										</div>
									</div>
								</form>
							</div>

							{* 編集不可 *}
							<div id="readcomment">
								<div class="you">
									<figure><span class="photo"><img src="/images/userStudent.png" height="38" width="38" alt=""></span></figure>
									<div class="content">
										<div class="sub">
											<div class="title">{t}あなたからのコメント{/t}</div>
											<time>{if !empty($reservecomments)}{$vDate->dateFormat($reservecomments->lastupdate, 'Y/m/d(wj) H:i')}{/if}</time>
										</div>
										<p>{if !empty($reservecomments)}{$reservecomments->reservecomment}{/if}</p>
										<div class="files">
											<div class="sub">
												<div class="title">{t}提出したファイル{/t}</div>
											</div>
											<div class="bezel fileup">
												{if !empty($reservecommentfiles) && count($reservecommentfiles) > 0}
												<ul class="MultiFile-existing">
													{foreach from=$reservecommentfiles item=commentfile name=reservecommentfiles}
														<li class="MultiFile-label">
															<i class="file"></i>
															<a href="{$baseurl}/{$controllerName}/download/id/{$commentfile['t_files_id']}" class="MultiFile-title">{$vhHtmlOut->escape($commentfile['t_files_name'])}</a>
														</li>
													{/foreach}
												</ul>
												{/if}
											</div>
										</div>
										<div id="pageControl">
											<div class="buttonSet single">
												<a href="#" onclick="enableEdit(true);" class="finish">{t}編集する{/t}</a>
											</div>
										</div>
									</div>

								</div>
							</div>
						{/if}
						{* コメント end *}
					{/if}
					{* スタッフ指導 end *}
				</div>
			</article>
			{/if}
		<!--/#contents-->
		</div>

			<aside id="sidebar">
				<h1>{t}履歴一覧{/t}</h1>
				<ul id="historylist">
				</ul>
				<div class="pager" id="historypager">
				</div>
			</aside>
			<!--/#contents-->
	</div>
			{include file="../common/foot_v2.php"}
			
			<script>
				$(function(){
					$('a.affirm.disabled').click(function(){
						return false;
					})
					
					$("#pageControl").find(".finish").decisionDialog($("#commentDialog"));
					$(".countText").each(function(){
						{literal}
						$(this).textAreaCD({"zeroCheck":true,"fileCheck":true});
						//$(this).textAreaCD({"zeroCheck":true});
						{/literal}
					});
					$(".replaceButton").each(function(){
						$(this).replaceButton();
					});
					$("#loginStatusTrigger").miniMenu($("#loginStatus"));
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
								var response = JSON.parse(data);
								if (response['error'] !== undefined)
								{	// 論理エラー
									alert(response['error']);
								}
								else
								{	// 成功

									// 完了後の飛び先を設定
									var link = '{$baseurl}/{$controllerName}/history/reserveid/' + '{if !empty($reserveid)}{$reserveid}{/if}';
									// 完了ダイアログ
									$("#commentDialog").find(".affirm").decisionDialog($("#compDialog"));
									$(this).delay(2000).queue(function() {
										location.href=link;
										$(this).dequeue();
									});
								}
							},
							error: function(jqXHR, textSatus, errorThrown) {
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