<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="ja">
<head>
<!--
	{t}アップロード上限を超えています(ファイルサイズ：%1){/t}
	{t}画像ファイルを選択してください{/t}
-->
	{include file='tecfolio/shared/common_head.tpl'}
	
	<script>
		function submitUpdateProfile()
		{
			$('#updateProfileForm').submit();
		}
		function cancelUpdateProfile()
		{
			$("#updateProfileDialog").bPopup().close();
		}
	</script>
</head>

<body class="commons">
	<div id="topbar">
		{include file='tecfolio/shared/common_top.tpl'}
	</div>
	<div id="contents" class="fix-wrap">
		<div id="profile">
			<article>
				<div id="wrapcontent" class="hasTabs" style="display:none;">
					<ul class="tabs">
						<li><a href="#profileBasic">{t}基本設定{/t}</a></li>
						<li><a href="#profileDetail">{t}詳細{/t}</a></li>
						<li><a href="#profileExperience">{t}経験{/t}</a></li>
					</ul>
					
					<form method="post" action="{$baseurl}/{$controllerName}/updateprofile" name="updateProfileForm" id="updateProfileForm" enctype="multipart/form-data">
					<input type="hidden" name="edittype" value="{if !empty($profile->m_member_id)}1{/if}" />
					<div class="contents container" id="profileBasic">
						<div class="innerWrapTab">
							<div class="innerLeft">
								<table class="profileTbl">
									<tbody class="contentsInner">
										<tr>
											<th>{t}学籍番号{/t}</th>
											<td>{$profile->m_members_student_id_jp}</td>
										</tr>
										<tr>
											<th>{t}氏名{/t}</th>
											<td>{$profile->m_members_name_jp}</td>
										</tr>
										<!--<tr>
											<th>ニックネーム</th>
											<td><input type="text" name="profile[nickname]" class="nickname" value="{$profile->nickname}" /></td>
										</tr>-->
										<!--<tr>
											<th>言語</th>
											<td>
												<select name="profile[languages]">
													<option value="1" {if $profile->languages == 1}selected{/if}>日本語</option>
													<option value="2" {if $profile->languages == 2}selected{/if}>英語</option>
												</select>
											</td>
										</tr>-->
										<tr>
											<th>{t}メールアドレス 1{/t}</th>
											<td>{$profile->m_members_email}</td>
										</tr>
										<tr>
											<th>{t}メールアドレス 2{/t}</th>
											<td><input type="text" name="profile[email_2]" value="{$profile->email_2}" /></td>
										</tr>
										<tr>
											<th>{t}メールアドレス 3{/t}</th>
											<td><input type="text" name="profile[email_3]" value="{$profile->email_3}" /></td>
										</tr>
										<tr>
											<th>{t}画像{/t}</th>
											<td>
												<input type="hidden" id="baseurl" name="baseurl" value="{$baseurl}" />
												<input type="hidden" id="controllerName" name="controllerName" value="{$controllerName}" />
												
												<div class="preview">
													{if !empty($profile->input_name)}<img src="{$profile->input_name}" class="thumbnail" />{/if}
												</div>
												<div class="fileUploader">
													{if !empty($profile->image_name)}<p class="delete">　</p>{/if}
													<input type="text" class="long txt" name="profile[image_name]" value="{if !empty($profile->image_name)}{$profile->image_name}{/if}" readonly>
													<a class="btn ref"><span class="refText">{t}参照{/t}</span></a>
													<input type="file" id="image_main" name="image_main" class="uploader check size" data-name="{t}会社画像{/t}" accept="image/*">
													<input type="hidden" id="image_main_hidden" name="image_main_hidden" value="">
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="innerRight">
								<table class="profileTbl">
									<tbody class="contentsInner">
										<tr>
											<th>{t}学部{/t}</th>
											<td>{$profile->syozoku1_szknam_c}</td>
										</tr>
										<tr>
											<th>{t}学科{/t}</th>
											<td>{$profile->syozoku2_szknam_c}</td>
										</tr>
										<tr>
											<th>{t}専攻/専修/コース{/t}</th>
											<td><input type="text" name="profile[speciality]" value="{$profile->speciality}" /></td>
										</tr>
										<tr>
											<th>{t}ゼミ{/t}</th>
											<td><input type="text" name="profile[seminar]" value="{$profile->seminar}" /></td>
										</tr>
										<tr>
											<th>{t}卒業した高校{/t}</th>
											<td><input type="text" name="profile[highschool]" value="{$profile->highschool}" /></td>
										</tr>
										<tr>
											<th>{t}誕生日{/t}</th>
											<td><input type="text" name="profile[birthday]" value="{$profile->birthday}" /></td>
										</tr>
										<tr>
											<th>{t}性別{/t}</th>
											<td><input type="text" name="profile[sex]" value="{$profile->sex}" /></td>
										</tr>
										<tr>
											<th>{t}出身地{/t}</th>
											<td><input type="text" name="profile[birthplace]" value="{$profile->birthplace}" /></td>
										</tr>
										<tr>
											<th>{t}メンターになる{/t}</th>
											<td>
												<div class="mentorCheck"><input type="checkbox" name="profile[mentor_flag]" value="1" {if $profile->mentor_flag == 1}checked="checked"{/if}>{t}メンターとして登録する{/t}</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="innerBottom">
								<div class="profWarn">{t}基本設定で入力された情報はメンターに開示されます{/t}</div>
								<a class="submit orange">{t}保存{/t}</a>
							</div>
						</div>
					</div>
					
					<div class="contents container" id="profileDetail">
						<div class="innerWrapTab">
							<div class="innerLeft">
								<table class="profileTbl">
									<tbody class="contentsInner">
										<tr>
											<th>{t}趣味{/t}</th>
											<td><input type="text" name="profile[hobby]" value="{$profile->hobby}" /></td>
										</tr>
										<tr>
											<th>{t}特技{/t}</th>
											<td><input type="text" name="profile[ability]" value="{$profile->ability}" /></td>
										</tr>
										<tr>
											<th>{t}好きなもの{/t}</th>
											<td><input type="text" name="profile[likes]" value="{$profile->likes}" /></td>
										</tr>
										<tr>
											<th>{t}苦手なもの{/t}</th>
											<td><input type="text" name="profile[dislikes]" value="{$profile->dislikes}" /></td>
										</tr>
										<tr>
											<th>{t}性格{/t}</th>
											<td><textarea name="profile[personality]" rows="2">{$profile->personality}</textarea></td>
										</tr>
										<tr>
											<th>{t}長所{/t}</th>
											<td><textarea name="profile[strength]" rows="2">{$profile->strength}</textarea></td>
										</tr>
										<tr>
											<th>{t}短所{/t}</th>
											<td><textarea name="profile[weekness]" rows="2">{$profile->weekness}</textarea></td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="innerRight">
								<table class="profileTbl">
									<tbody class="contentsInner">
										<tr>
											<th>{t}資格 1{/t}</th>
											<td><input type="text" name="profile[cert_1]" value="{$profile->cert_1}" /></td>
										</tr>
										<tr>
											<th>{t}資格 2{/t}</th>
											<td><input type="text" name="profile[cert_2]" value="{$profile->cert_2}" /></td>
										</tr>
										<tr>
											<th>{t}資格 3{/t}</th>
											<td><input type="text" name="profile[cert_3]" value="{$profile->cert_3}" /></td>
										</tr>
										<tr>
											<th>{t}資格 4{/t}</th>
											<td><input type="text" name="profile[cert_4]" value="{$profile->cert_4}" /></td>
										</tr>
										<tr>
											<th>{t}資格 5{/t}</th>
											<td><input type="text" name="profile[cert_5]" value="{$profile->cert_5}" /></td>
										</tr>
										<tr>
											<th>{t}自己PR{/t}</th>
											<td><textarea name="profile[pr]" rows="8">{$profile->pr}</textarea></td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="innerBottom">
								<a class="submit orange">{t}保存{/t}</a>
							</div>
						</div>
					</div>
					
					<div class="contents container" id="profileExperience">
						<div class="innerWrapTab">
							<div class="innerLeft">
								<table class="profileTbl">
									<tbody class="contentsInner">
										<tr>
											<th>{t}楽しかった思い出(400字程度){/t}</th>
											<td><textarea name="profile[memories]" rows="10">{$profile->memories}</textarea></td>
										</tr>
										<tr>
											<th>{t}がんばったこと(400字程度){/t}</th>
											<td><textarea name="profile[tried]" rows="10">{$profile->tried}</textarea></td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="innerRight">
								<table class="profileTbl">
									<tbody class="contentsInner">
										<tr>
											<th>{t}うまくいった経験(400字程度){/t}</th>
											<td><textarea name="profile[succeeded]" rows="10">{$profile->succeeded}</textarea></td>
										</tr>
										<tr>
											<th>{t}失敗した経験/対処(400字程度){/t}</th>
											<td><textarea name="profile[failed]" rows="10">{$profile->failed}</textarea></td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="innerBottom">
								<a class="submit orange">{t}保存{/t}</a>
							</div>
						</div>
					</div>
					
					</form>
				</div>
			</article>
			<div id="updateProfileDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancelUpdateProfile();"></i>
				<div class="sub">{t}個人設定情報の更新{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}個人設定情報を更新します。よろしいですか？{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancelUpdateProfile();" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitUpdateProfile();" class="affirm">{t}更新する{/t}</a>
				</div>
			</div>
			<div id="updateProfileCompDialog" class="dialog">
				<div class="cmpsub">{t}個人設定情報を更新しました。{/t}</div>
			</div>
		</div>
	</div>
	{include file="../../common/foot_v2.php"}
	<script>
		function callUpdateProfile(response)
		{
			// 完了ダイアログ
			$('#updateProfileCompDialog').bPopup({
				modalClose: false
			});
			$('#updateProfileDialog').bPopup().close();
			
			setTimeout(function(){
				window.location = '{$baseurl}/{$controllerName}/{$actionName}';
			},1500);
		}
		$(function(){
			$('#wrapcontent.hasTabs').tabs();
			$('#wrapcontent.hasTabs').css('display', 'block');
			
			$('.fileUploader').fileUploader();
			
			$('a.submit').each(function(){
				$(this).click(function(){
					$("#updateProfileDialog").bPopup();
				});
			});
			
			$('#updateProfileForm').submit(function(event) {
				ajaxSubmit(this, event, callUpdateProfile);
			});
		});
	</script>
</body>
</html>