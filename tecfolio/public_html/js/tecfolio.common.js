var baseUrl			= $('#commonjs').data('url');
var actionName		= $('#commonjs').data('action');
var selectedId		= $('#commonjs').data('selected');

// Myテーマ挿入
function submitMytheme()
{
	$('#mythemeForm').submit();
}

// Myテーマ編集
function submitMythemeEdit()
{
	$('#mythemeEditForm').submit();
}

// Myテーマ削除
function submitMythemeDelete()
{
	$('#mythemeDeleteForm').submit();
}

// 引数responseはparseしたJSONデータ
function callMytheme(response)
{
	// 完了ダイアログ
	$("#mythemeCompDialog").bPopup();
	$("#mythemeDialog").bPopup().close();
}

function callMythemeUpdate(response)
{
	// 完了ダイアログ
	$("#mythemeEditCompDialog").bPopup();
	$("#mythemeEditDialog").bPopup().close();

	setTimeout(function(){
		refreshPage(response['id']);
	},2000);
}

function callMythemeDelete(response)
{
	// 完了ダイアログ
	$("#mythemeDeleteCompDialog").bPopup();
	$("#mythemeDeleteDialog").bPopup().close();

	setTimeout(function(){
		refreshPage(response['id']);
	},2000);
}


// メニュー右端クリックイベント
function setMenu(obj)
{
	var item	= obj.next().children();
	obj.off('click');
	obj.on('click', function(e){
		e.stopPropagation();
		// 他のメニューは閉じる
		$('.miniMenu').each(function(){
			$(this).removeClass('active');
			$(this).next().children().fadeOut(300);
		});
		$(this).addClass('active');
		item.animate({opacity: "toggle",height: "toggle"},0);
	});
	// メニュー外要素クリック時イベント
	item.skOuterClick(function(){
		obj.removeClass('active');
		item.fadeOut(300);
	});
}

// メニュー右端クリック→出現メニュークリックイベント
function setMenuEvent()
{
	// Myテーマ編集
	$('.mythemeEdit').off('click');
	$('.mythemeEdit').on('click', function() {
		// 押下した項目のID/名称挿入(兼input初期化)
		$('#edittheme').val($(this).children('.hasMenu').data('name'));
		$('#mytheme_ed_id').val($(this).children('.hasMenu').data('id'));

		$('#mythemeEditDialog').bPopup();

		closeAllMenu();
	});

	// Myテーマ削除
	$('.mythemeDelete').off('click');
	$('.mythemeDelete').on('click', function() {
		// 押下した項目のID/名称挿入
		$('#mytheme_delete_id').val($(this).children('.hasMenu').data('id'));

		var name = $(this).children('.hasMenu').data('name');
		$('#mytheme_delete_name').children('.hasMenu').val(name);
		$('#mytheme_delete_name_disp').html(name);	// 削除ダイアログ表示名

		$('#mythemeDeleteDialog').bPopup();

		closeAllMenu();
	});

	// Myテーマ順序を上と入れ替え
	$('.mythemeUp').off('click');
	$('.mythemeUp').on('click', function() {
		ajaxSubmitUrl(baseUrl + '/switchmytheme/id/' + $(this).children('.hasMenu').data('id') + '/direction/1', function(){});
		drawMytheme();
		closeAllMenu();
	});

	// Myテーマ順序を下と入れ替え
	$('.mythemeDown').off('click');
	$('.mythemeDown').on('click', function() {
		ajaxSubmitUrl(baseUrl + '/switchmytheme/id/' + $(this).children('.hasMenu').data('id') + '/direction/0', function(){});
		drawMytheme();
		closeAllMenu();
	});

	// Myテーマを利用しないに格納
	$('.mythemeDisable').off('click');
	$('.mythemeDisable').on('click', function() {
		ajaxSubmitUrl(baseUrl + '/disablemytheme/id/' + $(this).children('.hasMenu').data('id'), function(){});
		drawMytheme();
		closeAllMenu();
		refreshPage($(this).children('.hasMenu').data('id'));
	});

	// Myテーマを利用しないから取り出し
	$('.mythemeEnable').off('click');
	$('.mythemeEnable').on('click', function() {
		ajaxSubmitUrl(baseUrl + '/enablemytheme/id/' + $(this).children('.hasMenu').data('id'), function(){});
		drawMytheme();
		closeAllMenu();
		refreshPage($(this).children('.hasMenu').data('id'));
	});
	
	// 各行右端のメニューイベント設定
	$('#menu > li#mytheme > div > a').each(function(){
		$(this).off('mouseenter mouseleave');
		$(this).on({
			'mouseenter':function(){
				$(this).siblings('.targetdrop').addClass('hover');
			},
			'mouseleave':function(){
				$(this).siblings('.targetdrop').removeClass('hover');
			}
		});
	});
}

