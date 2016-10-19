<!doctype html>
<html lang="ja">
<head>

{include file='admin/header.tpl'}
<script>
	var shiftarray = [];
	var avg_time = new Array(10);
	var avg_week = new Array(10);
	var avg_sum = [0,0];
	var t_height = 0;

	function disp()
	{
		var sc = document.getElementById('selected_campus').value;
		var df = document.getElementById('startdate_hidden').value;
		var dt = document.getElementById('enddate_hidden').value;
		createStatistics(sc, df, dt);
	}

	function createStatistics(shiftclass, startdate, enddate)
	{
		deleteStatistics();
		initGlobals();
		
		var t1 = $('#tableinput');
		var t2 = $('#tableindicator');
		
		if(!t1.hasClass('hidden'))
			t1.addClass('hidden');
		
		if(!t2.hasClass('hidden'))
			t2.addClass('hidden');

		var baseurl = '{$baseurl}/{$controllerName}';

		var tbody = document.getElementById('statinput');

		if(startdate == "")
			startdate = 0;

		if(enddate == "")
			enddate = 0;

		// シフト入力表を作成
		// 先に枠と見出しのみを作成する
		{foreach from=$shifts item=shift name=shifts}
			var tr = document.createElement('tr');
			var th = document.createElement('th');
			var shiftdata = toAlpha({$smarty.foreach.shifts.index}) + ' {$vDate->dateFormat($shift->m_timetables_starttime, 'H:i')}-{$vDate->dateFormat($shift->m_timetables_endtime, 'H:i')}';
			shiftarray.push(shiftdata);
			th.setAttribute('class', 'head');
			th.setAttribute('id', 'shift' + toAlpha({$smarty.foreach.shifts.index}));
			th.appendChild(document.createTextNode(shiftdata));
			tr.appendChild(th);
			
			var dowj = ["{t}日曜日{/t}", "{t}月曜日{/t}", "{t}火曜日{/t}", "{t}水曜日{/t}", "{t}木曜日{/t}", "{t}金曜日{/t}", "{t}土曜日{/t}"];
			
			for (var i = 1; i <= 6; i++)
			{
				if(i <= 5)
				{
					var td = document.createElement('td');
					td.setAttribute('class', 'head');
					td.setAttribute('id', i + '_{$shift->dayno}');	// 曜日 + 連番
					td.setAttribute('data-shift', dowj[i] + ' '+ shiftdata);
					td.setAttribute('data-dayno', '{$shift->dayno}');
					td.setAttribute('data-dow', i);
					td.appendChild(document.createTextNode(' '));
					tr.appendChild(td);
				}
				else
				{
					var td = document.createElement('td');
					td.setAttribute('class', 'avg_time ');
					td.setAttribute('id', 'avg_time_{$shift->dayno}');
					td.setAttribute('data-dayno', '{$shift->dayno}');
					td.appendChild(document.createTextNode(' '));
					tr.appendChild(td);
				}
			}

			tbody.appendChild(tr);
		{/foreach}

		var tr = document.createElement('tr');
		var th = document.createElement('th');
		tr.setAttribute('class', 'avg_week');
		th.setAttribute('id', 'avg_week_title');
		th.appendChild(document.createTextNode("{t}曜日平均{/t}"));
		tr.appendChild(th);

		for (var i = 1; i <= 6; i++)
		{
			if(i <= 5)
			{
				var td = document.createElement('td');
				td.setAttribute('id', 'avg_week_' + i);
				td.setAttribute('data-dow', i);
				td.appendChild(document.createTextNode(' '));
				tr.appendChild(td);
			}
			else
			{
				var td = document.createElement('td');
				td.setAttribute('id', 'avg_sum');
				td.appendChild(document.createTextNode(' '));
				tr.appendChild(td);
			}
		}

		tbody.appendChild(tr);

		// スタッフのシフトを取得し、入力表へ設定
		/*
		var request = createXMLHttpRequest();
		var scripturl = baseurl + "/getutilization/shiftclass/" + shiftclass + "/startdate/" + startdate + "/enddate/" + enddate;
		request.open("POST", scripturl , false);
		request.send(null);
		var json = request.responseText;
		var staffshift = JSON.parse(json);
		*/
		
		$("#loading").removeClass("hidden");
		
		$.ajax({
			async: true,	// 非同期通信
			url: baseurl + "/getutilization/shiftclass/" + shiftclass + "/startdate/" + startdate + "/enddate/" + enddate,
			type: "POST",
			timeout: 600000,
			datatype: 'json',
	
			beforeSend: function(xhr, settings) {
			},
			success: function(data, textStatus, jqXHR) {
				
				var staffshift = $.parseJSON(data);

				for (var dow in staffshift['shiftinput'])
				{
					for (var dayno in staffshift['shiftinput'][dow])
					{
						var td = document.getElementById(dow + '_' + dayno);
		
						var count_reserve = staffshift['shiftinput'][dow][dayno]['count_reserve'];
						var count_staff = staffshift['shiftinput'][dow][dayno]['count_staff'];
		
						if(count_reserve != undefined && count_staff != undefined)
						{
							var percent = Math.round(count_reserve / count_staff * 1000) / 10;
						}
						else
						{
							var percent = 0;
						}
		
						td.innerHTML = '' + percent + '%<br/>';
		
						if(avg_time[dayno-1] == undefined)
							avg_time[dayno-1] = [0,0];
		
						if(avg_week[dow-1] == undefined)
							avg_week[dow-1] = [0,0];
		
						if(count_reserve != undefined)
						{
							td.innerHTML += '( ' + count_reserve + ' / ';
		
							avg_time[dayno-1][0] += count_reserve;
							avg_week[dow-1][0] += count_reserve;
							avg_sum[0] += count_reserve;
						}
						else
						{
							td.innerHTML += '( 0 / ';
						}
		
						if(count_staff != undefined)
						{
							td.innerHTML += count_staff + ' )';
		
							avg_time[dayno-1][1] += count_staff;
							avg_week[dow-1][1] += count_staff;
							avg_sum[1] += count_staff;
						}
						else
						{
							td.innerHTML += '0 )';
						}
		
						if (percent == 0)
						{
							td.className = 'rei';
						}
						else if (percent <= 20)
						{
							td.className = 'ao1';
						}
						else if (percent <= 40)
						{
							td.className = 'ao2';
						}
						else if (percent <= 60)
						{
							td.className = 'ao3';
						}
						else if (percent <= 80)
						{
							td.className = 'ao4';
						}
						else
						{
							td.className = 'ao5';
						}
					}
				}
		
				// 時間帯毎の平均
				for(var i = 1; i <= {$countDayno}; i++)
				{
					var td = document.getElementById('avg_time_' + i);
		
					createInnerHTML(td, avg_time[i-1][0], avg_time[i-1][1]);
				}
		
				// 曜日毎の平均
				for(var i = 1; i <= 5; i++)
				{
					var td = document.getElementById('avg_week_' + i);
		
					createInnerHTML(td, avg_week[i-1][0], avg_week[i-1][1]);
				}
		
				// 総平均
				var td = document.getElementById('avg_sum');
		
				createInnerHTML(td, avg_sum[0], avg_sum[1]);
		
				$("table.hidden").removeClass("hidden");
				
			},
			error: function(jqXHR, textSatus, errorThrown) {
				// Ajax処理修了前にページ遷移するなどで分岐
				// 何か表示したければ表示する
			},
			complete: function(jqXHR, textStatus) {
				// 必ず最後に渡る部分
				$("#loading").addClass("hidden");
			},
		});
	}

	function createInnerHTML(td, v1, v2)
	{
		if(v1 != 0 && v2 != 0)
		{
			var percent = Math.round(v1 / v2 * 1000) / 10;
		}
		else
		{
			var percent = 0;
		}

		td.innerHTML = '' + percent + '%<br/>';

		if(v1 != undefined)
		{
			td.innerHTML += '( ' + v1 + ' / ';
		}
		else
		{
			td.innerHTML += '( 0 / ';
		}

		if(v2 != undefined)
		{
			td.innerHTML += v2 + ' )';
		}
		else
		{
			td.innerHTML += '0 )';
		}

		if (percent == 0)
		{
			td.className += 'rei';
		}
		else if (percent <= 20)
		{
			td.className += 'ao1';
		}
		else if (percent <= 40)
		{
			td.className += 'ao2';
		}
		else if (percent <= 60)
		{
			td.className += 'ao3';
		}
		else if (percent <= 80)
		{
			td.className += 'ao4';
		}
		else
		{
			td.className += 'ao5';
		}
	}

	function initGlobals()
	{
		shiftarray = [];
		avg_time = new Array(10);
		avg_week = new Array(10);
		avg_sum = [0,0];
	}

	function deleteStatistics()
	{
		var list = document.getElementById('statinput');
		while (list.firstChild)
			list.removeChild(list.firstChild);
	}
