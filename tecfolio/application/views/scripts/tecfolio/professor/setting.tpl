<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="ja">
<head>
	{include file='tecfolio/shared/common_head.tpl'}
	
	<script>
		function submitRegisterSubject()
		{
			$('#registerSubjectForm').submit();
		}
		function submitRegisterUser()
		{
			$('#registerUserForm').submit();
		}
	</script>
</head>

<body class="commons">
	<div id="topbar">
		{include file='tecfolio/shared/common_top.tpl'}
	</div>
	<div id="contents" class="contentClass">
		<div id="setting">
			<article>
				<div id="wrapcontent" class="hasTabs">
					<ul class="tabs">
						<li><a href="#settingBasic"><span style="font-size:11.5px;">{t}授業科目設定{/t}</span></a></li>
						<li><a href="#settingUsers"><span style="font-size:11.5px;">{t}所属ユーザー設定{/t}</span></a></li>
					</ul>
					
					<form method="post" action="{$baseurl}/{$controllerName}/registersubject" name="registerSubjectForm" id="registerSubjectForm" enctype="multipart/form-data">
					<div class="contents container" id="settingBasic">
						<div class="innerWrapTab">
							<div class="main innerLeft">
								<label for="">{t}選択可能な授業科目{/t}</label>
								<div class="available">
									<select id="availableSelect" class="multiple" multiple>
									{foreach from=$available item=subjectitem name=available}
										<option data-jyu_knr_no="{$subjectitem['jyu_knr_no']}" data-jwaricd="{$subjectitem['jwaricd']}"><span class="head">{$subjectitem['yogen']}　</span>{$subjectitem['class_subject']} ({$subjectitem['jwaricd']})</li>
									{/foreach}
									</select>
									
									<select id="copyAvailableSelect" class="multiple" size="10" multiple style="display:none;">
									{foreach from=$available item=subjectitem name=available}
										<option data-jyu_knr_no="{$subjectitem['jyu_knr_no']}" data-jwaricd="{$subjectitem['jwaricd']}"><span class="head">{$subjectitem['yogen']}　</span>{$subjectitem['class_subject']} ({$subjectitem['jwaricd']})</li>
									{/foreach}
									</select>
								</div>
							</div>
							<div class="main innerCenter">
								<div class="selectArrows">
									<p><span class="wrapBtn"><a class="btn right" id="rightArrow"></a></span></p>
									<p><span class="wrapBtn"><a class="btn reset" id="resetButton"></a></span></p>
								</div>
							</div>
							<div class="main innerRight">
								<label for="">{t}登録済み/選択された授業科目{/t}</label>
								<div class="selected">
									<select id="selectedSelect" size="10">
									{foreach from=$selected_all item=subjectitem name=selected}
										<option disabled><span class="head">{$subjectitem['jyu_nendo']}　{$subjectitem['yogen']}　</span>{$subjectitem['class_subject']} ({$subjectitem['jwaricd']})</option>
									{/foreach}
									</select>
									
									<select id="copySelectedSelect" size="10" style="display:none;">
									{foreach from=$selected_all item=subjectitem name=selected}
										<option disabled><span class="head">{$subjectitem['jyu_nendo']}　{$subjectitem['yogen']}　</span>{$subjectitem['class_subject']} ({$subjectitem['jwaricd']})</option>
									{/foreach}
									</select>
									
									<div id="completeValues" style="display:none;">
									</div>
								</div>
							</div>
							<div class="innerBottom">
								<a id="registerBtn" class="submit orange">{t}登録{/t}</a>
							</div>
						</div>
					</div>
					</form>
					
					<div class="contents container" id="settingUsers">
						<div class="innerWrapTab">
							<h1 class="subtitle">{t}1. 授業科目を選択{/t}</h1>
							<form method="post" action="{$baseurl}/{$controllerName}/getuser" name="getuserForm" id="getuserForm" enctype="multipart/form-data">
							<div class="innerTop">
								<select id="subjectSelect">
									<option selected disabled>{t}選択してください{/t}</option>
									{foreach from=$selected_year item=subjectitem name=selected}
										<option data-nendo="{$subjectitem['jyu_nendo']}" data-jwaricd="{$subjectitem['jwaricd']}"><span class="head">{$subjectitem['jyu_nendo']}　{$subjectitem['yogen']}　</span>{$subjectitem['class_subject']} ({$subjectitem['jwaricd']})</option>
									{/foreach}
								</select>
								
								<div id="completeSubject" style="display:none;">
								</div>
							</div>
							</form>
							<h1 class="subtitle">{t}2. 登録/除外するユーザーを選択{/t}</h1>
							<form method="post" action="{$baseurl}/{$controllerName}/registeruser" name="registerUserForm" id="registerUserForm" enctype="multipart/form-data">
							<div id="selectedSubject" style="display:none;"></div>
							<div class="main innerLeft">
								<label for="">{t}未登録のユーザー{/t}</label>
								<div class="available">
									<select id="unregisteredUsers" class="multiple" size="10" multiple disabled>
									</select>
									
									<div id="leavingUsers" style="display:none;">
									</div>
								</div>
							</div>
							<div class="main innerCenter">
								<div class="selectArrows">
									<p><span class="wrapBtn"><a class="btn right" id="joinUser"></a></span></p>
									<p><span class="wrapBtn"><a class="btn left" id="leaveUser"></a></span></p>
									<p><span class="wrapBtn"><a class="btn reset" id="resetUser"></a></span></p>
								</div>
							</div>
							<div class="main innerRight">
								<label for="">{t}登録済みのユーザー{/t}</label>
								<div class="selected">
									<select id="registeredUsers" class="multiple" size="10" multiple disabled>
									</select>
									
									<div id="joiningUsers" style="display:none;">
									</div>
								</div>
							</div>
							<div class="innerBottom">
								<a id="userBtn" class="submit orange">{t}登録{/t}</a>
							</div>
							</form>
						</div>
					</div>
				</div>
			</article>
			
			
			<div id="registerSubjectDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}授業科目設定{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}選択した授業科目を登録します。よろしいですか？{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitRegisterSubject();" class="affirm">{t}登録する{/t}</a>
				</div>
			</div>
			<div id="registerSubjectFailDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}授業科目設定{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}登録する授業科目を選択してください。{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			<div id="registerSubjectCompDialog" class="dialog">
				<div class="cmpsub">{t}授業科目を登録しました。{/t}</div>
			</div>
			
			
			<div id="registerUserDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">所属ユーザー設定</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}授業科目グループの所属ユーザーを設定します。よろしいですか？{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitRegisterUser();" class="affirm">{t}登録する{/t}</a>
				</div>
			</div>
			<div id="registerUserFailDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}所属ユーザー設定{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}登録/除外するユーザーを選択してください。{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			<div id="registerUserCompDialog" class="dialog">
				<div class="cmpsub">{t}所属ユーザーを設定しました。{/t}</div>
			</div>
			
			{include file='tecfolio/shared/common_dialog.tpl'}
		</div>
	</div>
	{include file="../../common/foot_v2.php"}
	<script>
		var num_join	= 0;
		var num_leave	= 0;
		
		function setSelectEvent()
		{
			// 各要素ダブルクリックで、矢印ボタン押下と同様の動作をさせる
			$('#availableSelect > option').off();
			$('#availableSelect > option').on('dblclick', function(event) {
				$('#rightArrow').triggerHandler('click');
			});
		}
		
		function resetUserValues()
		{
			// 左右のselect
			$('#unregisteredUsers > *').remove();
			$('#registeredUsers > *').remove();
			
			// hidden
			$('#joiningUsers > *').remove();
			$('#leavingUsers > *').remove();
		}
		
		function setUserEvents()
		{
			// 右矢印(登録)イベント
			// ・選択されたユーザーは、optionのvalueとtextをそのまま右にコピーし、これにdata-id(数値)を設定する。
			// 		同時に、登録の際に用いるhidden値に学籍番号を設定する。このinputのidは、上記data-idに応じたものである。
			// ・右矢印→左矢印で再度未登録となる場合、optionに設定されたdata-idを元に、上記hidden値の削除処理を行う。
			$('#joinUser').off('click');
			$('#joinUser').on('click', function(){
				var str = "";
				$('#unregisteredUsers > option:selected').each(function(){
					var v = $(this).val();
					var t = $(this).text();
					var i = $(this).attr('data-id');
					if($('#leave' + i).get(0))
					{
						str += "<option value='" + v + "'>" + t + "</option>";
						$('#leave' + i).remove();
					}
					else
					{
						str += "<option data-id='" + num_join + "' value='" + v + "'>" + t + "</option>";
						$('#joiningUsers').append($('<input type="hidden" name="join_gakse_id[]" id="join' + (num_join++) + '">').val(v));
					}
					$(this).remove();
				});
				if(str != undefined)
					$('#registeredUsers').prepend(str);
			});
			
			// 左矢印(削除)イベント
			// 右矢印と同様の仕組みで、逆方向の処理を行う
			$('#leaveUser').off('click');
			$('#leaveUser').on('click', function(){
				var str = "";
				$('#registeredUsers > option:selected').each(function(){
					var v = $(this).val();
					var t = $(this).text();
					var i = $(this).attr('data-id');
					if($('#join' + i).get(0))
					{
						str += "<option value='" + v + "'>" + t + "</option>";
						$('#join' + i).remove();
					}
					else
					{
						str += "<option data-id='" + num_leave + "' value='" + v + "'>" + t + "</option>";
						$('#leavingUsers').append($('<input type="hidden" name="leave_gakse_id[]" id="leave' + (num_leave++) + '">').val(v));
					}
					$(this).remove();
				});
				if(str != undefined)
					$('#unregisteredUsers').prepend(str);
			});
			
			// リセットイベント
			// 授業科目の選択イベントをトリガーする
			$('#resetUser').off('click');
			$('#resetUser').on('click', function(){
				$('#subjectSelect').triggerHandler('change');
			});
		}
		
		$(function(){
			$('#wrapcontent.hasTabs').tabs();
			
			// 登録ボタン押下時チェック
			$('#registerBtn').click(function(){
				var flg = 0;
				$('#completeValues > *').each(function(){
					flg = 1
					return true;
				});
				
				if(flg)
					$("#registerSubjectDialog").bPopup();
				else
					$("#registerSubjectFailDialog").bPopup();
			});
			
			// フォームサブミット
			$('#registerSubjectForm').submit(function(event) {
				ajaxSubmit(this, event, function(response){
					$('#registerSubjectCompDialog').bPopup({
						modalClose: false
					});
					$('#registerSubejctDialog').bPopup().close();
					
					setTimeout(function(){
						window.location = '{$baseurl}/{$controllerName}/{$actionName}';
					},1500);
				});
			});
			
			// 矢印ボタン
			$('#rightArrow').click(function(){
				var str = "";
				$('#availableSelect > option:selected').each(function(){
					var v1 = $(this).attr('data-jyu_knr_no');
					var v2 = $(this).attr('data-jwaricd');
					var t = $(this).text();
					str += "<option data-jyu_knr_no='" + v1 + "' data-jwaricd='" + v2 + "'>" + t + "</option>";
					$('#completeValues').append($('<input type="hidden" name="jyu_knr_no[]">').val(v1));
					$('#completeValues').append($('<input type="hidden" name="jwaricd[]">').val(v2));
					$(this).remove();
				});
				if(str != undefined)
					$('#selectedSelect').prepend(str);
			});
			
			setSelectEvent();
			
			// リセットボタン
			$('#resetButton').click(function(){
				// 右側「登録済み/選択された授業科目」を元に戻す
				$('#selectedSelect > option').remove();
				$('#copySelectedSelect > option').each(function(){
					var t = $(this).text();
					$('#selectedSelect').append($('<option disabled>').html(t));
				});
				
				// 左側「選択可能な授業科目」を元に戻す
				$('#availableSelect > option').remove();
				$('#copyAvailableSelect > option').each(function(){
					var v1 = $(this).attr('data-jyu_knr_no');
					var v2 = $(this).attr('data-jwaricd');
					var t = $(this).text();
					$('#availableSelect').append($('<option>').html(t).attr('data-jyu_knr_no', v1).attr('data-jwaricd', v2));
					$('#completeValues > *').remove();
				});
				setSelectEvent();
			});
			
			// 授業科目変更
			$('#subjectSelect').change(function(){
				var sel = $('#subjectSelect > option:selected');
				
				$('#completeSubject > *').remove();
				$('#completeSubject').append($('<input type="hidden" name="nendo">').val(sel.data('nendo')));
				$('#completeSubject').append($('<input type="hidden" name="jwaricd">').val(sel.data('jwaricd')));
				
				$('#selectedSubject > *').remove();
				$('#selectedSubject').append($('<input type="hidden" name="nendo">').val(sel.data('nendo')));
				$('#selectedSubject').append($('<input type="hidden" name="jwaricd">').val(sel.data('jwaricd')));
				
				$('#getuserForm').submit();
			});
			
			// 授業科目変更後の取得データ表示処理
			$('#getuserForm').submit(function(event){
				ajaxSubmit(this, event, function(response){
					resetUserValues();
					
					var ureg = response['unregistered'];
					var ut	= $('#unregisteredUsers');
					ut.removeAttr('disabled');
					for(var i in ureg)
					{
						ut.append($('<option>').text(ureg[i]['name_jp']).val(ureg[i]['gakse_id']));
					}
					
					var reg = response['registered'];
					var t	= $('#registeredUsers');
					t.removeAttr('disabled');
					for(var i in reg)
					{
						t.append($('<option>').text(reg[i]['name_jp']).val(reg[i]['gakse_id']));
					}
				});
				
				setUserEvents();
			});
			
			// 登録ボタン押下時チェック
			$('#userBtn').click(function(){
				var flg = 0;
				$('#joiningUsers > *').each(function(){
					flg = 1;
					return true;
				});
				$('#leavingUsers > *').each(function(){
					flg = 1;
					return true;
				});
				
				if(flg)
					$("#registerUserDialog").bPopup();
				else
					$("#registerUserFailDialog").bPopup();
			});
			
			// フォームサブミット
			$('#registerUserForm').submit(function(event) {
				ajaxSubmit(this, event, function(response){
					$('#registerUserCompDialog').bPopup({
						modalClose: false
					});
					$('#registerUserDialog').bPopup().close();
					
					setTimeout(function(){
						window.location = '{$baseurl}/{$controllerName}/{$actionName}';
					},1500);
				});
			});
		});
	</script>
</body>
</html>