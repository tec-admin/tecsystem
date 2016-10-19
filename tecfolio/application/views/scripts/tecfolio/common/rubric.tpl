<!doctype html>
<html lang="ja">
<head>
<!--
	{t}ファイル「%1」が見つかりません{/t}
	{t}Excelファイルを指定してください{/t}
	{t}評点を入力してください(%1%2){/t}
	{t}評点は0以上100以下の数値を入力してください(%1%2){/t}
	{t}評点は重複のない値を入力してください{/t}
	{t}尺度は%1項目以内で入力してください{/t}
	{t}観点は%1項目以内で入力してください{/t}
	{t}尺度の説明を入力してください(%1%2){/t}
	{t}タイトルを入力してください{/t}
	{t}コピー先に同一のルーブリックが存在します{/t}
	{t}ライセンスを選択してください{/t}
	{t}内容を入力してください{/t}
	{t}評点の数と尺度の数が一致しません{/t}
-->
	{include file='tecfolio/shared/common_head.tpl'}
	{assign var='subject_flg' value={$controllerName == 'tecfolio/professor' && !empty($subjectid)} nocache}
	
	<script>
		function submitDeleteRubric()
		{
			$('#deleteRubricForm').submit();
		}
		function submitCopyRubric()
		{
			if($('#copy_mytheme_id').val())
				$('#copyRubricForm').submit();
			else
				$('#copyRubricNoTargetDialog').bPopup();
		}
	</script>
</head>

