<!doctype html>
<html lang="ja">
<head>
<!--
	{t}お知らせタイトルを入力してください{/t}
	{t}お知らせ本文を入力してください{/t}
	{t}カレンダータイトルを入力してください{/t}
	{t}日付を選択してください{/t}
	{t}削除対象を指定してください{/t}
-->
{include file='admin/header.tpl'}
<style type="text/css">
	#editinfo_main {
		-webkit-box-flex: 1;
		-moz-box-flex: 1;
		-ms-box-flex: 1;
		box-flex: 1;
		-ms-flex: 1;
		flex: 1;
		position: relative;
		background-color: #fff;
		margin-right: -4px;
		border-right: 4px solid #d3d3d3;
		padding-right: 30px;
		min-width: 800px;
	}
</style>
<script src="/js/base.infomation.js" type="text/javascript"></script>
<script>

	window.onload = function()
	{
		createInfomationList('{$baseurl}/{$controllerName}', {if empty($infomationid)}0{else}{$infomationid}{/if}, {if empty($page)}1{else}{$page}{/if});
	}

	function submitData()
	{
		var userAgent = window.navigator.userAgent.toLowerCase();
		if(userAgent.indexOf('firefox') == -1)
		{
			var txt = $('.nicEdit-main').html();
			$('#noticeBody').val(txt.replace(/<p>/g, '<div>').replace(/<\/p>/g, '</div>'));
		}
		
		$('#newinfomation').submit();
	}
	function submitCancel()
	{
		var request = createXMLHttpRequest();
		var scripturl = "{$baseurl}/{$controllerName}/deleteinformation/informationid/" + document.getElementById('informationid').value;
		request.open("POST", scripturl , false);
		request.send(null);

		var json = request.responseText;
		var response = JSON.parse(json);
		if (response['error'] !== undefined)
		{	// エラー
			alert(response['error']);
		}
		else
		{	// 成功
			$("#delDialog").find(".affirm").decisionDialog($("#delCompDialog"));
		}
	}

</script>
</head>

