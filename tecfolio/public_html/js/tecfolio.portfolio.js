var subject_flg	= $('#portfoliojs').data('subject_flg');
var common_flg	= $('#portfoliojs').data('common_flg');
var mentor_flg	= $('#portfoliojs').data('mentor_flg');

function clearContentsTable()
{
	$('#dialogContentsInner > tr').each(function(){
		$(this).remove();
	});
}

function clearAddContentsTable()
{
	$('#dialogAddContentsInner > tr').each(function(){
		$(this).remove();
	});
}


//平均の★出力
//@param	avg		平均値
//@param	max		最大値
function intToStars(avg, max)
{
	var val = parseFloat(parseFloat(100 / max) * parseFloat(avg));
	var str = '';
	
	if(val <= parseFloat(20))
		str = '★';
	else if(val <= parseFloat(40))
		str = '★★';
	else if(val <= parseFloat(60))
		str = '★★★';
	else if(val <= parseFloat(80))
		str = '★★★★';
	else if(val <= parseFloat(100))
		str = '★★★★★';
	
	return str;
}

//平均の★と数値出力
//@param	sum		合計
//@param	num		「評価の観点」の数
//@param	max		評価の最大点
function getStars(sum, num, max, flg)
{
	if(flg == 0)
		var tgt = 'self';
	else
		var tgt = 'mentor';
	
	if(num != 0)
	{
		var avg = sum / num;
		var str = intToStars(avg, max);
		return '<span class="wrap_rate_stars valued"><span class="rate_stars" id="edit_' + tgt + '_rating_inner">' + str + '</span>(' + avg.toFixed(2) + '/' + max.toFixed(2) + ')</span>';
	}
	else
	{
		//return '<span class="wrap_rate_stars" id="edit_self_rating_inner">未評価</a>';
		return '<span class="wrap_rate_stars" id="edit_self_rating_inner" data-localize="未評価"></a>';
	}
}

function createContentsTable(target, array)
{
	for(i in array)
	{
		var tr		= document.createElement('tr');
		
		// チェックボックス
		var td 		= document.createElement('td');
		td.setAttribute('class', 'w1 fixed');
		var input	= document.createElement('input');
		input.setAttribute('type', 'checkbox');
		input.setAttribute('class', 'contentCheck');
		input.setAttribute('name', 'contentcheck[]');
		input.setAttribute('value', array[i]['id']);
		td.appendChild(input);
		tr.appendChild(td);
		
		// No.
		var td 		= document.createElement('td');
		td.setAttribute('class', 'w1 num');
		td.innerHTML = Number(i) + 1;
		tr.appendChild(td);
		
		// ファイルタイプ
		var td 		= document.createElement('td');
		td.setAttribute('class', 'w1 fixed');
		var div		= document.createElement('div');
		div.setAttribute('class', 'cl');
		
		if(array[i]['ref_class'] != undefined)
		{
			var icon	= document.createElement('i');
			
			if(array[i]['ref_class'] == '0')
				icon.setAttribute('class', 'cinii');
			else if(array[i]['ref_class'] == '1')
				icon.setAttribute('class', 'amazon');
			
			div.appendChild(icon);
		}
		else if(array[i]['content_files_type'] != undefined)
		{
			var icon	= document.createElement('i');
			
			switch(array[i]['content_files_type'])
			{
				case 'application/msword':
				case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
				case 'application/vnd.ms-word.document.macroEnabled.12':
					icon.setAttribute('class', 'hasIcon word');
					break;
				case 'application/vnd.ms-excel':
				case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
				case 'application/vnd.ms-excel.sheet.macroEnabled.12':
					icon.setAttribute('class', 'hasIcon excel');
					break;
				case 'application/vnd.ms-powerpoint':
				case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
				case 'application/vnd.ms-powerpoint.presentation.macroEnabled.12':
					icon.setAttribute('class', 'hasIcon powerpoint');
					break;
				case 'application/pdf':
					icon.setAttribute('class', 'hasIcon pdf');
					break;
				case 'text/plain':
					icon.setAttribute('class', 'hasIcon textdoc');
					break;
				default:
					icon.setAttribute('class', 'noIcon');
			}
			
			div.appendChild(icon);
		}
		td.appendChild(div);
		tr.appendChild(td);
		
		// コンテンツ
		var td 		= document.createElement('td');
		td.setAttribute('class', 'dcContents');
		var a		= document.createElement('a');
		if(array[i]['content_files_id'] != undefined)
		{
			a.setAttribute('href', baseUrl + '/downloadcontent/id/' + array[i]['content_files_id']);
			a.innerHTML = array[i]['content_files_name'];
		}
		else if(array[i]['ref_title'] != undefined)
		{
			a.setAttribute('href', array[i]['ref_url']);
			a.innerHTML = array[i]['ref_title'];
		}
		else
		{
			a.setAttribute('href', baseUrl + '/download/id/' + array[i]['id']);
			a.innerHTML = array[i]['name'];
		}
		td.appendChild(a);
		tr.appendChild(td);
		
		target.appendChild(tr);
	}
}

