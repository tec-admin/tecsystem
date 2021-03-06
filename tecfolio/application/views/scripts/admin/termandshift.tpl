<!doctype html>
<html lang="ja">
<head>
<!--
	{t}学期名称を入力してください{/t}
	{t}学期期間を入力してください{/t}
	{t}シフト入力許可期間を入力してください{/t}
-->
{include file='admin/header.tpl'}
	<style type="text/css">
	.hidden{
		display: none;
	}
	table#term_table{
		width: 70%;
		min-width: 680px;
		margin-left: auto;
    	margin-right: auto;
	}
	#terminput > tr > td{
		text-align: center;
	}
	th.term_name{
		width: 100px;
	}
	th.term_range, th.shift_range{
		width: 350px;
	}
	th.add_new_line{
		width: 200px;
	}
	td > input.range{
		width: 72px;
	}
	td > input.name{
		width: 72px;
	}
	td > input.uneditable{
		text-align: center;
		//pointer-events: none;
		border-width: 0px;
		border-style: none;
		margin: 2px;	// borderを消した分のmargin
	}
	</style>
	<script>
	// 新規に追加する仮のtermidとして扱う
	var nextid = {$nextid};
	
	// 既存データの日付の最大値：新規データの日付の最小値として設定する
	var term_maxdate;
	
	function selectYear(year)
	{
		var link = '{$baseurl}/{$controllerName}/termandshift/year/' + year;
		document.location = link;
	}
	
	function submitData()
	{
		$('#updateterm').submit();
	}
	
	function setNextId()
	{
		var tmp = 0
		$('tr.line').each(function(index) {
			var num = Number($(this).prop('id').substr(4));
			if(num >= tmp)
				tmp = num;
		});
		
		nextid = tmp + 1;
	}
	
	// 
	function oneLineAtLeast()
	{
		var cnt = 0;
		$('tr.line').each(function(){
			cnt++;
		});
		
		if(cnt == 0)
		{
			var add_button = document.getElementById('add_blank');
			add_button.click();
			
			$('td.add_new_line > input.add').removeClass('hidden');
			$('td.add_new_line > input.remove').addClass('hidden');
		}
	}
	
	// Datepickerとその最小・最大値を設定する
	// @param	scope	設定するスコープ
	// @return			なし
	function setMinandMaxValues(scope)
	{
		updateMaxVal();
		
		$(scope).each(function(){
				
			var num = $(this).parent().parent().find('.hidden > input').prop('value');
			
			if($(this).hasClass('term_start'))
			{
				var prev_enddate = $(this).parent().parent().prev().find('.term_range > input.term_end');
				
				$(this).datepicker({
					dateFormat: 'yy-mm-dd',//年月日の並びを変更
					minDate: prev_enddate.prop('value') != undefined && prev_enddate.prop('value') != '' ? datePlusTime(prev_enddate.prop('value'), 86400000) : '2000-01-01',
					maxDate: $('#term_enddate' + num).val(),
					changeMonth: true, //月をドロップボックスで選択化
					onSelect: function(dateText, inst){
						updateMaxVal();
						
						$('#term_enddate' + num).datepicker('option', 'minDate', dateText);
						$(this).parent().parent().prev().find('.term_range > input.term_end').datepicker('option', 'maxDate', datePlusTime(dateText, -86400000));		// onSelect内はprev_enddateのスコープ外
					}
				});
			}
			else if($(this).hasClass('term_end'))
			{
				var next_startdate = $(this).parent().parent().next().find('.term_range > input.term_start');
				
				$(this).datepicker({
					dateFormat: 'yy-mm-dd',//年月日の並びを変更
					minDate: $('#term_startdate' + num).prop('value') != '' ? $('#term_startdate' + num).val() : $('#term_startdate' + num).datepicker('option', 'minDate'),
					maxDate: next_startdate.prop('value') != undefined && next_startdate.prop('value') != '' ? datePlusTime(next_startdate.prop('value'), -86400000) : '2100-12-31',	// 設定する必要がなければ適当な最大値を設定
					changeMonth: true, //月をドロップボックスで選択化
					onSelect: function(dateText, inst){
						updateMaxVal();
						
						$('#term_startdate' + num).datepicker('option', 'maxDate', dateText);
						$(this).parent().parent().next().find('.term_range > input.term_start').datepicker('option', 'minDate', datePlusTime(dateText, 86400000));		// onSelect内はnext_enddateのスコープ外
					}
				});
			}
			else if($(this).hasClass('shift_start'))
			{
				$(this).datepicker({
					dateFormat: 'yy-mm-dd',//年月日の並びを変更
					maxDate: $('#shift_enddate' + num).prop('value') != '' ? $('#shift_enddate' + num).val() : '2100-12-31',
					changeMonth: true, //月をドロップボックスで選択化
					onSelect: function(dateText, inst){
						$('#shift_enddate' + num).datepicker('option', 'minDate', dateText);
					},
					defaultDate: $('#term_startdate' + num).datepicker('option', 'minDate') > $('#term_startdate' + num).datepicker('option', 'defaultDate') ? $('#term_startdate' + num).datepicker('option', 'minDate') : $('#term_startdate' + num).datepicker('option', 'defaultDate'),
				});
			}
			else if($(this).hasClass('shift_end'))
			{
				$(this).datepicker({
					dateFormat: 'yy-mm-dd',//年月日の並びを変更
					minDate: $('#shift_startdate' + num).prop('value') != '' ? $('#shift_startdate' + num).val() : '1900-12-31',
					changeMonth: true, //月をドロップボックスで選択化
					onSelect: function(dateText, inst){
						$('#shift_startdate' + num).datepicker('option', 'maxDate', dateText);
					},
					defaultDate: $('#term_enddate' + num).datepicker('option', 'minDate') > $('#term_enddate' + num).datepicker('option', 'defaultDate') ? $('#term_enddate' + num).datepicker('option', 'minDate') : $('#term_enddate' + num).datepicker('option', 'defaultDate'),
				});
			}
		});
	}
	
	// 日付に時間を加算し、文字列として返す
	// @param	date	解析(Date.parse)可能な形式の日付
	// @param	time	時間(ミリ秒)
	// @return			日付を表す文字列(例:2015-01-01 ※月日は必ず2桁)
	function datePlusTime(date, time)
	{
		var tmp_time = Date.parse(date);
		var tmp_date = new Date();
		tmp_date.setTime(tmp_time + time);
		
		var month	= zeroFill(tmp_date.getMonth() + 1, 2);
		var date	= zeroFill(tmp_date.getDate(), 2);
		
		var str = tmp_date.getFullYear()  + "-" + month + "-" + date;
		
		return str;
	}
	
	// 数値を0で埋める
	// @param	val		0で埋める値
	// @param	len		0で埋めた後の長さ
	// @return			値valを元に、長さlenに調整(0埋め)された文字列
	function zeroFill(val, len)
	{
		var tmp = String(val).length;
		
		if(len > tmp)
			return (new Array((len - tmp) + 1).join(0)) + val;
		else
			return val;
	}
	
	// 全行での日付の最大値を設定し、必要に応じて各inputの最小値とする
	function updateMaxVal()
	{
		var tmp;
		$('.term_end').each(function(){
			if(tmp == undefined || Date.parse(tmp) < Date.parse($(this).prop('value')))
				tmp = $(this).prop('value');
		});
		
		term_maxdate = tmp;
		
		// 追加されてから空のinputに対して最小値を更新する
		$('.added').each(function(){
			if(Date.parse($(this).datepicker('option', 'minDate')) > Date.parse(term_maxdate))
				$(this).datepicker('option', 'minDate', datePlusTime(term_maxdate, 86400000));
		});
	}
	
	function checkNull(line)
	{
		if(line == nextid - 1)
		{
			var num = 1;
			$('#line' + line + ' > td > input').each(function(){
				if($(this).prop('value') == '')
					num = 0;
			});
			
			if(num != 0)
			{
				$('#line' + line + ' > td.add_new_line > input.add').removeClass('hidden');
			}
			else
			{
				if(!$('#line' + line + ' > td.add_new_line > input.add').hasClass('hidden'))
					$('#line' + line + ' > td.add_new_line > input.add').addClass('hidden');
			}
		}
	}
	
	function countLine()
	{
		var cnt = 0;
		$('.line').each(function() {
			cnt++;
		});
		
		return cnt++;
	}
	function addYear(year)
	{
		//$("#addConfirm").html((year + 1) + '年度のデータを追加しますか？');
		$("#addConfirm").html('{t 1=$latest_year + 1}%1年度のデータを追加しますか？{/t}');
		var d = $("#addDialog");
		d.bPopup({
			closeClass:"cancel",
			modalColor:"#ffffff",
			opacity: 0.5,
			transitionClose: "fadeIn",
			zIndex: 110
		});
		d.find(".affirm, .delete").click(function(){
			d.bPopup().close();
		});
	}

	</script>
