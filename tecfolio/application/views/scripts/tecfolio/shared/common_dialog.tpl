<div id="infoDialog" class="dialog small notice">
	<form method="POST" action="{$baseurl}/tecfolio/student/updatementor" name="updateMentorForm" id="updateMentorForm" enctype="multipart/form-data">
	<input type="hidden" name="mentor_id" id="mentor_id" />
	<input type="hidden" name="mentor_flag" id="mentor_flag" />
	<i class="sCloseButton cancel" onclick="cancel(this);"></i>
	<div class="sub">{t}新着情報{/t}</div>
	<div class="infoWrap">
		<ul>
			{if !empty($news)}
				{foreach from=$news item=info name=news}
					{if !empty($info['t_mentors_lastupdate']) && $info['MyID'] == $info['t_mentors_m_member_id'] && $info['t_mentors_agreement_flag'] == 0}
						<li>
							<time class="new">{$vDate->dateFormat($info['studentnews_date'], 'Y/m/d(wj) H:i')}</time><span class="accept" data-id="{$info['t_mentors_id']}">{t}承諾{/t}</span><span class="reject" data-id="{$info['t_mentors_id']}">{t}拒否{/t}</span>
							<a class="mentor">{t 1={$info['requester_name_jp']}}%1さんからメンターの依頼が来ています。{/t}</a>
						</li>
					{elseif !empty($info['t_mentors_lastupdate']) && $info['MyID'] == $info['t_mentors_m_member_id'] && $info['t_mentors_agreement_flag'] != 0}
						<li>
							<time>{$vDate->dateFormat($info['studentnews_date'], 'Y/m/d(wj) H:i')}</time>
							{if $info['t_mentors_agreement_flag'] == 1}
							<a class="mentor">{t 1={$info['requester_name_jp']}}%1さんからのメンターの依頼を承諾しました。{/t}</a>
							{elseif $info['t_mentors_agreement_flag'] == 2}
							<a class="mentor">{t 1={$info['requester_name_jp']}}%1さんからのメンターの依頼を拒否しました。{/t}</a>
							{/if}
						</li>
					{elseif !empty($info['t_mentors_lastupdate']) && $info['MyID'] == $info['requester_id'] && $info['t_mentors_agreement_flag'] == 0}
						<li>
							<time>{$vDate->dateFormat($info['studentnews_date'], 'Y/m/d(wj) H:i')}</time>
							<a class="mentor">{t 1={$info['t_mentors_name_jp']}}%1さんにメンターの依頼をしています。{/t}</a>
						</li>
					{elseif !empty($info['t_mentors_lastupdate']) && $info['MyID'] == $info['requester_id'] && $info['t_mentors_agreement_flag'] != 0}
						<li>
							<time>{$vDate->dateFormat($info['studentnews_date'], 'Y/m/d(wj) H:i')}</time>
							{if $info['t_mentors_agreement_flag'] == 1}
							<a class="mentor">{t 1={$info['t_mentors_name_jp']}}%1さんへのメンターの依頼が承諾されました。{/t}</a>
							{elseif $info['t_mentors_agreement_flag'] == 2}
							<a class="mentor">{t 1={$info['t_mentors_name_jp']}}%1さんへのメンターの依頼が承諾されませんでした。{/t}</a>
							{/if}
						</li>
					{/if}
				{foreachelse}
					<li>{t}新着なし{/t}</li>
				{/foreach}
			{/if}
		</ul>
	</div>
	<div class="buttonSet dubble">
		<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
	</div>
	</form>
</div>

<div id="pageControl">
	<div id="acceptedDialog" class="dialog">
		<div class="cmpsub">{t}メンターの依頼を承諾しました。{/t}</div>
	</div>
	<div id="rejectedDialog" class="dialog">
		<div class="cmpsub">{t}メンターの依頼を拒否しました。{/t}</div>
	</div>
</div>


<div id="mythemeDialog" class="dialog small">
	<form method="POST" action="{$baseurl}/{$controllerName}/insertmytheme" name="mythemeForm" id="mythemeForm" enctype="multipart/form-data">
	<i class="sCloseButton cancel" onclick="cancel(this);"></i>
	<div class="sub">{t}テーマを追加する{/t}</div>
	<div class="mythemeWrap">
		<p class="mythemeDesc">
			{t}Myテーマに新たなテーマを追加します。テーマ名称を入力してください{/t}
		</p>
		<div class="mythemeForm">
			<label class="mythemeFormLabel">{t}テーマ名称：{/t}</label><input type="text" name="newtheme" id="newtheme" />
		</div>
	</div>
	<div class="buttonSet dubble">
		<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
		<a onclick="submitMytheme();" class="affirm">{t}登録する{/t}</a>
	</div>
	</form>
</div>
<div id="mythemeCompDialog" class="dialog">
	<div class="cmpsub">{t}Myテーマを追加しました{/t}</div>
</div>



<div id="mythemeEditDialog" class="dialog small">
	<form method="POST" action="{$baseurl}/{$controllerName}/updatemytheme" name="mythemeEditForm" id="mythemeEditForm" enctype="multipart/form-data">
	<input type="hidden" name="mytheme_edit_id" id="mytheme_ed_id" />
	<i class="sCloseButton cancel" onclick="cancel(this);"></i>
	<div class="sub">{t}テーマを編集する{/t}</div>
	<div class="mythemeWrap">
		<p class="mythemeDesc">
			{t}テーマ名称を編集してください{/t}
		</p>
		<div class="mythemeForm">
			<label class="mythemeFormLabel">{t}テーマ名称：{/t}</label><input type="text" name="edittheme" id="edittheme" />
		</div>
	</div>
	<div class="buttonSet dubble">
		<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
		<a onclick="submitMythemeEdit();" class="affirm">{t}編集する{/t}</a>
	</div>
	</form>
</div>
<div id="mythemeEditCompDialog" class="dialog">
	<div class="cmpsub">{t}Myテーマを変更しました{/t}</div>
</div>



<div id="mythemeDeleteDialog" class="dialog small">
	<form method="POST" action="{$baseurl}/{$controllerName}/deletemytheme" name="mythemeDeleteForm" id="mythemeDeleteForm" enctype="multipart/form-data">
	<input type="hidden" name="mytheme_delete_id" id="mytheme_delete_id" />
	<input type="hidden" name="mytheme_delete_name" id="mytheme_delete_name" />
	<i class="sCloseButton cancel" onclick="cancel(this);"></i>
	<div class="sub">{t}テーマを削除する{/t}</div>
	<div class="mythemeWrap">
		<p class="mythemeDesc">
			{t 1='<span id="mytheme_delete_name_disp"></span>'}Myテーマから「%1」を削除します。削除すると、「ファイル置場」「ポートフォリオ」「ルーブリック」すべてのデータが消滅します。よろしいですか？{/t}
		</p>
	</div>
	<div class="buttonSet dubble">
		<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
		<a onclick="submitMythemeDelete();" class="affirm">{t}削除する{/t}</a>
	</div>
	</form>
</div>
<div id="mythemeDeleteCompDialog" class="dialog">
	<div class="cmpsub">{t}Myテーマを削除しました{/t}</div>
</div>



<div id="errorDialog" class="dialog medium">
	<i class="sCloseButton cancel" onclick="cancel(this);"></i>
	<div class="sub">{t}エラー{/t}</div>
	<div class="mythemeWrap">
		<p class="mythemeDesc" id="errorContents">{t}エラー{/t}</p>
	</div>
	<div class="buttonSet dubble">
		<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
	</div>
</div>