<body class="admin">
	{include file='admin/menu.tpl'}
		<div id="editinfo_main">
			<article>
				<h1>{t}お知らせ管理{/t}：{t}新規お知らせ登録{/t}</h1>
				<form method="POST" action="{$baseurl}/{$controllerName}/newinfomation" name="newinfomation" id="newinfomation" enctype="multipart/form-data">
					<ul class="formSet noticeEdit">
						<li>
							<label for="noticeSub">{t}タイトル{/t}</label>
							<input type="text" name="title" id="noticeSub" value="">
						</li>
						<li>
							<label for="noticeBody">{t}本文{/t}</label>
							<div class="title">
								<textarea name="body" id="noticeBody" class="noticebody" style="height:150px; width:100%;"></textarea>
							</div>

						</li>
					</ul>
					<ul class="formSet noticeEdit" >
						<li>
							<label for="noticeSub">{t}カレンダー{/t}</label>
							<input type="checkbox" name="calendar_flag" id="calendar_flag" value="1"><i class="check">{t}掲載する{/t}</i>
						</li>
						<li class="title">
							<label for="noticeSub">{t}タイトル{/t}<br>{t}(14文字程度){/t}</label>
							<div class="titled">
								<input type="text" name="subtitle" id="noticeSubtitle" value="">
							</div>
						</li>
						<li>
							<label for="noticeDate" class="date">{t}開始日{/t}</label><div class="info_searchFrame">
								<input type="text" class="views" readonly="readonly" id="date_from" value="">
								<a class="clearsearchclass" id="clear_from" style="margin-right: 10px; color:#0073ea; cursor:pointer; font-weight: bold;">x</a>
								<input type="hidden" name="startdate" id="startdate_hidden" value="">
							</div>
							<select name="starthour" id="noticeTime" class="time">
								{for $num=0 to 23}
									<option value="{$num|string_format:'%02d'}" id="starthour{$num|string_format:'%02d'}">{$num|string_format:'%02d'}</option>
								{/for}
							</select><i>{t}時{/t}</i>

							<select name="startminute" id="noticeMin" class="time">
								{for $num=0 to 50 step 10}
								<option value="{$num|string_format:'%02d'}">{$num|string_format:'%02d'}</option>
								{/for}
							</select><i>{t}分{/t}</i>
							<input type="checkbox" name="allday_flag" id="allday_flag" value="1"><i class="check">{t}終日{/t}</i>
						</li>
						<li>
							<label for="noticeDate" class="date">{t}終了日{/t}</label><div class="info_searchFrame">
								<input type="text" class="views" id="date_to" readonly="readonly">
								<a class="clearsearchclass" id="clear_to" style="margin-right: 10px; color:#0073ea; cursor:pointer; font-weight: bold;">x</a>
								<input type="hidden" name="enddate" id="enddate_hidden" value="">
							</div>
							<select name="endhour" id="noticeTime" class="time">
								{for $num=0 to 23}
									<option value="{$num|string_format:'%02d'}" id="starthour{$num|string_format:'%02d'}">{$num|string_format:'%02d'}</option>
								{/for}
							</select><i>{t}時{/t}</i>
							<select name="endminute" id="noticeMin" class="time">
								{for $num=0 to 50 step 10}
								<option value="{$num|string_format:'%02d'}">{$num|string_format:'%02d'}</option>
								{/for}
							</select><i>{t}分{/t}</i>
						</li>
					</ul>

					<div id="pageControl">
						<button class="finish">{t}登録する{/t}</button>
						<div id="finishDialog" class="dialog">
							<i class="closeButton cancel"></i>
							<div class="sub">{t}お知らせを登録しますか？{/t}</div>
							<div class="buttonSet dubble">
								<a href="#" onclick="submitData();" class="affirm">{t}OK{/t}</a>
								<a href="#" class="cancel">{t}キャンセル{/t}</a>
							</div>
						</div>
						<div id="compDialog" class="dialog">
							<div class="cmpsub">{t}お知らせが登録されました。右のお知らせ一覧で確認できます。{/t}</div>
							<div class="buttonSet single">
								<!-- <a href="changeReserve.html" class="affirm" id="complocation">OK</a> -->
							</div>
						</div>
					</div>
				</form>
			</article>
			<!--/#main--></div>
			<aside id="sidebar">
				<h1>{t}お知らせ一覧{/t}</h1>
				<ul id="infomationlist">
				</ul>
				<div class="pager" id="infomationpager">
				</div>
			</aside>
			<!--/#contents--></div>
			{include file="../common/foot_v2.php"}
			<script>
			$(function(){
				$("#commentControl").find(".finish").decisionDialog($("#commentDialog"));
				$("#commentDialog").find(".affirm").decisionDialog($("#compDialog"));
				$(".countText").each(function(){
					$(this).textAreaCD();
				});
				$(".replaceButton").each(function(){
					$(this).replaceButton();
				});
				var nicElm = new nicEditor( { iconsPath : '/image/nicEditorIcons.gif' } ).panelInstance('noticeBody');
				
				
				$('#allday_flag').change(function(){
				    if ($(this).is(':checked')) {
				    	$('#date_to').attr("disabled",true);
				    	$('#date_to').val("");
				    	$('#date_from').datepicker('option', 'maxDate', '{$maxdate}');		//最大値は最新の学期の終了日
						$('select[name=starthour]').attr('disabled', true);
						$('select[name=startminute]').attr('disabled', true);
						$('select[name=endhour]').attr('disabled', true);
						$('select[name=endminute]').attr('disabled', true);
					} else {
						$('#date_to').attr("disabled",false);
						$('select[name=starthour]').attr('disabled', false);
						$('select[name=startminute]').attr('disabled', false);
						$('select[name=endhour]').attr('disabled', false);
						$('select[name=endminute]').attr('disabled', false);
					}
				});

				$('#date_to').attr("disabled",true);
				$('#date_from').attr("disabled",true);
				$('#allday_flag').attr("disabled",true);
				$('#noticeSubtitle').attr("disabled",true);
				$('select[name=starthour]').attr('disabled', true);
				$('select[name=startminute]').attr('disabled', true);
				$('select[name=endhour]').attr('disabled', true);
				$('select[name=endminute]').attr('disabled', true);

				$('#calendar_flag').change(function(){
					if ($(this).is(':checked')) {
						$('#date_to').attr("disabled",false);
						$('#date_from').attr("disabled",false);
						$('#allday_flag').attr("disabled",false);
						$('#noticeSubtitle').attr("disabled",false);
						$('select[name=starthour]').attr('disabled', false);
						$('select[name=startminute]').attr('disabled', false);
						$('select[name=endhour]').attr('disabled', false);
						$('select[name=endminute]').attr('disabled', false);
						if($("#allday_flag:checked").val()){
							$('#date_to').attr("disabled",true);
							$('select[name=starthour]').attr('disabled', true);
							$('select[name=startminute]').attr('disabled', true);
							$('select[name=endhour]').attr('disabled', true);
							$('select[name=endminute]').attr('disabled', true);
						}
					}
					else
					{
						$('#date_to').attr("disabled",true);
						$('#date_from').attr("disabled",true);
						$('#allday_flag').attr("disabled",true);
						$('#noticeSubtitle').attr("disabled",true);
						$('select[name=starthour]').attr('disabled', true);
						$('select[name=startminute]').attr('disabled', true);
						$('select[name=endhour]').attr('disabled', true);
						$('select[name=endminute]').attr('disabled', true);
					}
				});


				$("#pageControl").find(".finish").decisionDialog($("#finishDialog"));
				//$("#pageControl").find(".finish").decisionDialog($("#finishDialog"),false,$("#compDialog"));
				$("#pageControl").find(".delete").decisionDialog($("#delDialog"),true);
				//$("#delDialog").find(".delAffirm").decisionDialog($("#delCompDialog"),true);

				var dates_from = $('#date_from').datepicker({
					dateFormat: 'yy-mm-dd',//年月日の並びを変更
					maxDate: $('#date_to').val(),  //MAXがTOを超えないようにする
					onSelect: function(dateText, inst){
						// 実際に挿入するhidden値を保存
						$('#startdate_hidden').val(dateText);
						// 終了日の最小日が選択値になるように制限
						$('#date_to').datepicker('option', 'minDate', dateText);
						// 表示値をフォーマット
						if($('#enddate_hidden').val() != '')
							$('#date_to').val(dateFormat($('#enddate_hidden').val(), 'Y/m/d(wj)'));
						$('#date_from').val(dateFormat(dateText, 'Y/m/d(wj)'));
					}
				});

				var dates_to = $('#date_to').datepicker({
					dateFormat: 'yy-mm-dd',//年月日の並びを変更
					minDate: $('#date_from').val(),  //MINがFROMを超えないようにする
					onSelect: function(dateText, inst){
						// 実際に挿入するhidden値を保存
						$('#enddate_hidden').val(dateText);
						// 開始日の最大日が選択値になるように制限
						$('#date_from').datepicker('option', 'maxDate', dateText);
						// 表示値をフォーマット
						if($('#startdate_hidden').val() != '')
							$('#date_from').val(dateFormat($('#startdate_hidden').val(), 'Y/m/d(wj)'));
						$('#date_to').val(dateFormat(dateText, 'Y/m/d(wj)'));
					}
				});
				
				// 日付リセット処理の追加
				$('#clear_from').on('click', function(){
					dates_from.val('');
					$('#startdate_hidden').val('');
					$('#date_to').datepicker('option', 'minDate', '{$mindate}');		//最小値は最古の学期の開始日
					$('#date_to').val(dateFormat($('#enddate_hidden').val(), 'Y/m/d(wj)'));
				});
	
				$('#clear_to').on('click', function(){
					dates_to.val('');
					$('#enddate_hidden').val('');
					$('#date_from').datepicker('option', 'maxDate', '{$maxdate}');		//最大値は最新の学期の終了日
					$('#date_from').val(dateFormat($('#startdate_hidden').val(), 'Y/m/d(wj)'));
				});

				$("#pageControl").find(".finish").decisionDialog($("#finishDialog"));
				//$("#pageControl").find(".finish").decisionDialog($("#finishDialog"),false,$("#compDialog"));
				$("article .calendarSet").each(function(){
					var v = $(this).find(".view");
						v.datepicker({
							showOn:"button",
							buttonText: "{t}▼ 選んでください{/t}",
							beforeShow: function(input, inst) {
								var calendar = inst.dpDiv;
								setTimeout(function() {
									calendar.position({
										my: 'right top',
										at: 'right bottom',
										collision: 'none',
										of: ".ui-datepicker-trigger"
									});
								}, 1);
							}
						});
					});
					$(".replaceButton").each(function(){
						$(this).replaceButton();
					});
					$("#loginStatusTrigger").miniMenu($("#loginStatus"));

					$('#newinfomation').submit(function(event) {
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
									var link = '{$baseurl}/{$controllerName}/information/infomationid/' + response['success'] ;
									//var comp = document.getElementById('complocation');
									//comp.setAttribute('href', link);
									// 完了ダイアログ
									$("#finishDialog").find(".affirm").decisionDialog($("#compDialog"));
									$(this).delay(2200).queue(function() {
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