function refreshPage(id)
{
	if(selectedId == id)
	{
		window.location = baseUrl + '/' + actionName + '/id/' + selectedId;
	}
}

function closeAllMenu()
{
	$('.miniMenu').each(function(){
		$(this).removeClass('active');
		$(this).next().children().fadeOut(300);
	});
}

// ダイアログを閉じる
function cancel(obj)
{
	var tgt = $(obj);
	for(var i = 0; i < 5; i++)		// 5階層以内
	{
		if(tgt.parent('.dialog').get(0))
		{
			tgt.parent('.dialog').bPopup().close();
			closeAllMenu();
			return true;
		}
		else
		{
			tgt = tgt.parent();
		}
	}
}

// 開いている全てのダイアログを閉じる
function closeAllDialog()
{
	$('.dialog').each(function()
	{
		if($(this).prev().hasClass('b-modal'))
			$(this).bPopup().close();
	});
}

function callGetMytheme(response)
{
	$('#mytheme > .wrapTheme').each(function(){
		$(this).remove();
	});

	var mytheme		= response['mytheme'];
	var disabled	= response['disabled'];

	var target = document.getElementById('mytheme');

	var cnt = 0;

	for(var n in mytheme)
	{
		var myId	= mytheme[n]['id'];
		var myName	= mytheme[n]['name'];

		var outer = document.createElement('div');
		outer.setAttribute('class','wrapTheme');

		// クリック時リンク
		var a = document.createElement('a');
		if(myId === selectedId)
			a.setAttribute('class', 'active');
		a.setAttribute('href', baseUrl + '/' + actionName + '/id/' + myId);
		a.setAttribute('id', myId);
		a.innerHTML = myName;
		outer.appendChild(a);

		// メニュークリック可能部分
		var m = document.createElement('div');
		m.setAttribute('class', 'targetdrop miniMenu');
		outer.appendChild(m);

		// メニュー中身
		var wrap = document.createElement('div');
		wrap.setAttribute('class', 'wrapdrop');

			var ul = document.createElement('ul');
			ul.setAttribute('class', 'droplist');

				// 上へ
				var li = document.createElement('li');
				if(cnt == 0)
					li.setAttribute('class', 'mythemeUp hidden');
				else
					li.setAttribute('class', 'mythemeUp');

				var i = document.createElement('i');
				i.setAttribute('class', 'up');
				li.appendChild(i);

				var a = document.createElement('a');
				a.setAttribute('class', 'up hasMenu');
				a.setAttribute('data-id', myId);
				a.setAttribute('data-name', myName);
				a.setAttribute('data-localize', '上へ');
				//a.innerHTML = '上へ';
				li.appendChild(a);
				ul.appendChild(li);

				// 下へ
				var li = document.createElement('li');
				if(cnt == mytheme.length - 1)
					li.setAttribute('class', 'mythemeDown hidden');
				else
					li.setAttribute('class', 'mythemeDown');

				var i = document.createElement('i');
				i.setAttribute('class', 'down');
				li.appendChild(i);

				var a = document.createElement('a');
				a.setAttribute('class', 'down hasMenu');
				a.setAttribute('data-id', myId);
				a.setAttribute('data-name', myName);
				a.setAttribute('data-localize', '下へ');
				//a.innerHTML = '下へ';
				li.appendChild(a);
				ul.appendChild(li);

				// 名称編集
				var li = document.createElement('li');
				li.setAttribute('class', 'mythemeEdit');

				var i = document.createElement('i');
				i.setAttribute('class', 'edit');
				li.appendChild(i);

				var a = document.createElement('a');
				a.setAttribute('class', 'edit hasMenu');
				a.setAttribute('data-id', myId);
				a.setAttribute('data-name', myName);
				a.setAttribute('data-localize', '名称編集');
				//a.innerHTML = '名称編集';
				li.appendChild(a);
				ul.appendChild(li);

				// 利用しない
				var li = document.createElement('li');
				li.setAttribute('class', 'mythemeDisable');

				var i = document.createElement('i');
				i.setAttribute('class', 'etc');
				li.appendChild(i);

				var a = document.createElement('a');
				a.setAttribute('class', 'etc hasMenu');
				a.setAttribute('data-id', myId);
				a.setAttribute('data-name', myName);
				a.setAttribute('data-localize', '利用しない');
				//a.innerHTML = '利用しない';
				li.appendChild(a);
				ul.appendChild(li);

				// テーマ削除
				var li = document.createElement('li');
				li.setAttribute('class', 'mythemeDelete');

				var i = document.createElement('i');
				i.setAttribute('class', 'delete');
				li.appendChild(i);

				var a = document.createElement('a');
				a.setAttribute('class', 'delete hasMenu');
				a.setAttribute('data-id', myId);
				a.setAttribute('data-name', myName);
				a.setAttribute('data-localize', 'テーマ削除');
				//a.innerHTML = 'テーマ削除';
				li.appendChild(a);
				ul.appendChild(li);

			wrap.appendChild(ul);
		outer.appendChild(wrap);

		target.appendChild(outer);

		cnt++;
	}

	if(disabled.length > 0)
	{
		var outer = document.createElement('div');
		outer.setAttribute('class','wrapTheme');

		// クリック時リンク
		var i = document.createElement('i');
		i.setAttribute('class', 'up');
		outer.appendChild(i);

		var a = document.createElement('a');
		a.setAttribute('class', 'disabled');
		//a.innerHTML = '&lt;利用しないテーマ&gt;';
		a.setAttribute('data-localize', '<利用しないテーマ>');
		outer.appendChild(a);

		// メニュークリック可能部分
		var m = document.createElement('div');
		m.setAttribute('class', 'targetdrop miniMenu');
		outer.appendChild(m);

		// メニュー中身
		var wrap = document.createElement('div');
		wrap.setAttribute('class', 'wrapdrop');

			var ul = document.createElement('ul');
			ul.setAttribute('class', 'droplist');

			for(var n in disabled)
			{ 
				var li = document.createElement('li');
				li.setAttribute('class', 'mythemeEnable');

				var i = document.createElement('i');
				i.setAttribute('class', 'up');
				li.appendChild(i);

				var a = document.createElement('a');
				a.setAttribute('class', 'up hasMenu');
				a.setAttribute('data-id', disabled[n]['id']);
				a.setAttribute('data-name', disabled[n]['name']);
				a.innerHTML = disabled[n]['name'];
				li.appendChild(a);
				ul.appendChild(li);
			}

			wrap.appendChild(ul);
		outer.appendChild(wrap);

		target.appendChild(outer);
	}

	// Myテーマの編集後に、必要な処理がある場合(主にメインペインのMyテーマ引用)、
	// 各ページに下の関数を定義する
	if(typeof drawThemeList == "function")
		drawThemeList(response);
}