function clearPortfolioTable()
{
	$('#contentsInner > tr').each(function(){
		$(this).remove();
	});
}

function createTable(array)
{
	var target	= document.getElementById('contentsInner');
	
	if(array.length > 0)
	{
		for(i in array)
		{
			var tr		= document.createElement('tr');
			tr.setAttribute('class', 'pfline');
			tr.setAttribute('data-id', array[i]['id']);
			
			if(common_flg == undefined || common_flg == 0)
			{
				// チェックボックス
				var td 		= document.createElement('td');
				td.setAttribute('class', 'w1 fixed check');
				var input	= document.createElement('input');
				input.setAttribute('type', 'checkbox');
				input.setAttribute('class', 'contentCheck');
				input.setAttribute('name', 'removecheck[]');
				input.setAttribute('value', array[i]['id']);
				td.appendChild(input);
				tr.appendChild(td);
			}
			
			// No.
			var td 		= document.createElement('td');
			td.setAttribute('class', 'w1 num');
			td.innerHTML = Number(i) + 1;
			tr.appendChild(td);
			
			// タイトル
			var td 		= document.createElement('td');
			td.setAttribute('class', 'main');
			td.innerHTML = array[i]['title'];
			tr.appendChild(td);
			
//			// 最終更新日時
//			var td 		= document.createElement('td');
//			td.setAttribute('class', 'fixed timestamp');
//			td.innerHTML = dateFormat(array[i]['lastupdate']);
//			tr.appendChild(td);
			
			// ルーブリック
			var td 		= document.createElement('td');
			td.setAttribute('class', 'w8 fixed');
			if(array[i]['rubric_name'] != undefined)
				td.innerHTML = array[i]['rubric_name'];
			tr.appendChild(td);
			
			// 自己評価
			var td 		= document.createElement('td');
			td.setAttribute('class', 'rate fixed');
			td.innerHTML = '<span class="rate_stars">' + intToStars(array[i]['rubric_input_avg'], array[i]['rubric_max']) + '</span>';
			tr.appendChild(td);
			
			// メンター評価
			var td 		= document.createElement('td');
			td.setAttribute('class', 'rate fixed');
			td.innerHTML = '<span class="rate_stars">' + intToStars(array[i]['rubric_mentor_avg'], array[i]['rubric_max']) + '</span>';
			tr.appendChild(td);
			
//			// SC
//			var td 		= document.createElement('td');
//			td.setAttribute('class', 'w1 fixed');
//			tr.appendChild(td);
			
			// 編集
//			var td 		= document.createElement('td');
//			td.setAttribute('class', 'w1 fixed');
//			tr.appendChild(td);
			
			target.appendChild(tr);
		}
	}
}

