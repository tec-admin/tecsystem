<!doctype html>
<html lang="ja">
<head>
<!--
	{t}既に自分の予約が入っています{/t}
	{t}既に同じ内容の予約が入っています。ページを更新してください。{/t}
	{t}指定された予約枠に空きが無くなりました。文書の種類/相談場所/日時を再度選択してください。{/t}
	{t}予約可能時間外です{/t}
	{t}予約の枠がありません{/t}
	{t}★★★★★　ほぼ完成した{/t}
	{t}★★★★　　ひと通り書いた{/t}
	{t}★★★　　　半分くらい書いた{/t}
	{t}★★　　　　ちょっと書いた{/t}
	{t}★　　　　　まだ書いていない{/t}
-->
{include file='labo/header.tpl'}
<script src="/js/base.share.js?{($smarty.now|date_format:"%d%H%M%S")}" type="text/javascript"></script>
<script src="/js/base.reserve.js?{($smarty.now|date_format:"%d%H%M%S")}" type="text/javascript"></script>
<script src="/js/base.shiftcalendar.{$apptype}.js?{($smarty.now|date_format:"%d%H%M%S")}" type="text/javascript"></script>
<script>

	function cancel()
	{
		$("#dayDialog").bPopup().close();
		//alert(document.getElementById('item5').value);
		document.getElementById('item5').value = "";
		//alert(document.getElementById('item5').value);
		$('.ui-datepicker-trigger').trigger("click");
		$("p.remove3").text("");
	}
	function ok()
	{
		$("#dayDialog").bPopup().close();
	}
	window.onload = function()
	{
		$("#loading").hide();
		$(".shiftCalendar").hide();

		createReserveList('{$baseurl}/{$controllerName}', {if empty($reserveid)}0{else}$reserveid{/if});
	}

	function closesetting(string, rdate, shiftid)
	{
		document.getElementById('shiftid').value = shiftid;

		// 下記はダイアログ側で自動的に設定される
		// item1 = dockind
		// item2 = place
		// item3 = rdate
		$("#basicSettingDialog").setClose(string, rdate);
		changeFlg = true;
	}
	function changeday()
	{
		$("p.remove3").text("　");
		// 提出日が過去日の場合の確認ダイアログ
		var submitdate = document.getElementById('item5').value;
		submitdate = submitdate.split("/").join("-");
		document.getElementById('submitdate').value = submitdate;
		var item3 = document.getElementById('item3').value;
		item3 = item3.split("/").join("-");
		document.getElementById('item3').value = item3;
		if (submitdate < item3){
			$("#dayDialog").bPopup();
		}
		document.getElementById('item5').value = dateFormat(submitdate, 'Y/m/d(wj)', false, true);
	}
	
	var f_clicked = 0;
	
	function submitData()
	{
		// 二重クリック禁止
		if(f_clicked == 1){
			return false;
		}
		f_clicked = 1;
		
		// placeholder対策
		var tempText = document.getElementById('item8').value;
		if(document.getElementById('item8').value == "{t}例）レポートの書き方がわからないので、基本的なことを教えてほしい。{/t}"){
			document.getElementById('item8').value = '';
		}

		$('#newreserve').submit();
	}
	function Calendar()
	{
		var dockind = document.getElementById('basic-doctype');

		if(dockind != "")
		{
			$(this).delay(300).queue(function() {
				createShiftCalendar('{$baseurl}/{$controllerName}', '{$nendo}-03-31', '{$nowdate}');
				$(this).dequeue();
			});
		}
	}

	function ite4(){
		$(this).delay(10).queue(function() {
			$("#selected4").prepend("<p class='remove' id='delete4'>　</p>");
			$("#delete4").click(function () {
				$("#item4").remove();
				$("#selected4").empty();
			    $("p.remove").empty();
			});
			$(this).dequeue();
		});
	}
	function ite6(){
		$(this).delay(10).queue(function() {
			$("#selected6").prepend("<p class='remove1' id='delete6'>　</p>");
			$("#delete6").click(function () {
				$("#item6").remove();
				$("#selected6").empty();
				$("p.remove1").empty();
			});
			$(this).dequeue();
		});
	}
</script>
</head>