</script>
</head>

<body class="admin">
	{include file='admin/menu.tpl'}
		<div id="main">
			<article id="article_statistics">
				<h1>{t}利用統計{/t}：{t}稼働率{/t}</h1>
				<div id="statistics">
					<div class="container">
						<!--表の上部分-->
						<table>
							<thead>
								<tr>
									<th class="th_from">
										From:
										<div class="searchFrame">
											<input type="text" size="10" id="date_from" readonly="readonly" >
											<a class="clearsearchclass" id="clear_from" style="padding-right: 0.3em;padding-left: 0.3em; color:#0073ea; cursor:pointer;">x</a>
											<input type="hidden" name="date_from" id="startdate_hidden" value="">
										</div>
									</th>
									<th class="th_to">
										To:
										<div class="searchFrame">
											<input type="text" size="10" id="date_to" readonly="readonly" >
											<a class="clearsearchclass" id="clear_to" style="padding-right: 0.3em;padding-left: 0.3em; color:#0073ea; cursor:pointer;">x</a>
											<input type="hidden" name="date_to" id="enddate_hidden" value="">
										</div>
									</th>
									<th class="selected_campus">
										<select id="selected_campus">
											<option value="0">{t}全体{/t}</option>
											{foreach from=$campuses item=campus name=campuses}
												<option value="{$campus->id}">{$campus->campus_name}</option>
											{/foreach}
										</select>
									</th>
									<th class="disp">
										<input type="button" value="{t}表示{/t}" onclick="disp()">
									</th>
								</tr>
							</thead>
						</table>
						<table id="tableinput" class="hidden">
							<thead>
								<tr>
									<th class="blank"></th>
									{foreach from=$dowarray key=i item=dow name=downame}
									<th class="head">{$dow}</th>
									{/foreach}
									<th class="avg_time" id="avg_time_title">{t}時間帯平均{/t}</th>
								</tr>
							</thead>
							<tbody id="statinput">
							</tbody>
						</table>

						<table id="tableindicator" class="sita hidden">
							<tbody>
								<tr>
									<th class="rei">0%</th>
									<th class="ao1">～20%</th>
									<th class="ao2">～40%</th>
									<th class="ao3">～60%</th>
									<th class="ao4">～80%</th>
									<th class="ao5">～100%</th>
								</tr>
							</tbody>
						</table>
					</div>
					
					<div id="loading" class="hidden" style="text-align:center; padding:200px 0 200px 0;">
						<img src="/images/loading.gif" />
					</div>
					
				</div>
			</article>
		</div>
		{include file="admin/statistics_sidebar.tpl"}
	</div>
 	{include file="../common/foot_v2.php"}
 	
	<script>
		$(function() {
		
			var dates_from = $('#date_from').datepicker({
				dateFormat: 'yy-mm-dd',//年月日の並びを変更
				minDate: '{$mindate}',
				maxDate: '{$maxdate}',
				changeMonth: true, //月をドロップボックスで選択化
				onSelect: function(dateText, inst){
					// 実際に挿入するhidden値を保存
					$('#startdate_hidden').val(dateText);
					// Toの最小日が選択値あるいは$mindateになるように制限
					$('#date_to').datepicker('option', 'minDate', '{$mindate}' > dateText ? '{$mindate}' : dateText);	//MAXがTOを超えないようにする
					// 表示値をフォーマット
					if($('#enddate_hidden').val() != '')
						$('#date_to').val(dateFormat($('#enddate_hidden').val(), 'Y/m/d(wj)'));
					$('#date_from').val(dateFormat(dateText, 'Y/m/d(wj)'));
				}
			});

			var dates_to = $('#date_to').datepicker({
				dateFormat: 'yy-mm-dd',//年月日の並びを変更
				minDate: '{$mindate}',
				maxDate: '{$maxdate}',
				changeMonth: true, //月をドロップボックスで選択化
				onSelect: function(dateText, inst){
					// 実際に挿入するhidden値を保存
					$('#enddate_hidden').val(dateText);
					// Fromの最大日が選択値になるように制限
					$('#date_from').datepicker('option', 'maxDate', '{$maxdate}' < dateText ? '{$maxdate}' : dateText);	//MINがFROMを超えないようにする
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

			$("#loginStatusTrigger").miniMenu($("#loginStatus"));
			$("#download").basicSetting();
			
		});
	</script>
	<!--[if lte IE 9]>
	<script src="/js/flexie.min.js" type="text/javascript"></script>
	<![endif]-->
	</body>
</html>
