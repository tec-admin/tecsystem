<!doctype html>
<html lang="ja">
<head>
<!--
	{t}ユーザー登録{/t}
	{t}権限は最低一つのチェックが必要です{/t}
-->
{include file='admin/header.tpl'}

	<style type="text/css">
	.hidden{
		display: none;
	}
	input.button_hidden{
		display: none;
	}

	h2.campus_name{
		border-bottom: 1px solid #adadad;
		border-left: 5px solid #adadad;
		font-size: 14px;
		padding: 5px 0px 5px 10px;
		margin: 10px 0 0 20px;
	}

	input.makeuserid {  width: 80%; }

	hr.makeuserid {
		border: 0.5px solid #adadad;
		margin-left: 40px;
	}
	
	#makeuserid .csvfile{
		font-size: 14px;
		padding: 5px 0px 5px 10px;
		margin: 10px 0 0 30px;
	}

	#makeuserid .pageControl{
		font-size: 14px;
		padding: 5px 0px 5px 10px;
		margin: 10px 0 0 30px;
		margin-left: -50px;
	}
	#makeuserid .csvupload{
		margin-top: 10px;
		margin-left: 50px;
	}

	#makeuserid .uploadfile{
		margin-left: 80px;
	}

	#makeuserid .container {
		margin-top: 10px;
		margin-bottom: 100px;
		table-layout: fixed;
	}
	#makeuserid .container table {
		width: 50%;
		min-width: 500px;
		margin-left:auto;
		margin-right:auto;
	}
	#makeuserid .container table thead tr th {
		font-size: 12px;
		color: #636363;
		text-align: left;
	}	
	#makeuserid .container table td {
		text-align: left;
	}
	th.add_new_line{
		width: 100px;
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
	

	</style>
	<script>
	// 新規に追加する仮のplaceidとして扱う
	var nextid = 0;
	
	function submitData()
	{
		$('#insertuserid').submit();
	}

	function submitcsvData()
	{
		$('#uploadcsv').submit();
	}

	function errorFunc()
	{
		alert("ajaxError");
	};

	</script>
</head>
<body class="admin">
	{include file='admin/menu.tpl'}
		<div id="main">
			<article class="calendar">
			<h1>{t}ユーザー登録{/t}</h1>
				<div id="makeuserid">
					<div class="container">

						<h2 class="campus_name">WEBから登録</h2>
						<form method="POST" action="{$baseurl}/{$controllerName}/insertuserid" name="insertuserid" id="insertuserid" enctype="multipart/form-data">
							<div class="dockind_outer">
								<table id="table_makeuserid" >
									<thead>
										<tr>
											<th ></th>
											<th ></th>
										</tr>
									</thead>
									<tbody id="makeuseridinput">
											<tr class="line" >
												<td>ユーザーID</td>
												<td colspan="2">
													<input id="member_id" type="text" name="member_id" value="" class="makeuserid">
												</td>
											</tr>

											<tr>
												<td>
													パスワード
												</td>
												<td colspan="2">
													<input id="member_pw" type="text" name="member_pw" value="" class="makeuserid">
												</td>
											</tr>

											<tr>
												<td>
													名前
												</td>

												<td colspan="2">
													<input id="member_name_jp" type="text" name="member_name_jp" value="" class="makeuserid">
												</td>
											</tr>

											<tr>
												<td>
													名前(カナ)
												</td>

												<td colspan="2">
													<input id="member_name_kana" type="text" name="member_name_kana" value="" class="makeuserid">
												</td>
											</tr>

											<tr>
												<td>
													性別
												</td>

												<td colspan="2">
													<input type="radio" name="sex" value="1" checked="checked">男性
													<input type="radio" name="sex" value="2">女性
												</td>
											</tr>
											<tr>
												<td rowspan="2">
													権限
												</td>

										<div class="dialog_roles">
													{foreach from=$permissions item=permission name=permissions}
												<td>
														{if !$smarty.foreach.permissions.first && ($smarty.foreach.permissions.index) % 2 == 0}
<!--															<br />   -->
														{/if}
														{if ($smarty.foreach.permissions.index) == 1 || ($smarty.foreach.permissions.index) == 3}
															<input type="checkbox" name="roles[]" value="{$permission->m_member_roles}" >{$permission->roles_jp_clipped_form}
												</td></tr>
														{else}
															<input type="checkbox" name="roles[]" value="{$permission->m_member_roles}" >{$permission->roles_jp_clipped_form}
												</td>
														{/if}
													{/foreach}
										</div>
											</tr>
	
											<tr>
												<td>
													メールアドレス
												</td>

												<td colspan="2">
													<input id="mail_add" type="text" name="mail_add" value="" class="makeuserid">
												</td>
											</tr>
	
											<tr>
												<td>
													学籍番号
												</td>

												<td colspan="2">
													<input id="student_id" type="text" name="student_id" value="" class="makeuserid">
												</td>
											</tr>
	
											<tr>
												<td>
													職員番号
												</td>

												<td colspan="2">
													<input id="staff_no" type="text" name="staff_no" value="" class="makeuserid">
												</td>
											</tr>
	
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
									<div class="cmpsub">{t}ユーザー追加が完了しました。{/t}</div>
								</div>
							</div>

						</form>

						<h2 class="campus_name">CSVファイルから登録</h2>

						<div class="csvfile">
								<form method="post" action="{$baseurl}/{$controllerName}/downloadcsv" id="downloadcsv" enctype="multipart/form-data">
									CSVテンプレートファイルダウンロード　　
						</div>
							<div id="pageControl">
									<input type="submit" name="submit" value="ダウンロード" class="finish" />
							</div>
								</form>

							<hr class="makeuserid" >

						<div class="csvfile">
							CSVファイルアップロード
							<form method="POST" action="{$baseurl}/{$controllerName}/uploadcsv" name="uploadcsv" id="uploadcsv" enctype="multipart/form-data">
								<div class="csvupload">
								ファイル選択：<input name="file" type="file" size="50" class="uploadfile" />
								</div>
<!--　<input type="submit" name="submit" value="アップロード" class="affirm" />    -->
						</div>

							<div id="pageControl">
									<input type="button" value="{t}アップロード{/t}" onclick="submitcsvData()" class="finish" accept="text/csv">
							</div>
							</form>
							
					</div>
				</div>
			</article>
		<!--/#main--></div>
		<aside id="sidebar">

        </aside>
      </div>
		{include file="../common/foot_v2.php"}

		<script>
		
		$(function(){
			$("#basicSettingButton").basicSetting();
			$("#pageControl").find(".finish").decisionDialog($("#finishDialog"));
			
			$("#loginStatusTrigger").miniMenu($("#loginStatus"));
		});
		
		$('#insertuserid').submit(function(event) {
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
						var link = '{$baseurl}/{$controllerName}/makeuserid' ;
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

		$('#uploadcsv').submit(function(event) {
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

						var link = '{$baseurl}/{$controllerName}/makeuserid' ;
						window.location.href=link;
					}
					else
					{	// 成功
						alert("CSVファイルでUSER IDを登録しました。");
						// 完了後の飛び先を設定
						var link = '{$baseurl}/{$controllerName}/makeuserid' ;
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
					// 必ず最後に渡る部分
				},
			});

		});

		</script>
		
	<!--[if lte IE 9]>
	<script src="/js/flexie.min.js" type="text/javascript"></script>
	<![endif]-->
	</body>
</html>