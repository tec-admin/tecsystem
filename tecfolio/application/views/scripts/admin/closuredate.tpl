<!doctype html>
<html lang="ja">
<head>
{include file='admin/header.tpl'}
<script>
	window.onload = function()
	{
	}
	
	function createInput(obj)
	{
		var container = document.getElementById('cals_container');
		
		var elm = document.createElement('input');
		elm.setAttribute('type', 'hidden');
		elm.setAttribute('value', $(obj).data('date'));
		elm.setAttribute('id', $(obj).data('date'));
		elm.setAttribute('name', 'closuredates[]');
		container.appendChild(elm);
	}
	
	// 日付クリックイベント
	function chStat(obj)
	{
		if($(obj).hasClass('close'))
		{
			$(obj).removeClass('close');
			$('#'+$(obj).data('date')).remove();
		}
		else
		{
			$(obj).attr('class', 'close');
			createInput(obj);
		}
	}
	
	function chAll(obj, bool)
	{
		if(bool)
		{
			$(obj).removeClass('close');
			
			if($('#'+$(obj).data('date')) != undefined)
				$('#'+$(obj).data('date')).remove();
		}
		else
		{
			if(!$(obj).hasClass('close'))
			{
				$(obj).attr('class', 'close');
				createInput(obj);
			}
		}
	}
	
	function setAll(obj)
	{
		$(obj).parent().siblings('.org_calendar').find('a:not(.holiday)').each(function()
		{
			chAll($(this).get(0), !obj.checked);
		});
	}
	
	function submitData()
	{
		$('#closuredates').submit();
	}
	
	function deleteCalandar()
	{
		var list = document.getElementById('cals_container');
		while (list.firstChild)
			list.removeChild(list.firstChild);
	}
	
	//var dowStr	= ['日','月','火','水','木','金','土'];
	var dowStr = getDowArray();
	
	function fillZero(num)
	{
		return ('0' + num).slice(-2);
	}
	
	function selectTerm(obj)
	{
		var vterm = document.getElementById('vterm');
		var val = obj.options[obj.selectedIndex].value;
		vterm.setAttribute('value', val);
	}
	
	function selectPlace(obj)
	{
		var vplace = document.getElementById('vplace');
		var val = obj.options[obj.selectedIndex].value;
		vplace.setAttribute('value', val);
	}

	function createCalendar()
	{
		deleteCalandar();
		$('#pageControl').css('display', 'none');
		
		var termid = document.getElementById('vterm').value;
		var placeid = document.getElementById('vplace').value;
		
		var baseurl = '{$baseurl}/{$controllerName}';
		$.ajax({
			async: true,	// 非同期通信
			url: baseurl + "/getclosuredate/termid/" + termid + "/placeid/" + placeid,
			type: "POST",
			timeout: 600000,
			datatype: 'json',
	
			beforeSend: function(xhr, settings) {
			},
			success: function(data, textStatus, jqXHR) {
				
				var info = JSON.parse(data);
				
				var startdate	= new Date(info['term']['startdate']);
				var enddate		= new Date(info['term']['enddate']);
				
				var cnt = 0;
				
				var curdate = startdate;
				var y = startdate.getFullYear();
				for( var i = startdate.getMonth() + 1; i <= enddate.getMonth() + 1 || y < enddate.getFullYear(); i++, cnt++)
				{
					if(i > 12)
					{
						var curdate = new Date(startdate.getFullYear() + 1, 1, 1);
						i = 1;
						y = curdate.getFullYear();
					}
					
					// 当月1日のデータを保持する
					var startDay = new Date(curdate.getFullYear(), i - 1, 1);
					var lastDay = new Date(curdate.getFullYear(), i, 0);
					
				 	var container	= document.getElementById('cals_container');
					var cal		= document.createElement('div');
					cal.setAttribute('class', 'div_calendar');
					
					var hd		= document.createElement('div');
					hd.setAttribute('class', 'cal_header');
					
					// 一括設定チェックボックス
					var chk		= document.createElement('input');
					chk.setAttribute('type', 'checkbox');
					chk.setAttribute('class', 'setall');
					chk.setAttribute('onChange', 'setAll(this);');
					hd.appendChild(chk);
					
					var txt		= document.createElement('div');
					txt.setAttribute('class', 'cal_text');
					
					// 年月見出し
					txt.innerHTML = dateFormat(curdate.getFullYear() + '-' + i + '-01', 'Y年m月', false, true);
					hd.appendChild(txt);
					
					cal.appendChild(hd);
					
					var tbl		= document.createElement('table');
					tbl.setAttribute('class', 'org_calendar');
					var thd		= document.createElement('thead');
					var tr		= document.createElement('tr');
					
					// 曜日見出し
					for(var d = 0; d < dowStr.length; d++)
					{
						var th		= document.createElement('th');
						th.innerHTML = dowStr[d];
						
						if(d == 0)
							th.setAttribute('class', 'sun');
						else if(d == 6)
							th.setAttribute('class', 'sat');
						tr.appendChild(th);
					}
					
					// thead部分
					thd.appendChild(tr);
					tbl.appendChild(thd);
					
					var tbd		= document.createElement('tbody');
					
					var num 	= 0;
					
					for(var j = 0; j < 6; j++)	// 週ごと
					{
						var tr		= document.createElement('tr');
						
						for(var k = 0; k < 7; k++)	// 日ごと
						{
							var td		= document.createElement('td');
							
							if(num == 0 && startDay.getDay() == k)
							{
								num = 1;
							}
							
							if(num > 0 && num <= lastDay.getDate())
							{
								var a	= document.createElement('a');
								a.innerHTML = num;
								
								var dStr = curdate.getFullYear() + '-' + fillZero(i) + '-' + fillZero(num);
								a.setAttribute('data-date', dStr);
								a.setAttribute('id', 'cal_' + dStr);
								a.setAttribute('onMouseMove', 'return false;');
								a.setAttribute('onMouseDown', 'return false;');
								num++;
								
								if(k == 0 || k == 6)
									a.setAttribute('class', 'holiday');
								else
									a.setAttribute('onClick', 'chStat(this);');
								td.appendChild(a);
							}
							
							tr.appendChild(td);
						}
						tbd.appendChild(tr);
					}
					tbl.appendChild(tbd);
					cal.appendChild(tbl);
					container.appendChild(cal);
				}
				
				// 閉室設定
				if(info['close'].length > 0)
				{
					for(var d in info['close'])
					{
						var c = info['close'][d]['closuredate'];
						var obj = $('#cal_' + c);
						
						obj.attr('class', 'close');
						createInput(obj);
					}
				}
				$('#pageControl').css('display', 'block');
			},
			error: function(jqXHR, textSatus, errorThrown) {
				// Ajax処理修了前にページ遷移するなどで分岐
				// 何か表示したければ表示する
			},
			complete: function(jqXHR, textStatus) {
				// 必ず最後に渡る部分
			},
		});
	}