<body class="student">
	{include file='labo/menu.tpl'}
		<div id="main">
			<article>
				<h1>{t}新規予約{/t}</h1>
				<form method="POST" action="{$baseurl}/{$controllerName}/newreserve" name="newreserve" id="newreserve" enctype="multipart/form-data">
					<div class="hasDialog" id="basicSetting">
						<div class="sub">
							<!--<label for="basicSetting">相談の基本設定</label>--><input type="button" name="basicSettingButton" id="basicSettingButton" value="{t}▼ クリック！{/t}">
						</div>
						<ul class="formSet" id="basicReceiver">
							<li><label for="item1">{t}文書の種類{/t}</label><div class="bezel"><span class="selected fromSelector" data-source="basic-doctype"></span><input type="hidden" name="item1" id="item1"></div></li>
							<li><label for="item2">{t}相談場所{/t}</label><div class="bezel"><span class="selected fromSelector" data-source="basic-place"></span><input type="hidden" name="item2" id="item2"></div></li>
							<li><label for="item3">{t}相談日時{/t}</label><div class="bezel"><span class="selected fromInput" data-source="basic-date"></span><input type="hidden" name="item3" id="item3"></div></li>
							<input type="hidden" name="shiftid" id="shiftid">
							<input type="hidden" name="submitdate" id="submitdate">
						</ul>
						<div id="basicSettingDialog" class="dialog">
							<i class="closeButton cancel"></i>
							<div class="sub">{t}文書の種類/相談場所/日時を選択{/t}</div>
							<ul class="formSet">
								<li class="control">
								<label for="basic-doctype">{t}文書の種類{/t}</label>
									<div class="bezel selectMirror" style="height: 26px;">
										<span class="selecd" id="dc"></span>
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
									<div class="bezel selectMirror" style="height: 26px;">

										{if $places_cnt == "1"}
											{foreach from=$places item=placeitem name=places}
												<span class="selecd" id="pl">{$placeitem['consul_place']}</span>
												<div class="control">
													<input type="hidden" name="basic-place2" id="basic-place" value="{$placeitem['id']}">
												</div>
											{/foreach}
										{else}
										<span class="selecd" id="pl"></span>
										<div class="control">
											<input type="button" value="{t}▼ 選んでください{/t}" class="select" id="place">
											<ul class="options">
												{foreach from=$places item=placeitem name=places}
													<li data-value="{$placeitem['id']}" id="plaid{$smarty.foreach.places.index}" class="li_place li_dialog">{$placeitem['consul_place']}</li>
												{/foreach}
											</ul>
											<input type="hidden" name="basic-place2" id="basic-place" class="valueInput" value="">
										</div>
										{/if}
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
						<ul class="formSet inactive">
							<li>
								<label for="item4">{t}授業科目{/t}</label>
								<div class="bezel selectMirror">
									<span class="selected" id="selected4"></span>
									<div class="control">
										<input type="button" value="{t}▼ 選んでください{/t}" class="select" >
										<ul class="options">
											{foreach from=$subjects item=subjectitem name=subjects}
											<li data-value="{$subjectitem['jwaricd']}" onclick="ite4()"><span class="head">{$subjectitem['yogen']}　</span>{$subjectitem['class_subject']}</li>
											{/foreach}
										</ul>
										<input type="hidden" name="item4" id="item4" class="valueInput" value="">
									</div>
								</div>
							</li>
							<li>
								<label for="item5">{t}提出日{/t}</label>
								<div class="bezel">
									<!--<span class="selected"></span>-->
									<div class="calendarSet">
										<p class='remove3' id='delete5'></p><input type="text" id="item5" class="view" name="time" readonly="readonly" value="" onchange="changeday()"><br>
									</div>
								</div>
							</li>
							<li>
								<label for="item6">{t}進行状況{/t}</label>
								<div class="bezel selectMirror">
									<span class="selected" id="selected6"></span>
									<div class="control">
										<input type="button" value="{t}▼ 選んでください{/t}" class="select">
										<ul class="options">
											{foreach from=$progresssal key=k item=v name=progresssal}
											<li data-value="{$k}" onclick="ite6()">{$v}</li>
											{/foreach}
										</ul>
										<input type="hidden" name="item6" id="item6" class="valueInput">
									</div>
								</div>
							</li>
							<li>
								<label for="item7">{t}添付ファイル{/t}</label>
								<div class="bezel fileup">
									<div class="replaceButton">
									<!--.replaceButton クラスでは.viewを透明化した.substanceで覆って入力を実現する-->
										<i class="view">{t}ファイルを選択{/t}</i><input type="file" class="substance" id="item7" name="item7[]">
									</div>
								</div>
							</li>
						</ul>
					</div>
					<div class="freetext inactive">
						<label for="item8">{t}相談したいこと{/t}</label>
						<div class="countText">
							<span><textarea class="sou" cols="42" rows="4" maxlength="250" name="item8" id="item8" placeholder="{t}例）レポートの書き方がわからないので、基本的なことを教えてほしい。{/t}" onblur="if (this.value != '') style='color:#000'; else style='';"></textarea></span>
							<div class="counter"><span id="counter">0</span>/250</div>
						</div>
					</div>
					<div id="pageControl">
						<button class="finish inactive">{t}予約する{/t}</button>

						<div id="finishDialog" class="dialog">
							<i class="closeButton cancel"></i>
							<div class="sub">{t}予約しますか？{/t}</div>
							<div class="buttonSet dubble">
								<a href="#" onclick="submitData();" class="affirm">{t}OK{/t}</a>
								<a href="#" class="cancel">{t}キャンセル{/t}</a>
							</div>
						</div>
						<div id="compDialog" class="dialog">
							<div class="cmpsub">{t}予約完了しました。右の予約一覧で確認できます。{/t}</div>
						</div>
						
						<div id="dayDialog" class="dialog">
							<i class="closeButton cancel" onClick="cancel()"></i>
							<div class="sub">{t}提出日の確認{/t}</div>
							<p>{t}提出日が予約日より過去に設定されています。よろしいですか？{/t}</p>
							<div class="buttonSet dubble">
							<a href="#" class="affirm" onClick="ok()">{t}OK{/t}</a>
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
				<!--
				<a class="prev inactive hidden" href="#">{t}前の10件{/t}</a>
				<a class="next inactive hidden" href="#">{t}次の10件{/t}</a>
				-->
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
							createShiftCalendar('{$baseurl}/{$controllerName}', '{$nendo}-03-31', '{$nowdate}');
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
							createShiftCalendar('{$baseurl}/{$controllerName}', '{$nendo}-03-31', '{$nowdate}');
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
				});

				$('#item7').MultiFile({
					max:5, STRING: {
					duplicate:"{t}同じファイルが既に選択されています{/t}"
					}
				});

				$('#shiftTable').tooltip();
				$("#delete4").click(function () {
					$("#item4").remove();
					$("#subject").empty();
				    $("p.remove").empty();
				});
				$("#delete5").click(function () {
					document.getElementById('item5').value = "";
					$("p.remove3").empty();
				});
				$("#delete6").click(function () {
					$("#item6").remove();
					$("#selected6").empty();
					$("p.remove1").empty();
				});

