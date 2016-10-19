<!doctype html>
<html lang="ja">
<head>
<!--
	{t}権限は最低一つのチェックが必要です{/t}
-->
{include file='admin/header.tpl'}
	<style type="text/css">
	.hidden {
		display: none;
	}
	div#container {
		width: 100%;
	}
	table#user_table {
		border: 1px solid #c6c6c6;
		margin: 10px auto 0px auto;
		width: 100%;
		min-width: 650px;
	}
	table#user_table > thead > tr > th {
		color: #636363;
		transition-duration: 0.3s;
		transition-property: all;
		width: auto;
		padding: 12px 6px;
		font-size: 13px;
	}
	table#user_table > tbody > tr > th {
		padding: 8px 4px;
		font-size: 13px;
	}
	table#user_table > tbody > tr:hover{
		background-color: #d5e5f4;
	}
	table#user_table > thead > tr > th, table#user_table > tbody > tr > th {
		border: 1px solid #c6c6c6;
	}
	
	#roleChangeDialog > form > table.dialog_role {
		margin: 10px auto 0px auto;
		width: 600px;
	}
	#roleChangeDialog > form > table.dialog_role > tbody > tr > th {
		text-align: right;
		padding: 12px 6px;
	}
	#roleChangeDialog > form > table.dialog_role > tbody > tr > td {
		padding: 12px 6px;
	}
	#roleChangeDialog > form > table.dialog_role > tbody > tr#blank > td, #roleChangeDialog > form > table.dialog_role > tbody > tr#blank > th {
		padding-top: 5px;
	}
	#roleChangeDialog > form > table.dialog_role > tbody > tr#role > th, #roleChangeDialog > form > table.dialog_role > tbody > tr#role > td {
		border-top: dotted 1px #000000;
	}
	#roleChangeDialog > form > table.dialog_role > tbody > tr#role > td > div.dialog_roles {
		display: inline-block;
		width: 90px;
		padding-bottom: 5px;
	}
	#roleChangeDialog > form > div.buttonSet {
		margin-top: 10px;
	}
	.dialog > form > .sub {
		border-bottom: 1px solid #c8c8c8;
		color: #4a4a4a;
		font-size: 14px;
		font-weight: bold;
		margin: 0 25px;
		padding: 18px 0 16px;
		text-align: center;
	}
	.pager {
		font-size: 1px;
		margin: 30px 0;
		position: relative;
		text-align: center;
	}
	
	.pager li {
		display: inline-block;
		font-size: 12px;
		margin: 2px;
	}
	
	.pager li a {
		border: 1px solid #ddd;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		-ms-border-radius: 3px;
		-o-border-radius: 3px;
		border-radius: 3px;
		display: inline-block;
		height: 24px;
		line-height: 24px;
		padding: 2px 0;
		text-align: center;
		width: 32px;
		cursor: pointer;
		text-decoration: underline;
		color: #2980b9;
	}
	
	.pager li a:hover {
		text-decoration: none;
	}
	
	.pager li strong {
		border: 1px solid #015888;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		-ms-border-radius: 3px;
		-o-border-radius: 3px;
		border-radius: 3px;
		display: inline-block;
		font-weight: normal;
		height: 24px;
		line-height: 24px;
		padding: 2px 0;
		text-align: center;
		width: 32px;
	}
	
	.pager li select {
		font-family: "メイリオ",Meiryo,"ヒラギノ角ゴ Pro W3","Hiragino Kaku Gothic Pro",Osaka,"ＭＳ Ｐゴシック","MS PGothic",sans-serif;
		height: 32px;
		position: absolute;
		right: 0;
		top: 2px;
	}
	</style>