<body class="commons">
	<div id="topbar">
		{include file='tecfolio/shared/common_top.tpl'}
	</div>
	<div id="contents" class="fix-wrap">
		{include file='tecfolio/shared/common_menu.tpl'}
		<div id="main">
			<article>
				<div id="wrapcontent" class="noTabs">
				{if !empty($selected) && empty($error)}
					<div class="head">
						<h1 id="content_name">◆{$selected->name}</h1>
						{if !empty($selected->createdate) && empty($laboid)}<div id="starting_period">{t 1={$vDate->dateFormat($selected->createdate, 'Y/m/d')}}取り組み期間：%1～{/t}</div>{/if}
					</div>
					<div class="contents container" id="activeContents">
						<div class="icons">
							{if !empty($subject_flg) || empty($subjectid)}
							<div class="wrapicon">
								<div class="trashcan noMenu" id="trashcan" title="{t}ルーブリックを削除します{/t}"><i class="trashcan"></i></div>
							</div>
							
							<div class="wrapicon">
							<form method="POST" action="{$baseurl}/{$controllerName}/importrubric/id/{$selected->id}" name="importRubricForm" id="importRubricForm" enctype="multipart/form-data">
								<div class="import noMenu" id="import" title="{t}ルーブリックをインポートします{/t}"><i class="import"></i></div>
								<input type="file" name="file" id="input_import" style="display:none;" />
							</form>
							</div>
							{/if}
							<div class="wrapicon">
								<div class="export noMenu" id="export" title="{t}ルーブリックをエクスポートします{/t}"><i class="export"></i></div>
							</div>
							
							<div class="wrapicon">
								<div class="copy noMenu" title="{t}ルーブリックを他のテーマへコピーします{/t}"><i class="copy"></i></div>
							</div>
							
							<span style="clear: both;"></span>
						</div>
						<form method="POST" action="{$baseurl}/{$controllerName}/exportrubric" name="exportRubricForm" id="exportRubricForm" enctype="multipart/form-data">
							<input type="hidden" name="id" id="exportid" />
						</form>
						<form method="POST" action="{$baseurl}/{$controllerName}/deleterubric/id/{$selected->id}" name="deleteRubricForm" id="deleteRubricForm" enctype="multipart/form-data">
						<div class="wrapTableHead tblRubric">
							<table class="contentsTbl rubricTbl inverse">
								<thead>
									<tr>
										<th class="w1"><input type="checkbox" id="contentCheckAll" /></th>
										<th class="w1 hasOrder" data-id="rubric_num">{t}No.{/t}</th>
										<th class="hasOrder" data-id="rubric_title">{t}ルーブリック名称{/t}</th>
										<th class="w4 hasOrder" data-id="rubric_author">{t}原著者{/t}</th>
										<th class="w4 hasOrder" data-id="rubric_editor">{t}改変者{/t}</th>
										<th class="timestamp hasOrder" data-id="rubric_num">{t}最終更新日時{/t}</th>
										<th class="license hasOrder" data-id="rubric_license">{t}ライセンス{/t}</th>
									</tr>
								</thead>
							</table>
						</div>
						<div class="wrapTableBody tblRubric">
							<table class="contentsTbl rubricTbl" id="contentsTbl">
								<thead id="contentsInnerHead" style="display:none;">
									<tr>
										<th></th>
										<th id="rubric_num">{t}No.{/t}</th>
										<th id="rubric_title">{t}ルーブリック名称{/t}</th>
										<th id="rubric_author">{t}原著者{/t}</th>
										<th id="rubric_editor">{t}改変者{/t}</th>
										<th>{t}最終更新日時{/t}</th>
										<th id="rubric_license">{t}ライセンス{/t}</th>
									</tr>
								</thead>
								<tbody id="contentsInner">
								</tbody>
							</table>
						</div>
						</form>
						{include file='tecfolio/shared/pager.tpl' pagerId="pager"}
					</div>
				{elseif !empty($error)}
				<p class="empty">{t}存在しないか、アクセス許可のないIDです。{/t}</p>
				{else}
				<p class="empty">{t}左のメニューからテーマを選択してください。{/t}</p>
				{/if}
				</div>
			</article>
			
			<div id="showRubricDialog" class="dialog extra">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}ルーブリックの表示{/t}</div>
				<div class="dialogWrap">
					<div class="dialogInner dialogTop">
						<div class="dialogTitle dialogLeft">
							<div>{t}ユニット{/t}</div>
						</div>
						<div class="dialogTitle dialogRight">
							<div id="dialogTitle"></div>
						</div>
					</div>
					<div class="dialogInner dialogSecond">
						<div class="dialogTheme dialogLeft">
							<div>{t}課題文{/t}</div>
						</div>
						<div class="dialogTheme dialogRight">
							<div id="dialogTheme"></div>
						</div>
					</div>
					<div class="dialogMatrix" id="dialogMatrix">
						<table class="rubricMatrix" id="selectedMatrix">
						</table>
					</div>
					<div class="dialogInner dialogBottom">
						<div class="dialogMemo dialogLeft">
							<div>{t}メモ{/t}</div>
						</div>
						<div class="dialogMemo dialogRight">
							<div id="dialogMemo"></div>
						</div>
					</div>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="importRubricResultDialog" class="dialog">
				<div id="importRubricResultInner" class="cmpsub"></div>
			</div>
			
			<div id="deleteRubricDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}ルーブリックの削除{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}選択されたルーブリックを削除します。この操作は元に戻せません。よろしいですか？{/t}
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitDeleteRubric();" class="affirm">{t}削除する{/t}</a>
				</div>
			</div>
			<div id="deleteRubricCompDialog" class="dialog">
				<div class="cmpsub">{t}ルーブリックを削除しました{/t}</div>
			</div>
			
			<div id="deleteRubricFailDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}ルーブリックの削除{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}削除するルーブリックにチェックを入れてください。{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="exportRubricFailDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}ルーブリックのエクスポート{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}エクスポートするルーブリックにチェックを入れてください。{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="exportRubricMultipleDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}ルーブリックのエクスポート{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}同時に複数ルーブリックのエクスポートはできません。{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="exportRubricForbiddenDialog" class="dialog">
				<div id="exportRubricForbiddenInner" class="cmpsub"></div>
			</div>
			
			<div id="copyRubricFailDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}ルーブリックのコピー{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}コピーするルーブリックにチェックを入れてください。{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="copyRubricDialog" class="dialog small">
				<form method="POST" action="{$baseurl}/{$controllerName}/copyrubric/id/{$selected->id}" name="copyRubricForm" id="copyRubricForm" enctype="multipart/form-data">
				<input type="hidden" name="copy_mytheme_id" id="copy_mytheme_id" />
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}ルーブリックのコピー{/t}</div>
				<div class="dialogWrap">
					<div class="topDesc">
						<p>{t}以下のルーブリックをコピーします。{/t}</p>
						<div id="copyArea"></div>
						<p>{t}コピー先を選択してください。{/t}</p>
					</div>
					<ul class="dialogMenu centerBlock dialogBottom">
						<li id="dmMytheme">
							<div class="dmTitle dmMytheme">{t}Myテーマ{/t}</div>
							{foreach from=$mythemes item=mytheme}
								{if $mytheme->id != $selected->id}
									<div class="dmList dmMythemeList" id="dm_{$mytheme->id}" data-id="{$mytheme->id}">{$mytheme->name}</div>
								{/if}
							{/foreach}
							{if !empty($subject_flg)}
								<div class="dmTitle dmSubject">{t}授業科目{/t}</div>
								{foreach from=$subjects item=subject}
									{if $subject->id != $selected->id}
										<div class="dmList dmSubjectList" id="dm_{$subject->id}" data-id="{$subject->id}"><span class="head">{$subject->yogen}　</span>{$subject->class_subject}</div>
									{/if}
								{/foreach}
							{/if}
							{if "LABO_`$member->id`" !== $selected->id}
								<div class="dmTitle dmFacility">{t}学内施設{/t}</div>
								<div class="dmList dmFacilityList" id="dm_LABO_{$member->id}" data-id="LABO_{$member->id}">{t}ライティングラボ{/t}</div>
							{/if}
						</li>
					</ul>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitCopyRubric();" class="affirm">{t}コピーする{/t}</a>
				</div>
				</form>
			</div>
			
			<div id="copyRubricCompDialog" class="dialog">
				<div class="cmpsub">{t}ルーブリックをコピーしました{/t}</div>
			</div>
			
			<div id="copyRubricNoTargetDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}ルーブリックのコピー{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}コピー先を選択してください。{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="copyRubricDuplicateDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}ルーブリックのコピー{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}コピー先に同一のルーブリックが存在します。{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			{include file='tecfolio/shared/common_dialog.tpl'}
		</div>
	</div>
	{include file="../../common/foot_v2.php"}
	{if !empty($selected)}
	<script>
		function callGetRubric(response)
		{
			// マスタデータ項目挿入
			var master = response['rubric'];
			$('#dialogTitle').html(master['name']);
			$('#dialogTheme').html(master['theme']);
			$('#dialogMemo').html(master['memo']);
			
			var tgtStr = 'selectedMatrix';
			
			// テーブル要素削除
			$('#' + tgtStr + ' > *').each(function(){
				$(this).remove();
			});
			
			var res = response['matrix'];
			
			// 各要素の準備と固定項目の挿入
			var target	= document.getElementById(tgtStr);
			var thead	= document.createElement('thead');
				var trh		= document.createElement('tr');
					var th		= document.createElement('th');
					th.innerHTML = "{t}評価の観点{/t}";
					trh.appendChild(th);
					
					var th		= document.createElement('th');
					th.innerHTML = "{t}評価の観点の説明{/t}";
					trh.appendChild(th);
			
			var tbody	= document.createElement('tbody');
			
			var max_rank	= 0;
			var max_v		= 0;
			
			// 前提として、縦・横の順に整列したデータを取得している
			for(var i = 0; i < res.length; i++)
			{
				if(res[i]['rank'] != undefined && res[i]['rank'] > max_rank)
					max_rank	= res[i]['rank'];
				
				if(res[i]['vertical'] != undefined && res[i]['vertical'] > max_v)
					max_v		= res[i]['vertical'];
				
				// 1行目
				if(res[i]['vertical'] == 0)
				{
					var th			= document.createElement('th');
					th.innerHTML	= res[i]['description'];
					th.setAttribute('class', 'rate');
					trh.appendChild(th);
				}
				else
				{
					// 1列目
					if(res[i]['horizontal'] == 0)
					{
						// 評価の観点
						var tr			= document.createElement('tr');
						
						var th			= document.createElement('th');
						th.innerHTML	= '(' + res[i]['vertical'] + ')<br />' + res[i]['description'];
						tr.appendChild(th);
					}
					else if(res[i]['horizontal'] == 1)
					{
						// 評価の観点の説明
						var td			= document.createElement('td');
						td.innerHTML	= res[i]['description'];
						tr.appendChild(td);
					}
					else
					{
						var td			= document.createElement('td');
						td.innerHTML	= res[i]['description'];
						// row=縦位置, col=横位置, rank=評価値
						td.setAttribute('class', 'rating row' + res[i]['vertical'] + ' col' + res[i]['horizontal'] + ' rank' + res[i]['rank']);
						td.setAttribute('data-num', res[i]['vertical']);
						td.setAttribute('data-val', res[i]['rank']);
						tr.appendChild(td);
					}
					
					if(res[i+1] == undefined || res[i+1]['horizontal'] == 0)
						tbody.appendChild(tr);
				}
			}
			
			thead.appendChild(trh);
			target.appendChild(thead);
			target.appendChild(tbody);
			
			$('#showRubricDialog').bPopup({
				position	: ['auto',0],
				follow		: [true,false]
			});
		}
		
		function clearRubricTable()
		{
			$('#contentsInner > tr').each(function(){
				$(this).remove();
			});
		}
		
		function createTable(array)
		{
			var target	= document.getElementById('contentsInner');
			
			for(i in array)
			{
				var tr		= document.createElement('tr');
				tr.setAttribute('class', 'pfline');
				tr.setAttribute('data-id', array[i]['m_rubric_id']);
				
				// チェックボックス
				var td 		= document.createElement('td');
				td.setAttribute('class', 'w1 fixed check');
				var input	= document.createElement('input');
				input.setAttribute('type', 'checkbox');
				input.setAttribute('class', 'contentCheck');
				input.setAttribute('name', 'removecheck[]');
				input.setAttribute('value', array[i]['m_rubric_id']);
				input.setAttribute('data-name', array[i]['m_rubric_name']);
				input.setAttribute('data-flag', array[i]['export_flag']);
				td.appendChild(input);
				tr.appendChild(td);
				
				// No.
				var td 		= document.createElement('td');
				td.setAttribute('class', 'w1 num');
				td.innerHTML = Number(i) + 1;
				tr.appendChild(td);
				
				// ルーブリック名称
				var td 		= document.createElement('td');
				td.innerHTML = array[i]['m_rubric_name'];
				tr.appendChild(td);
				
				// 原案者
				var td 		= document.createElement('td');
				td.setAttribute('class', 'w4 fixed');
				if(array[i]['m_rubric_original_name_jp'] != undefined)
					td.innerHTML = array[i]['m_rubric_original_name_jp'];
				tr.appendChild(td);
				
				// 改変者
				var td 		= document.createElement('td');
				td.setAttribute('class', 'w4 fixed');
				if(array[i]['m_rubric_editor_name_jp'] != undefined)
					td.innerHTML = array[i]['m_rubric_editor_name_jp'];
				tr.appendChild(td);
				
				// 最終更新日時
				var td 		= document.createElement('td');
				td.setAttribute('class', 'fixed timestamp');
				td.innerHTML = dateFormat(array[i]['lastupdate']);
				tr.appendChild(td);
				
				// ライセンス
				var td 		= document.createElement('td');
				td.setAttribute('class', 'license fixed');
				td.innerHTML = array[i]['license_name'];
				tr.appendChild(td);
				
				target.appendChild(tr);
			}
		}
		
		function drawRubrics()
		{
			ajaxSubmitUrl(baseUrl + '/getrubric/id/{$selected->id}', function(response){
				clearRubricTable();
				
				// ポートフォリオ一覧テーブルの作成
				createTable(response['rubrics']);
				
				if(response['rubrics'].length > 0)
				{
					setTableSorter($('#contentsTbl'));
				}
				else
				{
					$('#pager .pagedisplay').html('データなし');
				}
				
				// ポートフォリオ一覧テーブルから各行クリック時に詳細を表示するイベント
				$('.pfline > td:not(.check)').each(function(){
					$(this).click(function(){
						ajaxSubmitUrl(baseUrl + '/getrubricandmatrix/id/' + $(this).parent().data('id'), callGetRubric);
					});
				});
			});
		}
		
		function clearThemeList()
		{
			$('.dmList').each(function(){
				$(this).remove();
			});
		}
		
		function createThemeList(array)
		{
			var target 	= document.getElementById('dmMytheme');
			
			$(target).children('.dmSubject').remove();
			$(target).children('.dmFacility').remove();
			
			var arr_mytheme = array['mytheme'];
			var arr_subject = array['subject'];
			
			for(var i in arr_mytheme)
			{
				if(selectedId !== arr_mytheme[i]['id'])
				{
					var div = document.createElement('div');
					div.setAttribute('class', 'dmList dmMythemeList');
					div.setAttribute('id', 'dm_' + arr_mytheme[i]['id']);
					div.setAttribute('data-id', arr_mytheme[i]['id']);
					div.innerHTML = arr_mytheme[i]['name'];
					
					target.appendChild(div);
				}
			}
			
			{if !empty($subject_flg)}
			var div = document.createElement('div');
			div.setAttribute('class', 'dmTitle dmSubject');
			div.innerHTML = "{t}授業科目{/t}";
			
			target.appendChild(div);
			
			for(var i in arr_subject)
			{
				if(selectedId !== arr_subject[i]['id'])
				{
					var div = document.createElement('div');
					div.setAttribute('class', 'dmList dmSubjectList');
					div.setAttribute('id', 'dm_' + arr_subject[i]['id']);
					div.setAttribute('data-id', arr_subject[i]['id']);
					div.innerHTML = arr_subject[i]['name'];
					
					target.appendChild(div);
				}
			}
			{/if}
			
			if(selectedId !== 'LABO_{$member->id}')
			{
				var div = document.createElement('div');
				div.setAttribute('class', 'dmTitle dmFacility');
				div.innerHTML = "{t}学内施設{/t}";
				
				target.appendChild(div);
				
				var div = document.createElement('div');
				div.setAttribute('class', 'dmList dmFacilityList dmInsertList');
				div.setAttribute('id', 'dm_LABO_{$member->id}');
				div.setAttribute('data-id', 'LABO_{$member->id}');
				div.innerHTML = "{t}ライティングラボ{/t}";
				
				target.appendChild(div);
			}
		}

		// Myテーマを引用する部分の再描画
		function drawThemeList(response)
		{
			clearThemeList();
			
			// Myテーマ(など)の描画
			createThemeList(response);
			
			setListEvent();
		}
		
		// コピーダイアログ内のテーマ選択時
		function setListEvent()
		{
			$('.dmList').click(function() {
				$('.dmList').each(function() {
					$(this).removeClass('active');
				});
				
				$(this).addClass('active');
				$('#copy_mytheme_id').prop('value', $(this).data('id'));
			});
		}
		
		$(function(){
			drawRubrics();
			
			// チェックボックスによる全選択
			$('#contentCheckAll').change(function(){
				var v 	= $(this).prop('checked');
				$('#contentsInner .contentCheck').each(function(){
					$(this).prop('checked', v);
				});
			});
		
			// エクスポートボタン押下時
			$('#export').click(function(){
				var flg = 0;
				var cnt = 0;
				
				$('.contentCheck').each(function(){
					if($(this).prop('checked'))
					{
						cnt++;
					}
				});
				
				$('.contentCheck').each(function(){
					if($(this).prop('checked'))
					{
						if($(this).data('flag') == '0')
						{
							//$('#exportRubricForbiddenInner').html('ルーブリック「' + $(this).data('name') + '」はエクスポートが許可されていません');
							var txt = "{t}ルーブリック「%1」はエクスポートが許可されていません{/t}";
							$('#exportRubricForbiddenInner').html(txt.sprintf($(this).data('name')));
							flg = 2;
							return false;
						}
						
						$('#exportid').prop('value', $(this).prop('value'));
						flg = 1;
						return true;
					}
				});
				
				if(cnt == 1 && flg == 1)
					$('#exportRubricForm').submit();				// 正常
				else if(flg == 2)
					$('#exportRubricForbiddenDialog').bPopup();		// エクスポート禁止
				else if(cnt > 1)
					$('#exportRubricMultipleDialog').bPopup();		// 複数チェック
				else
					$('#exportRubricFailDialog').bPopup();			// 未チェック
			});
			
			// インポートボタン押下時、ファイルボタン押下イベントを発火する
			$('#import').click(function(){
				$('#input_import').click();
			});
			
			// ファイル選択時イベント
			$('#input_import').change(function(event){
				$('#importRubricForm').submit();
			});
			
			// インポート実行
			$('#importRubricForm').submit(function(event){
				ajaxSubmit(this, event, 
				function(response){
					//$('#importRubricResultInner').html('ルーブリック「' + response['name'] + '」をインポートしました');
					var txt = "{t}ルーブリック「%1」をインポートしました{/t}";
					$('#importRubricResultInner').html(txt.sprintf(response['name']));
					$('#importRubricResultDialog').bPopup();
					drawRubrics();
				});
			});
			
			// 削除ボタン押下時
			$('#activeContents .trashcan').click(function(){
				var flg = 0;
				$('#contentsInner .contentCheck').each(function()
				{
					if($(this).prop('checked')){
						flg = 1;
						return true;
					}
				});
				
				if(flg)
					$('#deleteRubricDialog').bPopup();
				else
					$('#deleteRubricFailDialog').bPopup();
			});
			
			setListEvent();
			
			// コピーボタン押下時
			$('#activeContents .copy').click(function() {
				var flg = 0;
				$('#contentsInner .contentCheck').each(function(){
					if($(this).prop('checked')){
						flg = 1;
						return true;
					}
				});
				
				if(flg)
				{
					$('#copyArea > *').each(function(){
						$(this).remove();
					});
					$('.contentCheck').each(function(){
						if($(this).prop('checked'))
						{
							$('#copyArea').append('<div style="font-weight: bold;">「' + $(this).data('name') + '」</div>');
							$('#copyArea').append('<input type="hidden" name="copy_id[]" value="' + $(this).prop('value') + '" />');
						}
					});
					$('#copyRubricDialog').bPopup();
				}
				else
				{
					$('#copyRubricFailDialog').bPopup();
				}
			});
			
			$('#deleteRubricForm').submit(function(event){
				ajaxSubmit(this, event, 
				function(response){
					$('#deleteRubricCompDialog').bPopup();
					$('#deleteRubricDialog').bPopup().close();
					drawRubrics();
				});
			});
			
			$('#copyRubricForm').submit(function(event){
				ajaxSubmit(this, event, 
				function(response){
					$('#copyRubricCompDialog').bPopup();
					$('#copyRubricDialog').bPopup().close();
				},
				function(response){
					$('#copyRubricDuplicateDialog').bPopup();
					$('#copyRubricDialog').bPopup().close();
				});
			});
		});
	</script>
	{/if}
</body>
</html>