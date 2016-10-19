function clearCinii()
{
	// 結果テーブル要素
	$('#ciniiResultTbl > *').each(function(){
		$(this).remove();
	});
	
	// ページネーション要素
	$('#ciniiPageLink > *').each(function(){
		$(this).remove();
	});
	
	$('#ciniiDescription').html('');					// 結果数・現在位置
	$('#ciniiResultTbl').addClass('hidden');			// 結果テーブル
	$('#ciniiDialog .buttonSet').addClass('hidden');	// 追加・キャンセルボタン
}
function readyCinii()
{
	clearCinii();
	$('#ciniiDialog .loading').removeClass('hidden');	// ローディング画像
}

function searchCinii()
{
	$('#getCiniiForm').submit();
}

function changeCiniiPage(num)
{
	// アクティブな値ではなく、hidden値で検索するためのフラグ
	$('#cinii_search_flag').prop('value', 1);
	
	$('#cinii_start_num').prop('value', (num - 1) * 50 + 1);
	$('#getCiniiForm').submit();
}

function callGetCinii(response)
{
	// この二つの値は毎回初期化する
	$('#cinii_search_flag').prop('value', 0);
	$('#cinii_start_num').prop('value', 1);
	
	var desc = document.getElementById('ciniiDescription');
	var tbl = document.getElementById('ciniiResultTbl');
	var lnk = document.getElementById('ciniiPageLink');
	
	$('#ciniiDialog .loading').addClass('hidden');
	
	var totalResults = response['channel']['opensearch'] != undefined ? parseInt(response['channel']['opensearch']['totalResults']) : 0;
	
	if(totalResults == 0)
	{
		desc.setAttribute('data-localize', '見つかりませんでした。');
		return true;
	}
	
	$('#ciniiResultTbl').removeClass('hidden');
	$('#ciniiDialog .buttonSet').removeClass('hidden');
	
	var startIndex	= parseInt(response['channel']['opensearch']['startIndex']);
	var startNum	= startIndex;
	var endNum		= startNum + 49 > totalResults ? totalResults : startNum + 49;
	
	// pager
	var m = Math.ceil(totalResults / 10);
	var n = m > 10 ? 10 : m;
	var pager = document.createElement('ul');
	pager.setAttribute('class', 'pager');
	var start = document.createElement('li');
	var a = document.createElement('a');
	a.setAttribute('id', 'page_start');
	a.innerHTML = '≪';
	a.setAttribute('onclick', 'changeCiniiPage(1)');
	start.appendChild(a);
	pager.appendChild(start);
	
	curpage = (Number(startIndex) - 1) / 50 + 1;
	
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
			a.setAttribute('onclick', 'changeCiniiPage(' + i + ')');
			li.appendChild(a);
		}
		pager.appendChild(li);
	}
	
	var end = document.createElement('li');
	var a = document.createElement('a');
	a.setAttribute('id', 'page_end');
	a.innerHTML = '≫';
	a.setAttribute('onclick', 'changeCiniiPage(' + n + ')');
	end.appendChild(a);
	pager.appendChild(end);
	
	lnk.appendChild(pager);
	// pager
	
	//desc.innerHTML = totalResults + ' 件中 ' + startNum + ' - ' + endNum + ' 件を表示';
	desc.setAttribute('data-localize', '%1 件中 %2 - %3 件を表示');
	desc.setAttribute('data-arg1', totalResults);
	desc.setAttribute('data-arg2', startNum);
	desc.setAttribute('data-arg3', endNum);
	
	for(var i in response['rows'])
	{
		var wrap = document.createElement('div');
		if(i % 2 == 0)
			wrap.setAttribute('class', 'wrapCinii odd');
		else
			wrap.setAttribute('class', 'wrapCinii even');
		
		var inner_left = document.createElement('div');
		inner_left.setAttribute('class', 'innerCinii ci-left');
		var chk = document.createElement('input');
		chk.setAttribute('type', 'checkbox');
		chk.setAttribute('name', 'checkbox[' + i + ']');
		chk.setAttribute('value', i);
		chk.setAttribute('class', 'ciniiCheckbox');
		inner_left.appendChild(chk);
		wrap.appendChild(inner_left);
		
		var inner_right = document.createElement('div');
		inner_right.setAttribute('class', 'innerCinii ci-right');
		
		// タイトル
		var link = document.createElement('a');
		link.setAttribute('class', 'ci-link');
		link.setAttribute('target', '_blank');
		var title = document.createElement('div');
		title.setAttribute('class', 'ci-title');
		
		link.appendChild(title);
		inner_right.appendChild(link);
		
		// 著作者
		var creator = document.createElement('div');
		creator.setAttribute('class', 'ci-creator');
		inner_right.appendChild(creator);
		
		// 抄録
		var description = document.createElement('div');
		description.setAttribute('class', 'ci-description');
		inner_right.appendChild(description);
		
		// 収録刊行物
		var prism = document.createElement('div');
		prism.setAttribute('class', 'ci-prism');
		inner_right.appendChild(prism);
		
		// 発行者
		var publisher = document.createElement('div');
		publisher.setAttribute('class', 'ci-publisher');
		inner_right.appendChild(publisher);
		
		wrap.appendChild(inner_right);
		tbl.appendChild(wrap);
		
		for(var content in response['rows'][i])
		{
			switch(content)
			{
				case 'title':
					title.innerHTML = response['rows'][i]['title'][0];
					chk.setAttribute('data-title', response['rows'][i]['title'][0]);
					break;
				case 'link':
					link.setAttribute('href', response['rows'][i]['link'][0]);
					chk.setAttribute('data-url', response['rows'][i]['link'][0]);
					break;
				case 'dc':
					if(response['rows'][i]['dc']['creator'] != undefined)
						creator.innerHTML	= response['rows'][i]['dc']['creator'];
					else
						creator.setAttribute('data-localize', '著者なし');
					
					if(response['rows'][i]['dc']['publisher'] != undefined)
						publisher.innerHTML	= response['rows'][i]['dc']['publisher'];
					else
						publisher.setAttribute('data-localize', '出版者なし');
					break;
				case 'description':
					if(response['rows'][i]['description'][0] != undefined)
						description.innerHTML = response['rows'][i]['description'][0];
					else
						description.setAttribute('data-localize', '抄録なし');
					break;
				case 'prism':
					if(response['rows'][i]['prism']['publicationName'] != undefined)
						prism.innerHTML += response['rows'][i]['prism']['publicationName'];
					
					if(response['rows'][i]['prism']['volume'] != undefined)
						prism.innerHTML += ' ' + response['rows'][i]['prism']['volume'];
					
					if(response['rows'][i]['prism']['number'] != undefined)
						prism.innerHTML += '(' + response['rows'][i]['prism']['number'] + ')';
					
					if(response['rows'][i]['prism']['startingPage'] != undefined)
						prism.innerHTML += ', ' + response['rows'][i]['prism']['startingPage'];
					
					if(response['rows'][i]['prism']['endingPage'] != undefined)
						prism.innerHTML += '-' + response['rows'][i]['prism']['endingPage'];
					
					if(response['rows'][i]['prism']['publicationDate'] != undefined)
						prism.innerHTML += ', ' + response['rows'][i]['prism']['publicationDate'];
					
					break;
				default:
					var typeOf = eval("typeof " + content);		// 上で定義していない要素に対しては処理しない
					if(typeOf != 'undefined')
					{
						var cur = eval(content);
						if(typeof response['rows'][i][content] != 'object')
							cur.innerHTML = response['rows'][i][content];
						else
							cur.innerHTML = response['rows'][i][content][0];
					}
					break;
			}
		}
	}
	tbl.scrollTop = 0;
	setCheckboxEvent('.ciniiCheckbox', 'cinii');
}

