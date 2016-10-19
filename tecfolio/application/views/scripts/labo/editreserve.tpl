<!doctype html>
<html lang="ja">
<head>
{include file='labo/header.tpl'}
<script src="/js/base.share.js?{($smarty.now|date_format:"%d%H%M%S")}" type="text/javascript"></script>
<script src="/js/base.reserve.js?{($smarty.now|date_format:"%d%H%M%S")}" type="text/javascript"></script>
<script src="/js/base.shiftcalendar.{$apptype}.js?{($smarty.now|date_format:"%d%H%M%S")}" type="text/javascript"></script>
<script>

	function cancel()
	{
		$("#dayDialog").bPopup().close();
		document.getElementById('item5').value = "";
		document.getElementsByClassName('ui-datepicker-trigger').id = "datepick";
		$('.ui-datepicker-trigger').trigger("click");
		$("p.remove3").text("");
	}
	function ok()
	{
		$("#dayDialog").bPopup().close();
		$(".finish").removeClass("inactive");
	}
	
	window.onload = function()
	{
		// 初期値設定
		{if !empty($reserve)}
			document.getElementById('reserveid').value = '{$reserveid}';

			document.getElementById('basic-doctype').value = {$shift->m_dockind_id};
			document.getElementById('basic-place').value = {$shift->m_place_id};

			var shift_code	= String.fromCharCode(65 + ({$shift->dayno}-1));
			var shift_string = toHM('{$shift->m_timetables_starttime}') + '-' + toHM('{$shift->m_timetables_endtime}');
			var dates		='{$vDate->dateFormat($reserve->reservationdate, 'Y/m/d(wj)', false, true, true)}';
			var date_string = dates  + ' ' + shift_code + ' ' + shift_string;

			document.getElementById('v-basic-date').appendChild(document.createTextNode(date_string));
			document.getElementById('defaultitem0').value = date_string;

			// 提出日の初期値
			var submitdate = '{$vDate->dateFormat($reserve->submitdate, 'Y/m/d(wj)', false, true)}';
			if(submitdate != '')
				$("p.remove3").text("　");
			document.getElementById('item5').value = submitdate;
			document.getElementById('defaultitem4').value = submitdate;
		{/if}
		createReserveList('{$baseurl}/{$controllerName}', {if empty($reserveid)}'0'{else}'{$reserveid}'{/if}, {if empty($page)}1{else}{$page}{/if});
		createShiftCalendar('{$baseurl}/{$controllerName}', '{$nendo}-03-31', '{$nowdate}', '{$reserveid}');
	}

	function removeFile(reservefileid)
	{
		var ul = document.getElementById('existingfiles');
		var li = document.getElementById('reservefile' + reservefileid);
		ul.removeChild(li);
		$(".finish").removeClass("inactive");
	}

	function closesetting(string, rdate, shiftid)
	{
		document.getElementById('shiftid').value = shiftid;

		// 下記はダイアログ側で自動的に設定される
		// item1 = dockind
		// item2 = place
		// item3 = rdate
		$("#basicSettingDialog").setClose(string, rdate);
		page();
	}
	
	var f_clicked = 0;
	
	function submitData()
	{
		// 二重クリック禁止
		if(f_clicked == 1){
			return false;
		}
		f_clicked = 1;
		
		clean();
		$('#updatereserve').submit();
	}

	function submitCancel()
	{
		// 二重クリック禁止
		if(f_clicked == 1){
			return false;
		}
		f_clicked = 1;
		
		var request = createXMLHttpRequest();
		var scripturl = "{$baseurl}/{$controllerName}/cancelreserve/reserveid/" + document.getElementById('reserveid').value;
		request.open("POST", scripturl , false);
		request.send(null);

		var json = request.responseText;
		var response = JSON.parse(json);
		if (response['error'] !== undefined)
		{	// エラー
			alert(response['error']);
		}
		else
		{
			// 成功
			changeFlg = false;
			$(this).delay(2200).queue(function() {
				location.href="{$baseurl}/{$controllerName}/reserve";
				$(this).dequeue();
			});
			$("#delDialog").find(".affirm").decisionDialog($("#delCompDialog"));
		}
	}

	function changeday()
	{
		page();
		$("p.remove3").text("　");
		// 提出日が過去日の場合の確認ダイアログ
		var submitdate = document.getElementById('item5').value;
		submitdate = submitdate.split("/").join("-");
		document.getElementById('submitdate').value = submitdate;
		var item3 = document.getElementById('item3').value;
		document.getElementById('item3').value = item3;
		
		if (submitdate < item3){
			$("#dayDialog").bPopup();
		}
		else{
			removeInactive();
		}
		document.getElementById('item5').value = dateFormat(submitdate, 'Y/m/d(wj)', false, true);
	}
	function Calendar()
	{
		$(this).delay(300).queue(function() {
			createShiftCalendar('{$baseurl}/{$controllerName}', '{$nendo}-03-31', '{$nowdate}', '{$reserveid}');
			$(this).dequeue();
		 });
	}
	function page(){
		changeFlg = true;
	}
	function ite4(){
		$(this).delay(10).queue(function() {
			$("#subject").prepend("<p class='remove' id='delete4'>　</p>");
			$("#delete4").click(function () {
				$('#subject').empty();
				$("p.remove").empty();
			});
			$(this).dequeue();
		});
	}
	function ite6(){
		$(this).delay(10).queue(function() {
			$("#progress").prepend("<p class='remove1' id='delete6'>　</p>");
			$("#delete6").click(function () {
				$('#progress').empty();
				$("p.remove1").empty();
			});
			$(this).dequeue();
		});
	}
	function clean()
	{
		$("p.remove").empty();
		$("p.remove1").empty();
		$("p.remove3").empty();
		$(this).delay(300).queue(function() {
			back()
			$(this).dequeue();
		});
	}
	function back()
	{
		if(document.getElementById('subject').value != "")
			$("p.remove").text("　");

		if(document.getElementById('item5').value != "")
			$("p.remove3").text("　");

		if(document.getElementById('progress').value != "")
			$("p.remove1").text("　");
	}
	function removeInactive()
	{
		$(".finish").removeClass("inactive");
	}