</script>
<style>
	#pageControl{
		clear:both;
	}
	div.inner_head select{
		margin-right: 20px;
		padding: 3px 9px 3px 3px;
	}
	div#cals_container div.div_calendar{
		width: 240px;
		height: 200px;
		padding: 2px 2px 4px 2px;
		margin: 20px 20px 20px 20px;
		border: 1px solid #adadad;
		float:left;
	}
	div#cals_container div.cal_header{
		width: 100%;
		background: url("../../../css/images/ui-bg_highlight-soft_50_dddddd_1x100.png") repeat-x scroll 50% 50% #ddd;
		text-align: center;
		height: 20px;
		padding: 4px 0 4px 0;
		position: relative;
	}
	div#cals_container input.setall{
		cursor: pointer;
		display: inline-block;
	}
	div#cals_container div.cal_text{
		font-size: 14px;
		font-weight: bold;
		display: inline-block;
		padding-left: 3px;
	}
	div#cals_container table.org_calendar{
		width: 100%;
		margin: 0;
	}
	div#cals_container table.org_calendar thead th{
		text-align: center;
		font-weight: bold;
		padding: 6px 0;
		width: 14.28%;
	}
	div#cals_container table.org_calendar thead th.sun{
		color: #e74c3c;
	}
	div#cals_container table.org_calendar thead th.sat{
		color: #2980b9;
	}
	div#cals_container table.org_calendar tbody td{
		padding: 1px;
	}
	div#cals_container table.org_calendar tbody td a{
		text-align: right;
		background: url("../../../css/images/ui-bg_highlight-soft_100_f6f6f6_1x100.png") repeat-x scroll 50% 50% #f6f6f6;
		border: 1px solid #ddd;
		color: #0073ea;
		padding: 4px 2px;
		display: block;
		cursor: pointer;
	}
	div#cals_container  table.org_calendar tbody td a.close,
	div#cals_container  table.org_calendar tbody td a.holiday{
		background: url("../../../css/images/ui-bg_highlight-soft_100_c1c3c9_1x100.png") repeat-x scroll 50% 50% #f6f6f6;
	}
