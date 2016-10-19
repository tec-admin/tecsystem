<!doctype html>
<html lang="ja">
<head>
<!--
	{t}文書種類の名称を入力してください{/t}
-->
{include file='admin/header.tpl'}
	<style type="text/css">
	<!--
	.hidden{
		display: none;
	}
	input.button_hidden{
		display: none;
	}
	#dockind .container {
		margin-top: 10px;
		margin-bottom: 100px;
		table-layout: fixed;
	}
	#dockind .container table {
		width: 50%;
		min-width: 500px;
		margin-left:auto;
		margin-right:auto;
	}
	#dockind .container table thead tr th {
		font-size: 12px;
		color: #636363;
	}	
	#dockind .container table td {
		text-align: center;
	}
	th.add_new_line{
		width: 100px;
	}
	
	//-->
	</style>
	<script>
	// 新規に追加する仮のplaceidとして扱う
	var nextid = {$nextid};
	
	function submitData()
	{
		$('#updatedockind').submit();
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

	</script>
</head>
<body class="admin">
	{include file='admin/menu.tpl'}
		<div id="main">
			<article class="calendar">
			<h1>{t}文書種類設定{/t}</h1>
				<div id="dockind">
					<div class="container">
						<form method="POST" action="{$baseurl}/{$controllerName}/updatedockind" name="updatedockind" id="updatedockind" enctype="multipart/form-data">
							<div class="dockind_outer">
								<table id="table_dockind">
									<thead>
										<tr>
											<th class="display" style="text-align: center;">{t}非表示{/t}</th>
											<th class="document_category" style="text-align: center;">{t}名称{/t}</th>
											<th class="order_button" style="text-align: center;" colspan="2">{t}表示順序{/t}</th>
											<th class="add_new_line" style="text-align: center;">{t}追加{/t}</th>
										</tr>
									</thead>
									<tbody id="dockindinput">
										{foreach from=$dockinds item=dockind name=dockinds}
											<tr class="line" id="line{$dockind->id}">
												<td class="hidden">
													<input id="order_num{$dockind->id}" type="text" name="order_num[{$dockind->id}]" value="{$dockind->order_num}" style="display:none;" />
												</td>
												<td class="display">
													<input id="checkbox{$dockind->id}" type="checkbox" name="checkbox[{$dockind->id}]" value="1" {if $dockind->display_flg == 0}checked="checked" {/if}/>
												</td>
												<td class="document_category">
													<input id="document_category{$dockind->id}" type="text" name="document_category[{$dockind->id}]" value="{$dockind->document_category}">
												</td>
												<td class="order_button_left">
													<input id="order_button_left{$dockind->id}" type="button" value="↓" class="replace_down" {if $smarty.foreach.dockinds.last}disabled="disabled" {/if}/>
												</td>
												<td class="order_button_right">
													<input id="order_button_right{$dockind->id}" type="button" value="↑" class="replace_up" {if $smarty.foreach.dockinds.first}disabled="disabled" {/if}/>
												</td>
												<td class="add_new_line">
													<input class="add{if !$smarty.foreach.dockinds.last} button_hidden{/if}" id="add_button{$dockind->id}" type="button" value="＋" />
													{if $flg[$dockind->id] == 0}<input class="default remove" id="remove_button{$dockind->id}" type="button" value="－" />{/if}
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
	
								<div id="compDialog" class="dialog">
									<div class="cmpsub">{t}更新が完了しました。{/t}</div>
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
		// ↑の処理：値を上の行と入れ替える
		$(document).on("click", ".replace_up", function()
		{
			/* 表示順序ボタン状態入れ替え */
			// ↓ボタン
			var tmp = $(this).parent().parent().find('.order_button_left > input').prop('disabled');
			var above = $(this).parent().parent().prev().find('.order_button_left > input').prop('disabled');

			$(this).parent().parent().find('.order_button_left > input').prop('disabled', above);
			$(this).parent().parent().prev().find('.order_button_left > input').prop('disabled', tmp);
			
			// ↑ボタン
			var tmp = $(this).parent().parent().find('.order_button_right > input').prop('disabled');
			var above = $(this).parent().parent().prev().find('.order_button_right > input').prop('disabled');

			$(this).parent().parent().find('.order_button_right > input').prop('disabled', above);
			$(this).parent().parent().prev().find('.order_button_right > input').prop('disabled', tmp);
			
			
			/* ＋ボタン状態入れ替え */
			var tmp = $(this).parent().parent().find('.add_new_line > input.add').prop('class');
			var above = $(this).parent().parent().prev().find('.add_new_line > input.add').prop('class');

			$(this).parent().parent().find('.add_new_line > input.add').prop('class', above);
			$(this).parent().parent().prev().find('.add_new_line > input.add').prop('class', tmp);
			
			
			/* hidden値(order_num)入れ替え */
			var tmp = $(this).parent().parent().find('.hidden > input').val();
			var above = $(this).parent().parent().prev().find('.hidden > input').val();

			$(this).parent().parent().find('.hidden > input').val(above);
			$(this).parent().parent().prev().find('.hidden > input').val(tmp);
			
			/* 行ごと入れ替え */
			var tmp = $(this).parent().parent();
			var above = $(this).parent().parent().prev();
			
			above.insertAfter(tmp);
		});
		
		// ↓の処理：値を下の行と入れ替える
		$(document).on("click", ".replace_down", function()
		{
			/* 表示順序ボタン状態入れ替え */
			// ↓ボタン
			var tmp = $(this).parent().parent().find('.order_button_left > input').prop('disabled');
			var below = $(this).parent().parent().next().find('.order_button_left > input').prop('disabled');

			$(this).parent().parent().find('.order_button_left > input').prop('disabled', below);
			$(this).parent().parent().next().find('.order_button_left > input').prop('disabled', tmp);
			
			// ↑ボタン
			var tmp = $(this).parent().parent().find('.order_button_right > input').prop('disabled');
			var below = $(this).parent().parent().next().find('.order_button_right > input').prop('disabled');

			$(this).parent().parent().find('.order_button_right > input').prop('disabled', below);
			$(this).parent().parent().next().find('.order_button_right > input').prop('disabled', tmp);
			
			
			/* ＋ボタン状態入れ替え */
			var tmp = $(this).parent().parent().find('.add_new_line > input.add').prop('class');
			var below = $(this).parent().parent().next().find('.add_new_line > input.add').prop('class');

			$(this).parent().parent().find('.add_new_line > input.add').prop('class', below);
			$(this).parent().parent().next().find('.add_new_line > input.add').prop('class', tmp);
			
			
			/* hidden値(order_num)入れ替え */
			var tmp = $(this).parent().parent().find('.hidden > input').val();
			var below = $(this).parent().parent().next().find('.hidden > input').val();

			$(this).parent().parent().find('.hidden > input').val(below);
			$(this).parent().parent().next().find('.hidden > input').val(tmp);
			
			
			/* 行ごと入れ替え */
			var tmp = $(this).parent().parent();
			var below = $(this).parent().parent().next();
			
			tmp.insertAfter(below);
		});
		
		// ＋の処理：下に新しい行を追加する
		$(document).on("click", ".add", function()
		{
			var tbody = document.getElementById('dockindinput');
		
			var tr = document.createElement('tr');
			
			var td_hidden = document.createElement('td');
			var td_display = document.createElement('td');
			var td_document_category = document.createElement('td');
			var td_order_button_left = document.createElement('td');
			var td_order_button_right = document.createElement('td');
			var td_add_new_line = document.createElement('td');
			
			tr.setAttribute('class', 'line');
			tr.setAttribute('id', 'line' + nextid);
				td_hidden.setAttribute('class', 'hidden');
					td_hidden.innerHTML = '<input id="order_num' + nextid + '" type="text" name="order_num[' + nextid + ']" value="' + nextid + '" style="display:none;" />';
				tr.appendChild(td_hidden);
				
				td_display.setAttribute('class', 'display');
					td_display.innerHTML = '<input id="checkbox' + nextid + '" type="checkbox" name="checkbox[' + nextid + ']" value="1" />';
				tr.appendChild(td_display);
				
				td_document_category.setAttribute('class', 'document_category');
					td_document_category.innerHTML = '<input id="document_category' + nextid + '" type="text" name="document_category[' + nextid + ']" value="">';
				tr.appendChild(td_document_category);
				
				td_order_button_left.setAttribute('class', 'order_button_left');
					td_order_button_left.innerHTML = '<input id="order_button_left' + nextid + '" type="button" value="↓" disabled="disabled" class="replace_down" />';
				tr.appendChild(td_order_button_left);
				
				td_order_button_right.setAttribute('class', 'order_button_right');
					td_order_button_right.innerHTML = '<input id="order_button_right' + nextid + '" type="button" value="↑" class="replace_up" />';
				tr.appendChild(td_order_button_right);
				
				td_add_new_line.setAttribute('class', 'add_new_line');
					td_add_new_line.innerHTML = '<input class="add" id="add_button' + nextid + '" type="button" value="＋" />';
					td_add_new_line.innerHTML += '<input class="remove" id="remove_button' + nextid + '" type="button" value="－" />';
				tr.appendChild(td_add_new_line);
			tbody.appendChild(tr);
			
			// disabledだったボタンをenabledにする
			$(this).parent().parent().find('.order_button_left > input.replace_down').removeAttr('disabled');
			
			// 追加buttonをhiddenにする
			$(this).parent().parent().find('.add_new_line > input.add').prop('class', 'add button_hidden');
			
			setNextId();
		});
		
		// －の処理：この行を削除する
		$(document).on("click", ".remove", function()
		{
			var tbody = $(this).parent().parent().parent().prop('id');
			var line = $(this).parent().parent();
			var linenum = $(this).parent().parent().prop('id').substr(4);
			
			/* 既に登録されているデータを削除する場合以外では、上の行の各要素を操作する */
			if(!$(this).siblings('input.add').hasClass('button_hidden'))
			{
				// 上の行の↓を有効化する
				$(this).parent().parent().prev().find('.order_button_left > input.replace_down').prop('disabled', 'disabled');
				
				// 上の行の追加buttonからhiddenを削除する
				$(this).parent().parent().prev().find('.add_new_line > input.add').removeClass('button_hidden');
				
				// 上の行の削除buttonからhiddenを削除する
				var remove_button = $(this).parent().parent().prev().find('.add_new_line > input.remove');
				if(remove_button != undefined)
					remove_button.removeClass('button_hidden');
			}
			
			/* 既に登録されているデータを削除する場合はinputを追加する */
			if($(this).hasClass('default'))
			{
				$('<input id="deleteid'+ linenum +'" type="text" value="'+ linenum +'" name="deleteid[]" style="display:none;">').appendTo("#updatedockind");
			}
			
			// この行を削除する
			line.remove();
			
			setNextId();
		});
		
		$(function(){
			$("#basicSettingButton").basicSetting();
			$("#pageControl").find(".finish").decisionDialog($("#finishDialog"));
			
			$("#loginStatusTrigger").miniMenu($("#loginStatus"));
		});
		
		$('#updatedockind').submit(function(event) {
			event.preventDefault();	// 本来のsubmit処理をキャンセル

			var $form = $(this);
			var fd = new FormData($form[0]);

			$.ajax({
				async: false,
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
					var response = JSON.parse(data);
					if (response['error'] !== undefined)
					{	// 論理エラー
						alert(response['error']);
					}
					else
					{	// 成功
						// 完了後の飛び先を設定
						var link = '{$baseurl}/{$controllerName}/dockind' ;
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
		</script>
	<!--[if lte IE 9]>
	<script src="/js/flexie.min.js" type="text/javascript"></script>
	<![endif]-->
	</body>
</html>