</script>
</head>

<body class="student">
	{include file='labo/menu.tpl'}
		<div id="main">
			<article>
				<h1>{t}予約詳細・変更{/t}</h1>
				<form method="POST" action="{$baseurl}/labo/updatereserve" name="updatereserve" id="updatereserve" enctype="multipart/form-data">
					<div class="hasDialog" id="basicSetting">
						<div class="sub">
							<input type="button" name="basicSettingButton" id="basicSettingButton" value="{t}▼ 選んでください{/t}">
						</div>
						<ul class="formSet" id="basicReceiver">
							<li>
								<label for="item1">{t}文書の種類{/t}</label>
								<div class="bezel">
									<span class="selected fromSelector" data-source="basic-doctype" id="v-basic-doctype">{$doctype}</span>
									<input type="hidden" name="pre-doctype" id="pre-doctype" value="{$shift->m_dockind_id}">
									<input type="hidden" name="item1" id="item1" value="{$shift->m_dockind_id}">
								</div>
							</li>
							<li>
								<label for="item2">{t}相談場所{/t}</label>
								<div class="bezel">
									<span class="selected fromSelector" data-source="basic-place" id="v-basic-place">{$place}</span>
									<input type="hidden" name="item2" id="item2" value="{$shift->m_place_id}">
								</div>
							</li>
							<li>
								<label for="item3">{t}相談日時{/t}</label>
								<div class="bezel">
									<span class="selected fromInput" data-source="basic-date" id="v-basic-date"></span>
									<input type="hidden" name="item3" id="item3" value="{$reserve->reservationdate}">
								</div>
							</li>
							<input type="hidden" name="reserveid" id="reserveid">
							<input type="hidden" name="shiftid" id="shiftid" value="{$reserve->m_shift_id}">
							<input type="hidden" name="submitdate" id="submitdate" value="{$reserve->submitdate}">

							<!--
							ダイアログにて、変更箇所を赤文字にするため、変更前データを保持
							-->
							<input type="hidden" name="defaultitem0" id="defaultitem0" value="">
							<input type="hidden" name="defaultitem1" id="defaultitem1" value="{$doctype}">
							<input type="hidden" name="defaultitem2" id="defaultitem2" value="{$place}">
							<input type="hidden" name="defaultitem3" id="defaultitem3" value="{$subject}">
							<input type="hidden" name="defaultitem4" id="defaultitem4" value="">
							<input type="hidden" name="defaultitem5" id="defaultitem5" value="{$progresssal[$reserve->progress]}">
						</ul>
						<div id="basicSettingDialog" class="dialog">
							<i class="closeButton cancel"></i>
							<div class="sub">{t}文書の種類/相談場所/日時を選択{/t}</div>
							<ul class="formSet">
								<li class="control">
								<label for="basic-doctype">{t}文書の種類{/t}</label>
									<div class="bezel selectMirror">
										<span class="selecd" id="dc" data-source="basic-doctype">{$doctype}</span>
										<div class="control">
											<input type="button" value="{t}▼ 選んでください{/t}" class="select" id="dockind">
											<ul class="options">
												{foreach from=$dockinds item=dockinditem name=dockinds}
													<li data-value="{$dockinditem['id']}" id="docid{$smarty.foreach.dockinds.index}" class="li_dockind li_dialog">{$dockinditem['document_category']}</li>
												{/foreach}
											</ul>
											<input type="hidden" name="basic-doctype" id="basic-doctype" class="valueInput" value="">
										</div>
									</div>
								</li>
								<li class="control">
								<label for="basic-place">{t}相談場所{/t}</label>
									<div class="bezel selectMirror">

										{if {$baseurl} == "/kwl"}
										<span class="selecd" data-source="basic-place" id="pl">{$place}</span>
										<div class="control">
											<input type="button" value="{t}▼ 選んでください{/t}" class="select" id="place">
											<ul class="options">
												{foreach from=$places item=placeitem name=places}
													<li data-value="{$placeitem['id']}" id="plaid{$smarty.foreach.places.index}" class="li_place li_dialog">{$placeitem['consul_place']}</li>
												{/foreach}
											</ul>
											<input type="hidden" name="basic-place2" id="basic-place" class="valueInput" value="">
										{/if}

										</div>
									</div>
								</li>
							</ul>

							<div class="outer" id="loading" style="text-align:center; padding:50px 0px 50px 0px;">
								<img src="/images/loading.gif" />
							</div>

							<div class="shiftCalendar">
								<label for="basic-date">{t}相談日時{/t}</label>
								<div class="container" id="item3Set">
									<div class="pager" id="shiftPager"></div>
									<table id="shiftTable"></table>
								</div>
								<input type="hidden" name="basic-date" id="basic-date" value="">
								<div class="explain">
									<img class="dlog" src="/image/overtime.jpg" alt="{t}予約可能時間外{/t}" width="30" height="27"> <i>{t}予約可能時間外{/t}</i>
									<img class="dlog" src="/image/Settled.jpg" alt="{t}予約済み{/t}" width="30" height="27"> <i>{t}予約済み{/t}</i>
									<img class="dlog" src="/image/filledcapacity.jpg" alt="{t}予約枠なし{/t}" width="30" height="27"> <i>{t}予約枠なし{/t}</i>
								</div>
							</div>
						</div>
					</div>
					<div>
						<ul class="formSet">
							<li>
								<label for="item4">{t}授業科目{/t}</label>
								<div class="bezel selectMirror">
								{if !empty($subject)}
									<span class="selected" id="subject"><p class="remove" id="delete4">　</p>{$subject}</span>
								{else}
									<span class="selected" id="subject"></span>
								{/if}
									<div class="control">
										<input type="button" value="{t}▼ 選んでください{/t}" class="select">
										<ul class="options">
											{foreach from=$subjects item=subjectitem name=subjects}
												<li data-value="{$subjectitem['jwaricd']}" onclick="page(); ite4();"><span class="head">{$subjectitem['yogen']}　</span>{$subjectitem['class_subject']}</li>
											{/foreach}
										</ul>
										<input type="hidden" name="item4" id="item4" class="valueInput" value="{$reserve->jwaricd}">
									</div>
								</div>
							</li>
							<li>
								<label for="item5">{t}提出日{/t}</label>
								<div class="bezel">
									<!-- <div class="control calendarSet"> -->
									<div class="calendarSet">
										<p class="remove3" id="delete5"></p><input type="text" id="item5" class="view" readonly="readonly" value="" onchange="changeday()"><br>
									</div>
								</div>

							</li>
							<li>
								<label for="item6">{t}進行状況{/t}</label>
								<div class="bezel selectMirror">
								{if $reserve->progress > 0}
									<span class="selected" id="progress"><p class="remove1" id="delete6">　</p>{$progresssal[$reserve->progress]}</span>
								{else}
									<span class="selected" id="progress"></span>
								{/if}
									<div class="control">
										<input type="button" value="{t}▼ 選んでください{/t}" class="select">
										<ul class="options">
											{foreach from=$progresssal key=k item=v name=progresssal}
											<li data-value="{$k}" onclick="page(); ite6();">{$v}</li>
											{/foreach}
										</ul>
										<input type="hidden" name="item6" id="item6" class="valueInput" value="{$reserve->progress}">
									</div>
								</div>
							</li>
							<li>
								<label for="item7">{t}添付ファイル{/t}</label>
								<div class="bezel fileup">
									<div class="replaceButton">
									<!--.replaceButton クラスでは.viewを透明化した.substanceで覆って入力を実現する-->
										<i class="view">{t}ファイルを選択{/t}</i><input type="file" class="substance" id="item7" name="item7[]" onclick="page()" onchange="removeInactive();">
									</div>
									{if !empty($reservefiles) && count($reservefiles) > 0}
									<ul class="MultiFile-existing" id="existingfiles">
										{foreach from=$reservefiles item=reservefile name=reservefiles}
											<li class="MultiFile-label" id="reservefile{$reservefile['id']}">
												<a class="MultiFile-remove" href="javascript:void(0);" onclick="removeFile({$reservefile['id']});page();">x</a>
												<a href="{$baseurl}/{$controllerName}/download/id/{$reservefile['t_files_id']}" class="MultiFile-title">{$vhHtmlOut->escape($reservefile['t_files_name'])}</a>
												<input type="hidden" name="keepfile[]" value="{$reservefile['id']}">
											</li>
										{/foreach}
									</ul>
									{/if}
								</div>
							</li>
						</ul>
					</div>
					<div class="freetext">
						<label for="item8">{t}相談したいこと{/t}</label>
						<div class="countText">
							<span><textarea name="item8" id="item8" cols="42" rows="4" maxlength="250" onfoucus=" page()">{$reserve->question}</textarea></span>
							<div class="counter"><span id="counter">0</span>/250</div>
						</div>
					</div>
					<div id="pageControl">
						<div class="buttonSet dubble">
							<a class="delete" style="width: 150px; padding: 10px 20px; margin-right: 20px;" onclick="clean()">{t}予約の取り消し{/t}</a>
							<a class="finish inactive" style="width: 150px; padding: 10px 20px;" onclick="clean()">{t}変更の保存{/t}</a>
						</div>
						<div id="finishDialog" class="dialog">
							<i class="closeButton cancel" ></i>
							<div class="sub">{t}以下の情報で予約を変更します{/t}</div>
							<ul class="formSet">
								<li>
									<span class="label">{t}相談日時{/t}</span>
									<div class="bezel">
										<span class="selected" data-get=".selected:eq(2)"></span>
									</div>
								</li>

								<li>
									<span class="label">{t}文書の種類{/t}</span>
									<div class="bezel">
										<span class="selected" data-get=".selected:eq(0)"></span>
									</div>
								</li>

								<li>
									<span class="label">{t}相談場所{/t}</span>
									<div class="bezel">
										<span class="selected" data-get=".selected:eq(1)"></span>
									</div>
								</li>

								<li>
									<span class="label">{t}授業科目{/t}</span>
									<div class="bezel">
										<span class="selected" data-get=".selected:eq(3)"></span>
									</div>
								</li>

								<li>
									<span class="label">{t}提出日{/t}</span>
									<div class="bezel">
										<span class="selected" data-get="#item5"></span>
									</div>
								</li>

								<li>
									<span class="label">{t}進行状況{/t}</span>
									<div class="bezel">
										<span class="selected" data-get=".selected:eq(4)"></span>
									</div>
								</li>

							</ul>
							<div class="buttonSet dubble">
								<a class="affirm" onclick="submitData();">{t}OK{/t}</a>
								<a class="cancel" onClick="back()">{t}キャンセル{/t}</a>
							</div>
						</div>
						<div id="compDialog" class="dialog">
							<div class="cmpsub">{t}変更が反映されました。{/t}</div>
							<div class="buttonSet single">
								<!-- <a href="{$baseurl}/{$controllerName}/editreserve/reserveid/{$reserve->id}" class="affirm">OK</a> -->
							</div>
						</div>
						<div id="delDialog" class="dialog">
							<i class="closeButton cancel" onClick="back()"></i>
							<div class="sub">{t}この予約を取り消しますか？{/t}</div>
							<ul class="formSet">
								<li>
									<span class="label">{t}相談日時{/t}</span>
									<div class="bezel">
										<span class="selected" data-get=".selected:eq(2)"></span>
									</div>
								</li>

								<li>
									<span class="label">{t}文書の種類{/t}</span>
									<div class="bezel">
										<span class="selected" data-get=".selected:eq(0)"></span>
									</div>
								</li>

								<li>
									<span class="label">{t}相談場所{/t}</span>
									<div class="bezel">
										<span class="selected" data-get=".selected:eq(1)"></span>
									</div>
								</li>

								<li>
									<span class="label">{t}授業科目{/t}</span>
									<div class="bezel">
										<span class="selected" data-get=".selected:eq(3)"></span>
									</div>
								</li>

								<li>
									<span class="label">{t}提出日{/t}</span>
									<div class="bezel">
										<span class="selected" data-get="#item5"></span>
									</div>
								</li>

								<li>
									<span class="label">{t}進行状況{/t}</span>
									<div class="bezel">
										<span class="selected" data-get=".selected:eq(4)"></span>
									</div>
								</li>
							</ul>
							<div class="buttonSet dubble">
								<a onclick="submitCancel();" class="affirm"><i></i>{t}OK{/t}</a>
								<a href="#" class="cancel" onClick="back()">{t}キャンセル{/t}</a>
							</div>
						</div>
						<div id="delCompDialog" class="dialog">
							<div class="cmpsub">{t}予約を取り消しました。{/t}</div>
							<div class="buttonSet single">
								<!-- <a href="{$baseurl}/{$controllerName}/reserve" class="affirm">OK</a> -->
							</div>
						</div>
						
						<div id="dayDialog" class="dialog">
							<i class="closeButton cancel" onClick="cancel()"></i>
								<div class="sub">{t}提出日の確認{/t}</div>
								<p>{t}提出日が予約日より過去に設定されています。よろしいですか？{/t}</p>
								<div class="buttonSet dubble">
								<a href="#" class="affirm" onClick="ok();removeInactive()">{t}OK{/t}</a>
								<a href="#" class="cancel" onClick="cancel()">{t}キャンセル{/t}</a>
							</div>
						</div>
					</div>
				</form>
			</article>
		</div>
		
		<aside id="sidebar">
			<h1>{t}予約一覧{/t}</h1>
			<ul id="reservelist">
			</ul>
			<div class="pager" id="reservepager">
			</div>
		</aside>
	</div>
	
	{include file="../common/foot_v2.php"}
		
		<script>
			$(function(){
				
				$(".li_dockind").bind('click', function() {
					var dockind = document.getElementById('basic-doctype');
					var place = document.getElementById('basic-place');

					dockind.value = $(this).prop('data-value');

					if(dockind.value != "" && place.value != "")
					{
						$(this).delay(300).queue(function() {
							createShiftCalendar('{$baseurl}/{$controllerName}', '{$nendo}-03-31', '{$nowdate}', '{$reserveid}');
							$(this).dequeue();
						});
					}
				});

				$(".li_place").bind('click', function() {
					var dockind = document.getElementById('basic-doctype');
					var place = document.getElementById('basic-place');

					place.value = $(this).prop('data-value');

					if(dockind.value != "" && place.value != "")
					{
						$(this).delay(300).queue(function() {
							createShiftCalendar('{$baseurl}/{$controllerName}', '{$nendo}-03-31', '{$nowdate}', '{$reserveid}');
							$(this).dequeue();
						});
					}
				});
				
				$(".selectMirror").each(function(){
					$(this).selectMirror();
				});
				// 残り文字数の表示の仕方変更
				$('#item8').bind('keyup',function(){
					var tnum  = $(this).val().length;
					$('#counter').text(tnum);
					var tmax = 250 - tnum //250文字制限
					if(tmax > 0){
						$('#txtmax').text(tmax);
					}else{
						$('#counter').text("250");//文字数オーバーの際の挙動

					}
					page();
				});
				
				$('#item7').MultiFile({
					max:5, STRING: {
					duplicate:"{t}同じファイルが既に選択されています{/t}"
					}
				});

				$("#basicSettingButton").basicSetting();
				$("#pageControl").find(".finish").decisionDialog($("#finishDialog"),true);
				$("#pageControl").find(".delete").decisionDialog($("#delDialog"),true);
				
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
				changeFlg = false;
				$(window).on('beforeunload', function() {
					//変更がある場合のみ警告をだす
					if (changeFlg) {
						return "入力した情報が消えてしまいますがよろしいですか";
					}
				});

				$('#shiftTable').tooltip();

				$("#delete4").click(function () {
					$("#item4").remove();
					$("#subject").empty();
				    $("p.remove").empty();
				    page();
				    $(".finish").removeClass("inactive");
				});
				$("#delete5").click(function () {
					document.getElementById('item5').value = "";
					$("p.remove3").empty();
					$("#submitdate").val('');
					 page();
					 $(".finish").removeClass("inactive");
				});
				$("#delete6").click(function () {
					$("#item6").remove();
					$("#selected6").empty();
					$('#progress').empty();
					$("p.remove1").empty();
					page();
					$(".finish").removeClass("inactive");
				});

				$("#item8").bind('input propertychange', function() {
					$(".finish").removeClass("inactive");
				});

				$(".replaceButton").each(function(){
					$(this).replaceButton();
				});
				$("#loginStatusTrigger").miniMenu($("#loginStatus"));

				$('#updatereserve').submit(function(event) {
					event.preventDefault();	// 本来のsubmit処理をキャンセル
					var $form = $(this);
					var fd = new FormData($form[0]);

					$.ajax({
						async: false,				// 同期通信
						url: $form.attr('action'),
						type: $form.attr('method'),
						//data: $form.serialize(),	// ファイルアップロード時はシリアライズしない
						//dataType: 'json',			// 特に指定する必要はない
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
								alert(response['error']);
							}
							else
							{	// 成功
								changeFlg = false;

								$(this).delay(2200).queue(function() {
									location.reload();
									$(this).dequeue();
								});
								$("#finishDialog").find(".affirm").decisionDialog($("#compDialog"));
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