</style>
</head>
<body class="admin">
	{include file='admin/menu.tpl'}
		<div id="main">
			<article>
			<h1>{t}閉室日設定{/t}</h1>
				<div class="container">
				<form method="POST" action="{$baseurl}/{$controllerName}/setclosuredates" name="closuredates" id="closuredates" enctype="multipart/form-data">
					<div class="inner_head">
						{t}学期{/t}
						<select id="sle_term" onChange="selectTerm(this);">
						{foreach from=$terms item=term name=terms}
							<option value="{$term['id']}" {if $termid == $term['id']}selected{/if}>{t 1=$term['year'] 2=$term['name']}%1年度 %2{/t}</li>
						{/foreach}
						</select>
						<input type="hidden" id="vterm" name="vterm" value="{$termid}" />
						{t}場所{/t}
						<select id="sle_place" onChange="selectPlace(this);">
						{foreach from=$places item=place name=places}
							<option value="{$place['id']}">{$place['consul_place']}</li>
						{/foreach}
						</select>
						<input type="hidden" id="vplace" name="vplace" value="{$placeid}" />
						<input type="button" value="{t}表示{/t}" onclick="createCalendar()" />
					</div>
					<div id="cals_container">
					</div>
					
					<div id="pageControl" style="display: none;">
						<input type="button" value="{t}登録{/t}" class="finish">
						<div id="finishDialog" class="dialog">
							<i class="closeButton cancel"></i>
							<div class="sub">{t}更新しますか？{/t}</div>
							<div class="buttonSet dubble">
								<a href="#" onclick="submitData();" class="affirm">{t}OK{/t}</a>
								<a href="#" class="cancel">{t}キャンセル{/t}</a>
							</div>
						</div>
						
						<div id="compDialog" class="dialog">
							<div class="cmpsub">{t}更新が完了しました。{/t}</div>
						</div>
					</div>
				</form>
				</div>
			</article>
		<!--/#main--></div>
		<aside id="syssidebar">
        </aside>
	 </div>
	{include file="../common/foot_v2.php"}
	
	<script>
		$(function() {
			$("#loginStatusTrigger").miniMenu($("#loginStatus"));
			$("#pageControl").find(".finish").decisionDialog($("#finishDialog"));
			$("#finishDialog").find(".affirm").decisionDialog($("#compDialog"));
		});
		
		$('#closuredates').submit(function(event) {
			event.preventDefault();	// 本来のsubmit処理をキャンセル

			var $form = $(this);
			var fd = new FormData($form[0]);

			$.ajax({
				async: false,
				url: $form.attr('action'),
				type: $form.attr('method'),
				timeout: 600000,

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
					{	// 成功→完了ダイアログ表示
						/*
						$(this).delay(2000).queue(function() {
							$("#compDialog").bPopup().close();
						});
						*/
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
	</body>
</html>