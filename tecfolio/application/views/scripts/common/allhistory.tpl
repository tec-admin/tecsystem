<!doctype html>
<html lang="ja">
<head>

{if $included == 'staff'}
	{include file='staff/header.tpl'}
{else}
	{include file='admin/header.tpl'}
{/if}
<link rel="stylesheet" href="/css/ui.jqgrid.css" type="text/css" />
<style type="text/css">

#allhistoryTable tr td {
	white-space: normal;
}

</style>
<script src="/js/base.reserve.js" type="text/javascript"></script>
<script src="/js/jquery.printArea.js" type="text/javascript"></script>
{if $locale == 'ja'}
<script src="/js/jqgrid/i18n/grid.locale-ja.js" type="text/javascript"></script>
{else}
<script src="/js/jqgrid/i18n/grid.locale-en.js" type="text/javascript"></script>
{/if}
<script src="/js/jqgrid/jquery.jqGrid.src.js" type="text/javascript"></script>

<script>
	function loadcss(href)
	{
		var d = document;
		var link = d.createElement('link');
		link.href = href;
		link.rel = 'stylesheet';
		link.type = 'text/css';
		var h = d.getElementsByTagName('head')[0];
		h.appendChild(link);
	}
	
	function removecss(href)
	{
		$("head link").each(function(){
			if($(this).attr('href') == href)
				$(this).remove();
		});
	}
	
	function printModal()
	{
		loadcss('/css/ui.jqgrid.print.css');
		
		$(this).delay(200).queue(function() {
			printElement(document.getElementById("basicSettingDialog"));
		
			$("#printSection #basicSettingDialog").attr("style", "display: block; position: absolute; z-index: 9999; left: 0; top: 0;");	// style修正
			$("#printSection #basicSettingDialog .buttonSet").attr("style", "display:none;");		// 下部ボタン非表示
			$("#printSection #basicSettingDialog i.closeButton").attr("style", "display:none;");	// ×ボタン非表示
			$("#printSection #basicSettingDialog div").removeClass("ac-container");					// スクロールの基準となるdivのクラス削除
			
			$("#printSection #basicSettingDialog section.ac-container").find("input:checkbox").each(function(){
				$(this).prop("checked", true);
			});
			
			window.print();
			
			deletePrintElement();
			
			removecss('/css/ui.jqgrid.print.css');
			$(this).dequeue();
		});
	}

	function printElement(elem)
	{
		var domClone = elem.cloneNode(true);

		var $printSection = document.getElementById("printSection");

		if (!$printSection) {
			var $printSection = document.createElement("div");
			$printSection.id = "printSection";
			document.body.appendChild($printSection);
		}
		
		$printSection.setAttribute('style', 'position: absolute; left: 50%; -ms-zoom: 0.98; -webkit-transform: translate(-50%,-50%);');

		$printSection.innerHTML = "";
		$printSection.appendChild(domClone);
	}
	
	function deletePrintElement()
	{
		var p = document.getElementById('printSection');
		while (p.firstChild)
			p.removeChild(p.firstChild);
	}

	//キャンセル
	function cancel()
	{
		$("#basicSettingDialog").bPopup().close();
	}
	window.onload = function()
	{

	}

	function selectPlace(obj)
	{
		var placeid = obj.options[obj.selectedIndex].value;

		var elmTerm = document.getElementById('vterm');
		var termid = elmTerm.value;

		var link = '{$baseurl}/{$controllerName}/{$actionName}/placeid/' + placeid + '/termid/' + termid;
		document.location = link;
	}

	function selectTerm(obj)
	{
		var termid = obj.options[obj.selectedIndex].value;

		var elmPlace = document.getElementById('vplace');
		var placeid = elmPlace.value;

		var link = '{$baseurl}/{$controllerName}/{$actionName}/placeid/' + placeid + '/termid/' + termid;
		document.location = link;
	}

	//履歴ポップアップ時の処理
	function historypopup(reserveid)
	{
		var baseurl = '{$baseurl}/{$controllerName}';
		//jsonで予約番号に紐づくデータを取得する
		var request = createXMLHttpRequest();
		var scripturl = baseurl + "/getallhistory/reserveid/" + reserveid;
		request.open("POST", scripturl , false);
		request.send(null);
		var json = request.responseText;
		var history = JSON.parse(json);

		//ダイアログの中身を動的に生成
		//スタッフ
		var tbody = document.getElementById('ac-staff');
		tbody.innerHTML="";
		createHistoryText("{t}担当スタッフ{/t}",history['charge_name_jp'],tbody);
		//相談内容
		var tbody = document.getElementById('ac-small');
		tbody.innerHTML="";
		var rDate = new Date(history['reservationdate']);
    	//var w = ["日", "月", "火", "水", "木", "金", "土"];
    	var w = getDowArray();
    	var alpha = toAlpha(history['shifts_dayno'] - 1);
    	
    	//var reservationdate = (rDate.getYear()+1900) + '/' + (rDate.getMonth() + 1) + '/' + rDate.getDate() + '(' + w[rDate.getDay()] + ')';
		var reservationdate = dateFormat(rDate, 'Y/m/d(wj)');
		
		createHistoryText1("{t}年月日{/t}",reservationdate,"{t}シフト{/t}",alpha + ' ' + toHM(history['timetables_starttime']) + '-' + toHM(history['timetables_endtime']),tbody);
		createHistoryText1("{t}学籍番号{/t}",history['reserver_student_id'],"{t}氏名{/t}",history['reserver_name_jp'],tbody);

		{if $baseurl == "/kwl"}
			createHistoryText1("{t}文書の種類{/t}",history['m_dockinds_document_category'],"{t}相談場所{/t}",history['m_places_consul_place'],tbody);
		{else}
			createHistoryText1("{t}文書の種類{/t}",history['m_dockinds_clipped_form'],"{t}相談場所{/t}",history['m_places_consul_place'],tbody);
		{/if}
		
		if(history['submitdate'] != undefined)
		{
			var sDate = new Date(history['submitdate']);
			//var submitdate = (sDate.getYear()+1900) + '/' + (sDate.getMonth() + 1) + '/' + sDate.getDate() + '(' + w[sDate.getDay()] + ')';
    		var submitdate = dateFormat(sDate, 'Y/m/d(wj)');
    	}
    	else
    	{
    		var submitdate = '';
    	}
		
		var progress = '';
		if(history['progress'] != 0)
		{
			for(var i = 0; i < (history['progress']/10); i++)
				progress += '★';
		}
		
		createHistoryText1("{t}進行状況{/t}",progress,"{t}提出日{/t}",submitdate,tbody);
		
		
		createHistoryText1("{t}授業科目{/t}",history['m_subjects_class_subject'],"{t}担当教員{/t}",history['kyoin'],tbody);

		// 複数ファイル添付に対応
		var tmp_count = 1;
		for(var i in history['t_files_name'])
			tmp_count++;

		if(tmp_count > 2)
		{
			tmp_count = 1;
			for(var i in history['t_files_name'])
			{
				createHistoryText1("{t}添付ファイル{/t}" + tmp_count,history['t_files_name'][i],"{t}提出ファイル{/t}" + tmp_count,'',tbody);
				tmp_count++;
			}
		}
		else
		{
			for(var i in history['t_files_name'])
			{
				createHistoryText1("{t}添付ファイル{/t}",history['t_files_name'][i],"{t}提出ファイル{/t}",'',tbody);
			}
		}

		createHistoryText2("{t}相談したいこと{/t}",history['question'],tbody);
		
		//相談内容
		var tbody = document.getElementById('ac-counsel');
		tbody.innerHTML="";
		createHistoryText3(history['t_leadings_counsel'],tbody);
		//指導内容
		var tbody = document.getElementById('ac-medium');
		tbody.innerHTML="";
		createHistoryText3(history['t_leadings_teaching'],tbody);
		//所感
		var tbody = document.getElementById('ac-remark');
		tbody.innerHTML="";
		createHistoryText3(history['t_leadings_remark'],tbody);
		//備考
		var tbody = document.getElementById('ac-summary');
		tbody.innerHTML="";
		createHistoryText3(history['t_leadings_summary'],tbody);
		//コメント
		var tbody = document.getElementById('ac-large');
		tbody.innerHTML="";
		createHistoryText3(history['t_leadings_comment'],tbody);

		//サイドバーに履歴追加
		var historylist = document.getElementById('historylist');
		var li = document.createElement('li');
		var a = document.createElement('a');
		var p1 = document.createElement('p');
		var p2 = document.createElement('p');
		p1.appendChild(document.createTextNode(reservationdate));
		if(history['charge_name_jp'] != null)
			p2.appendChild(document.createTextNode(history['reserver_name_jp']+"/"+history['charge_name_jp']));
		else
			p2.appendChild(document.createTextNode(history['reserver_name_jp']+"/{t}なし{/t}"));
		a.appendChild(p1);
		a.appendChild(p2);
		a.setAttribute('onclick', "historypopup('" + reserveid + "');");
		li.setAttribute('reserveid', reserveid);
		li.appendChild(a);
		//先頭から追加
		historylist.insertBefore(li, historylist.firstChild);


		var cnt=10;
		if(cnt >historylist.children.length){
			cnt=historylist.children.length;
		}
		var deletechild=0;
		//同じであれば削除
		for (var i=1 ; i<cnt ; i++){
			if(historylist.firstChild.getAttribute('reserveid') == historylist.children[i].getAttribute('reserveid')){
				//一致したら削除
				deletechild=i;
			}
		}
		if(deletechild>0){
			historylist.removeChild(historylist.children[deletechild]);
		}
		//10番目を削除
		if(historylist.children[10]){
			historylist.removeChild(historylist.children[10]);
		}

		//印刷用の出力を動的に生成
		//スタッフ
		var tbody = document.getElementById('print-ac-staff');
		tbody.innerHTML="";
		createHistoryText("{t}担当スタッフ{/t}",history['charge_name_jp'],tbody);

		//相談内容
		var tbody = document.getElementById('print-ac-small');
		tbody.innerHTML="";
		createHistoryText1("{t}年月日{/t}",reservationdate,"{t}シフト{/t}",alpha + ' ' + toHM(history['timetables_starttime']) + '-' + toHM(history['timetables_endtime']),tbody);
		createHistoryText1("{t}学籍番号{/t}",history['reserver_student_id'],"{t}氏名{/t}",history['reserver_name_jp'],tbody);
		createHistoryText1("{t}文書の種類{/t}",history['m_dockinds_document_category'],"{t}相談場所{/t}",history['m_places_consul_place'],tbody);
		createHistoryText1("{t}進行状況{/t}",progress,"{t}提出日{/t}",submitdate,tbody);
		createHistoryText1("{t}授業科目{/t}",history['m_subjects_class_subject'],"{t}担当教員{/t}",'',tbody);
		createHistoryText1("{t}添付ファイル{/t}",history['t_reserve_files_id'],"{t}提出ファイル{/t}",'',tbody);
		createHistoryText2("{t}相談したいこと{/t}",history['question'],tbody);
		//相談内容
		var tbody = document.getElementById('print-ac-counsel');
		tbody.innerHTML="";
		createHistoryText3(history['t_leadings_counsel'],tbody);
		//指導内容
		var tbody = document.getElementById('print-ac-medium');
		tbody.innerHTML="";
		createHistoryText3(history['t_leadings_teaching'],tbody);
		//所感
		var tbody = document.getElementById('print-ac-remark');
		tbody.innerHTML="";
		createHistoryText3(history['t_leadings_remark'],tbody);
		//備考
		var tbody = document.getElementById('print-ac-summary');
		tbody.innerHTML="";
		createHistoryText3(history['t_leadings_summary'],tbody);
		//コメント
		var tbody = document.getElementById('print-ac-large');
		tbody.innerHTML="";
		createHistoryText3(history['t_leadings_comment'],tbody);
		
		$("section.ac-container").find("input:checkbox").each(function(){
			$(this).prop("checked", false);
		});
		
		$("section.ac-container").find("input:checkbox:first").prop("checked", true);
		
		$("#basicSettingDialog").bPopup();
	}

	//履歴詳細項目追加(スタッフ)
	function createHistoryText(title1,text1, tbody)
	{
		var tr = document.createElement("tr");
		var th1 = document.createElement("th");
		var td1 = document.createElement("td");
		th1.appendChild(document.createTextNode(title1));
		if(text1 != null)
			td1.appendChild(document.createTextNode(text1));
		else
			td1.appendChild(document.createTextNode("{t}なし{/t}"));
		tr.appendChild(th1);
		tr.appendChild(td1);
		tbody.appendChild(tr);
	}
	//履歴詳細項目追加1
	function createHistoryText1(title1,text1,title2,text2, tbody)
	{
		var tr = document.createElement("tr");
		var th1 = document.createElement("th");
		var td1 = document.createElement("td");
		var th2 = document.createElement("th");
		var td2 = document.createElement("td");
		th1.appendChild(document.createTextNode(title1));
		if(text1 != null)
			td1.appendChild(document.createTextNode(text1));
		th2.appendChild(document.createTextNode(title2));
		if(text2 != null)
			td2.appendChild(document.createTextNode(text2));
		tr.appendChild(th1);
		tr.appendChild(td1);
		tr.appendChild(th2);
		tr.appendChild(td2);
		tbody.appendChild(tr);
	}
	//履歴詳細項目追加2
	function createHistoryText2(title1,text1, tbody)
	{
		var tr = document.createElement("tr");
		var th1 = document.createElement("th");
		var td1 = document.createElement("td");
		th1.appendChild(document.createTextNode(title1));
		td1.appendChild(document.createTextNode(text1));
		td1.setAttribute('colSpan', '3');
		tr.appendChild(th1);
		tr.appendChild(td1);
		tbody.appendChild(tr);
	}

	//履歴詳細項目追加3
	function createHistoryText3(text1, tbody)
	{
		var tr = document.createElement("tr");
		var td1 = document.createElement("td");
		if(text1 != null)
			td1.appendChild(document.createTextNode(text1));
		tr.appendChild(td1);
		tbody.appendChild(tr);
	}
	