// 「メンターと相談する」ブロックの描画
function createMentor(array)
{
	$('#chat_title').val('');
	$('#chat_body').val('');
	
	$('#wrapChatMentor > *').each(function()
	{
		$(this).remove();
	});
	
	var mentorText = document.getElementById('mentorText');
	var mentorButton = document.getElementById('searchMentorButton');
	if(array['name_jp'] != undefined)
	{
		$('#chat_mentor_id').prop('value', array['id']);
		
		mentorText.innerHTML = array['syzkcd_c'] + '<br />';
		mentorText.innerHTML += '<span>' + array['name_jp'] + '</span>';
		if(array['agreement_flag'] == 0)
			mentorText.innerHTML += '<br /><span data-localize="(承認待ち)"></span>';
		mentorText.setAttribute('class', 'selectedText');
		mentorText.removeAttribute('data-localize');
		
		// ボタンを非表示にする
		mentorButton.setAttribute('class', 'submit orange hidden');
	}
	else
	{
		mentorText.setAttribute('data-localize', '未選択');
		mentorText.setAttribute('class', 'selectedText notYet');
		
		// ボタンを表示する
		mentorButton.setAttribute('class', 'submit orange');
	}
	
	var mentorImg = document.getElementById('mentorImg');
	if(array['name_jp'] != undefined)
	{
		if(array['input_name'] != undefined)
		{
			mentorImg.setAttribute('src', array['input_name']);
			mentorImg.setAttribute('class', 'selectedImg');
		}
		else
		{
			// 個人設定で画像が設定されていない場合、デフォルトの画像を表示
			mentorImg.removeAttribute('src');
			mentorImg.setAttribute('class', 'selectedImg noImg');
		}
	}
	else
	{
		mentorImg.removeAttribute('src');
		mentorImg.setAttribute('class', 'selectedImg notYet');
	}
}

// メンターと相談するブロック描画
function createChatMentor(array)
{
	var chatMentor = document.getElementById('wrapChatMentor');
	if(array.length > 0)
	{
		var num = 0;
		
		for(var i in array)
		{
			var line = document.createElement('div');
			line.setAttribute('class', 'chatLine');
			
				var left = document.createElement('div');
				left.setAttribute('class', 'chatLeft');
					var leftInner = document.createElement('div');
					leftInner.setAttribute('class', 'chatLeftInner');
					
						var img = document.createElement('img');
						if(array[i]['t_profiles_input_name'] != undefined)
						{
							img.setAttribute('src', array[i]['t_profiles_input_name']);
							img.setAttribute('class', 'selectedImg');
						}
						else
						{
							// 個人設定で画像が設定されていない場合、デフォルトの画像を表示
							img.removeAttribute('src');
							img.setAttribute('class', 'selectedImg noImg');
						}
						leftInner.appendChild(img);
						
						var name_jp = document.createElement('div');
						name_jp.innerHTML = array[i]['m_members_name_jp'];
						if(array[i]['m_member_id'] == array[i]['t_mentors_m_member_id'])
							name_jp.setAttribute('class', 'mentorName');
						else
							name_jp.setAttribute('class', 'studentName');
						
						leftInner.appendChild(name_jp);
						
					left.appendChild(leftInner);
				line.appendChild(left);
				
				var right = document.createElement('div');
				right.setAttribute('class', 'chatRight');
					var rightInner = document.createElement('div');
					rightInner.setAttribute('class', 'chatRightInner');
					
						var time = document.createElement('time');
						time.innerHTML = dateFormat(array[i]['lastupdate']);
						rightInner.appendChild(time);
						
						var title = document.createElement('label');
						title.innerHTML = array[i]['title'];
						rightInner.appendChild(title);
						
						var body = document.createElement('div');
						body.innerHTML = array[i]['body'];
						rightInner.appendChild(body);
						
					right.appendChild(rightInner);
				line.appendChild(right);
			chatMentor.appendChild(line);
			
			if(array.length > ++num)
			{
				var br = document.createElement('br');
				br.setAttribute('class', 'clear');
				chatMentor.appendChild(br);
			}
		}
	}
}

// 選択可能なルーブリックの表示
function createSelectRubric(array)
{
	$('#selectrubric > option').each(function(){
		$(this).remove();
	});
	
	var tgt = document.getElementById('selectrubric');
	
	var opt = document.createElement('option');
	opt.setAttribute('value', '0');
	opt.setAttribute('disabled', 'disabled');
	opt.setAttribute('data-localize', '選択してください');
	tgt.appendChild(opt);
	
	for(var i in array)
	{
		var opt = document.createElement('option');
		opt.setAttribute('value', array[i]['m_rubric_id']);
		opt.innerHTML = array[i]['m_rubric_name'];
		tgt.appendChild(opt);
	}
}