function drawMytheme()
{
	ajaxSubmitUrl(baseUrl + '/getmytheme/', callGetMytheme, undefined, true);

	$('#mytheme').find('.miniMenu').each(function(){
		setMenu($(this));
	});
	setMenuEvent();
}

function setTableSorter(targetObj)
{
	if(targetObj[0].config == undefined)
	{
		var obj = targetObj.tablesorter().tablesorterPager({
			container: $("#pager"),
			size: $("#pager").find('.pagesize').val()
		});
		
		// 事前定義の関数 bindTableSorter が存在する場合、任意のイベント発生時における処理を行う(obj.bind(...))
		if(typeof bindTableSorter == "function")
			bindTableSorter(obj);
	}
	else
	{
		targetObj.trigger("update");
		var sorting = targetObj[0].config.sortList;
		setTimeout(function () {
			targetObj.trigger("sorton", [sorting]);
		}, 100);
	}
}

function callUpdateMentor()
{
	var val = $('#mentor_flag').prop('value');
	if(val == 1)
		$('#acceptedDialog').bPopup();
	else
		$('#rejectedDialog').bPopup();

	$(this).delay(1000).queue(function()
	{
		//window.location.href=link;
		window.location.reload();
		$(this).dequeue();
	});
}

$(function(){
	// jQueryUIでツールチップの実装、かつ改行タグを反映させる
	$('#menu, .icons').tooltip({
		position: {
			my: "left bottom",
			at: "center top-25%"
		},
		tooltipClass: "custom-tooltip",
		content: function() {
			return $(this).attr('title');
		}
	});

	setMenuEvent();

	// ログアウトメニュー(旧機能のため、下記とは別処理)
	$('#loginStatusTrigger').miniMenu($('#loginStatus'));

	// その他メニュー
	$('.miniMenu').each(function(){
		setMenu($(this));
	});

	// 教員Info
	$('#info_mark').click(function(){
		$('#infoDialog').bPopup();
	});
	// 承諾・拒否ボタン処理
	$('.accept').each(function(){
		$(this).click(function(){
			$('#mentor_id').prop('value', $(this).data('id'));
			$('#mentor_flag').prop('value', '1');
			$('#updateMentorForm').submit();
		});
	});
	$('.reject').each(function(){
		$(this).click(function(){
			$('#mentor_id').prop('value', $(this).data('id'));
			$('#mentor_flag').prop('value', '2');
			$('#updateMentorForm').submit();
		});
	});
	// 承諾・拒否フォーム処理
	$('#updateMentorForm').submit(function(event) {
		ajaxSubmit(this, event, callUpdateMentor);
	});

	// メインペインアイコンの子メニュークリック時
	$('.mainMenu').each(function(){
		$(this).click(function(){
			closeAllMenu();
		});
	});

	$('.menuDummyRight').each(function(){
		var t = $(this).siblings('.title');
		$(this).hover(
			function(){
				t.addClass('on-the-right');
			},
			function(){
				t.removeClass('on-the-right');
			}
		);
	});

	// Myテーマ挿入イベント
	$('#tMytheme').siblings('.menuDummyRight').off('click');
	$('#tMytheme').siblings('.menuDummyRight').on('click', function() {
		// input初期化
		$('#newtheme').val();

		$('#mythemeDialog').bPopup();
	});

	// Myテーマクリックイベント
	$('#tMytheme, .menuBar').off('click');
	$('#tMytheme, .menuBar').on('click', function() {
		$(this).siblings('.wrapTheme').each(function() {
			var minus = $(this).siblings('.menuBar.minus');
			var plus = $(this).siblings('.menuBar.plus');

			if(!$(this).hasClass('hidden'))
			{
				$(this).addClass('hidden');
				$(this).siblings('.selectNendo').addClass('hidden');
				minus.addClass('hidden');
				plus.removeClass('hidden');
			}
			else
			{
				$(this).removeClass('hidden');
				$(this).siblings('.selectNendo').removeClass('hidden');
				plus.addClass('hidden');
				minus.removeClass('hidden');
			}
		});
	});

	$('#mythemeForm').submit(function(event) {
		closeAllMenu();
		ajaxSubmit(this, event, callMytheme);
		drawMytheme();
	});

	$('#mythemeEditForm').submit(function(event) {
		closeAllMenu();
		ajaxSubmit(this, event, callMythemeUpdate);
		drawMytheme();
	});

	$('#mythemeDeleteForm').submit(function(event) {
		closeAllMenu();
		ajaxSubmit(this, event, callMythemeDelete);
		drawMytheme();
	});

	// ソート機能
	$('.hasOrder').each(function(){
		var up		= 'headerSortUp';
		var down	= 'headerSortDown';

		// 見た目状のヘッダ(偽)にクリックイベントを設定する
		$(this).on('click', function(){
			var obj	= $(this);
			var id	= $(this).attr('data-id');
			var tgt = $('#' + id);

			// 本物のヘッダー(真)のクリックイベントを発火する
			tgt.triggerHandler('click');

			// 上記クリックイベントの実行を一瞬待つ
			setTimeout(function(){
				// 全ての(偽)のソート矢印をデフォルトに戻す
				$('.hasOrder').each(function(){
					$(this).removeClass(up);
					$(this).removeClass(down);
				});

				// クリックされた(偽)のソート矢印を、(真)に従って設定する
				if(tgt.hasClass(up))
				{
					obj.addClass(up);
				}
				else if(tgt.hasClass(down))
				{
					obj.addClass(down);
				}
			},10);
		});
	});

	$('#selectYear, #selectTerm').change(function()
	{
		$('#getSubjectForm').submit();
	});

	$('#getSubjectForm').submit(function(event)
	{
		ajaxSubmit(this, event, function(response)
		{
			$('#subject > .wrapTheme').each(function()
			{
				$(this).remove();
			});

			var subject	= response['subject'];

			var target = document.getElementById('subject');

			var cnt = 0;

			var outer = document.createElement('div');
			outer.setAttribute('class','wrapTheme');

			for(var n in subject)
			{
				var myId	= subject[n]['id'];
				var myHead	= subject[n]['yogen'];
				var myName	= subject[n]['class_subject'];

				// クリック時リンク
				var a = document.createElement('a');
				if(myId === selectedId)
					a.setAttribute('class', 'active');
				a.setAttribute('href', baseUrl + '/' + actionName + '/id/' + myId);
				a.setAttribute('id', myId);
				a.innerHTML = '<span class="head">' + myHead + '　</span>' + myName;
				outer.appendChild(a);
			}

			target.appendChild(outer);
		});
	});
	
	$("#contactAdmin").click(function(e){
		e.preventDefault();
		$("#contactDialog").bPopup({
			closeClass:"cancel",
			modalColor:"#ffffff",
			opacity: 0.5,
			transitionClose: "fadeIn",
			zIndex: 120
		});
	});
});