</head>
<body class="admin">
	{include file='admin/menu.tpl'}
		<div id="main">
			<article class="calendar">
			<h1>{t}学期/シフト入力許可設定{/t}</h1>
				<div id="termandshift">
					<div class="container">
						<div class="facility">
							<div class="bezel selectMirror">
								<span class="selected" id="selectyear"><span class="sel_before">{t}年度選択：{/t}</span>{$year_selected}</span>
								<div class="control">
									<input class="select" type="button" value="{t}▼ 選んでください{/t}">
									<ul class="options">
									{*{foreach from=$years item=year name=years}
										<li onclick="selectYear({$year->year})" data-value="{$year->year}">{$year->year}</li>
									{/foreach}*}
									{assign var="i" value=0}
									{for $i=-1 to ($latest_year - $this_year)}
										<li onclick="selectYear({$this_year + $i})" data-value="{$this_year + $i}">{$this_year + $i}</li>
									{/for}
									<li onclick="addYear({$latest_year})" id="selectAdd">{t}年度追加{/t}</li>
									</ul>
								</div>
							</div>
						</div>
						<input id="add_blank" type="button" class="add" style="display:none;" />
						<form method="POST" action="{$baseurl}/{$controllerName}/updateterm" name="updateterm" id="updateterm" enctype="multipart/form-data">
							<input id="year" type="text" name="year" value="{$year_selected}" style="display:none;" />
							<div class="term">
								<table id="term_table">
									<thead>
										<tr>
											<th class="term_name" style="text-align: center;">{t}名称{/t}</th>
											<th class="term_range" style="text-align: center;">{t}学期期間{/t}</th>
											<th class="shift_range" style="text-align: center;">{t}シフト入力許可期間{/t}</th>
											<th class="add_new_line" style="text-align: center;">{t}追加{/t}</th>
										</tr>
									</thead>
									<tbody id="terminput">
										{foreach from=$terms item=term name=terms}
											<tr class="line" id="line{$term->id}">
												<td class="hidden">
													<input id="termid{$term->id}" type="text" name="termid[{$term->id}]" value="{$term->id}" style="display:none;" />
												</td>
												<td class="term_name">
													<input class="name" id="term_name{$term->id}" type="text" name="term_name[{$term->id}]" value="{$term->name}" />
												</td>
												<td class="term_range">
													<input class="default range term_start" id="term_startdate{$term->id}" type="text" name="term_startdate[{$term->id}]" value="{$term->startdate}" readonly />
													～
													<input class="default range term_end{if $smarty.foreach.terms.last} last{/if}" id="term_enddate{$term->id}" type="text" name="term_enddate[{$term->id}]" value="{$term->enddate}" readonly />
												</td>
												<td class="shift_range">
													<input class="default range shift_start" id="shift_startdate{$term->id}" type="text" name="shift_startdate[{$term->id}]" value="{$term->shift_startdate}" readonly />
													～
													<input class="default range shift_end" id="shift_enddate{$term->id}" type="text" name="shift_enddate[{$term->id}]" value="{$term->shift_enddate}" readonly />
												</td>
												<td class="add_new_line">
													<input class="default add{if !$smarty.foreach.terms.last} hidden{/if}" id="add_button{$term->id}" type="button" value="＋" />
													{if $flg[$term->id] == 0}<input class="default remove" id="remove_button{$term->id}" type="button" value="－" />{/if}
												</td>
											</tr>
										{/foreach}
									</tbody>
								</table>
							</div>
						
							<div id="pageControl">
								<input type="button" value="{t}登録{/t}" class="finish">
								<div id="finishDialog" class="dialog">
									<i class="closeButton cancel"></i>
									<div class="sub">{t}更新しますか？{/t}</div>
									<div class="buttonSet dubble">
										<a href="#" onclick="submitData()" class="affirm">{t}OK{/t}</a>
										<a href="#" class="cancel">{t}キャンセル{/t}</a>
									</div>
								</div>
								<div id="addDialog" class="dialog">
									<i class="closeButton cancel"></i>
									<div class="sub" id="addConfirm"></div>
									<div class="buttonSet dubble">
										<a href="#" id="addYearOK" class="affirm">{t}OK{/t}</a>
										<a href="#" class="cancel">{t}キャンセル{/t}</a>
									</div>
								</div>
	
								<div id="compDialog" class="dialog">
									<div class="cmpsub">{t}更新が完了しました。{/t}</div>
								</div>
								<div id="addCompDialog" class="dialog">
									<div class="cmpsub">{t}追加が完了しました。{/t}</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</article>
		<!--/#main--></div>
		<aside id="syssidebar">

        </aside>
      </div>
		{include file="../common/foot_v2.php"}
		
		<script>
		// ＋の処理：下に新しい行を追加する
		$(document).on("click", ".add", function()
		{
			var tbody = document.getElementById('terminput');
		
			var tr = document.createElement('tr');
			
			var td_hidden = document.createElement('td');
			var td_term_name = document.createElement('td');
			var td_term_range = document.createElement('td');
			var td_shift_range = document.createElement('td');
			var td_add_new_line = document.createElement('td');
			
			tr.setAttribute('class', 'line');
			tr.setAttribute('id', 'line' + nextid);
				td_hidden.setAttribute('class', 'hidden');
					td_hidden.innerHTML = '<input id="termid' + nextid + '" type="text" name="termid[' + nextid + ']" value="' + nextid + '" style="display:none;" />';
				tr.appendChild(td_hidden);
				
				td_term_name.setAttribute('class', 'term_name');
					td_term_name.innerHTML = '<input class="name" id="term_name' + nextid + '" type="text" name="term_name[' + nextid + ']" value="" />';
				tr.appendChild(td_term_name);
				
				td_term_range.setAttribute('class', 'term_range');
					td_term_range.innerHTML = '<input class="range term_start added" id="term_startdate' + nextid + '" type="text" name="term_startdate[' + nextid + ']" value="" />';
					td_term_range.innerHTML += ' ～ ';
					td_term_range.innerHTML += '<input class="range term_end added" id="term_enddate' + nextid + '" type="text" name="term_enddate[' + nextid + ']" value="" />';
				tr.appendChild(td_term_range);
				
				td_shift_range.setAttribute('class', 'shift_range');
					td_shift_range.innerHTML = '<input class="range shift_start" id="shift_startdate' + nextid + '" type="text" name="shift_startdate[' + nextid + ']" value="" />';
					td_shift_range.innerHTML += ' ～ ';
					td_shift_range.innerHTML += '<input class="range shift_end" id="shift_enddate' + nextid + '" type="text" name="shift_enddate[' + nextid + ']" value="" />';
				tr.appendChild(td_shift_range);
				
				td_add_new_line.setAttribute('class', 'add_new_line');
					if(countLine() < 3)
						td_add_new_line.innerHTML = '<input class="add" id="add_button' + nextid + '" type="button" value="＋" />';
					else
						td_add_new_line.innerHTML = '<input class="add hidden" id="add_button' + nextid + '" type="button" value="＋" />';
					
					td_add_new_line.innerHTML += '<input class="remove" id="remove_button' + nextid + '" type="button" value="－" />';
				tr.appendChild(td_add_new_line);
			tbody.appendChild(tr);

			// 押された追加buttonをhiddenにする
			$(this).parent().parent().find('.add_new_line > input.add').addClass('hidden');
			
			setNextId();
			
			setMinandMaxValues('.range');
		});
		
		// －の処理：この行を削除する
		$(document).on("click", ".remove", function()
		{
			var line = $(this).parent().parent();
			var linenum = $(this).parent().parent().prop('id').substr(4);
			
			var plus = 0;
			var minus = 0;
			$('td.add_new_line > input.add').each(function(){
				if(!$(this).hasClass('hidden'))
					plus++;
			});
			$('td.add_new_line > input.remove').each(function(){
				if(!$(this).hasClass('hidden'))
					minus++;
			});
			
			/* －ボタンと＋ボタンが一切存在しなくなることは避ける */
			if(plus == 0 || (plus == 1 && !$(this).siblings('.add').hasClass('hidden')))
			{
				// 上の行の追加buttonからhiddenを削除する
				$(this).parent().parent().prev().find('.add_new_line > input.add').removeClass('hidden');
				
				// 上の行の削除buttonからhiddenを削除する
				var remove_button = $(this).parent().parent().prev().find('.add_new_place > input.remove');
				if(remove_button != undefined)
					remove_button.removeClass('button_hidden');
			}
			
			/* 既に登録されているデータを削除する場合はinputを追加する */
			if($(this).hasClass('default'))
			{
				$('<input id="deleteid'+ linenum +'" type="text" value="'+ linenum +'" name="deleteid[]" style="display:none;">').appendTo("#updateterm");
			}
			
			// この行を削除する
			line.remove();
			
			oneLineAtLeast();
			
			setNextId();
			
			setMinandMaxValues('.range');
		});
		
		$(document).on("blur", ".name", function()
		{
			if(!$(this).hasClass('default'))
				checkNull($(this).parent().parent().find('.hidden > input').prop('value'));
		});
			
		$(function(){
			$(".selectMirror").each(function(){
				$(this).selectMirror();
			});
			
			$("#selectAdd").unbind("click");
			$("#selectAdd").bind("click", function(){
				$(".options").fadeOut(150);
			})
			
			$("#basicSettingButton").basicSetting();
			$("#pageControl").find(".finish").decisionDialog($("#finishDialog"));
			
			$("#loginStatusTrigger").miniMenu($("#loginStatus"));
			
			oneLineAtLeast();
			
			setMinandMaxValues(".range");
		});
		
		$('#updateterm').submit(function(event) {
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
						var link = '{$baseurl}/{$controllerName}/termandshift{if !empty($year_selected)}/year/{$year_selected}{/if}';
						//var comp = document.getElementById('complocation');
						//comp.setAttribute('href', link);
						// 完了ダイアログ
						$("#finishDialog").find(".affirm").decisionDialog($("#compDialog"));
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
		
		$("#addYearOK").click(function(){
			$.ajax({
				async: false,				// 同期通信
				url: '{$baseurl}/{$controllerName}/addyear', 
				type: 'POST',
				timeout: 600000,

				// 以下、ファイルアップロードに必須
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
						//alert('論理エラー');
						alert(response['error']);
					}
					else
					{	// 成功

						// 完了後の飛び先を設定
						var link = '{$baseurl}/{$controllerName}/termandshift{if !empty($year_selected)}/year/{$year_selected}{/if}';
						// 完了ダイアログ
						$("#addCompDialog").bPopup({
							closeClass:"cancel",
							modalColor:"#ffffff",
							opacity: 0.5,
							transitionClose: "fadeIn",
							zIndex: 110,
						});
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
		</script>
	<!--[if lte IE 9]>
	<script src="/js/flexie.min.js" type="text/javascript"></script>
	<![endif]-->
	</body>
</html>