//				$(".countText").each(function(){
//					{literal}
//					$(this).textAreaCD({"max":250});
//					{/literal}
//				});
				$("#basicSettingButton").basicSetting();
				$("#pageControl").find(".finish").decisionDialog($("#finishDialog"));
				$(".replaceButton").each(function(){
					$(this).replaceButton();
				});
				$("#loginStatusTrigger").miniMenu($("#loginStatus"));

				changeFlg = false;
				$(window).on('beforeunload', function() {
					//変更がある場合のみ警告をだす
					if (changeFlg) {
						return "入力した情報が消えてしまいますがよろしいですか";
					}
				});

				$('#newreserve').submit(function(event) {
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

								// 完了後の飛び先を設定
								var link = '{$baseurl}/{$controllerName}/editreserve/reserveid/' + response['success'];
								//var comp = document.getElementById('complocation');
								//while (comp.firstChild)
								//	comp.removeChild(comp.firstChild);
								//var loc = document.createElement('a');
								//loc.setAttribute('class', 'affirm');
								//loc.setAttribute('href', link);
								//loc.appendChild(document.createTextNode('OK'));
								//comp.appendChild(loc);
								changeFlg = false;
								// 完了ダイアログ
								$("#finishDialog").find(".affirm").decisionDialog($("#compDialog"));
								$(this).delay(2200).queue(function() {
									window.location.href=link;
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