function clearAmazon()
{
	// 結果テーブル要素
	$('#amazonResultWrap > *').each(function(){
		$(this).remove();
	});
	
	// ページネーション要素
	$('#amazonPageLink > *').each(function(){
		$(this).remove();
	});
	
	$('#amazonDescription').html('');					// 検索結果数・現在位置
	$('#amazonDescription').addClass('hidden');
	$('#amazonResultWrap').addClass('hidden');			// 結果テーブルラッパー
	$('#amazonPageLink').addClass('hidden');			// ページネーション
	$('#amazonDialog .buttonSet').addClass('hidden');	// 追加・キャンセルボタン
}
function readyAmazon()
{
	clearAmazon();
	$('#amazonDialog .loading').removeClass('hidden');	// ローディング画像
}

function displayAmazon()
{
	$('#amazonDescription').removeClass('hidden');
	$('#amazonDialog .loading').addClass('hidden');
	$('#amazonResultWrap').removeClass('hidden');
	$('#amazonPageLink').removeClass('hidden');
	$('#amazonDialog .buttonSet').removeClass('hidden');
}

function searchAmazon()
{
	$('#getAmazonForm').submit();
}

function changeAmazonPage(num)
{
	// アクティブな値ではなく、hidden値で検索するためのフラグ
	$('#amazon_search_flag').prop('value', 1);
	
	$('#amazon_start_num').prop('value', num);
	$('#getAmazonForm').submit();
}