</head>
<body class="admin">
	{include file='admin/menu.tpl'}
		<div id="main">
			<article class="calendar">
			<h1>{t}ユーザー権限設定{/t}</h1>
				<div class="container">

					<div id="default_sentence">{t}右のメニューから検索してください{/t}</div>

					<table id="user_table" class="hidden">
						<thead>
							<tr>
								<th class="th_userid" style="text-align: center; width: 15%;">{t}学籍番号/職員番号{/t}</th>
								<th class="th_name" style="text-align: center; width: 25%;">{t}名前{/t}</th>
								<th class="th_roles" style="text-align: center; width: 25%;">{t}権限{/t}</th>
								<th class="th_division" style="text-align: center;">{t}学部・学科/所属{/t}</th>
							</tr>
						</thead>
						<tbody id="user_tbody">
						</tbody>
					</table>
					
					<div id="pageControl">
						<div id="roleChangeDialog" class="dialog" style="width:700px;">
							<form method="POST" action="{$baseurl}/{$controllerName}/updaterole" name="updaterole" id="updaterole" enctype="multipart/form-data">
							<i class="closeButton cancel" onclick="cancel();"></i>
							<input type="text" name="userid" id="dialog_userid" value="" style="display: none;" />
							<div class="sub">{t}権限設定{/t}</div>
							<table class="dialog_role">
							<tbody>
								<tr class="tr_userinfo">
									<th class="th_userinfo" id="userinfo_id_title">{t}学籍番号/職員番号{/t}：</td>
									<td class="td_userinfo" id="userinfo_id_value"></td>
								</tr>
								<tr class="tr_userinfo">
									<th class="th_userinfo" id="userinfo_name_title">{t}名前{/t}：</td>
									<td class="td_userinfo" id="userinfo_name_value"></td>
								</tr>
								<tr class="tr_userinfo">
									<th class="th_userinfo" id="userinfo_division_title">{t}学部・学科/所属{/t}：</td>
									<td class="td_userinfo" id="userinfo_division_value"></td>
								</tr>
								<tr id="blank">
									<td></td>
								</tr>
								<tr id="role">
									<th id="authority">{t}権限{/t}：</td>
									<td class="authorities">
									{foreach from=$permissions item=permission name=permissions}
										{if !$smarty.foreach.permissions.first && $smarty.foreach.permissions.index % 3 == 0}
											<br />
										{/if}
										<div class="dialog_roles">
											<input type="checkbox" name="update_roles[]" class="update_roles" id="{$permission->m_member_roles}" value="{$permission->m_member_roles}" />{$permission->roles_jp}
										</div>
									{/foreach}
									</td>
								</tr>
							</tbody>
							</table>
							<div class="buttonSet dubble">
								<a class="affirm" onclick="finish()">{t}登録{/t}</a>
								<a class="cancel" onclick="cancel()">{t}キャンセル{/t}</a>
							</div>
							</form>
						</div>
						
						<div id="compDialog" class="dialog">
							<div class="cmpsub">{t}更新が完了しました。{/t}</div>
							<div class="buttonSet single" id="complocation">
								<!-- <a href="#" class="affirm" onclick="cancel()">OK</a> -->
							</div>
						</div>
					</div>
					
					<div id="loading" style="text-align:center; padding-top:20%;">
						<img src="/images/loading.gif" />
					</div>
				</div>
		</article>
		<!--/#main--></div>
		<aside id="sidebar">
		<!--ユーザー検索-->
			<h1 class="user">{t}ユーザー検索{/t}</h1>
				<ul class="user">
					<li class="user">{t}権限{/t}</li>
					<li class="useritem">
					{foreach from=$permissions item=permission name=permissions}
						{if !$smarty.foreach.permissions.first && ($smarty.foreach.permissions.index) % 1 == 0}
							</li>
							<li class="useritem">
						{/if}
						<input type="checkbox" name="roles" value="{$permission->m_member_roles}">{$permission->roles_jp_clipped_form}
					{/foreach}
					</li>
					<li class="user">{t}学籍番号/職員番号{/t}</li>
					<li class="useritem">
						<input type="text" name="userid" id="userid">
					</li>
					<li class="user">{t}名前{/t}</li>
					<li class="useritem">
						<input type="text" name="name" id="name">
					</li>
					<li class="user">{t}学部/所属{/t}</li>
					<li class="useritem">
						<input type="text" name="faculty" id="faculty">
					</li>
					<li class="user">{t}学科{/t}</li>
					<li class="useritem">
						<input type="text" name="department" id="department">
					</li>
					<li class="user">
						<input type="hidden" id="curpage" value="1">
						<input type="button" class="searchUser" value="{t}検索{/t}"  onclick="movePage(1)">
					</li>
				</ul>
			<h1 class="user hidden">{t}CSV登録{/t}</h1>
				<ul class="usersub hidden">
					<li class="usersubmit">
					<form method="POST" action="{$baseurl}/{$controllerName}/updatecsv" name="uploadfile" id="uploadfile">
					    <div class="bezel fileup">
							<div class="replaceButton">
							<!--.replaceButton クラスでは.viewを透明化した.substanceで覆って入力を実現する-->
								<i class="view">{t}参照{/t}</i><input type="file" class="multi substance" id="csvfile" name="csvfile">
							</div>
						</div>
					</li>
					<li class="user"><input class="submitUser" type="button" value="{t}登録{/t}" onclick="updateCSV()"></li>
					</form>
				</ul>
		</aside>
		</div>
		{include file="../common/foot_v2.php"}
		<script>
		
		var limit = 20;
		
		function clearTable()
		{
			$('#user_tbody > tr').each(function(){
				$(this).remove();
			});
		}
		
		function search()
		{
			var baseurl = '{$baseurl}/{$controllerName}';
			
			var roles = document.getElementsByName('roles');
			var userid = document.getElementById('userid').value;
			var name = document.getElementById('name').value;
			var faculty = document.getElementById('faculty').value;
			var department = document.getElementById('department').value;
			
			var curpage = document.getElementById('curpage').value;
			
			var role_array = [];
			// チェックのある値のみを配列に格納する
			for (var i = 0; i < roles.length; i++)
			{
				if(roles[i].checked)
				{
					role_array.push(roles[i].value);
				}
			}
			
			clearTable();
			$('article').find('.pager').remove();
			$('#user_table').addClass('hidden');
			$('#default_sentence').addClass('hidden');
			$("#loading").show();
			
			$.ajax({
				async: true,	// 非同期通信
				url: baseurl + "/search",
				data: {
					'roles[]': role_array,
					'userid': userid,
					'name': name,
					'faculty': faculty,
					'department': department,
					'curpage' : curpage,
					'limit' : limit
				},
				type: "POST",
				timeout: 600000,
				datatype: 'json',
		
				beforeSend: function(xhr, settings) {
				},
				success: function(data, textStatus, jqXHR) {
					
					var parsed = $.parseJSON(data);
					
					if(parsed['count'] > 0)
					{
						$('#default_sentence').addClass('hidden');
						$('#user_table').removeClass('hidden');
						
						var tbody = document.getElementById("user_tbody");
						
						for (var index in parsed['result'])
						{
							var tr = document.createElement("tr");
							
							var th = document.createElement("th");
							var input = document.createElement("input");
							// メンバーIDを値に持つinputを設定
							input.setAttribute('class', 'm_member_id');
							input.setAttribute('value', parsed['result'][index]['members_id']);
							th.appendChild(input);
							// ロールを値に持つinputを設定
							input = document.createElement("input");
							input.setAttribute('class', 'role');
							input.setAttribute('value', parsed['result'][index]['roles']);
							th.appendChild(input);
							// この行は表示しない
							th.setAttribute('class', 'id hidden');
							tr.appendChild(th);
								
							th = document.createElement("th");
							if(parsed['result'][index]['members_student_id_jp'] != undefined)
								th.innerHTML = parsed['result'][index]['members_student_id_jp'];
							else
								th.innerHTML = parsed['result'][index]['members_staff_no'];
							th.setAttribute('class', 'student_id');
							tr.appendChild(th);
							
							th = document.createElement("th");
							th.innerHTML = parsed['result'][index]['members_name_jp'];
							th.setAttribute('class', 'name_jp');
							tr.appendChild(th);
							
							th = document.createElement("th");
							var tmp_rolestr = parsed['result'][index]['roles'];
							{foreach from=$permissions item=permission name=permissions}
							tmp_rolestr = tmp_rolestr.replace("{$permission->m_member_roles}","{$permission->roles_jp}");
							{/foreach}
							tmp_rolestr = tmp_rolestr.replace(/,/g,", ");
							th.innerHTML = tmp_rolestr;
							tr.appendChild(th);
							
							th = document.createElement("th");
							if(parsed['result'][index]['members_syzkcd_c'] != undefined)
								th.innerHTML = parsed['result'][index]['members_syzkcd_c'];
							if(parsed['result'][index]['t_syozoku1_szknam_c'] != undefined)
								th.innerHTML = parsed['result'][index]['t_syozoku1_szknam_c'];
							if(parsed['result'][index]['t_syozoku2_szknam_c'] != undefined)
								th.innerHTML += parsed['result'][index]['t_syozoku2_szknam_c'];
							th.setAttribute('class', 'division');
							tr.appendChild(th);
							
							tbody.appendChild(tr);
						}
						
						// 以下、ページャー処理
						var n = Math.ceil(parsed['count'] / limit);
						var pager = document.createElement('ul');
						pager.setAttribute('class', 'pager');
						var start = document.createElement('li');
						var a = document.createElement('a');
						a.setAttribute('id', 'page_start');
						a.innerHTML = '≪';
						a.setAttribute('onclick', 'movePage(1)');
						start.appendChild(a);
						pager.appendChild(start);
						
						curpage = Number(curpage);
						
						if(curpage > 5 && n > 9)
							var s = curpage - 4;
						else
							var s = 1;
						
						for(var i = s; i < s + 9 && i <= n; i++)
						{
							var li = document.createElement('li');
							if(i == curpage)
							{
								var str = document.createElement('strong');
								str.innerHTML = i;
								li.appendChild(str);
							}
							else
							{
								var a = document.createElement('a');
								a.innerHTML = i;
								a.setAttribute('onclick', 'movePage(' + i + ')');
								li.appendChild(a);
							}
							pager.appendChild(li);
						}
						
						var end = document.createElement('li');
						var a = document.createElement('a');
						a.setAttribute('id', 'page_end');
						a.innerHTML = '≫';
						a.setAttribute('onclick', 'movePage(' + n + ')');
						end.appendChild(a);
						pager.appendChild(end);
						
						// セレクトボックス
						var li = document.createElement('li');
						var sel = document.createElement('select');
						sel.setAttribute('onchange', 'changeLimit(this)');
						
						var values = [10, 20, 30, 50, 100];
						
						for (var key in values)
						{
							var opt = document.createElement('option');
							opt.setAttribute('value', values[key]);
							opt.innerHTML = values[key];
							if(values[key] == limit)
								opt.setAttribute('selected', 'selected');
							
							sel.appendChild(opt);
						}
						
						li.appendChild(sel);
						pager.appendChild(li);
						
						$('article').append($(pager));
					}
					else
					{
						$('#user_table').addClass('hidden');
						$('#default_sentence').removeClass('hidden');
						$('#default_sentence').text("{t}検索条件に一致するユーザーが見つかりませんでした。{/t}");
					}
				},
				error: function(jqXHR, textSatus, errorThrown) {
					// Ajax処理修了前にページ遷移するなどで分岐
					// 何か表示したければ表示する
				},
				complete: function(jqXHR, textStatus) {
					// 必ず最後に渡る部分
					$("#loading").hide();
				},
			});
		}
		
		function movePage(i)
		{
			$('#curpage').val(i);
			search();
		}
		
		function changeLimit(obj)
		{
			limit = obj.options[obj.selectedIndex].value;
			movePage(1);
		}
		
		function updateCSV()
		{
			$('#uploadfile').submit();
		}
		
		function finish()
		{
			$('#updaterole').submit();
		}
		
		function cancel()
		{
			$("#roleChangeDialog").bPopup().close();
			$("#finishDialog").bPopup().close();
		}
		
		$('#updaterole').submit(function(event) {
			event.preventDefault();	// 本来のsubmit処理をキャンセル
			
			var $form = $(this);
			var fd = new FormData($form[0]);

			$.ajax({
				async: false,				// 同期通信
				url: $form.attr('action'),
				data: fd,
				type: $form.attr('method'),
				timeout: 600000,
				
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
						$('#compDialog').bPopup();
						$(this).delay(2000).queue(function() {
							// ポップアップを閉じ、再検索することでフォームの値を維持しながら画面を更新
							$("#roleChangeDialog").bPopup().close();
							$("#compDialog").bPopup().close();
							search();
						});
					}
				},
				error: function(jqXHR, textSatus, errorThrown) {
				},
				complete: function(jqXHR, textStatus) {
				},
			});
		});
		
		$('#uploadfile').submit(function(event) {
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
					alert(data);
					var response = JSON.parse(data);
					if (response['error'] !== undefined)
					{	// 論理エラー
						alert(response['error']);
					}
					else
					{	// 成功
						
					}
				},
				error: function(jqXHR, textSatus, errorThrown) {
				},
				complete: function(jqXHR, textStatus) {
				},
			});
		});
		
		$(function(){
			
			$("#loading").hide();
			
			$(document).on("click", "table#user_table > tbody > tr", function() {
				$("#dialog_userid").prop("value", $(this).find("th.id > input.m_member_id").prop("value"));
				$("#userinfo_id_value").text($(this).find("th.student_id").text());
				$("#userinfo_name_value").text($(this).find("th.name_jp").text());
				$("#userinfo_division_value").text($(this).find("th.division").text());
				
				var roles = $(this).find("th.id > input.role").prop("value").split(",");
				
				$(".update_roles").each(function(){
					$(this).removeAttr("checked");
					for(var key in roles){
						if($(this).prop("value") == roles[key])
							$(this).prop("checked", "checked");
					}
				});
				
				
				$("#roleChangeDialog").bPopup();
			});
			
			$(".replaceButton").each(function(){
				$(this).replaceButton();
			});
			
			$("#loginStatusTrigger").miniMenu($("#loginStatus"));
			
		});
		</script>
	<!--[if lte IE 9]>
	<script src="/js/flexie.min.js" type="text/javascript"></script>
	<![endif]-->
	</body>
</html>