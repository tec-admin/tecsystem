	<footer id="pageFoot">
		<div class="fix-wrap" style="margin-left:auto; margin-right:auto;">
			<small>Copyright &copy; 2015 Kansai University.</small>
			<div class="contact" id="contactAdmin"><a href="/contact.html">{t}管理者に連絡{/t}</a></div>
		</div>

		<div id="contactDialog" class="dialog">
			<i class="closeButton cancel"></i>
			<div class="sub">{t}TECsystemに関するお問い合わせ{/t}</div>
			<p>{t}こちらにご連絡ください{/t}</p>
			<a href="mailto:tec-info@ml.kandai.jp?subject=TECsystem:{if !empty($member->student_id)}{$member->student_id}{else}[{t}学籍番号{/t}]{/if}%20{if !empty($member->name_jp)}{$member->name_jp}{else}[氏名]{/if}%20[{t}お問い合わせの概要を入力してください{/t}]&amp;body={if !empty($member->student_id)}{$member->student_id}{else}[{t}学籍番号{/t}]{/if}%20{if !empty($member->name_jp)}{$member->name_jp}{else}[{t}氏名{/t}]{/if}%0d%0a[{t}以下にお問い合わせ内容を入力してください{/t}]" class="adminMail">tec-info@ml.kandai.jp</a>
			<p>{t}学籍番号と氏名、お問い合わせ内容の概要をメールのタイトルに記入してください。{/t}</p>
			<div class="buttonSet single">
				<a href="#" class="cancel">{t}閉じる{/t}</a>
			</div>
		</div>
	</footer>