</script>
</head>

	<body class="{$included}">
		{if $included == 'staff'}
			{include file='staff/menu.tpl'}
		{else}
			{include file='admin/menu.tpl'}
		{/if}

			<div id="main">
				<article class="calendar">
					<h1 id="maintitle">{t}全指導履歴{/t}</h1>
					<div id="history">
						<div class="container">
							<div id="select_place_and_term">
								{t}場所{/t}
								<select id="sl_place" onChange="selectPlace(this)">
								{foreach from=$places item=place name=places}
									<option value="{$place['id']}" {if $placeid == $place['id']}selected{/if}>{$place['consul_place']}</li>
								{/foreach}
								</select>
								<input type="hidden" id="vplace" value="{$placeid}" />

								{t}学期{/t}
								<select id="sl_term" onChange="selectTerm(this)">
								{foreach from=$terms item=term name=terms}
									<option value="{$term['id']}" {if $termid == $term['id']}selected{/if}>{t 1=$term['year'] 2=$term['name']}%1年度 %2{/t}</li>
								{/foreach}
								</select>
								<input type="hidden" id="vterm" value="{$termid}" />
							</div>

							<div id="div_dummy" style="width:100%">
								<table id="allhistoryTable"></table>	<!-- テーブル -->
								<div id="pager"></div>					<!-- ページャー -->
							</div>

						</div>
					</div>
				</article>
			</div>

			<aside id="sidebar">
				<h1>{t}閲覧履歴{/t}</h1>
				<ul id="historylist">
				</ul>
			</aside>
		</div>

		<div id="basicSettingDialog" class="dialog">
			<i class="closeButton cancel" onClick="cancel()"></i>
			<div class="sub">{t}履歴詳細{/t}</div>
			<table class="tan">
				<tbody id="ac-staff">
					<tr><th>{t}担当スタッフ{/t}</th><td></td></tr>
				</tbody>
			</table>
			<div class="ac-container">
			<section class="ac-container">
				<div>
					<input id="Panel0" name="accordion-0" type="checkbox">
					<label for="Panel0" class="his">{t}相談内容{/t}</label>
					<article class="ac-small">
					<table>
						<tbody id="ac-small">
						</tbody>
					</table>
					</article>
				</div>
				<div>
					<input id="Panel1" name="accordion-1" type="checkbox">
					<label for="Panel1" class="his">{t}相談内容{/t}</label>
					<article class="ac-counsel">
					<table>
						<tbody id="ac-counsel" class="ac">
						</tbody>
					</table>
					</article>
				</div>
				<div>
					<input id="Panel2" name="accordion-2" type="checkbox">
					<label for="Panel2" class="his">{t}指導内容{/t}</label>
					<article class="ac-medium">
					<table>
						<tbody id="ac-medium" class="ac">
						</tbody>
					</table>
					</article>
				</div>
				<div>
					<input id="Panel3" name="accordion-3" type="checkbox">
					<label for="Panel3" class="his">{t}所感{/t}</label>
					<article class="ac-remark">
					<table>
						<tbody id="ac-remark" class="ac">
						</tbody>
					</table>
					</article>
				</div>
				<div>
					<input id="Panel4" name="accordion-4" type="checkbox">
					<label for="Panel4" class="his">{t}備考{/t}</label>
					<article class="ac-summary">
					<table>
						<tbody id="ac-summary" class="ac">
						</tbody>
					</table>
					</article>
				</div>
				<div>
					<input id="Panel5" name="accordion-5" type="checkbox">
					<label for="Panel5" class="his">{t}コメント{/t}</label>
					<article class="ac-large">
					<table>
						<tbody id="ac-large" class="ac">
						</tbody>
					</table>
					</article>
				</div>
			</section>
			</div>
			<div class="buttonSet dubble" style="margin-top: 20px;">
				<a class="affirm">{t}印刷する{/t}</a>
				<a class="cancel" onClick="cancel()">{t}キャンセル{/t}</a>
			</div>
		</div>

		<div id="print_parent">
			<div class="print_toptitle">{t}履歴詳細{/t}</div>
			<table class="print-ac-staff">
				<tbody id="print-ac-staff">
				<tr><th>{t}担当スタッフ{/t}</th><td></td></tr>
				</tbody>
			</table>
			<div class="print_title">{t}▲相談内容{/t}</div>
			<table class="print_table">
				<tbody id="print-ac-small">
				</tbody>
			</table>
			<div class="print_title">{t}▲相談内容{/t}</div>
			<table class="print_table">
				<tbody id="print-ac-counsel">
				</tbody>
			</table>
			<div class="print_title">{t}▲指導内容{/t}</div>
			<table class="print_table">
				<tbody id="print-ac-medium">
				</tbody>
			</table>
			<div class="print_title">{t}▲所感{/t}</div>
			<table class="print_table">
				<tbody id="print-ac-remark">
				</tbody>
			</table>
			<div class="print_title">{t}▲備考{/t}</div>
			<table class="print_table">
				<tbody id="print-ac-summary">
				</tbody>
			</table>
			<div class="print_title">{t}▲コメント{/t}</div>
			<table class="print_table">
				<tbody id="print-ac-large">
				</tbody>
			</table>
		</div>
	<!--/#contents--></div>

		{include file="../common/foot_v2.php"}
		
		<script>
			$(document).ready(function() {

				/********** テーブル表示処理 **********/
				var postData ={};
				var rowData =[];
				var count = 0;
				/* データ取得 */
				$.ajax({
					url: '{$baseurl}/{$controllerName}/getallhistorylist/{if !empty($placeid)}placeid/{$placeid}/{/if}{if !empty($termid)}termid/{$termid}/{/if}',
					type : 'post',
					dataType: 'json',
					cache: false,
					async: false,
					data: postData,
					error: function() {
						alert("Javascript ajax Test Error");
					},
					success: function(data){
						for(var i in data['history']){
							postData[i] = eval(data['history'][i]);
							var tableData = {};
							tableData = {
									reserveid: postData[i]['id'],
									reservationdate: postData[i]['reservationdate'],
									starttime: toAlpha(postData[i]['m_shifts_dayno']-1)+' '+toHM(postData[i]['m_timetables_starttime']),
									reserver_name_jp: postData[i]['name_jp'],
									charge_name_jp: postData[i]['t_leadings_name_jp'],
									document_category: postData[i]['m_dockinds_document_category'],
									class_subject: postData[i]['class_subject'],
							};
							rowData.push(tableData);
							count++;
						};
					}
				});
				
				//var w = ["日","月","火","水","木","金","土"];
				var w = getDowArray();
				function weekdayFmatter (cellvalue, options, rowObject)
				{
					//var date = new Date(cellvalue);
					//return date.getFullYear() + '/' + ('0' + (date.getMonth() + 1)).slice(-2) + '/' + ('0' + date.getDate()).slice(-2) + '(' + w[date.getDay()] + ')'; 
					return dateFormat(cellvalue, 'Y/m/d(wj)');
				}
				
				// Smartyがエラーを吐くため連想多重配列については構文解析を回避する
				{literal}
				// 列の設定
				var colModelSettings= [
					// ID(hidden)
					{name:'reserveid', index:'reserveid', classes:'reserveid_class', hidden: true},
					// 年月日
					{name:'reservationdate', index:'reservationdate', align:'center', classes:'reservationdate_class',
						sorttype:'date', searchoptions : { sopt : ['ge'] }, width: "195px", formatter:weekdayFmatter,
					},
					// シフト
					{name:"starttime", index:"starttime", align:"center", classes:"starttime_class", sorttype:'string', stype:"select",
						searchoptions: { value: ':{/literal}{t}全シフト{/t}{literal}', clearSearch: false }, width: "100px"
					},
					// 学生氏名
					{name:"reserver_name_jp", index:"reserver_name_jp", align:"center", classes:"reserver_name_jp_class", sorttype:'string',
						searchoptions : { sopt : ['cn'] }, width: "140px"
					},
					// 担当スタッフ
					{name:"charge_name_jp", index:"charge_name_jp", align:"center", classes:"charge_name_jp_class", sorttype:'string',
						searchoptions : { sopt : ['cn'] }, width: "140px"
					},
					// 文書の種類
					{name:"document_category", index:"document_category", align:"center", classes:"document_category_class", stype:"select",
						searchoptions: { value: ':{/literal}{t}全種類{/t}{literal}', clearSearch: false }
					},
					// 科目名
					{name:"class_subject", index:"class_subject", align:"center", classes:"class_subject_class", sorttype:'string',
						searchoptions : { sopt : ['cn'] }, width: "150px"
					},
				];
				{/literal}

				// 列の表示名
				var colNames = ["{t}ID{/t}","{t}年月日{/t}","{t}シフト{/t}","{t}学生氏名{/t}","{t}担当スタッフ{/t}","{t}文書の種類{/t}","{t}科目名{/t}"];

				// テーブルの作成
				$("#allhistoryTable").jqGrid({
					data : rowData,
					datatype : "local",
					colNames : colNames,
					colModel : colModelSettings,
					rowNum : 20,
					rowList : [20, 30, 50, 100],
					height : 'auto',
					pager : '#pager',
					shrinkToFit : true,
					viewrecords : true,
					loadonce : true,
					gridview : true,
					onSelectRow : function(id){
						var reserveid =  $("tr#" + id).children("td.reserveid_class").attr("title");
						historypopup(reserveid);
					},
					loadComplete : function(){
						// リサイズ設定その1
						$("#allhistoryTable").jqGrid('setGridWidth', $("#div_dummy").width(), true);
					},
				});

				// フィルタ設定
				jQuery("#allhistoryTable").jqGrid('filterToolbar',{
					//searchOnEnter : true
				});

				/* 日付範囲フィルタリング設定 */
				$("input#gs_reservationdate").attr("style", "width:77%; padding:0px; display:inline;");
				$('<br /><div id="gs_from" style="width:35px; padding:0px; display:inline-block; vertical-align:middle; text-align:right; font-size: 12px;">To:</div><input id="gs_reservationdate_greater" type="text" value="" name="reservationdate_greater" style="width:77%; padding:0px; display:inline;">').insertAfter("#gs_reservationdate");
				$('<div id="gs_to" style="width:35px; padding:0px; display:inline-block; vertical-align:middle; text-align:right; font-size: 12px;">From:</div>').insertBefore('#gs_reservationdate');

				{literal}
				var ldate, gdate;

				$('#gs_reservationdate').datepicker({
					dateFormat: 'yy/mm/dd(D)',
					onSelect: function(dateText, inst){
						ldate = dateText;
						
						var d = $('#gs_reservationdate').val();
						var r = d.replace(/\(.*\)/, '');
						$('#gs_reservationdate').val( dateFormat(r,'Y/m/d(wj)') );
						
						filterByDate();
					}
				});

				$('#gs_reservationdate_greater').datepicker({
					dateFormat: 'yy/mm/dd(D)',
					onSelect: function(dateText, inst){
						gdate = dateText;
						
						var d = $('#gs_reservationdate_greater').val();
						var r = d.replace(/\(.*\)/, '');
						$('#gs_reservationdate_greater').val( dateFormat(r,'Y/m/d(wj)') );
						
						filterByDate();
					}
				});

				function filterByDate(){
					var myfilter = {groupOp: "AND", rules: []};

					if(ldate != undefined)
						myfilter.rules.push({field:"reservationdate",op:"ge",data:ldate});
					if(gdate != undefined)
						myfilter.rules.push({field:"reservationdate",op:"le",data:gdate});

					var grid = $("#allhistoryTable");
					grid[0].p.search = myfilter.rules.length>0;
					$.extend(grid[0].p.postData,{filters:JSON.stringify(myfilter)});
					grid.trigger("reloadGrid",[{page:1}]);
				}
				{/literal}

				/* シフトと文書はセレクトボックスを表示 */
				{foreach from=$shifts item=shift name=shifts}
					var option = document.createElement('option');
					var gs_starttime = document.getElementById('gs_starttime');
					option.setAttribute('value', toAlpha({$smarty.foreach.shifts.index})+" {$vDate->dateFormat($shift->m_timetables_starttime, 'H:i')}")
					option.appendChild(document.createTextNode(toAlpha({$smarty.foreach.shifts.index})+" {$vDate->dateFormat($shift->m_timetables_starttime, 'H:i')}"));
					gs_starttime.appendChild(option);
				{/foreach}

				{foreach from=$dockinds item=dockind name=dockinds}
					var option = document.createElement('option');
					var gs_document_category = document.getElementById('gs_document_category');
					option.setAttribute('value', '{$dockind->document_category}');
					option.appendChild(document.createTextNode('{$dockind->document_category}'));
					gs_document_category.appendChild(option);
				{/foreach}

				$("select#gs_starttime").attr("style", "width:85%; padding:0px;");
				$("select#gs_document_category").attr("style", "width:85%; padding:0px;");

				// 日付はreadonly
				$("input#gs_reservationdate").attr("readonly", "readonly");
				$("input#gs_reservationdate_greater").attr("readonly", "readonly");

				// リサイズ設定その2
				$(window).bind('resize', function () {
					$('#allhistoryTable').setGridWidth('915px');//$("#div_dummy").width()*0.99);
				}).trigger('resize');

				// 印刷処理
				$("a.affirm").click(function(){
					printModal();
					//$.printArea("#print_parent");
				});

				$("#loginStatusTrigger").miniMenu($("#loginStatus"));
				$('#allhistoryTable').tooltip();
				$(window).bind('resize', function(){
					$('#allhistoryTable').setGridWidth($('#main').width());
				}).trigger('resize');
			});
		</script>
		<!--[if lte IE 9]>
		<script src="/js/flexie.min.js" type="text/javascript"></script>
		<![endif]-->
	</body>
</html>