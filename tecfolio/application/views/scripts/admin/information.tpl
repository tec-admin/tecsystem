<!doctype html>
<html lang="ja">
<head>

{include file='admin/header.tpl'}
<script src="/js/base.infomation.js" type="text/javascript"></script>
<script>

	window.onload = function()
	{
		{if !empty($infomation['startdate'])}
		setDateSelected(document.getElementById('starthour{$vDate->dateFormat($infomation['startdate'], 'H')}'));
		setDateSelected(document.getElementById('startminute{$vDate->dateFormat($infomation['startdate'], 'i')}'));
		{/if}
		
		{if !empty($infomation['enddate'])}
		setDateSelected(document.getElementById('endhour{$vDate->dateFormat($infomation['enddate'], 'H')}'));
		setDateSelected(document.getElementById('endminute{$vDate->dateFormat($infomation['enddate'], 'i')}'));
		{/if}

		{if !empty($infomation['calendar_flag'])}
			var calendar_flag = document.getElementById('calendar_flag');
			calendar_flag.setAttribute('checked', 'checked');
		{/if}

		{if !empty($infomation['allday_flag'])}
			var allday_flag = document.getElementById('allday_flag');
			allday_flag.setAttribute('checked', 'checked');
		{/if}
		createInfomationList('{$baseurl}/{$controllerName}', {if empty($infomationid)}0{else}{$infomationid}{/if}, {if empty($page)}1{else}{$page}{/if});

	}

	function setDateSelected(dateid)
	{
		if(dateid != undefined)
		{
			dateid.setAttribute('selected','selected');
		}
	}
	
	function cancel()
	{
		$("#baseDialog").bPopup().close();
	}
	
	function delCancel()
	{
		$("#delDialog").bPopup().close();
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

	function submitDelete()
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
			$("#delCompDialog").bPopup();
			$(this).delay(2000).queue(function() {
				window.location.href="{$baseurl}/{$controllerName}/editinformation";
				$(this).dequeue();
			});
		}
	}

</script>
</head>