// flg=0	自身のポートフォリオ画面
// flg=1	メンターとして閲覧する画面
function createEditPortfolio(array, flg)
{
	// 20150911(定例日) 自己評価前後で表示を変更するためのフラグ
	// 20150925(定例日) メンター評価前後に変更
	var beforeMentorRate = (flg == 0 && array['portfolio']['mentor_comment'] == undefined);
	
	// タイトル
	var title 		= document.getElementById('edit_title');
	title.innerHTML = array['portfolio']['title'];
	
	if(array['portfolio']['contents_mytheme_id'] != undefined)
	{
		// 選択されているコンテンツが属するMyテーマID
		title.setAttribute('data-id', array['portfolio']['contents_mytheme_id']);
	}
	else if(array['contents'].length > 0 && array['contents'][0]['files_id'] != undefined)
	{
		title.setAttribute('data-id', 'LABO_' + array['contents'][0]['creator']);
	}
	else
	{
		title.setAttribute('data-id', null);
	}
	
	$('#icon_edit_title, #wrap_edit_title').off('click');
	$('#icon_edit_title').css('display', 'none');
	if(beforeMentorRate)
	{
		$('#icon_edit_title').css('display', 'inline-block');
		
		$('#icon_edit_title, #wrap_edit_title').on('click', function() {
			$('#updateTitleDialog').bPopup();
		});
		
		// タイトル変更ダイアログ内Input
		var update_title = document.getElementById('update_title');
		update_title.setAttribute('value', array['portfolio']['title']);
	}
	
	// コンテンツ
	$("#contentsButton").off('click');
	$("#contentsButton").css('display', 'none');
	$('#edit_contents > *').each(function(){
		$(this).remove();
	});
	var contents 	= document.getElementById('edit_contents');
	for(var num in array['contents'])
	{
		if(array['contents'][num]['content_files_id'] != undefined)
		{
			// 自身のポートフォリオかつ自己評価入力前
			if(beforeMentorRate)
			{
				var i = document.createElement('i');
				i.setAttribute('class', 'delete delete_contents');
				i.setAttribute('data-id', array['contents'][num]['pfc_id']);
				i.setAttribute('data-name', array['contents'][num]['content_files_name']);
				contents.appendChild(i);
			}
			
			var a = document.createElement('a');
			a.setAttribute('href', baseUrl + '/downloadcontent/id/' + array['contents'][num]['content_files_id']);
			if(array['contents'][num]['subjects_class_subject'] != undefined)
			{
				a.setAttribute('title', '参照元：' + array['contents'][num]['subjects_yogen'] + '　' + array['contents'][num]['subjects_class_subject']);
				a.setAttribute('data-localize', '参照元：%1');
				a.setAttribute('data-arg1', array['contents'][num]['subjects_yogen'] + '　' + array['contents'][num]['subjects_class_subject']);
			}
			else
			{
				a.setAttribute('title', '参照元：' + array['contents'][num]['mythemes_name']);
				a.setAttribute('data-localize', '参照元：%1');
				a.setAttribute('data-arg1', array['contents'][num]['mythemes_name']);
			}
			a.innerHTML = array['contents'][num]['content_files_name'];
			contents.appendChild(a);
		}
		else if(array['contents'][num]['ref_title'] != undefined)
		{
			if(beforeMentorRate)
			{
				var i = document.createElement('i');
				i.setAttribute('class', 'delete delete_contents');
				i.setAttribute('data-id', array['contents'][num]['pfc_id']);
				i.setAttribute('data-name', array['contents'][num]['ref_title']);
				contents.appendChild(i);
			}
			
			var a = document.createElement('a');
			a.setAttribute('href', array['contents'][num]['ref_url']);
			if(array['contents'][num]['subjects_class_subject'] != undefined)
			{
				a.setAttribute('title', '参照元：' + array['contents'][num]['subjects_yogen'] + '　' + array['contents'][num]['subjects_class_subject']);
				a.setAttribute('data-localize', '参照元：%1');
				a.setAttribute('data-arg1', array['contents'][num]['subjects_yogen'] + '　' + array['contents'][num]['subjects_class_subject']);
			}
			else
			{
				a.setAttribute('title', '参照元：' + array['contents'][num]['mythemes_name']);
				a.setAttribute('data-localize', '参照元：%1');
				a.setAttribute('data-arg1', array['contents'][num]['mythemes_name']);
			}
			a.innerHTML = array['contents'][num]['ref_title'];
			contents.appendChild(a);
		}
		else
		{
			if(beforeMentorRate)
			{
				var i = document.createElement('i');
				i.setAttribute('class', 'delete delete_contents');
				i.setAttribute('data-id', array['contents'][num]['pfc_id']);
				i.setAttribute('data-name', array['contents'][num]['files_name']);
				contents.appendChild(i);
			}
			
			var a = document.createElement('a');
			a.setAttribute('href', baseUrl + '/download/id/' + array['contents'][num]['files_id']);
			a.setAttribute('title', '参照元：ライティングラボ');
			a.setAttribute('data-localize', '参照元：%1');
			a.setAttribute('data-arg1', 'ライティングラボ');
			a.innerHTML = array['contents'][num]['files_name'];
			contents.appendChild(a);
		}
	}
	
	// 自身のポートフォリオかつ自己評価入力前
	if(beforeMentorRate)
	{
		// コンテンツ解除ダイアログ
		$('#edit_contents > i').each(function(){
			$(this).click(function(){
				$('#pfc_id').val($(this).data('id'));
				$('#pfc_name').html($(this).data('name'));
				$('#deletePFCDialog').bPopup();
			});
		});
		
		// コンテンツ追加ボタン
		$("#contentsButton").on('click', function() {
			$('#addContentsToPortfolioDialog').bPopup();
			
			// 追加済みのコンテンツが存在する場合
			if(array['portfolio']['contents_mytheme_id'] != undefined || (array['contents'].length > 0 && array['contents'][0]['files_id'] != undefined))
			{
				// 対応するMyテーマIDのクリックイベント発火
				// ※.dataで取得するとキャッシュ値を取得するため回避
				$('#dm_add_' + $('#edit_title').attr('data-id')).trigger('click');
			}
			else
			{
				$('#dmMythemeAdd > .dmMytheme').removeClass('hidden');
				$('#dmMythemeAdd > .dmFacility').removeClass('hidden');
				
				$('.dmAddList').each(function() {
					$(this).removeClass('active');
					$(this).removeClass('hidden');
				});
				
				$('#dialogAddContentsInner > *').each(function(){
					$(this).remove();
				});
			}
		});
		
		$("#contentsButton").css('display', 'block');
	}
	
	// jQueryUIでツールチップの実装、かつ改行タグを反映させる
	$('#edit_contents').tooltip({
		position: {
			my: "left bottom",
			at: "center top-25%"
		},
		tooltipClass: "contents-tooltip",
		content: function() {
			return $(this).attr('title');
		}
	});
	
	// ルーブリック
	// 先に二次元表を展開し、各値取得の準備をする
	// 20151215 メンター評価後は各セルを選択不可とする
	if(flg == 0)
		callGetRubric(array, beforeMentorRate ? undefined : true);
	else
		callGetRubric(array, undefined);
	
	$("#rubricButton").off('click');
	$("#rubricButton").css('display', 'none');
	
	var rubric 		= document.getElementById('edit_rubric');
	if(array['portfolio']['rubric_name'] != undefined)
	{
		rubric.innerHTML = '';
		if(beforeMentorRate)
		{
			var i = document.createElement('i');
			i.setAttribute('class', 'delete');
			rubric.appendChild(i);
		}
		
		rubric.innerHTML += array['portfolio']['rubric_name'];
		
		$(rubric).removeAttr('data-localize');
		$(rubric).removeClass('noRubric');
	}
	else
	{
		rubric.setAttribute('data-localize', '選択してください');
		$(rubric).addClass('noRubric');
	}
	
	if(beforeMentorRate)
	{
		// ルーブリック選択ボタン押下イベント
		$('#rubricButton').click(function() {
			$('#selectRubricDialog').bPopup({
					position	: ['auto',0],
					follow		: [true,false]
			});
			$('html, body').animate({scrollTop:0}, 600);
		});
		
		$("#rubricButton").css('display', 'block');
	}
	
	// コンテンツ解除ボタン押下イベント
	$('#edit_rubric > i').each(function(){
		$(this).click(function(){
			$('#deleteSelectedRubricDialog').bPopup();
		});
	});
	
	
	$('#edit_rate_stars').off('mouseenter mouseleave');
	$('#edit_rate_stars').on({
		'mouseenter':function(){
			$('#edit_self_rating').addClass('hover');
		},
		'mouseleave':function(){
			$('#edit_self_rating').removeClass('hover');
		}
	});
	
	// マウスオーバー時のcssに関連するクラス
	$('#edit_self_rating').removeClass('active');
	// 鉛筆アイコン
	$('#edit_rate_stars').css('display', 'none');
	// 自己評価ボタン押下時のイベント設定解除
	$('#edit_rate_stars, #edit_self_rating').off('click');
	
	// ダイアログのクラス除去(各セルマウスオーバーの判定)
	$('#ratingDialog').removeClass('active');
	// テーブル赤枠除去
	$('#rubricMatrix').removeClass('editable');
	// コメント欄除去
	$('#ratingDialog .dialogInner').addClass('hidden');
	// ボタン調整
	$('#ratingDialog .ratingAfter').removeClass('hidden');
	$('#ratingDialog .ratingBefore').addClass('hidden');
	
	// 自己評価
	if(array['portfolio']['m_rubric_id'] != undefined)
	{
		if(beforeMentorRate)
		{
			// 鉛筆アイコン表示
			$('#edit_rate_stars').css('display', 'inline-block');
			
			// ダイアログのクラス追加
			$('#ratingDialog').addClass('active');
			// テーブル赤枠付与
			$('#rubricMatrix').addClass('editable');
			// コメント欄表示
			$('#ratingDialog .dialogInner').removeClass('hidden');
			// ボタン調整
			$('#ratingDialog .ratingAfter').addClass('hidden');
			$('#ratingDialog .ratingBefore').removeClass('hidden');
		}
		
		// 自己評価ボタン押下時のイベント設定
		$('#edit_rate_stars, #edit_self_rating').on('click', function() {
			$('#ratingDialog').bPopup({
				position	: ['auto',0],
				follow		: [true,false]
			});
			$('html, body').animate({scrollTop:0}, 600);
		});
		// hover時のcssに関連するクラス
		$('#edit_self_rating').addClass('active');
	}
	
	var rate 	= document.getElementById('edit_self_rating');
	var sum		= 0;
	var cnt		= 0;
	
	if(array['selfrating'].length > 0)
	{
		for(var i in array['selfrating'])
		{
			var rank		= Number(array['selfrating'][i]['rank']);
			sum += rank;
			cnt++;
			
			// 既存の入力値を画面に反映
			$('#rubricMatrix .row' + (Number(i)+1) + '.rank' + rank).addClass('active');
			
			if(beforeMentorRate)
			{
				$("#input_rating" + (Number(i)+1)).val(rank);
			}
		}
	}
	else
	{
		// 表の背景色をリセット
		$('#rubricMatrix .rating').each(function(){
			$(this).removeClass('active');
		});
		
		// inputの値をリセット
		$('#ratingDialog .input_rating').each(function(){
			$(this).val('0');
		});
	}
	
	var max = $('#rubric_max_vals').data('rank');
	var str = getStars(sum, cnt, max, 0);
	rate.innerHTML = str;
	
	$('#ratingDialog .rubric_avg').html(str);
	
	
	$('#mentor_rate_stars').off('mouseenter mouseleave');
	$('#mentor_rate_stars').on({
		'mouseenter':function(){
			$('#edit_mentor_rating').addClass('hover');
		},
		'mouseleave':function(){
			$('#edit_mentor_rating').removeClass('hover');
		}
	});
	
	// マウスオーバー時のCSSに関連するクラス
	$('#edit_mentor_rating').removeClass('active');
	// 鉛筆アイコン
	$('#mentor_rate_stars').css('display', 'none');
	// メンター評価ボタン押下時のイベント設定解除
	$('#mentor_rate_stars, #edit_mentor_rating').off('click');
	
	// メンター評価
	if(array['portfolio']['m_rubric_id'] != undefined)
	{
		if(flg == 1)
		{
			$('#mentor_rate_stars').css('display', 'inline-block');
		}
		
		// メンター評価ボタン押下時のイベント設定
		$('#mentor_rate_stars, #edit_mentor_rating').on('click', function() {
			$('#ratingMentorDialog').bPopup({
				position	: ['auto',0],
				follow		: [true,false]
			});
			$('html, body').animate({scrollTop:0}, 600);
		});
		// hover時のcssに関連するクラス
		$('#edit_mentor_rating').addClass('active');
	}
	
	var rate 	= document.getElementById('edit_mentor_rating');
	var sum		= 0;
	var cnt		= 0;
	
	if(array['mentorrating'].length > 0)
	{
		for(var i in array['mentorrating'])
		{
			var rank		= Number(array['mentorrating'][i]['rank']);
			sum += rank;
			cnt++;
			
			// 既存の入力値を画面に反映
			$('#rubricMatrixMentor .row' + (Number(i)+1) + '.rank' + rank).addClass('active');
			
			if(flg == 1)
			{
				$("#input_rating" + (Number(i)+1)).val(rank);
			}
		}
	}
	else
	{
		// 表の背景色をリセット
		$('#rubricMatrixMentor .rating').each(function(){
			$(this).removeClass('active');
		});
		
		// inputの値をリセット
		$('#ratingMentorDialog .input_rating').each(function(){
			$(this).val('0');
		});
	}
	
	var str = getStars(sum, cnt, max, 1);
	rate.innerHTML = str;
	
	$('#ratingMentorDialog .rubric_avg').html(str);
	
	// 自己評価コメント:
	var self 		= document.getElementById('edit_self_comment');
	var self_input 	= document.getElementById('input_self_comment');
	if(array['portfolio']['self_comment'] != undefined)
	{
		self.innerHTML = array['portfolio']['self_comment'];
		
		if(self_input != undefined)
			self_input.value = array['portfolio']['self_comment'];
	}
	else
	{
		self.setAttribute('data-localize', '未記入');
		
		if(self_input != undefined)
			self_input.value = '';
	}
	
	// メンターコメント
	var mentor			= document.getElementById('edit_mentor_comment');
	var mentor_input 	= document.getElementById('input_mentor_comment');
	if(array['portfolio']['mentor_comment'] != undefined)
	{
		mentor.innerHTML = array['portfolio']['mentor_comment'];
		
		if(mentor_input != undefined)
			mentor_input.value = array['portfolio']['mentor_comment'];
	}
	else
	{
		mentor.setAttribute('data-localize', '未記入');
		
		if(mentor_input != undefined)
			mentor_input.value = '';
	}
	
	// ショーケースチェックボックス
	$('#showcase_flag').prop('checked', array['portfolio']['showcase_flag'] == 1);
}

