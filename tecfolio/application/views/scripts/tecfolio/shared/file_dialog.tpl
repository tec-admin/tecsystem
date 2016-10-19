			<div id="contentsInsertDialog" class="dialog">
				<div class="cmpsub">{t}ファイルをアップロードしました{/t}</div>
			</div>
			
			<div id="contentsRemoveDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}コンテンツをゴミ箱へ移動{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}選択したコンテンツをゴミ箱へ移動します。完全な削除はゴミ箱タブをクリックして削除操作を行ってください。{/t}
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitContentsRemove();" class="affirm">{t}移動する{/t}</a>
				</div>
			</div>
			<div id="contentsRemoveCompDialog" class="dialog">
				<div class="cmpsub">{t}コンテンツをゴミ箱へ移動しました{/t}</div>
			</div>
			
			<div id="recoverCompDialog" class="dialog">
				<div class="cmpsub">{t}コンテンツをゴミ箱から元に戻しました{/t}</div>
			</div>
			
			<div id="contentsRemoveFailDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}コンテンツをゴミ箱へ移動{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}削除するコンテンツにチェックを入れてください。{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="contentsRecoverFailDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}コンテンツを元に戻す{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}元に戻すコンテンツにチェックを入れてください。{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="contentsDeleteFailDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}コンテンツを完全削除{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}削除するコンテンツにチェックを入れてください。{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="contentsDownloadFailDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}コンテンツを一括ダウンロード{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}ダウンロードするコンテンツにチェックを入れてください。{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="affirm">{t}OK{/t}</a>
				</div>
			</div>
			
			<div id="permDeleteDialog" class="dialog small">
				<i class="sCloseButton cancel" onclick="cancel(this);"></i>
				<div class="sub">{t}コンテンツの完全削除{/t}</div>
				<div class="mythemeWrap">
					<p class="mythemeDesc">
						{t}選択したコンテンツを完全に削除します。ルーブリックとの関連/自己評価/メンター評価/ポートフォリオとの関連が削除されます。これらは元に戻せません。よろしいですか？{/t}<br />
					</p>
				</div>
				<div class="buttonSet dubble">
					<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
					<a onclick="submitManipulateTrashes();" class="affirm">{t}削除する{/t}</a>
				</div>
			</div>
			<div id="permDeleteCompDialog" class="dialog">
				<div class="cmpsub">{t}コンテンツを完全に削除しました{/t}</div>
			</div>