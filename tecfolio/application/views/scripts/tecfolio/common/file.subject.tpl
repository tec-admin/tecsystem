<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="ja">
<head>
	{include file='tecfolio/shared/common_head.tpl'}
	<script src="/js/tecfolio.api.js" type="text/javascript"></script>
	<script src="/js/jquery.tablesorter.js" type="text/javascript"></script>
	<script src="/js/jquery.tablesorter.pager.js" type="text/javascript"></script>
	<script src="/js/jquery.metadata.js" type="text/javascript"></script>
	<link href="/css/jquery.tablesorter.pager.css" type="text/css" rel="stylesheet" />
	
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
		
		function clearChatSubject()
		{
			$('#wrapChatSubject > *').each(function(){
				$(this).remove();
			});
		}
		
		function resetAllChatValues()
		{
			$('#chat_body').val('');
			
			$('#attachListHidden > *').each(function(){
				$(this).remove();
			});
			
			$('#attachListTextDialog .attachList > *').each(function(){
				$(this).remove();
			});
			
			$("#attachListText").css('display', 'none');
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
				// 削除可能なコンテンツに専用のクラスを付与する
				if($('#commonjs').data('url') == '/kwl/tecfolio/professor' || $('#commonjs').data('memberid') == array[i]['creator'])
					input.setAttribute('class', 'contentCheck owned');
				else
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
				td.setAttribute('class', 'break');
				
				// 公開設定
				if(array[i]['publicity'] == '0')
				{
					var lock	= document.createElement('i');
					lock.setAttribute('class', 'lock locked_items');
					lock.setAttribute('title', '{/literal}{t}担当教員とアップロードした本人のみに公開されています{/t}{literal}');
					td.appendChild(lock);
				}
				
				var a		= document.createElement('a');
				
				var isEnabled	= $('#commonjs').data('url') == '/kwl/tecfolio/professor' || 
									$('#commonjs').data('memberid') == array[i]['creator'] ||
									array[i]['publicity'] == '1';
				if(isEnabled)
				{
					var a = document.createElement('a');
				}
				else
				{
					var a = document.createElement('span');
					a.setAttribute('class', 'gray');
				}
					
				
				if(array[i]['content_files_name'] != undefined)
				{
					// コンテンツアップロードの場合
					if(isEnabled)
						a.setAttribute('href', baseUrl + '/downloadcontent/id/' + array[i]['content_files_id']);
					a.innerHTML = array[i]['content_files_name'];
				}
				else if(array[i]['name'] != undefined)
				{
					// 学内施設の引用の場合
					if(isEnabled)
						a.setAttribute('href', baseUrl + '/download/id/' + array[i]['id']);
					a.innerHTML = array[i]['name'];
				}
				else
				{
					if(isEnabled)
					{
						a.setAttribute('href', array[i]['ref_url']);
						a.setAttribute('target', '_blank');
					}
					a.innerHTML = array[i]['ref_title'];
				}
				
				td.appendChild(a);
				tr.appendChild(td);
				
				// サイズ
				var td 		= document.createElement('td');
				td.setAttribute('class', 'w4 num');
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
				
				// 投稿者
				var td 		= document.createElement('td');
				td.setAttribute('class', 'w6 fixed');
				if(array[i]['poster_name'] != undefined)
				{
					td.innerHTML = array[i]['poster_name'];
					td.className += " {sortValue: '" + array[i]['poster_name'] + "'}";
				}
				else
				{
					td.className += " {sortValue: null}";
				}
				tr.appendChild(td);
				
				target.appendChild(tr);
			}

			// jQueryUIでツールチップの実装、かつ改行タグを反映させる
			$('.locked_items').tooltip({
				position: {
					my: "left bottom",
					at: "center top-25%"
				},
				tooltipClass: "contents-tooltip",
				content: function() {
					return $(this).attr('title');
				}
			});
		}
		{/literal}
		
		function drawContents()
		{
			{if !empty($subjectid)}
				var url = '{$baseurl}/{$controllerName}/getcontents/id/{$subjectid}';
				
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
		
		
		// メンバーと相談するペインの描画
		function createChatSubject(array)
		{
			var chatWrap = document.getElementById('wrapChatSubject');
			var num = 0;
			
			for(var i in array)
			{
				// 教員もしくは自分の投稿
				var hasEdit = $('#commonjs').data('url') == '/kwl/tecfolio/professor' || $('#commonjs').data('memberid') == array[i]['m_member_id'];
				
				var line = document.createElement('div');
				line.setAttribute('class', 'chatLine');
				
					// 左側：画像・名前
					var left = document.createElement('div');
					left.setAttribute('class', 'chatLeft');
						var leftInner = document.createElement('div');
						leftInner.setAttribute('class', 'chatLeftInner');
						
							var img = document.createElement('img');
							if(array[i]['t_profiles_input_name'] != undefined)
							{
								img.setAttribute('src', array[i]['t_profiles_input_name']);
								img.setAttribute('class', 'selectedImg');
							}
							else
							{
								// 個人設定で画像が設定されていない場合、デフォルトの画像を表示
								img.removeAttribute('src');
								img.setAttribute('class', 'selectedImg noImg');
							}
							leftInner.appendChild(img);
							
							var name_jp = document.createElement('div');
							name_jp.innerHTML = array[i]['m_members_name_jp'];
							name_jp.setAttribute('class', 'studentName');
							
							leftInner.appendChild(name_jp);
							
						left.appendChild(leftInner);
					line.appendChild(left);
					
					// 右側：本文・コンテンツ
					var right = document.createElement('div');
					right.setAttribute('class', 'chatRight');
						var rightInner = document.createElement('div');
						if(!hasEdit)
							rightInner.setAttribute('class', 'chatRightInner');
						else
							rightInner.setAttribute('class', 'chatRightInner hasEdit');
						
							var time = document.createElement('time');
							time.innerHTML = dateFormat(array[i]['lastupdate']);
							rightInner.appendChild(time);
							
							var body = document.createElement('div');
							body.setAttribute('class', 'body');
							body.innerHTML = array[i]['body'];
							rightInner.appendChild(body);
							
							// 添付ファイル・参考文献の数だけループ
							for(var n in array[i]['contents'])
							{
								var disp = array[i]['contents'][n]['display'];
								var content = document.createElement('div');
								content.setAttribute('class', 'content');
								
									if(disp == '0')
										var a = document.createElement('span');
									else
										var a = document.createElement('a');
									
									if(array[i]['contents'][n]['content_file_id'] != undefined)
									{
										if(disp == '0')
											a.setAttribute('class', 'gray');
										else
											a.setAttribute('href', baseUrl + '/downloadcontent/id/' + array[i]['contents'][n]['content_file_id']);
										
										a.innerHTML = array[i]['contents'][n]['content_file_name'];
									}
									else if(array[i]['contents'][n]['ref_class'] != undefined)
									{
										if(disp == '0')
											a.setAttribute('class', 'gray');
										else
											a.setAttribute('href', array[i]['contents'][n]['ref_url']);
										
										var icon = document.createElement('i');
										
										if(array[i]['contents'][n]['ref_class'] == 0)
											icon.setAttribute('class', 'cinii');
										else if(array[i]['contents'][n]['ref_class'] == 1)
											icon.setAttribute('class', 'amazon');
										
										a.appendChild(icon);
										
										var txt = document.createTextNode(array[i]['contents'][n]['ref_title']);
										a.appendChild(txt);
									}
									
									content.appendChild(a);
								rightInner.appendChild(content);
							}
							
							// 教員もしくは自分の投稿
							if(hasEdit)
							{
								// 削除
								var edit = document.createElement('div');
								edit.setAttribute('class', 'edit');
								
									var del = document.createElement('a');
									del.setAttribute('class', 'delete');
									del.setAttribute('data-id', array[i]['id']);
									del.innerHTML = '削除'
									edit.appendChild(del);
								
								rightInner.appendChild(edit);
							}
						right.appendChild(rightInner);
					line.appendChild(right);
				chatWrap.appendChild(line);
			}
		}
		
		function drawChatSubject()
		{
			{if !empty($subjectid)}
				ajaxSubmitUrl('{$baseurl}/{$controllerName}/getchatsubject/id/{$subjectid}', function(response){
				
					if (response['error'] !== undefined)
					{	// 論理エラー
						alert(response['error']);
					}
					else
					{	// 成功
						clearChatSubject()
						
						createChatSubject(response['chat_log']);
						
						// 削除ボタンはホバー時のみ表示
						$('.chatRightInner .edit > *').css('display', 'none');
						$('.chatLine').off('mouseenter','mouseleave');
						$('.chatLine').on({
							'mouseenter':function(){
								$(this).find('.chatRightInner .edit > *').css('display', 'block');
							},
							'mouseleave':function(){
								$(this).find('.chatRightInner .edit > *').css('display', 'none');
							}
						});
						
						// 削除ボタンイベント
						$('.chatRightInner .edit .delete').off('click');
						$('.chatRightInner .edit .delete').on('click',function(){
							$('#chat_subject_id').val($(this).attr('data-id'));
							$('#chatSubjectDeleteDialog').bPopup();
						});
					}
					
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
		
		// メンバーと相談する
		function submitInsertChatSubject()
		{
			if($('#chat_body').val() != '')
				$('#insertChatSubjectForm').submit();
			else
				$('#insertChatSubjectFailDialog').bPopup();
		}
		
		// 投稿削除
		function submitDeleteChatSubject()
		{
			$('#deleteChatSubjectForm').submit();
		}
		
		// 公開設定 -> 公開
		function submitOpenPublicity()
		{
			$('#openPublicityDialog').bPopup().close();
			$('#pub_setting').prop('checked', true);
			$('#updatePublicitySettingForm').submit();
		}
		
		// 公開設定 -> 非公開
		function submitClosePublicity()
		{
			$('#closePublicityDialog').bPopup().close();
			$('#pub_setting').prop('checked', false);
			$('#updatePublicitySettingForm').submit();
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
			<article class="subject">
				<div id="wrapcontent" class="hasTabs" style="display:none;">
				{if !empty($subjectid) && empty($error)}
					<div class="head">
						<h1 id="content_name">◆{$selected->yogen}　{$selected->class_subject}</h1>
						{if count($class_members) > 0}
							<a class="popup" id="member_num" href="#">{t 1={$class_members|@count}}メンバー： %1 名{/t}</a>
						{else}
							{t 1='0'}メンバー： %1 名{/t}
						{/if}
					</div>
					<div class="body">
						<ul class="tabs{if !empty($laboid)} ui-single{/if}">
							<li><a href="#activeContents">{t}コンテンツ{/t}</a></li>
							{if empty($laboid)}<li><a href="#removedContents">{t}ゴミ箱{/t}</a></li>{/if}
						</ul>
							<div class="contents container portfolio" id="activeContents">
							<div class="main icons">
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
								
								<div class="wrapicon">
									<div class="download noMenu" title="{t}コンテンツを一括ダウンロードします{/t}"><i class="download"></i></div>
								</div>
								
								{if isset($publicity)}
								<div class="wrapicon">
									<div class="key miniMenu" title="{t}選択したコンテンツの公開設定を変更します{/t}"><i class="key"></i></div>
									<div class="wrapdrop">
										<ul class="key droplist">
											<form method="post" action="{$baseurl}/{$controllerName}/updatepublicity/id/{$selected->id}" name="updatePublicityForm" id="updatePublicityForm" enctype="multipart/form-data">
											<input type="hidden" name="publicity" id="publicity" />
											<li class="mainMenu list_lock" title="{t}担当教員とアップロードした本人のみに公開します{/t}"><i class="lock"></i><a class="lock">{t}参照不可にする{/t}</a></li>
											<li class="mainMenu list_unlock" title="{t}この授業科目の関係者すべてに公開します{/t}"><i class="unlock"></i><a class="unlock">{t}参照可にする{/t}</a></li>
											</form>
										</ul>
									</div>
								</div>
								
								<form method="post" action="{$baseurl}/{$controllerName}/updatepublicitysetting/id/{$selected->id}" name="updatePublicitySettingForm" id="updatePublicitySettingForm" enctype="multipart/form-data">
									<div style="display:inline-block;margin: 8px 5px 5px 5px"><input type="checkbox" name="pub_setting" id="pub_setting" value="1" {if $publicity == 1}checked="checked"{/if}> 公開設定</div>
								</form>
								{/if}
								
								<span style="clear: both;"></span>
							</div>
							
							<div class="wrapOuterLeft">
								<div class="wrapContentTop">
									<div class="wrapContentTopTable">
										<form method="post" action="{$baseurl}/{$controllerName}/checkcontents" name="removeContentsForm" id="removeContentsForm" enctype="multipart/form-data">
										<input type="hidden" name="id" id="check_contents_id" value="{$selected->id}" />
										<input type="hidden" name="name" id="check_contents_name" value="{$selected->yogen} {$selected->class_subject}" />
										<input type="hidden" name="switch_flg" id="switch_flg" />
										<div class="wrapTableHead tblSubject">
											<table class="contentsTbl">
												<thead>
													<tr>
														<th class="w1 main"><input type="checkbox" id="contentCheckAll" /></th>
														<th class="w1 main hasOrder" data-id="content_num">{t}No.{/t}</th>
														<th class="w1 main hasOrder" data-id="content_ref_class">{t}類{/t}</th>
														<th class="main hasOrder" data-id="content_title">{t}コンテンツ{/t}</th>
														<th class="w4 main hasOrder" data-id="content_filesize">{t}サイズ{/t}</th>
														<th class="timestamp main hasOrder" data-id="content_num">{t}アップロード日時{/t}</th>
														<th class="w6 skyblue hasOrder" data-id="content_poster">{t}投稿者{/t}</th>
													</tr>
												</thead>
											</table>
										</div>
										
										<div class="wrapTableBody tblSubject">
										{literal}
											<table class="contentsTbl" id="contentsTbl">
												<thead id="contentsInnerHead" style="display:none;">
													<tr>
														<th></th>
														<th id="content_num">{t}No.{/t}</th>
														<th class="{sorter:'metadata'}" id="content_ref_class">{t}類{/t}</th>
														<th id="content_title" id="content_ref_class">{t}コンテンツ{/t}</th>
														<th class="{sorter:'metadata'}" id="content_filesize">{t}サイズ{/t}</th>
														<th>{t}アップロード日時{/t}</th>
														<th class="{sorter:'metadata'}" id="content_poster">{t}投稿者{/t}</th>
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
								</div>
							</div>
							<div class="wrapOuterRight">
								<div class="contentBottomRight">
									<div class="bbs">
										<div class="desc"><i>{t}メンバーと相談する{/t}</i></div>
										<div class="bbsWrap">
											<div class="bbsContent">
												<div class="bbsTop">
													<div class="bbsTopInner">
														<input type="file" name="addbypc[]" id="addChatbypc" style="display:none;" multiple="multiple" />
														<form method="post" action="{$baseurl}/{$controllerName}/insertchatsubject/id/{$selected->id}" name="insertChatSubjectForm" id="insertChatSubjectForm" enctype="multipart/form-data">
															<input type="text" name="chat_body" id="chat_body">
															<div class="chat icons">
																<div class="wrapicon">
																	<div class="add miniMenu" title="{t}コンテンツを添付します{/t}"><i class="add"></i></div>
																	<div class="wrapdrop">
																		<ul class="add droplist">
																			<li class="mainMenu list_pc" title="{t}パソコンからコンテンツを添付します※Ctrlで複数選択ができます{/t}"><i class="pc"></i><a class="pc">{t}パソコンから{/t}</a></li>
																			<li class="mainMenu list_amazon" title="{t}Amazonから文献情報を添付します{/t}"><i class="amazon"></i><a class="amazon">Amazon</a></li>
																			<li class="mainMenu list_cinii" title="{t}Ciniiから文献情報を添付します{/t}"><i class="cinii"></i><a class="cinii">Cinii</a></li>
																		</ul>
																	</div>
																</div>
																<div class="attachList">
																	<a class="submit list" id="attachListText" style="display: none;">{t}添付リスト{/t}</a>
																</div>
																<div class="attachListHidden" id="attachListHidden">
																</div>
																<div class="button">
																	<a onclick="resetAllChatValues();" class="submit reset">{t}リセット{/t}</a>
																	<a onclick="submitInsertChatSubject();" class="submit orange">{t}送信{/t}</a>
																</div>
															</div>
														</form>
													</div>
												</div>
												<div class="bbsBottom">
													<div class="wrapChat subject" id="wrapChatSubject">
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
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
											<th class="w4 main">{t}サイズ{/t}</th>
											<th class="timestamp main">{t}アップロード日時{/t}</th>
											<th class="w6 skyblue">{t}投稿者{/t}</th>
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
					</div>
				{elseif !empty($error)}
				<p class="empty">{t}存在しないか、アクセス許可のないIDです。{/t}</p>
				{else}
				<p class="empty">{t}左のメニューからテーマを選択してください。{/t}</p>
				{/if}
				</div>
			</article>
			
			{include file='tecfolio/shared/file_dialog.tpl'}
			
			<div id="contentsNotOwnedFailDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}エラー{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}他のメンバーがアップロードしたファイルは操作できません。{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="attachListTextDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}添付リスト{/t}</div>
				<div class="mythemeWrap">
					<div class="wrapList">
						<ul class="dialogList attachList">
						</ul>
					</div>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="insertChatSubjectFailDialog" class="dialog">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}メンバーと相談する{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}本文を入力してください{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">OK</a>
				</div>
			</div>
			<div id="chatSubjectCompDialog" class="dialog">
				<div class="cmpsub">{t}掲示板に書き込みました{/t}</div>
			</div>
			
			<div id="chatSubjectDeleteDialog" class="dialog small">
				<form method="post" action="{$baseurl}/{$controllerName}/deletechatsubject" name="deleteChatSubjectForm" id="deleteChatSubjectForm" enctype="multipart/form-data">
					<input type="hidden" name="id" id="chat_subject_id" />
				</form>
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}投稿の削除{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}投稿を削除しますか？（添付ファイルはコンテンツリストに残ります）{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitDeleteChatSubject();" class="affirm">{t}削除する{/t}</a>
				</div>
			</div>
			<div id="chatSubjectDeleteCompDialog" class="dialog">
				<div class="cmpsub">{t}投稿を削除しました{/t}</div>
			</div>
			
			<div id="memberListDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}メンバーの表示{/t}</div>
				<div class="mythemeWrap">
					<div class="infoWrap">
						<table class="memberList">
							<thead>
								<tr>
									<th class="num">{t}No.{/t}</th>
									<th>{t}学籍番号{/t}</th>
									<th>{t}氏名{/t}</th>
								</tr>
							</thead>
							<tbody>
							{foreach from=$class_members item=member name=member}
								<tr>
									<td class="num">{$smarty.foreach.member.index + 1}</td>
									<td>{$member->student_id_jp}</td>
									<td>{$member->name_jp}</td>
								</tr>
							{/foreach}
							</tbody>
						</table>
					</div>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="closePublicityDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}公開設定の変更{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t escape=no}今後追加されるコンテンツに対する、既定の公開設定を変更します。<br>現在の設定は「参照可」(全てのユーザーに公開)です。<br>設定を参照不可に変更しますか？{/t}
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitClosePublicity();" class="affirm">{t}変更する{/t}</a>
				</div>
			</div>
			<div id="openPublicityDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}公開設定の変更{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t escape=no}今後追加されるコンテンツに対する、既定の公開設定を変更します。<br>現在の設定は「参照不可」(担当教員と追加した本人のみに公開)です。<br>設定を参照可に変更しますか？{/t}
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitOpenPublicity();" class="affirm">{t}変更する{/t}</a>
				</div>
			</div>
			<div id="publicitySettingCompDialog" class="dialog">
				<div class="cmpsub">{t}公開設定を変更しました{/t}</div>
			</div>
			
			<div id="updatePublicityCloseFailDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}参照不可にする{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}参照不可にするコンテンツにチェックを入れてください{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			<div id="updatePublicityOpenFailDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}参照可にする{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}参照可にするコンテンツにチェックを入れてください{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			<div id="updatePublicityCloseCompDialog" class="dialog">
				<div class="cmpsub">{t}選択したコンテンツを参照不可にしました{/t}</div>
			</div>
			<div id="updatePublicityOpenCompDialog" class="dialog">
				<div class="cmpsub">{t}選択したコンテンツを参照可にしました{/t}</div>
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
			drawChatSubject();
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
			drawChatSubject();
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
		
		function checkContentsOwned()
		{
			var flg = 1;
			$('#contentsInner .contentCheck').each(function(){
				if($(this).prop('checked') && !$(this).hasClass('owned'))
				{
					flg = 0;
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
		
		function checkRemovedOwned()
		{
			var flg = 1;
			$('#removedInner .contentCheck').each(function(){
				if($(this).prop('checked') && !$(this).hasClass('owned'))
				{
					flg = 0;
					return true;
				}
			});
			
			return flg;
		}
		
		// 掲示板からのAPI関連処理では、メインペインとダイアログを共有するが、
		// このフラグを用いることで、メインペインの処理と区別する
		// 0=メインペイン, 1=掲示板ペイン
		var chatFlg = 0;
		
		function submitAmazon()
		{
			if(chatFlg)
			{
				// チェックの入った要素に属するinputをクローン
				$('#submitAmazonForm').find(':checked').siblings().each(function(){
					// 配列の添字を削除して、送信用に退避(hiddenであるinput要素ごとコピー)
					var str = $(this).prop('name');
					$(this).clone(false).prop('name', str.replace(/[0-9*]/g, '')).appendTo($('#attachListHidden'));
					
					// 添付リストに追加
					if($(this).hasNameRegExp(/^amazon_title/))
						$('#attachListTextDialog .attachList').append('<li>' + $(this).val() + '</li>');
				});
				$('#amazonDialog').bPopup().close();
				$("#attachListText").css('display', 'inline-block');
			}
			else
			{
				$('#submitAmazonForm').submit();
			}
		}

		function submitCinii()
		{
			if(chatFlg)
			{
				// チェックの入った要素に属するinputをクローン
				$('#submitCiniiForm').find(':checked').siblings().each(function(){
					// 配列の添字を削除して、送信用に退避(hiddenであるinput要素ごとコピー)
					var str = $(this).prop('name');
					$(this).clone(false).prop('name', str.replace(/[0-9*]/g, '')).appendTo($('#attachListHidden'));
					
					// 添付リストに追加
					if($(this).hasNameRegExp(/^cinii_title/))
						$('#attachListTextDialog .attachList').append('<li>' + $(this).val() + '</li>');
				});
				$('#ciniiDialog').bPopup().close();
				$("#attachListText").css('display', 'inline-block');
			}
			else
			{
				$('#submitCiniiForm').submit();
			}
		}
		
		function addFileNames()
		{
			var fileList = document.getElementById("addChatbypc").files;
			var list = "";
			for(var i = 0; i < fileList.length; i++){
				list += "<li>" + fileList[i].name + "</li>";
			}
			$('#attachListTextDialog .attachList').append(list);
		} 
		
		function setChatMentorFile()
		{
			// input type='file'を移動しIDを削除、元の位置にクローンを生成してIDとイベント設定
			$('#addChatbypc').off('change');
			$('#addChatbypc').on('change', function(event) {
				addFileNames();
				$(this).removeAttr('id').appendTo($('#attachListHidden'));
				$(this).clone(false).attr('id', 'addChatbypc').insertBefore($('#insertChatSubjectForm'));
				$("#attachListText").css('display', 'inline-block');
				
				setChatMentorFile();
			});
		}
		
		$(function(){
		
			drawContents();
			drawChatSubject();
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
			
			/***** 共通アイコン関連イベント *****/
			
			// 追加メニュー押下時、ファイルボタン押下イベントを発火する
			$('.main.icons').find('.list_pc').click(function() {
				$('#addbypc').click();
			});
			// ファイル選択時イベント
			$('#addbypc').change(function(event) {
				$('#insertContentsForm').submit();
				
				// 2016/02/29 Chromeでのエラーに伴い追加
				$(this).wrap('<form>').closest('form').get(0).reset();
				$(this).unwrap();
			});
			
			// 掲示板では別のファイルボタンを発火する
			$('.chat.icons').find('.list_pc').click(function() {
				$('#addChatbypc').click();
			});
			// ファイル選択時イベント
			setChatMentorFile();
			
			
			// 添付リスト押下時イベント
			$('#attachListText').click(function(){
				$('#attachListTextDialog').bPopup();
			});
			
			
			// メインペイン：Ciniiから選択
			$('.main.icons').find('.list_cinii').click(function() {
				chatFlg = 0;
				$('#ciniiDialog').bPopup({
					position	: ['auto',0],
					follow		: [true,false]
				});
			});
			// 掲示板ペイン：Ciniiから選択
			$('.chat.icons').find('.list_cinii').click(function() {
				chatFlg = 1;
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
				ajaxSubmit(this, event, callGetCinii);
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
			
			// メインペイン：Amazonから選択
			$('.main.icons').find('.list_amazon').click(function() {
				chatFlg = 0;
				$('#amazonDialog').bPopup({
					position	: ['auto',0],
					follow		: [true,false]
				});
			});
			// 掲示板ペイン：Amazonから選択
			$('.chat.icons').find('.list_amazon').click(function() {
				chatFlg = 1;
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
				ajaxSubmit(this, event, callGetAmazon);
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
			
			$('.main.icons').find('.list_lock').click(function() {
				$('#publicity').prop('value', '0');
				
				if(checkContents())
				{
					$('#updatePublicityForm').submit();
				}
				else
				{
					$('#updatePublicityCloseFailDialog').bPopup();
				}
			});
			$('.main.icons').find('.list_unlock').click(function() {
				$('#publicity').prop('value', '1');
				
				if(checkContents())
				{
					$('#updatePublicityForm').submit();
				}
				else
				{
					$('#updatePublicityOpenFailDialog').bPopup();
				}
			});
			$('#updatePublicityForm').submit(function(event) {
				$('#contentsInner .contentCheck').each(function() {
					if($(this).prop('checked')) {
						var input = '<input type="hidden" name="selected_id[]" class="pubSelectedId" value="' + $(this).val() + '">';
						$('#publicity').after(input);
					}
				});
				
				ajaxSubmit(this, event, function(){
					drawContents();
					$('.pubSelectedId').each(function(){
						$(this).remove();
					});
					
					var val = $('#publicity').val();
					if(val == 0)
						$('#updatePublicityCloseCompDialog').bPopup();
					else
						$('#updatePublicityOpenCompDialog').bPopup();
				});
			});
			
			$('#pub_setting').mousedown(function(event){
				event.preventDefault();
				event.stopPropagation();
				
				if($(this).prop('checked'))
					$('#closePublicityDialog').bPopup();
				else
					$('#openPublicityDialog').bPopup();
			});
			$('#updatePublicitySettingForm').submit(function(event) {
				ajaxSubmit(this, event, function(){
					$('#publicitySettingCompDialog').bPopup();
				});
			});
			
			
			/***** 共通アイコン関連イベントここまで *****/
			
			// コンテンツ削除
			$('#activeContents .trashcan').click(function() {
				$('#switch_flg').val(0);
				
				if(checkContents())
				{
					if(checkContentsOwned())
						$('#contentsRemoveDialog').bPopup();
					else
						$('#contentsNotOwnedFailDialog').bPopup();
				}
				else
				{
					$('#contentsRemoveFailDialog').bPopup();
				}
			});
			
			// テーマ選択時のみ
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
			
			
			// 一括ダウンロード
			$('#activeContents .download').click(function() {
				$('#switch_flg').val(1);
				
				if(checkContents())
					downloadContentsZip();
				else
					$('#contentsDownloadFailDialog').bPopup();
			});
			
			// ゴミ箱タブ：元に戻す押下時
			// ※即サブミット
			$('#removedContents .recover').click(function() {
				$('#removedflg').val('0');
				
				if(checkRemoved())
				{
					if(checkRemovedOwned())
						submitManipulateTrashes();
					else
						$('#contentsNotOwnedFailDialog').bPopup();
				}
				else
				{
					$('#contentsRecoverFailDialog').bPopup();
				}
			});
			// ゴミ箱タブ：完全削除押下時
			$('#removedContents .perm_delete').click(function() {
				$('#removedflg').val('1');
				
				if(checkRemoved())
				{
					if(checkRemovedOwned())
						$('#permDeleteDialog').bPopup();
					else
						$('#contentsNotOwnedFailDialog').bPopup();
				}
				else
				{
					$('#contentsDeleteFailDialog').bPopup();
				}
			});
			
			// ページ上部、メンバー数押下時
			$('#member_num').click(function(event){
				$('#memberListDialog').bPopup();
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
			
			$('#insertChatSubjectForm').submit(function(event) {
				ajaxSubmit(this, event, function(response){
					$('#chatSubjectCompDialog').bPopup();
					resetAllChatValues();
					drawContents();
					drawChatSubject();
				});
			});
			
			$('#deleteChatSubjectForm').submit(function(event) {
				ajaxSubmit(this, event, function(response){
					$('#chatSubjectDeleteCompDialog').bPopup();
					$('#chatSubjectDeleteDialog').bPopup().close();
					drawChatSubject();
				});
			});
		});
	</script>
</body>
</html>