function drawPortfolio(id)
{
	if(subject_flg == undefined || subject_flg == 0)
	{
		if(mentor_flg == undefined || mentor_flg == 0)
			var tmp = baseUrl + '/getportfolio/id/' + id;
		else
			var tmp = baseUrl + '/getportfolio/id/' + id + '/mentor/1';
		
		ajaxSubmitUrl(tmp, function(response){
			proceedDrawPortfio(response);
		}, undefined, true);
	}
	else
	{
		$('#memberSelect').trigger('change');
	}
}

function proceedDrawPortfio(response)
{
	clearPortfolioTable();
	
	// ポートフォリオ一覧テーブルの作成
	createTable(response['portfolio']);
	
	if(response['portfolio'].length > 0)
	{
		setTableSorter($('#contentsTbl'));
	}
	else
	{
		$('#pager .pagedisplay').attr('data-localize', 'データなし');
	}
	
	// ポートフォリオ一覧テーブルから各行クリック時に詳細を表示するイベント
	$('.pfline > td:not(.check)').each(function(){
		$(this).click(function(){
			submitGetPortfolioDetail($(this).parent().data('id'));
		});
	});
	
	// メンターと相談するブロックの生成
	createMentor(response['mentors']);
	createChatMentor(response['chat_log']);
	
	// 選択可能なルーブリックを表示
	createSelectRubric(response['rubrics']);
}

// ポートフォリオ追加時
function submitGetContents()
{
	$('#getContentsForm').submit();
}
// コンテンツ追加時
function submitGetAvailableContents()
{
	$('#getAvailableContentsForm').submit();
}

// ポートフォリオ追加時
function callGetContents(response)
{
	clearContentsTable();
	var contents	= document.getElementById('dialogContentsInner');
	
	createContentsTable(contents, response['contents']);
}
// コンテンツ追加時
function callGetAvailableContents(response)
{
	clearAddContentsTable();
	var contents	= document.getElementById('dialogAddContentsInner');
	
	createContentsTable(contents, response['contents']);
}

$(function(){
	$('#getContentsForm').submit(function(event) {
		ajaxSubmit(this, event, callGetContents);
	});
	
	$('#getAvailableContentsForm').submit(function(event) {
		ajaxSubmit(this, event, callGetAvailableContents);
	});
});