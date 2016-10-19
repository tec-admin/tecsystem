<!doctype html>
<html lang="ja">
<head>
<!--
	{t}利用規約を入力してください{/t}
-->
{include file='admin/header.tpl'}
	<style type="text/css">
	<!--
	.hidden {
		display: none;
	}
	div.container {
		text-align: center;
	}
	.agreement_outer{
		width: 600px;
		display: inline-block;
	}
	#content {
		overflow-y: scroll;
		border: 1px solid #cccccc;
		height: 400px;
		font-family: "Verdana","ヒラギノ角ゴ ProN W3", "Hiragino Kaku Gothic ProN", "メイリオ", Meiryo, sans-serif;
	}
	//-->
	</style>
	<script>
	function submitData()
	{
		$('#updateagreement').submit();
	}
	</script>
	
</head>
<body class="admin">
	{include file='admin/menu.tpl'}
		<div id="main">
			<article class="calendar">
			<h1>{t}利用規約設定{/t}</h1>
				<div id="dockind">
					<div class="container">
						<form method="POST" action="{$baseurl}/{$controllerName}/updateagreement" name="updateagreement" id="updateagreement" enctype="multipart/form-data">
							<div class="agreement_wrap">
								<div class="agreement_outer">
									<div style="padding: 30px 30px 0 30px;">
										<input class="hidden" id="id" name="id" value="{if !empty($agreement)}{$agreement->id}{else}0{/if}"></input>
										<textarea id="content" name="content" placeholder="{t}利用規約を入力してください{/t}">{if !empty($agreement)}{$agreement->content}{/if}</textarea>
									</div>
								</div>
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
		<script src="/js/jquery-1.11.0.min.js" type="text/javascript"></script>
		<script src="/js/jquery-ui-1.10.4.custom.min.js" type="text/javascript"></script>
		<script src="/js/jquery.skOuterClick.js" type="text/javascript"></script>
		<script src="/js/jquery.bpopup.min.js" type="text/javascript"></script>
		<script src="/js/jquery.modalDialog.js" type="text/javascript"></script>
		<script src="/js/jquery.selectMirror.js" type="text/javascript"></script>
		<script src="/js/jquery.textAreaCD.js" type="text/javascript"></script>
		<script src="/js/jquery.replaceButton.js" type="text/javascript"></script>
		<script src="/js/jquery.miniMenu.js" type="text/javascript"></script>
		<script src="/js/jquery.MultiFile.pack.js" type="text/javascript"></script>
		<script src="/js/jquery.ui.datepicker-ja.js" type="text/javascript"></script>
		<script>
		$(function(){
			$("#basicSettingButton").basicSetting();
			$("#pageControl").find(".finish").decisionDialog($("#finishDialog"));
			
			$("#loginStatusTrigger").miniMenu($("#loginStatus"));
		});
		
		$('#updateagreement').submit(function(event) {
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
						var link = '{$baseurl}/{$controllerName}/agreement' ;
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