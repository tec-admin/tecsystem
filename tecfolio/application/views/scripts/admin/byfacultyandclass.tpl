<!doctype html>
<html lang="ja">
<head>

{include file='admin/header.tpl'}
<script>
	var facultyarray = [];
	var sum_faculty = new Array(10);
	var sum_year = new Array(10);
	var sum_sum = [0,0];
	var yearx = [0, {$year}, {$year-1}, {$year-2}, {$year-3}, 'others'];

	function disp()
	{
		var sp = document.getElementById('selected_place').value;
		var sc = document.getElementById('selected_class').value;
		var df = document.getElementById('startdate_hidden').value;
		var dt = document.getElementById('enddate_hidden').value;
		createStatistics(sp, sc, df, dt);
	}

	function createStatistics(placeid, shiftclass, startdate, enddate)
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

		// 表を作成
		// 先に枠と見出しのみを作成する
		{foreach from=$faculties item=faculty name=faculties}
			var tr = document.createElement('tr');
			var th = document.createElement('th');
			var facultydata = '{$faculty->szknam_c}';
			facultyarray.push(facultydata);
			th.setAttribute('class', 'head');
			th.setAttribute('id', 'faculty{$faculty->setti_cd}_{$faculty->syozkcd1}');
			th.appendChild(document.createTextNode(facultydata));
			tr.appendChild(th);
			for (var i = 1; i <= 6; i++)
			{
				if(i <= 5)
				{
					var td = document.createElement('td');
					td.setAttribute('class', 'head');
					td.setAttribute('id', yearx[i] + '_{$faculty->setti_cd}_{$faculty->syozkcd1}');
					td.setAttribute('data-x', yearx[i]);
					td.setAttribute('data-y', i);
					td.appendChild(document.createTextNode(' '));
					tr.appendChild(td);
				}
				else
				{
					var td = document.createElement('td');
					td.setAttribute('class', 'sum_faculty ');
					td.setAttribute('id', 'sum_faculty_{$faculty->setti_cd}_{$faculty->syozkcd1}');
					td.setAttribute('data-x', yearx[i]);
					td.appendChild(document.createTextNode(' '));
					tr.appendChild(td);
				}
			}

			tbody.appendChild(tr);
		{/foreach}

		var tr = document.createElement('tr');
		var th = document.createElement('th');
		tr.setAttribute('class', 'sum_year');
		th.setAttribute('id', 'sum_year_title');
		th.appendChild(document.createTextNode('計'));
		tr.appendChild(th);

		for (var i = 1; i <= 6; i++)
		{
			if(i <= 5)
			{
				var td = document.createElement('td');
				td.setAttribute('id', 'sum_year_' + i);
				td.setAttribute('data-y', i);
				td.appendChild(document.createTextNode(' '));
				tr.appendChild(td);
			}
			else
			{
				var td = document.createElement('td');
				td.setAttribute('id', 'sum_sum');
				td.appendChild(document.createTextNode(' '));
				tr.appendChild(td);
			}
		}

		tbody.appendChild(tr);

		// スタッフのシフトを取得し、入力表へ設定
		/*
		var request = createXMLHttpRequest();
		var scripturl = baseurl + "/getfacultyandclass/placeid/" + placeid + "/shiftclass/" + shiftclass + "/startdate/" + startdate + "/enddate/" + enddate;
		request.open("POST", scripturl , false);
		request.send(null);
		var json = request.responseText;
		var reserves = JSON.parse(json);
		*/
		
		$("#loading").removeClass("hidden");
		
		$.ajax({
			async: true,	// 非同期通信
			url: baseurl + "/getfacultyandclass/placeid/" + placeid + "/shiftclass/" + shiftclass + "/startdate/" + startdate + "/enddate/" + enddate,
			type: "POST",
			timeout: 600000,
			datatype: 'json',
	
			beforeSend: function(xhr, settings) {
			},
			success: function(data, textStatus, jqXHR) {
				
				var reserves = $.parseJSON(data);

				for (var x in reserves['inputdata'])
				{
					for (var y in reserves['inputdata'][x])
					{
						if(y == '') continue;
						var td = document.getElementById(x + '_' + y);
						
						var count_male = reserves['inputdata'][x][y]['count_male'];
						var count_female = reserves['inputdata'][x][y]['count_female'];
		
						if(sum_faculty[y] == undefined)
							sum_faculty[y] = [0,0];
		
						if(sum_year[x] == undefined)
							sum_year[x] = [0,0];
		
						if(count_male != undefined)
						{
							sum_faculty[y][0] += count_male;
							sum_year[x][0] += count_male;
							sum_sum[0] += count_male;
						}
		
						if(count_female != undefined)
						{
							sum_faculty[y][1] += count_female;
							sum_year[x][1] += count_female;
							sum_sum[1] += count_female;
						}
						createInnerHTML(td, count_male, count_female);
					}
				}
		
				// 学部毎の平均
				{foreach from=$faculties item=faculty name=faculties}
					var td = document.getElementById('sum_faculty_{$faculty->setti_cd}_{$faculty->syozkcd1}');
					
					if(sum_faculty['{$faculty->setti_cd}_{$faculty->syozkcd1}'] != undefined)
						createInnerHTML(td, sum_faculty['{$faculty->setti_cd}_{$faculty->syozkcd1}'][0], sum_faculty['{$faculty->setti_cd}_{$faculty->syozkcd1}'][1]);
					else
						createInnerHTML(td, 0, 0);
				{/foreach}
		
				// 学年毎の平均
				for(var i = 1; i <= 5; i++)
				{
					var td = document.getElementById('sum_year_' + i);
					
					if(sum_year[yearx[i]] != undefined)
						createInnerHTML(td, sum_year[yearx[i]][0], sum_year[yearx[i]][1]);
					else
						createInnerHTML(td, 0, 0);
				}
		
				// 総平均
				var td = document.getElementById('sum_sum');
		
				createInnerHTML(td, sum_sum[0], sum_sum[1]);
		
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
		if(td == undefined)
			return;
		
		if(v1 == undefined && v2 == undefined)
		{
			td.innerHTML += '-';
			return;
		}

		var tmp = 0;

		if(v1 != undefined)
			tmp += v1;

		if(v2 != undefined)
			tmp += v2;

		td.innerHTML += tmp + '<br />';
		
		var str = '';

		if(v1 != undefined)
		{
			str += ' ( ' + v1 + ' , ';
		}
		else
		{
			str += '( 0 , ';
		}

		if(v2 != undefined)
		{
			str += v2 + ' )';
		}
		else
		{
			str += '0 )';
		}
		td.innerHTML += str;
	}

	function initGlobals()
	{
		facultyarray = [];
		sum_faculty = new Array(10);
		sum_year = new Array(10);
		sum_sum = [0,0];
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
			<h1>{t}利用統計{/t}：{t}学部・入学年度別(相談件数, 男女件数){/t}</h1>
				<div id="statistics">
					<div class="container">
						<table><thead><tr>
						<th class="th_from">
							From:
							<div class="searchFrame">
								<input type="text" name="date_from" size="10" id="date_from" readonly="readonly" >
								<a class="clearsearchclass" id="clear_from" style="color:#0073ea; cursor:pointer; font-weight: bold;">x</a>
								<input type="hidden" name="date_from" id="startdate_hidden" value="">
							</div>
						</th>
						<th class="th_to">
							To:
							<div class="searchFrame">
								<input type="text" name="date_to" size="10" id="date_to" readonly="readonly" >
								<a class="clearsearchclass" id="clear_to" style="color:#0073ea; cursor:pointer; font-weight: bold;">x</a>
								<input type="hidden" name="date_to" id="enddate_hidden" value="">
							</div>
						</th>
						<th>
							<select id="selected_place">
								<option value="0">{t}場所全体{/t}</option>
								{foreach from=$places item=place name=places}
									<option value="{$place->id}">{$place->consul_place}</option>
								{/foreach}
							</select>
						</th>
						<th>
							<select id="selected_class">
								<option value="0">{t}種別全体{/t}</option>
								{foreach from=$shiftclasses item=shiftclass name=shiftclasses}
									<option value="{$shiftclass->id}">{if $baseurl == "/kwl"}{$shiftclass->document_category}{else}{$shiftclass->class_name}{/if}</option>
								{/foreach}
							</select>
						</th>
						<th class="disp"><input type="button" value="{t}表示{/t}" onclick="disp()" style="position: relative ; top:1px;"></th></tr></thead></table>

						<table id="tableinput" class="hidden">
							<thead>
								<tr>
									<th class="blank"></th>
									<th class="head">{t 1={$year}}%1年入学{/t}</th>
									<th class="head">{t 1={$year-1}}%1年入学{/t}</th>
									<th class="head">{t 1={$year-2}}%1年入学{/t}</th>
									<th class="head">{t 1={$year-3}}%1年入学{/t}</th>
									<th class="head">{t}過年度生{/t}</th>
									<th class="sum_faculty" id="sum_faculty_title">{t}計{/t}</th>
								</tr>
							</thead>
							<tbody id="statinput">
							</tbody>
						</table>

						<table id="tableindicator" class="hidden">
							<thead>
								<tr>
									<th></th><th></th><th></th>
									<th></th><th></th><th>{t}相談件数 (男性, 女性){/t}</th>
								</tr>
							</thead>
						</table>
						
						<div id="loading" class="hidden" style="text-align:center; padding:200px 0 200px 0;">
							<img src="/images/loading.gif" />
						</div>

					</div>
				</div>
			</article>
		<!--/#main--></div>
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