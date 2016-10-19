<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="ja">
<head>
<!--
	{t}ポートフォリオで参照されているコンテンツをゴミ箱に移動することはできません{/t}
	{t}検索ワードを入力してください{/t}
	{t}検索ワードを入力してください{/t}
-->
	{include file='tecfolio/shared/common_head.tpl'}
	
	<script>
		
		var baseUrl		= '{$baseurl}/{$controllerName}';
		
		function clearTables()
		{
			$('#contentsInner > tr').each(function(){
				$(this).remove();
			});
			
			$('#removedInner > tr').each(function(){
				$(this).remove();
			});
		}
		
		{literal}
		function createTable(target, array)
		{
			for(i in array)
			{
				var tr		= document.createElement('tr');
				
				// チェックボックス
				var td 		= document.createElement('td');
				td.setAttribute('class', 'w1 fixed');
				
				var input	= document.createElement('input');
				input.setAttribute('type', 'checkbox');
				input.setAttribute('class', 'contentCheck');
				input.setAttribute('name', 'removecheck[' + i + ']');
				input.setAttribute('value', array[i]['id']);
				
				if(array[i]['content_files_name'] != undefined)
				{
					input.setAttribute('data-name', array[i]['content_files_name']);
				}
				else if(array[i]['name'] != undefined)
				{
					input.setAttribute('data-name', array[i]['name']);
				}
				else
				{
					input.setAttribute('data-name', array[i]['ref_title']);
					input.setAttribute('data-url', array[i]['ref_url']);
					input.setAttribute('data-class', array[i]['ref_class']);
				}
				
				td.appendChild(input);
				
				if(array[i]['ref_title'] != undefined)
				{
					var rUrl	= document.createElement('input');
					rUrl.setAttribute('type', 'hidden');
					rUrl.setAttribute('name', 'ref_url[' + i + ']');
					rUrl.setAttribute('value', array[i]['ref_url']);
					
					td.appendChild(rUrl);
					
					var rTitle	= document.createElement('input');
					rTitle.setAttribute('type', 'hidden');
					rTitle.setAttribute('name', 'ref_title[' + i + ']');
					rTitle.setAttribute('value', array[i]['ref_title']);
					
					td.appendChild(rTitle);
				}
				
				tr.appendChild(td);
				
				// No.
				var td 		= document.createElement('td');
				td.setAttribute('class', 'w1 num');
				td.innerHTML = Number(i) + 1;
				tr.appendChild(td);
				
				// ファイルタイプ
				var td 		= document.createElement('td');
				td.setAttribute('class', 'w1 fixed');
				var div		= document.createElement('div');
				div.setAttribute('class', 'cl');
				
				if(array[i]['ref_class'] != undefined)
				{
					var icon	= document.createElement('i');
					
					if(array[i]['ref_class'] == '0')
					{
						icon.setAttribute('class', 'cinii');
						td.className += " {sortValue: 1}";
					}
					else if(array[i]['ref_class'] == '1')
					{
						icon.setAttribute('class', 'amazon');
						td.className += " {sortValue: 2}";
					}
					
					div.appendChild(icon);
				}
				else if(array[i]['content_files_type'] != undefined)
				{
					var icon	= document.createElement('i');
					
					switch(array[i]['content_files_type'])
					{
						case 'application/msword':
						case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
						case 'application/vnd.ms-word.document.macroEnabled.12':
							icon.setAttribute('class', 'hasIcon word');
							td.className += " {sortValue: 3}";
							break;
						case 'application/vnd.ms-excel':
						case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
						case 'application/vnd.ms-excel.sheet.macroEnabled.12':
							icon.setAttribute('class', 'hasIcon excel');
							td.className += " {sortValue: 4}";
							break;
						case 'application/vnd.ms-powerpoint':
						case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
						case 'application/vnd.ms-powerpoint.presentation.macroEnabled.12':
							icon.setAttribute('class', 'hasIcon powerpoint');
							td.className += " {sortValue: 5}";
							break;
						case 'application/pdf':
							icon.setAttribute('class', 'hasIcon pdf');
							td.className += " {sortValue: 6}";
							break;
						case 'text/plain':
							icon.setAttribute('class', 'hasIcon textdoc');
							td.className += " {sortValue: 7}";
							break;
						default:
							icon.setAttribute('class', 'noIcon');
							td.className += " {sortValue: 0}";
					}
					
					div.appendChild(icon);
				}
				else
				{
					td.className += " {sortValue: 0}";
				}
				
				td.appendChild(div);
				tr.appendChild(td);
				
				// コンテンツ
				var td 		= document.createElement('td');
				td.setAttribute('class', '');
				var a		= document.createElement('a');
				
				if(array[i]['content_files_name'] != undefined)
				{
					// コンテンツアップロードの場合
					a.setAttribute('href', baseUrl + '/downloadcontent/id/' + array[i]['content_files_id']);
					a.innerHTML = array[i]['content_files_name'];
				}
				else if(array[i]['name'] != undefined)
				{
					// 学内施設の引用の場合
					a.setAttribute('href', baseUrl + '/download/id/' + array[i]['id']);
					a.innerHTML = array[i]['name'];
				}
				else
				{
					a.setAttribute('href', array[i]['ref_url']);
					a.setAttribute('target', '_blank');
					a.innerHTML = array[i]['ref_title'];
				}
				td.appendChild(a);
				tr.appendChild(td);
				
				// サイズ
				var td 		= document.createElement('td');
				td.setAttribute('class', 'w3 num');
				var filesize = 0;
				if(array[i]['content_files_createdate'] != undefined)
				{
					filesize = array[i]['content_files_filesize'];
					td.innerHTML = bytesToSize(filesize);
					td.className += " {sortValue: " + filesize + "}";
				}
				else if(array[i]['ref_title'] == undefined)
				{
					filesize = array[i]['filesize'];
					td.innerHTML = bytesToSize(filesize);
					td.className += " {sortValue: " + filesize + "}";
				}
				else
				{
					td.className += " {sortValue: null}";
				}
				tr.appendChild(td);
				
				// アップロード日時
				var td 		= document.createElement('td');
				td.setAttribute('class', 'fixed timestamp');
				var createdate = 0;
				if(array[i]['content_files_createdate'] != undefined)
				{
					createdate = array[i]['content_files_createdate'];
					td.innerHTML = dateFormat(createdate);
					td.className += " {sortValue: '" + createdate + "'}";
				}
				else
				{
					createdate = array[i]['createdate'];
					td.innerHTML = dateFormat(array[i]['createdate']);
					td.className += " {sortValue: '" + createdate + "'}";
				}
				tr.appendChild(td);
				
				// ポートフォリオ
				var td 		= document.createElement('td');
				td.setAttribute('class', 'portfolio fixed');
				if(array[i]['portfolio_contents_count'] != undefined)
					td.innerHTML = 'p';
				tr.appendChild(td);
				
				target.appendChild(tr);
			}
		}
		{/literal}
		
		function drawContents(order, asc)
		{
			{if !empty($selected)}
				var url = '{$baseurl}/{$controllerName}/getcontents/id/{$selected->id}';
				if(order != undefined)
					url += '/order/' + order;
				if(asc != undefined)
					url += '/asc/' + asc;
				
				ajaxSubmitUrl(url, function(response){
					clearTables();
					
					var contents	= document.getElementById('contentsInner');
					createTable(contents, response['contents']);
					
					if(response['contents'].length > 0)
					{
						setTableSorter($('#contentsTbl'));
					}
					else
					{
						$('#pager .pagedisplay').html("{t}データなし{/t}");
					}
					
					var removed		= document.getElementById('removedInner');
					createTable(removed, response['trashes']);
				});
			{/if}
		}
		
		// コンテンツ削除
		function submitContentsRemove()
		{
			$('#removeContentsForm').submit();
		}
		
		// コンテンツ圧縮ファイルダウンロード
		function downloadContentsZip()
		{
			$('#removeContentsForm').submit();
		}
		
		// コンテンツ復帰
		function submitManipulateTrashes()
		{
			$('#manipulateTrashesForm').submit();
		}
		
		// コンテンツコピー
		function submitCopyContents()
		{
			if($('#copy_mytheme_id').val())
				$('#copyContentsForm').submit();
			else
				$('#copyContentsNoTargetDialog').bPopup();
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
				<div id="wrapcontent" class="hasTabs" style="display:none;">
				{if !empty($selected) && empty($error)}
					<div class="head">
						<h1 id="content_name">◆{$selected->name}</h1>
						{if empty($laboid)}<div id="starting_period">{t 1={$vDate->dateFormat($selected->createdate, 'Y/m/d')}}取り組み期間：%1～{/t}</div>{/if}
					</div>
					<div class="body">
						<ul class="tabs{if !empty($laboid)} ui-single{/if}">
							<li><a href="#activeContents">{t}コンテンツ{/t}</a></li>
							{if empty($laboid)}<li><a href="#removedContents">{t}ゴミ箱{/t}</a></li>{/if}
						</ul>
						<div class="contents container" id="activeContents">
							<div class="icons">
								{if empty($laboid)}
								<div class="wrapicon">
									<div class="trashcan noMenu" title="{t}選択したコンテンツをゴミ箱へ移動します{/t}"><i class="trashcan"></i></div>
								</div>
								
								<div class="wrapicon">
									<div class="add miniMenu" title="{t}コンテンツを追加します{/t}"><i class="add"></i></div>
									<div class="wrapdrop">
										<ul class="add droplist">
											<form method="post" action="{$baseurl}/{$controllerName}/insertcontents/id/{$selected->id}" name="insertContentsForm" id="insertContentsForm" enctype="multipart/form-data">
											<li class="mainMenu list_pc" title="{t}パソコンからコンテンツを追加します※Ctrlで複数選択ができます{/t}"><i class="pc"></i><a class="pc">{t}パソコンから{/t}</a></li>
											<input type="file" name="addbypc[]" id="addbypc" style="display:none;" multiple="multiple" />
											</form>
											<li class="mainMenu list_amazon" title="{t}Amazonから文献情報を追加します{/t}"><i class="amazon"></i><a class="amazon">{t}Amazon{/t}</a></li>
											<li class="mainMenu list_cinii" title="{t}Ciniiから文献情報を追加します{/t}"><i class="cinii"></i><a class="cinii">{t}Cinii{/t}</a></li>
										</ul>
									</div>
								</div>
								{/if}
								<div class="wrapicon">
									<div class="copy noMenu" title="{t}他テーマのファイル置場へコンテンツをコピーします{/t}"><i class="copy"></i></div>
								</div>
								
								<div class="wrapicon">
									<div class="download noMenu" title="{t}コンテンツを一括ダウンロードします{/t}"><i class="download"></i></div>
								</div>
								
								<span style="clear: both;"></span>
							</div>
							<form method="post" action="{$baseurl}/{$controllerName}/checkcontents" name="removeContentsForm" id="removeContentsForm" enctype="multipart/form-data">
							<input type="hidden" name="id" id="check_contents_id" value="{$selected->id}" />
							<input type="hidden" name="name" id="check_contents_name" value="{$selected->name}" />
							<input type="hidden" name="switch_flg" id="switch_flg" />
							<div class="wrapTableHead tblFile">
								<table class="contentsTbl">
									<thead>
										<tr>
											<th class="w1 main"><input type="checkbox" id="contentCheckAll" /></th>
											<th class="w1 main hasOrder" data-id="content_num">{t}No.{/t}</th>
											<th class="w1 main hasOrder" data-id="content_ref_class">{t}類{/t}</th>
											<th class="main hasOrder" data-id="content_title">{t}コンテンツ{/t}</th>
											<th class="w3 main hasOrder" data-id="content_filesize">{t}サイズ{/t}</th>
											<th class="timestamp main hasOrder" data-id="content_num">{t}アップロード日時{/t}</th>
											<th class="portfolio green hasOrder" data-id="content_portfolio">{t}ポートフォリオ{/t}</th>
										</tr>
									</thead>
								</table>
							</div>
							<div class="wrapTableBody tblFile">
							{literal}
								<table class="contentsTbl" id="contentsTbl">
									<thead id="contentsInnerHead" style="display:none;">
										<tr>
											<th></th>
											<th id="content_num">{t}No.{/t}</th>
											<th class="{sorter:'metadata'}" id="content_ref_class">{t}類{/t}</th>
											<th id="content_title">{t}コンテンツ{/t}</th>
											<th class="{sorter:'metadata'}" id="content_filesize">{t}サイズ{/t}</th>
											<th>{t}アップロード日時{/t}</th>
											<th id="content_portfolio">{t}ポートフォリオ{/t}</th>
										</tr>
									</thead>
									<tbody id="contentsInner">
									</tbody>
								</table>
							{/literal}
							</div>
							</form>
							{include file='tecfolio/shared/pager.tpl' pagerId="pager"}
						</div>
						{if empty($laboid)}
						<div class="contents container" id="removedContents">
							<div class="icons">
								<div class="wrapicon">
									<div class="recover noMenu" title="{t}選択したコンテンツを元に戻します{/t}"><i class="recover"></i></div>
								</div>
								<div class="wrapicon">
									<div class="perm_delete noMenu" title="{t}選択したコンテンツを完全に削除します{/t}"><i class="perm_delete"></i></div>
								</div>
							</div>
							
							<span style="clear: both;"></span>
							
							<form method="post" action="{$baseurl}/{$controllerName}/manipulateremoved" name="manipulateTrashesForm" id="manipulateTrashesForm" enctype="multipart/form-data">
							<input type="hidden" name="removedflg" id="removedflg" value="0" />
							<div class="wrapTableHead tblFile">
								<table class="contentsTbl">
									<thead>
										<tr>
											<th class="w1 main"><input type="checkbox" id="removedCheckAll" /></th>
											<th class="w1 main">{t}No.{/t}</th>
											<th class="w1 main">{t}類{/t}</th>
											<th class="main">{t}コンテンツ{/t}</th>
											<th class="w3 main">{t}サイズ{/t}</th>
											<th class="timestamp main">{t}アップロード日時{/t}</th>
											<th class="portfolio green">{t}ポートフォリオ{/t}</th>
										</tr>
									</thead>
								</table>
							</div>
							<div class="wrapTableBody tblFile">
								<table class="contentsTbl">	
									<tbody id="removedInner">
									</tbody>
								</table>
							</div>
							</form>
						</div>
						{/if}
					</div>
				{elseif !empty($error)}
				<p class="empty">{t}存在しないか、アクセス許可のないIDです。{/t}</p>
				{else}
				<p class="empty">{t}左のメニューからテーマを選択してください。{/t}</p>
				{/if}
				</div>
			</article>
			
			{include file='tecfolio/shared/file_dialog.tpl'}
			
			<div id="copyContentsDialog" class="dialog small">
				<form method="post" action="{$baseurl}/{$controllerName}/{if empty($laboid)}copycontents{else}copyfiles{/if}" name="copyContentsForm" id="copyContentsForm" enctype="multipart/form-data">
				<input type="hidden" name="copy_mytheme_id" id="copy_mytheme_id" />
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}コンテンツのコピー{/t}</div>
				<div class="dialogWrap">
					<div class="topDesc">
						<p>{t}以下のコンテンツをコピーします。{/t}</p>
						<div id="copyArea"></div>
						<p>{t}コピー先を選択してください。{/t}</p>
					</div>
					<ul class="dialogMenu centerBlock dialogBottom">
						<li id="dmMytheme">
							<div class="dmTitle dmMytheme">Myテーマ</div>
							{foreach from=$mythemes item=mytheme}
								{if $mytheme->id != $selected->id}
									<div class="dmList dmMythemeList" id="dm_{$mytheme->id}" data-id="{$mytheme->id}">{$mytheme->name}</div>
								{/if}
							{/foreach}
						</li>
					</ul>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitCopyContents();" class="affirm">{t}コピーする{/t}</a>
				</div>
				</form>
			</div>
			
			<div id="copyContentsFailDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}コンテンツのコピー{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}コピーするコンテンツにチェックを入れてください。{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="copyContentsNoTargetDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}コンテンツのコピー{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}コピー先を選択してください。{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="copyContentsCompDialog" class="dialog">
				<div class="cmpsub">{t 1='<span id="copyTargetName"></span>'}コンテンツを %1 へコピーしました{/t}</div>
			</div>
			
			{include file='tecfolio/shared/api.tpl'}
			{include file='tecfolio/shared/common_dialog.tpl'}
		</div>
	</div>
	{include file="../../common/foot_v2.php"}
	<script>
		function callContentsInsert(response)
		{
			// 完了ダイアログ
			$("#contentsInsertDialog").bPopup();
			
			drawContents();
		}
		function callContentsRemove(response)
		{
			// 完了ダイアログ
			$("#contentsRemoveCompDialog").bPopup();
			$("#contentsRemoveDialog").bPopup().close();
			
			drawContents();
		}
		function callManipulateTrashes(response)
		{
			// 完了ダイアログ
			var flg = $('#removedflg').val();
			if(flg == 0)
				$("#recoverCompDialog").bPopup();
			else
			{
				$("#permDeleteCompDialog").bPopup();
				$("#permDeleteDialog").bPopup().close();
			}
			
			drawContents();
		}
		function callCopyContents(response)
		{
			$("#copyContentsCompDialog").bPopup();
			$("#copyContentsDialog").bPopup().close();
			
			drawContents();
		}
		/***** 各処理成功時のコールバック関数ここまで ****/
		
		function checkContents()
		{
			var flg = 0;
			$('#contentsInner .contentCheck').each(function(){
				if($(this).prop('checked')){
					flg = 1;
					return true;
				}
			});
			
			return flg;
		}
		
		function checkRemoved()
		{
			var flg = 0;
			$('#removedInner .contentCheck').each(function(){
				if($(this).prop('checked')){
					flg = 1;
					return true;
				}
			});
			
			return flg;
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
			
			var arr_mytheme = array['mytheme'];
			
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
		}

		// Myテーマを引用する部分の再描画
		function drawThemeList(response)
		{
			clearThemeList();
			
			// Myテーマ(など)の描画
			createThemeList(response);
			
			setListEvent();
		}
		
		function setListEvent()
		{
			$('.dmMythemeList').click(function() {
				$('.dmMythemeList').each(function() {
					$(this).removeClass('active');
				});
				
				$(this).addClass('active');
				$('#copy_mytheme_id').prop('value', $(this).data('id'));
				$('#copyTargetName').html($(this).html());
			});
		}
		
		$(function(){
			drawContents();
			$("#wrapcontent.hasTabs").tabs();
			$("#wrapcontent.hasTabs").css('display', 'block');
			
			// チェックボックスによる全選択
			$('#contentCheckAll').change(function() {
				var v 	= $(this).prop('checked');
				$('#contentsInner .contentCheck').each(function() {
					$(this).prop('checked', v);
				});
			});
			
			$('#removedCheckAll').change(function() {
				var v 	= $(this).prop('checked');
				$('#removedInner .contentCheck').each(function() {
					$(this).prop('checked', v);
				});
			});
			
			// 追加メニュー押下時、ファイルボタン押下イベントを発火する
			$('.icons').find('.list_pc').click(function() {
				$('#addbypc').click();
			});
			
			// ファイル選択時イベント
			$('#addbypc').change(function(event) {
				$('#insertContentsForm').submit();
			});
			
			// Ciniiから選択
			$('.icons').find('.list_cinii').click(function() {
				$('#ciniiDialog').bPopup({
					position	: ['auto',0],
					follow		: [true,false]
				});
			});
			
			$('#getCiniiForm').submit(function(event) {
				readyCinii();
				if($('#cinii_search_flag').prop('value') != 1)
				{
					// 検索値を退避させる
					$('#cinii_search_text_hidden').prop('value', $('#cinii_search_text').val());
					$('#cinii_search_index_hidden').prop('value', $('#cinii_search_index').val());
					$('#cinii_search_order_hidden').prop('value', $('#cinii_search_order').val());
				}
				ajaxSubmit(this, event, callGetCinii, undefined, true);
			});
			
			$('#submitCiniiForm').submit(function(event) {
				ajaxSubmit(this, event, function(){
					$('#apiContentsCompDialog').bPopup();
					$('#ciniiDialog').bPopup().close();
					clearCinii();
					resetForm($('#getCiniiForm'));
					drawContents();
				});
			});
			
			// Amazonから選択
			$('.icons').find('.list_amazon').click(function() {
				$('#amazonDialog').bPopup({
					position	: ['auto',0],
					follow		: [true,false]
				});
			});
			
			$('#getAmazonForm').submit(function(event) {
				readyAmazon();
				if($('#amazon_search_flag').prop('value') != 1)
				{
					// 検索値を退避させる
					$('#amazon_search_text_hidden').prop('value', $('#amazon_search_text').prop('value'));
				}
				ajaxSubmit(this, event, callGetAmazon, undefined, true);
			});
			
			$('#submitAmazonForm').submit(function(event) {
				ajaxSubmit(this, event, function(){
					$('#apiContentsCompDialog').bPopup();
					$('#amazonDialog').bPopup().close();
					clearAmazon();
					resetForm($('#getAmazonForm'));
					drawContents();
				});
			});
			
			// コンテンツ削除
			$('#activeContents .trashcan').click(function() {
				$('#switch_flg').val(0);
				
				if(checkContents())
					$('#contentsRemoveDialog').bPopup();
				else
					$('#contentsRemoveFailDialog').bPopup();
			});
			
			// テーマ選択時のみ
			{if !empty($selected)}
			// コピー
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
					var num = 0;
					$('.contentCheck').each(function(){
						if($(this).prop('checked'))
						{
							$('#copyArea').append('<div style="font-weight: bold;">「' + $(this).data('name') + '」</div>');
							$('#copyArea').append('<input type="hidden" name="copy_val[' + num + '][id]" value="' + $(this).prop('value') + '" />');
							$('#copyArea').append('<input type="hidden" name="copy_val[' + num + '][title]" value="' + $(this).data('name') + '" />');
							if($(this).data('url') != undefined)
								$('#copyArea').append('<input type="hidden" name="copy_val[' + num + '][url]" value="' + $(this).data('url') + '" />');
							if($(this).data('class') != undefined)
								$('#copyArea').append('<input type="hidden" name="copy_val[' + num + '][class]" value="' + $(this).data('class') + '" />');
							
							num++;
						}
					});
					$('#copyContentsDialog').bPopup({
						follow		: [true,false]
					});
				}
				else
				{
					$('#copyContentsFailDialog').bPopup();
				}
			});
			
			{/if}
			
			// 一括ダウンロード
			$('#activeContents .download').click(function() {
				$('#switch_flg').val(1);
				
				if(checkContents())
					downloadContentsZip();
				else
					$('#contentsDownloadFailDialog').bPopup();
			});
			
			setListEvent();
			
			// ゴミ箱タブ：元に戻す押下時
			// ※即サブミット
			$('#removedContents .recover').click(function() {
				$('#removedflg').val('0');
				
				if(checkRemoved())
					submitManipulateTrashes();
				else
					$('#contentsRecoverFailDialog').bPopup();
			});
			// ゴミ箱タブ：完全削除押下時
			$('#removedContents .perm_delete').click(function() {
				$('#removedflg').val('1');
				
				if(checkRemoved())
					$('#permDeleteDialog').bPopup();
				else
					$('#contentsDeleteFailDialog').bPopup();
			});
			
			$('#insertContentsForm').submit(function(event) {
				ajaxSubmit(this, event, callContentsInsert);
			});
			
			$('#removeContentsForm').submit(function(event) {
				var flg = $('#switch_flg').val();
				if(flg == 0)
					ajaxSubmit(this, event, callContentsRemove);
			});
			
			$('#manipulateTrashesForm').submit(function(event) {
				ajaxSubmit(this, event, callManipulateTrashes);
			});
			
			$('#copyContentsForm').submit(function(event) {
				ajaxSubmit(this, event, callCopyContents);
			});
		});
	</script>
</body>
</html>