{if $member->roles|mb_strpos:'Administrator' !== FALSE}
<body class="admin">
{elseif $member->roles|mb_strpos:'Staff' !== FALSE}
<body class="staff">
{else}
<body class="student">
{/if}
	{include file='admin/menu.tpl'}
		<div id="main" style="min-width: 780px;">
			<article>
				<h1>{t}お知らせ管理{/t}：{t}お知らせ詳細{/t}</h1>
				<div id="Control" class="osh">
					<table class="back">
						<thead>
							<tr>
							<th class="tit">{$infomation['title']}</th>
							</tr>
						</thead>
						<tbody>
							<tr>
							<td>{$vDate->dateFormat($infomation['createdate'], 'Y/m/d(wj) H:i', false, true)}</td>
							</tr>
							<tr>
							<td class="txt">{$infomation['body']}</td>
							</tr>
							<tr>
							<td class="rink">
								<div id="pageControl" style=" text-align : right ; ">
									<img class="pencil" src="/image/index.png" alt="{t}編集{/t}">
									<img class="garbage" src="/image/gomi.png"alt="{t}削除{/t}">
								</div>
							</td>
							</tr>
						</tbody>
					</table>

					<div id="baseDialog" class="dialog" style="width: 800px;">
						<i class="closeButton cancel" onclick="cancel();"></i>
							<div class="sub">{t}お知らせ編集{/t}</div>

						<form method="POST" action="{$baseurl}/{$controllerName}/updateinformation" name="newinfomation" id="newinfomation" enctype="multipart/form-data">
						<input type="hidden" name="informationid" id="informationid" value="{$infomation['id']}">

						<ul class="formSet noticeEdit">
						<li>
							<label for="noticeSub">{t}タイトル{/t}</label>
							<input type="text" name="title" id="noticeSub" value="{$infomation['title']}" style="color:#000;">
						</li>
						<li>
							<label for="noticeBody">{t}本文{/t}</label>
							<div class="title">
								<textarea name="body" id="noticeBody" class="noticebody" style="height:150px; width:510px;">{$infomation['body']}</textarea>
							</div>
						</li>
						</ul>
						<ul class="formSet noticeEdit">
							<li>
								<label for="noticeSub">{t}カレンダー{/t}</label>
								<input type="checkbox" name="calendar_flag" id="calendar_flag" value="1"><i class="check">{t}掲載する{/t}</i>
							</li>
							<li class="title">
								<label for="noticeSub">{t}タイトル{/t}<br>{t}(14文字程度){/t}</label>
								<div class="titled">
									<input type="text" name="subtitle" id="noticeSubtitle" value="{$infomation['subtitle']}" style="color:#000;">
								</div>
							</li>
							<li>
								<label for="noticeDate" class="date">{t}開始日{/t}</label><div class="searchFrame">
									<input type="text" name="startdate" class="views" readonly="readonly" id="date_from" value="{if !empty($infomation['startdate'])}{$vDate->dateFormat($infomation['startdate'], 'Y/m/d(wj)')}{/if}">
									<a class="clearsearchclass" id="clear_from" style="color:#0073ea; cursor:pointer; font-weight: bold;">x</a>
									<input type="hidden" name="startdate" id="startdate_hidden" value="{if !empty($infomation['startdate'])}{$vDate->dateFormat($infomation['startdate'], 'Y-m-d', true)}{/if}">
								</div>
								<select name="starthour" id="noticeTime" class="time">
									{for $num=0 to 23}
									<option value="{$num|string_format:'%02d'}" id="starthour{$num|string_format:'%02d'}">{$num|string_format:'%02d'}</option>
									{/for}
								</select><i>{t}時{/t}</i>

								<select name="startminute" id="noticeMin" class="time">
									{for $num=0 to 50 step 10}
									<option value="{$num|string_format:'%02d'}" id="startminute{$num|string_format:'%02d'}">{$num|string_format:'%02d'}</option>
									{/for}
								</select><i>{t}分{/t}</i>
								<input type="checkbox" name="allday_flag" id="allday_flag" value="1"><i class="check">{t}終日{/t}</i>
							</li>
							<li>
								<label for="noticeDate" class="date">{t}終了日{/t}</label><div class="searchFrame">
									<input type="text" name="enddate" class="views" id="date_to" readonly="readonly" value="{if !empty($infomation['enddate'])}{$vDate->dateFormat($infomation['enddate'], 'Y/m/d(wj)')}{/if}">
									<a class="clearsearchclass" id="clear_to" style="color:#0073ea; cursor:pointer; font-weight: bold;">x</a>
									<input type="hidden" name="enddate" id="enddate_hidden" value="{if !empty($infomation['enddate'])}{$vDate->dateFormat($infomation['enddate'], 'Y-m-d', true)}{/if}">
								</div>
								<select name="endhour" id="noticeTime" class="time">
									{for $num=0 to 23}
									<option value="{$num|string_format:'%02d'}" id="endhour{$num|string_format:'%02d'}">{$num|string_format:'%02d'}</option>
									{/for}
								</select><i>{t}時{/t}</i>
								<select name="endminute" id="noticeMin" class="time">
									{for $num=0 to 50 step 10}
									<option value="{$num|string_format:'%02d'}" id="endminute{$num|string_format:'%02d'}">{$num|string_format:'%02d'}</option>
									{/for}
								</select><i>{t}分{/t}</i>
							</li>
						</ul>
						<div class="buttonSet dubble">
							<a onclick="submitData();" class="affirm">{t}登録する{/t}</a>
							<a onclick="cancel();" class="cancel">{t}キャンセル{/t}</a>
						</div>

						</form>
					</div>
					<div id="compDialog" class="dialog">
						<div class="cmpsub">{t}お知らせが更新されました。右のお知らせ一覧で確認できます。{/t}</div>
						<div class="buttonSet single">
							<!-- <a href="changeReserve.html" class="affirm" id="complocation">OK</a> -->
						</div>
					</div>
					<div id="delDialog" class="dialog">
						<i class="closeButton cancel" onclick="delCancel();"></i>
						<div class="sub">{t}このお知らせを削除しますか？{/t}</div>
						<div class="buttonSet dubble">
							<a onclick="submitDelete();" class="del affirm"><i></i>{t}OK{/t}</a>
							<a onclick="delCancel();" class="cancel">{t}キャンセル{/t}</a>
						</div>
					</div>
					<div id="delCompDialog" class="dialog">
						<div class="cmpsub">{t}お知らせを削除しました。{/t}</div>
						<div class="buttonSet single">
							<!-- <a href="{$baseurl}/{$controllerName}/editinformation" class="affirm">OK</a> -->
						</div>
					</div>
				</div>
			</article>
		<!--/#main--></div>
		<aside id="sidebar">
			<h1>{t}履歴一覧{/t}</h1>
			<ul id="infomationlist">
			</ul>
			<div class="pager" id="infomationpager">
			</div>
		</aside>
		<!--/#contents--></div>
		{include file="../common/foot_v2.php"}
		
		<script>
			$(function(){
				//$("#pageControl").find(".pencil").decisionDialog($("#baseDialog"));
				{literal}
				$("#pageControl").find(".pencil").click(function() {
					$("#baseDialog").bPopup({
						position	: ['auto',0],
						follow		: [true,false]
					});
					new nicEditor( { iconsPath : '/image/nicEditorIcons.gif' } ).panelInstance('noticeBody');
				});
				{/literal}
				//$("#pageControl").find(".garbage").decisionDialog($("#delDialog"),true);
				$("#pageControl").find(".garbage").click(function() {
					$("#delDialog").bPopup();
				});

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
				if({$infomation['calendar_flag']} == 0){
					$('#date_to').attr("disabled",true);
					$('#date_from').attr("disabled",true);
					$('#allday_flag').attr("disabled",true);
					$('#noticeSubtitle').attr("disabled",true);
					$('select[name=starthour]').attr('disabled', true);
					$('select[name=startminute]').attr('disabled', true);
					$('select[name=endhour]').attr('disabled', true);
					$('select[name=endminute]').attr('disabled', true);
				}
				else
				{
					$('#calendar_flag').attr("checked",true);
				}
				if({$infomation['allday_flag']} == 0){
					$('#allday_flag').attr("checked",false);
				}
				else
				{
					$('#allday_flag').attr("checked",true);
					$('#date_to').attr("disabled",true);
					$('select[name=starthour]').attr('disabled', true);
					$('select[name=startminute]').attr('disabled', true);
					$('select[name=endhour]').attr('disabled', true);
					$('select[name=endminute]').attr('disabled', true);
				}
				$('#allday_flag').change(function(){
				    if ($(this).is(':checked')) {
				    	$('#date_to').attr("disabled",true);
				    	$('#date_to').removeAttr("value");
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
				$('#calendar_flag').change(function(){
					if ($(this).is(':checked')) {
						$('#newinfomationfinish').attr('disabled', true);
						$('#date_to').attr("disabled",false);
						$('#date_from').attr("disabled",false);
						$('#allday_flag').attr("disabled",false);
						$('#noticeSubtitle').attr("disabled",false);
						$('select[name=starthour]').attr('disabled', false);
						$('select[name=startminute]').attr('disabled', false);
						$('select[name=endhour]').attr('disabled', false);
						$('select[name=endminute]').attr('disabled', false);
						if($('#noticeSubtitle').val() != 'カレンダータイトルを入力してください' && $("#date_from").val() !="")
							$('#newinfomationfinish').attr('disabled', false);
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
				
				var dates_from = $('#date_from').datepicker({
					dateFormat: 'yy-mm-dd',//年月日の並びを変更
					maxDate: $('#enddate_hidden').val(),  //MAXがTOを超えないようにする
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
					minDate: $('#startdate_hidden').val(),  //MINがFROMを超えないようにする
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
				})
				
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
								var link = '{$baseurl}/{$controllerName}/information/informationid/' + response['success'];
								//var comp = document.getElementById('complocation');
								//comp.setAttribute('href', link);
								// 完了ダイアログ
								$("#compDialog").bPopup();
								$(this).delay(2000).queue(function() {
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