var imgMax = 0;
var imgCnt = 0;

function countLoaded()
{
	if(++imgCnt == imgMax)
		displayAmazon();
}

function callGetAmazon(response)
{
	// この二つの値は毎回初期化する
	$('#amazon_search_flag').prop('value', 0);
	$('#amazon_start_num').prop('value', 1);
	
	imgMax = 0;
	imgCnt = 0;
	
	var desc = document.getElementById('amazonDescription');
	var wrap = document.getElementById('amazonResultWrap');
	var lnk = document.getElementById('amazonPageLink');
	
	var totalResults = response['channel']['TotalResults'][0] != undefined ? parseInt(response['channel']['TotalResults'][0]) : 0;
	
	if(totalResults == 0)
	{
		desc.setAttribute('data-localize', '見つかりませんでした。');
		$('#amazonDescription').removeClass('hidden');
		$('#amazonDialog .loading').addClass('hidden');
		return true;
	}
	
	
	var startIndex	= parseInt(response['channel']['ItemPage']);
	var startNum	= (startIndex - 1) * 10 + 1;
	var endNum		= startNum + 9 > totalResults ? totalResults : startNum + 9;
	
	// pager---
	var m = Math.ceil(totalResults / 10);
	var n = m > 10 ? 10 : m;
	var pager = document.createElement('ul');
	pager.setAttribute('class', 'pager');
	var start = document.createElement('li');
	var a = document.createElement('a');
	a.setAttribute('id', 'page_start');
	a.innerHTML = '<<';
	a.setAttribute('onclick', 'changeAmazonPage(1)');
	start.appendChild(a);
	pager.appendChild(start);
	
	curpage = Number(startIndex);
	
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
			a.setAttribute('onclick', 'changeAmazonPage(' + i + ')');
			li.appendChild(a);
		}
		pager.appendChild(li);
	}
	
	var end = document.createElement('li');
	var a = document.createElement('a');
	a.setAttribute('id', 'page_end');
	a.innerHTML = '>>';
	a.setAttribute('onclick', 'changeAmazonPage(' + n + ')');
	end.appendChild(a);
	pager.appendChild(end);
	
	lnk.appendChild(pager);
	// ---pager
	
	//desc.innerHTML = totalResults + ' 件中 ' + startNum + ' - ' + endNum + ' 件を表示';
	desc.setAttribute('data-localize', '%1 件中 %2 - %3 件を表示');
	desc.setAttribute('data-arg1', totalResults);
	desc.setAttribute('data-arg2', startNum);
	desc.setAttribute('data-arg3', endNum);
	
	var div_head = document.createElement('div');
	div_head.setAttribute('class', 'innerTableHead tblAmazon');
	
	var table_head = document.createElement('table');
	table_head.setAttribute('class', 'amazonTbl');
	var thead = document.createElement('thead');
	var tr = document.createElement('tr');
	var th1 = document.createElement('th');
	th1.setAttribute('class', 'w1 th_checkbox');
	var th2 = document.createElement('th');
	th2.setAttribute('class', 'th_image');
	//th2.innerHTML = '表紙';
	th2.setAttribute('data-localize', '表紙');
	var th3 = document.createElement('th');
	th3.setAttribute('class', 'th_title');
	//th3.innerHTML = '書名';
	th3.setAttribute('data-localize', '書名');
	var th4 = document.createElement('th');
	th4.setAttribute('class', 'w6 th_author');
	//th4.innerHTML = '著者名';
	th4.setAttribute('data-localize', '著者名');
	var th5 = document.createElement('th');
	th5.setAttribute('class', 'w4 th_publish');
	//th5.innerHTML = '発行年月';
	th5.setAttribute('data-localize', '発行年月');
	tr.appendChild(th1);
	tr.appendChild(th2);
	tr.appendChild(th3);
	tr.appendChild(th4);
	tr.appendChild(th5);
	thead.appendChild(tr);
	table_head.appendChild(thead);
	div_head.appendChild(table_head);
	wrap.appendChild(div_head);
	// headここまで、以下body
	
	var div_body = document.createElement('div');
	div_body.setAttribute('class', 'innerTableBody tblAmazon');
	var table_body = document.createElement('table');
	table_body.setAttribute('class', 'amazonTbl');
	var tbody = document.createElement('tbody');
	
	for(var i in response['rows'])
	{
		var row = document.createElement('tr');
		if(i % 2 == 0)
			row.setAttribute('class', 'rowAmazon odd');
		else
			row.setAttribute('class', 'rowAmazon even');
		
		// チェックボックス
		var td_chk = document.createElement('td');
		td_chk.setAttribute('class', 'w1 am-check');
		
		var chk = document.createElement('input');
		chk.setAttribute('type', 'checkbox');
		chk.setAttribute('name', 'checkbox[' + i + ']');
		chk.setAttribute('value', i);
		chk.setAttribute('class', 'amazonCheckbox');
		td_chk.appendChild(chk);
		row.appendChild(td_chk);
		
		
		chk.setAttribute('data-asin', response['rows'][i]['ASIN'][0]);
		chk.setAttribute('data-url', response['rows'][i]['DetailPageURL']);
		chk.setAttribute('data-title', response['rows'][i]['ItemAttributes']['Title']);
		
		if(response['rows'][i]['ItemAttributes']['Author'] != undefined)
			chk.setAttribute('data-author', response['rows'][i]['ItemAttributes']['Author'])
		else
			chk.setAttribute('data-author', '');
		if(response['rows'][i]['ItemAttributes']['PublicationDate'] != undefined)
			chk.setAttribute('data-publication', response['rows'][i]['ItemAttributes']['PublicationDate'])
		else
			chk.setAttribute('data-publication', '');
		
		
		// 表紙画像
		var td = document.createElement('td');
		td.setAttribute('class', 'am-image');
		
		var img = document.createElement('img');
		if(response['rows'][i]['ImageSets']['ImageSet'] != undefined)
		{
			if(response['rows'][i]['ImageSets']['ImageSet'][0] == undefined)
				var url = response['rows'][i]['ImageSets']['ImageSet']['MediumImage']['URL'];
			else
				var url = response['rows'][i]['ImageSets']['ImageSet'][0]['MediumImage']['URL'];
		}
		if(url != undefined)
		{
			url = url.replace(/http\:\/\/ecx\.images-amazon\.com/, 'https://images-na.ssl-images-amazon.com');
			img.setAttribute('src', url);
			img.setAttribute('onload', 'countLoaded();');
			imgMax++;
			imageLoader(url);
		}
		
		td.appendChild(img);
		row.appendChild(td);
		
		// 書名
		var td = document.createElement('td');
		td.setAttribute('class', 'am-title');
		var a = document.createElement('a');
		a.setAttribute('href', response['rows'][i]['DetailPageURL'])
		a.innerHTML = response['rows'][i]['ItemAttributes']['Title'];
		td.appendChild(a);
		row.appendChild(td);
		
		// 著者名
		var td = document.createElement('td');
		td.setAttribute('class', 'w6 am-author');
		if(response['rows'][i]['ItemAttributes']['Author'] != undefined)
			td.innerHTML = response['rows'][i]['ItemAttributes']['Author'];
		else if(response['rows'][i]['ItemAttributes']['Creator'] != undefined)	// 著者無しで編集者のみの場合 ex.)知のナヴィゲーター
			td.innerHTML = response['rows'][i]['ItemAttributes']['Creator'];
		else
			td.innerHTML = '-';
		row.appendChild(td);
		
		// 発行年月
		var td = document.createElement('td');
		td.setAttribute('class', 'w4 am-publish');
		if(response['rows'][i]['ItemAttributes']['PublicationDate'] != undefined)
			td.innerHTML = response['rows'][i]['ItemAttributes']['PublicationDate'];
		else
			td.innerHTML = '-';
		row.appendChild(td);
		
		
		tbody.appendChild(row);
	}
	table_body.appendChild(tbody);
	div_body.appendChild(table_body);
	wrap.appendChild(div_body);
	
	setCheckboxEvent('.amazonCheckbox', 'amazon');
}

function setCheckboxEvent(target, prefix)
{
	$(target).each(function(){
		$(this).change(function(){
			if($(this).is(':checked'))
			{
				$(this).after('<input type="hidden" name="' + prefix + '_title[' + $(this).val() + ']" value="' + $(this).data('title') + '">'
							+ '<input type="hidden" name="' + prefix + '_url[' + $(this).val() + ']" value="' + $(this).data('url') + '">');
			}
			else
			{
				$(this).siblings().each(function(){
					$(this).remove();
				});
			}
		});
	});
}

function submitAmazon()
{
	$('#submitAmazonForm').submit();
}

function submitCinii()
{
	$('#submitCiniiForm').submit();
}