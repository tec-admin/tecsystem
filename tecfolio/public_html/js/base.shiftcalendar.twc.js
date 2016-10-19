// シフトカレンダーを作成
function createShiftCalendar(baseurl, termstart, termend, ymd, reserveid)
{
	if(typeof(reserveid) === 'undefined')
		reserveid = 0;

	deleteShiftCalendar();

	var dockind = document.getElementById('basic-doctype').value;
	if (dockind === undefined || dockind == 0)
		dockind = "1";

	// 編集画面用、呼び出した時点での文書ID
	if(document.getElementById('pre-doctype')){
		var preDockind = document.getElementById('pre-doctype').value;
	}else{
		var preDockind = 0;
	}

	var place;
	if (place === undefined || place == 0)
		place = "1";

	var nowdate = new Date(ymd);

	var termStart = new Date(termstart);
	var termEnd   = new Date(termend);
	
	// 今週、先週、来週の月曜日の日付を算出
	var this_monday =  new Date(nowdate.getFullYear(), nowdate.getMonth(), nowdate.getDate());
	// 木曜日16時を過ぎると来週の日付が表示されるように変更
	if( ((this_monday.getDay() == 4) && (new Date().getHours() < 16)) 	// 木曜かつ16時以前
			|| (this_monday.getDay() < 4) ){							// もしくは木曜以前ならその週の日付
		this_monday.setDate(this_monday.getDate() - (this_monday.getDay() - 1));
	}else{
		this_monday.setDate(this_monday.getDate() + 7 - (this_monday.getDay() - 1));
		// ymd（getweekshiftの引数）も同様に修正
		ymd = "" + this_monday.getFullYear() + "-" + (this_monday.getMonth() + 1) + "-" + this_monday.getDate();
	}
	var this_friday =  new Date(this_monday.getFullYear(), this_monday.getMonth(), this_monday.getDate());
	this_friday.setDate(this_friday.getDate() + 4);
	var last_monday =  new Date(this_monday.getFullYear(), this_monday.getMonth(), this_monday.getDate());
	last_monday.setDate(last_monday.getDate() - 7);
	var next_monday =  new Date(this_monday.getFullYear(), this_monday.getMonth(), this_monday.getDate());
	next_monday.setDate(next_monday.getDate() + 7);

	var tempDate = new Date();
	if(this_monday.getTime() > tempDate.getTime()){
		var last_link = "<a class=\"prev\" href=\"javascript:void(0);\" onclick=\"createShiftCalendar('" + baseurl + "', '" + termstart + "', '" + termend + "', '" + last_monday.getFullYear() + '-' + (toDD(last_monday.getMonth() + 1)) + '-' + toDD(last_monday.getDate()) + "', '" + reserveid + "');\">prev</a>";
	}else{
		var last_link = "";
	}
	// 三週間以上先へnextで移動できないように修正
	var after3Date = new Date(tempDate.getTime() + 21*24*60*60*1000);
	if(next_monday.getTime() < after3Date.getTime()){
		var next_link =  "<a class=\"next\" href=\"javascript:void(0);\" onclick=\"createShiftCalendar('" + baseurl + "', '" + termstart + "', '" + termend + "', '" + next_monday.getFullYear() + '-' + (toDD(next_monday.getMonth() + 1)) + '-' + toDD(next_monday.getDate()) + "', '" + reserveid + "');\">next</a>";
	}else{
		var next_link = "";
	}

	//////////////////////////////////////////////////////////
	// ヘッダ部
	var div = document.getElementById("shiftPager");
	div.innerHTML = "<span class=\"date\">" + last_link + this_monday.getFullYear() + '&nbsp;' + (this_monday.getMonth() + 1) + '/' + this_monday.getDate() + '&nbsp;-&nbsp;' + (this_friday.getMonth() + 1) + '/' + this_friday.getDate() + next_link + "</span>";


	var table = document.getElementById("shiftTable");

	//////////////////////////////////////////////////////////
	// 日付部(月～金)
    var w = ["日", "月", "火", "水", "木", "金", "土"];
	var day =  new Date(this_monday.getFullYear(), this_monday.getMonth(), this_monday.getDate());
	thead = document.createElement("thead");
	tr = document.createElement("tr");
	td = document.createElement("th");
	td.setAttribute('class', 'blank');
	td.innerHTML ="";	// 空行
	tr.appendChild(td);
	for (var dow = 0; dow < 5; dow++)
	{
		td = document.createElement("th");
		td.innerHTML = "<span class=\"day\">" + (day.getMonth() + 1) + '/' + day.getDate() + "</span>" + '(' + w[day.getDay()] + ')';
		tr.appendChild(td);

		day.setDate(day.getDate() + 1);
	}
	thead.appendChild(tr);
	table.appendChild(thead);


	//////////////////////////////////////////////////////////
	// シフト部と日付各本体
	$("#loading").show();			// ローディング画像
	$(".shiftCalendar").hide();		// カレンダーを隠す

	var dockindButton = $("#basicSettingDialog").find("#dockind");
	var placeButton = $("#basicSettingDialog").find("#place");

	dockindButton.addClass("inactive").attr("disabled", "disabled");	// 非同期通信中はボタンを押せないようにする
	placeButton.addClass("inactive").attr("disabled", "disabled");

	// Ajaxでの非同期通信に処理を変更、ローディング画像の追加
	$.ajax({
		async: true,	// 非同期通信
		url: baseurl + "/getweekshift/ymd/" + ymd + "/dockind/" + dockind + "/place/" + place + "/preDockind/" + preDockind + "/reserveid/" + reserveid,
		type: "POST",
		timeout: 600000,
		datatype: 'json',

		beforeSend: function(xhr, settings) {
		},
		success: function(data, textStatus, jqXHR) {

			$("#loading").hide();			// ローディング画像
			$(".shiftCalendar").show();		// カレンダー表示

			dockindButton.removeClass("inactive").removeAttr("disabled");		// ボタンを再度アクティブに
			placeButton.removeClass("inactive").removeAttr("disabled");

			// カレンダーを後で表示する分の位置調整
			if(bPopup != undefined)
				bPopup.reposition(100);

			var shiftdata = $.parseJSON(data);

			// 名前取得
			var dockindname = shiftdata['dockinds'][dockind]['document_category'];
			var placename = shiftdata['places'][place]['consul_place'];

			tbody = document.createElement("tbody");
			notbox = "";
			i=0;

			// シフトカレンダー作成
			for (var shift_index = 1; shiftdata[0][shift_index]; shift_index++)
			{
				tr = document.createElement("tr");
				for (var dow_index = 0; dow_index <= 5; dow_index++)
				{
					var shift_code	= String.fromCharCode(65 + (shift_index-1));
					var shift_string =  shiftdata[0][shift_index]['starttime'] + '-' + shiftdata[0][shift_index]['endtime'];
					if (dow_index == 0)
					{	// シフト
						td = document.createElement("th");

						td.innerHTML = shift_code + '&nbsp;' + shift_string;
					}
					else
					{	// 日毎のデータ

						td = document.createElement("td");

						// 津田塾大ではユーザーはある文書タイプでの予約は同時に一つしかできない
						if(shiftdata['mydockind'] > 0)
						{
							td.setAttribute('class', 'collision');
							td.innerHTML = '×';
							td.setAttribute('id', 'notbox');
							td.setAttribute('title', '既に自分の予約が入っています');
							i++
							maxshift = 5 * 8;
							if(i == maxshift){
								notbox = dockindname +"の相談が終了するまで" + dockindname +"で予約は出来ません";
								td.setAttribute('value', notbox);
								$("#basicSettingDialog").find("#item3Set").addClass("inactive");
								$('#alertnotbox').html(notbox);
								$("#doubleDialog").bPopup();
							}

						}
						else
						{

							var limit = shiftdata[dow_index][shift_index]['limit'];
							if (limit === '')
							{
								td.setAttribute('class', 'inactive');
								td.innerHTML = '×';
								td.setAttribute('title', '予約時間外です');
							}
							else if (limit == 0)
							{
								td.setAttribute('class', 'inactive');
								// 閉室中表記を×に変更
								td.innerHTML = '×';
								td.setAttribute('class', 'filledcapacity');
								td.setAttribute('title', '予約の枠がありません');
							}
							else
							{	// 予約可能

								// 予約パラメータ
								var string = shiftdata[dow_index][shift_index]['date'] + ' ' + shift_code + ' ' + shift_string;
								var rdate = shiftdata[dow_index][shift_index]['date'];
								var shiftid = shiftdata[0][shift_index]['m_shift_id'];

								if (shiftdata[dow_index][shift_index]['myidreservecount'] > 0)
								{	// すでに自分の予約が入っている
									td.setAttribute('class', 'collision');
									td.innerHTML = '×';
									td.setAttribute('title', '既に自分の予約が入っています');
								}
								else if (shiftdata[dow_index][shift_index]['rest'] == 0)
								{	// 枠なし
									td.setAttribute('class', 'filledcapacity');
									td.innerHTML = '×';
									td.setAttribute('title', '予約の枠がありません');
								}
								else if (shiftdata[dow_index][shift_index]['rest'] == 1)
								{	// 残り1枠
									td.removeAttribute('class');
									td.innerHTML = "<a href=\"javascript:void(0);\" onclick=\"closesetting('" + string + "', '" + rdate + "'," + shiftid + ");\">○</a>";
								}
								else
								{	// 2つ以上予約が可能
									td.removeAttribute('class');
									td.innerHTML = "<a href=\"javascript:void(0);\" onclick=\"closesetting('" + string + "', '" + rdate + "'," + shiftid + ");\">○</a>";
								}
							}
						}
					}
					tr.appendChild(td);
				}
				tbody.appendChild(tr);
			}
			table.appendChild(tbody);
		},
		error: function(jqXHR, textSatus, errorThrown) {
			// Ajax処理修了前にページ遷移するなどで分岐
			// 何か表示したければ表示する
		},
		complete: function(jqXHR, textStatus) {
			// 必ず最後に渡る部分
		},
	});
}

function deleteShiftCalendar()
{
	var table = document.getElementById('shiftTable');
	while (table.firstChild)
		table.removeChild(table.firstChild);
}

