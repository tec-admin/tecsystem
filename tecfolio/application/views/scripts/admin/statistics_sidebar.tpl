<script>
function cancel()
{
	$("#basicSettingDialog").bPopup().close();
}
</script>
<aside id="sidebar">
	<h1>{t}利用統計{/t}</h1>
	<nav id="kado">
		<ul>
			<li{if $actionName=='utilization'} class="active"{/if}><a href="{$baseurl}/{$controllerName}/utilization">{t}稼働率{/t}<br>{t}(稼働件数/スタッフ){/t}</a></li>
			<li{if $actionName=='byreserveform'} class="active"{/if}><a href="{$baseurl}/{$controllerName}/byreserveform">{t}予約形態別利用状況{/t}<br>{t}(予約形態別相談件数/相談件数){/t}</a></li>
			<li{if $actionName=='byfacultyandclass'} class="active"{/if}><a href="{$baseurl}/{$controllerName}/byfacultyandclass">{t}学部・学年別利用状況{/t}</a></li>
			<li><a id="download" href="#">{t escape=no}利用データ<br>ダウンロード{/t}</a></li>
		</ul>
		<div id="pageControl">
			<div id="basicSettingDialog" class="dialog">
				<i class="closeButton cancel"></i>
				<div class="sub">{t}利用データのダウンロード{/t}</div>
				
				<div align="center" style="padding-top: 5px;">
					<font size="3">{t}以下の利用規約をよく読んでダウンロードしてください{/t}</font>
				</div>
				<div style="width: 540px; padding: 30px 30px 0 30px;">
					<div class="sc">{$agreement->content}</div>
				</div>
				<div class="buttonSet dubble" style="padding-top: 30px">
					<a href="{$baseurl}/{$controllerName}/exporthistory" class="affirm" onClick="cancel()" style="width: 210px; padding: 10px 20px;">{t}同意してダウンロード{/t}</a>
					<a class="delete" onClick="cancel()" style="width: 100px; padding: 10px 20px;">{t}同意しない{/t}</a>
				</div>
			</div>
		</div>
	</nav>
</aside>