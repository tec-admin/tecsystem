<div id="ciniiDialog" class="dialog extra api_dialog">
	<i class="sCloseButton cancel" onclick="cancel(this);"></i>
	<i class="icon cinii"></i><div class="sub hasIcon">{t}文献情報を追加する{/t}</div>
	<div class="dialogWrap">
		<div class="dialogWrapTop">
			<form method="POST" action="{$baseurl}/{$controllerName}/getcinii" name="getCiniiForm" id="getCiniiForm" enctype="multipart/form-data">
				{t}検索対象：{/t}<select id="cinii_search_index" name="search_index">
					<option value="title" selected="selected">{t}論文名{/t}</option>
					<option value="author">{t}著者名{/t}</option>
					<option value="issn">{t}ISSN{/t}</option>
					<option value="publisher">{t}出版者{/t}</option>
					<option value="affiliation">{t}著者所属{/t}</option>
					<option value="journal">{t}刊行物名{/t}</option>
					<option value="references">{t}参考文献名{/t}</option>
				</select>
				<input type="text" id="cinii_search_text" name="search_text" />
				<select id="cinii_search_order" name="search_order">
					<option value="1" selected="selected">{t}出版年 降順{/t}</option>
					<option value="2">{t}出版年 昇順{/t}</option>
					<option value="3">{t}論文名 降順{/t}</option>
					<option value="4">{t}論文名 昇順{/t}</option>
					<option value="5">{t}刊行物 降順{/t}</option>
					<option value="6">{t}刊行物 昇順{/t}</option>
				</select>
				<input type="hidden" id="cinii_search_index_hidden" name="search_index_hidden" />
				<input type="hidden" id="cinii_search_text_hidden" name="search_text_hidden" />
				<input type="hidden" id="cinii_search_order_hidden" name="search_order_hidden" />
				<input type="hidden" id="cinii_search_flag" name="search_flag" />
				<input type="hidden" id="cinii_start_num" name="start_num" value="1" />
				<input type="button" onclick="searchCinii();" value="{t}検索{/t}" />
			</form>
			<div id="ciniiDescription" class="description">
			</div>
			
		</div>
		
		<div class="loading hidden">
			<img src="/images/loading.gif" />
		</div>
		
		<form method="POST" action="{$baseurl}/{$controllerName}/insertcinii/id/{if !empty($selected->id)}{$selected->id}{else}{$subjectid}{/if}" name="submitCiniiForm" id="submitCiniiForm" enctype="multipart/form-data">
			<div class="resultTbl hidden" id ="ciniiResultTbl">
				<div class="wrapCinii odd">
					<div class="innerCinii ci-left">
						<input class="ciniiCheckbox" type="checkbox" name="checkbox[0]" value="0" data-title="JASDF Air Development & Test Wing 60th Anniversary" data-url="http://ci.nii.ac.jp/naid/40020686396/en">
					</div>
					<div class="innerCinii ci-right">
						<a class="ci-link" target="_blank" href="http://ci.nii.ac.jp/naid/40020686396/en">
							<div class="ci-title">JASDF Air Development &amp; Test Wing 60th Anniversary</div>
						</a>
						<div class="ci-creator">{t}著者なし{/t}</div>
						<div class="ci-description">{t}抄録なし{/t}</div>
						<div class="ci-prism">航空ファン 65(2), 1-5, 2016-02</div>
						<div class="ci-publisher">{t}出版者なし{/t}</div>
					</div>
				</div>
			</div>
		</form>
		
		<div id="ciniiPageLink">
		</div>
	</div>
	<div class="buttonSet dubble hidden">
		<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
		<a onclick="submitCinii();" class="affirm">{t}追加する{/t}</a>
	</div>
</div>

<div id="amazonDialog" class="dialog extra api_dialog">
	<i class="sCloseButton cancel" onclick="cancel(this);"></i>
	<i class="icon amazon"></i><div class="sub hasIcon">{t}文献情報を追加する{/t}</div>
	<div class="dialogWrap">
		<div class="dialogWrapTop">
			<form method="POST" action="{$baseurl}/{$controllerName}/getamazon" name="getAmazonForm" id="getAmazonForm" enctype="multipart/form-data">
				<select name="search_index">
					<option value="Books">{t}和書{/t}</option>
					<option value="ForeignBooks">{t}洋書{/t}</option>
				</select>
				<input type="text" id="amazon_search_text" name="search_text" />
				<input type="hidden" id="amazon_search_text_hidden" name="search_text_hidden" />
				<input type="hidden" id="amazon_search_flag" name="search_flag" />
				<input type="hidden" id="amazon_start_num" name="start_num" value="1" />
				<input type="button" onclick="searchAmazon();" value="{t}検索{/t}" />
			</form>
			<div id="amazonDescription" class="description hidden" data-localize="{t}%1 件中 %2 - %3 件を表示{/t}">
				{t}見つかりませんでした。{/t}
			</div>
		</div>
		
		<div class="loading hidden">
			<img src="/images/loading.gif" />
		</div>
		
		<form method="POST" action="{$baseurl}/{$controllerName}/insertamazon/id/{if !empty($selected->id)}{$selected->id}{else}{$subjectid}{/if}" name="submitAmazonForm" id="submitAmazonForm" enctype="multipart/form-data">
			<div class="resultWrap hidden" id ="amazonResultWrap">
				<div class="innerTableHead tblAmazon">
					<table class="amazonTbl">
						<thead>
							<tr>
								<th class="w1 th_checkbox"></th>
								<th class="th_image">{t}表紙{/t}</th>
								<th class="th_title">{t}書名{/t}</th>
								<th class="w6 th_author">{t}著者名{/t}</th>
								<th class="w4 th_publish">{t}発行年月{/t}</th>
							</tr>
						</thead>
					</table>
				</div>
			</div>
		</form>
		
		<div id="amazonPageLink">
		</div>
	</div>
	<div class="buttonSet dubble hidden">
		<a onclick="cancel(this);" class="cancel">{t}キャンセル{/t}</a>
		<a onclick="submitAmazon();" class="affirm">{t}追加する{/t}</a>
	</div>
</div>

<div id="apiContentsCompDialog" class="dialog">
	<div class="cmpsub">{t}文献情報を追加しました{/t}</div>
</div>