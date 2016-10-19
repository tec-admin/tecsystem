var bPopup;		// 他ファイルでモーダルの設定値を変更したい場合などに用いる

(function( $ ) {
	$.fn.basicSetting = function() {
		var steps;
		var t = $("#basicSettingDialog"),//ダイアログ本体
		v1=	t.find("#basic-doctype"),
		v2=	t.find("#basic-place"),
		v2s=t.find("#place"),// koiwa
		v3=	t.find("#basic-date"),
		v3s=t.find("#item3Set"),
		v3t=v3s.find("table"),
			r = $("#basicReceiver");//ダイアログ内容うけ

			function fillCheck(){
				if(v1.val()==0 || !v1.val()){
					v2s.addClass("inactive").attr("disabled", "disabled");
					v3s.addClass("inactive");
					steps=1;
				}else{
					if (v2.val()==0 || !v2.val())
						v2s.removeClass("inactive").removeAttr("disabled");
					steps=2;
				}
				if(v2.val()==0 || !v2.val()){
					v3s.addClass("inactive");
					steps=2;
				}else{
					v3s.removeClass("inactive");
					steps=3;
				}
			};
			v3t.find("a:not(.collision),a:not(.inactive)").each(function(){
				//画面への反映
				$(this).click(function(){
					var d =$(this).data("date");
					v3.val(d);
					fullFill = "true";
					r.find(".fromSelector").each(function(){
						var source = $(this).data("source");
						var txt = $("#"+source).find("option:selected").text();
						var v = $("#"+source).val();
						$(this).html(txt);
						$(this).next("input:hidden").val(v);
					});
					r.find(".fromInput").each(function(){
						var source = $(this).data("source");
						var v = $("#"+source).val();
						$(this).html(v);
						$(this).next("input:hidden").val(v);
					});
					//disable解除
					$("#pageControl .finish, .formSet, .freetext").removeClass("inactive");
					$("article .calendarSet").each(function(){
						var v = $(this).find(".view");
						v.datepicker({
							showOn:"button",
							buttonText: "▼ 選んでください",
							beforeShow: function(input, inst) {
								$(".selectMirror").each(function(){
									$(this).find(".options").fadeOut(150);
								});
								var calendar = inst.dpDiv;
								setTimeout(function() {
									calendar.position({
										my: 'right top',
										at: 'right bottom',
										collision: 'none',
										of: ".ui-datepicker-trigger"
									});
								}, 1);
							}
						});
					});
					t.bPopup().close();
			});
		});

		this.bind("click", function(e) {
			e.preventDefault();
			// モーダルオープン後の返り値を代入
			bPopup = t.bPopup({
				closeClass:"closeButton",
				modalColor:"#3f3f3f",
				opacity: 0.9,
				zIndex: 110,
				onOpen: function(){
					fillCheck();
					//foreachのループ回数を持ってこれないため暫定15回
					for(i=0 ; i < 15 ; i++){
						//v1.bind("mouseup change",function(){
						$("#docid"+i).bind("click",function(){
							fillCheck();
						});
						//v2.bind("mouseup change",function(){
						$("#plaid"+i).bind("click",function(){
							fillCheck();
						});
					}
				},
				onClose: function() {
					if(steps != 3){
	            	//cancel
	            	}
					//津田側でモーダルが出た場合に入力をクリアする
					alertbox = $("#notbox").attr("title");
					if (alertbox !==""){
						if(!$.isEmptyObject(alertbox) && document.URL.match('/twc/labo/reserve')){
							if (notbox.indexOf("相談") != -1){
								$("#basic-doctype").val(0);
								$("#basic-place").val(0);
								$("#dc").empty();
								v3s.addClass("inactive");

							}
						}
						if(!$.isEmptyObject(alertbox) && document.URL.match('/twc/labo/editreserve')){
							if (notbox.indexOf("相談") != -1){
								$("#basic-doctype").val($("#item1").val());
								$("#dc").text($("#v-basic-doctype").text());
								$("#basic-place").val(0);
								can();
							}
						}
					}
	        	}
	    	});
		});

		$.fn.setClose = function(string, rdate){
			fullFill = "true";
			r.find(".fromSelector").each(function(){
				var source = $(this).data("source");
				if(source == "basic-doctype"){
					var txt = $("#dc").text();
				}
				if(source == "basic-place"){
					var txt = $("#pl").text();
				}
				var v = $("#"+source).val();
				$(this).html(txt);
				$(this).next("input:hidden").val(v);
			});
			r.find(".fromInput").each(function(){
				$(this).html(string);
				$(this).next("input:hidden").val(rdate);
			});
			//disable解除
			$("#pageControl .finish, .formSet, .freetext").removeClass("inactive");
			$("article .calendarSet").each(function(){
				var v = $(this).find(".view");
				v.datepicker({
					showOn:"button",
					//buttonText: "▼ 選んでください",
					beforeShow: function(input, inst) {
						$(".selectMirror").each(function(){
							$(this).find(".options").fadeOut(150);
						});
						var calendar = inst.dpDiv;
						setTimeout(function() {
							calendar.position({
								my: 'right top',
								at: 'right bottom',
								collision: 'none',
								of: ".ui-datepicker-trigger"
							});
						}, 1);
					}
				});
				$(this).find('.ui-datepicker-trigger').attr('data-localize', '▼ 選んでください');
				jqTranslate();
			});
			t.bPopup().close();
		};

 	};
 	
	$.fn.decisionDialog = function(t,mirrorValues) {
		//mirrorValuesがtrueの時、dialog内の各selectedはdata-getに指定されたjQueryセレクタがinputの場合はvalueを、それ以外ならtextを取得し表示する
		this.bind("click", function(e) {
			e.preventDefault();
			bPopup = t.bPopup({
				closeClass:"cancel",
				modalColor:"#ffffff",
				opacity: 0.5,
				transitionClose: "fadeIn",
				zIndex: 110,

				onOpen: function(){
					if (document.URL.match('/admin/information')){
						new nicEditor( { iconsPath : '/image/nicEditorIcons.gif', maxHeight : 150} ).panelInstance('noticeBody');
					}
				},
				onClose: function() {
					//closing function
				}
			});
			if(mirrorValues){
				t.find(".selected").each(function(i){
					var mirror = $(this),
						origin = $(mirror.data("get"));
					if(origin.is("input")) {
						mirror.text($(origin).val());

						// 変更箇所は赤文字
						if($("#defaultitem" + i)){
							if($(origin).val() != $("#defaultitem" + i).val()){
								mirror.css("color", "red");
							}
							else{
								mirror.css("color", "black");
							}
						}
					}else{
						mirror.text($(origin).text());

						if($("#defaultitem" + i)){
							if($(origin).text() != $("#defaultitem" + i).val()){
								mirror.css("color", "red");
							}
							else{
								mirror.css("color", "black");
							}
						}
					}
				});
			}
			t.find(".buttonSet a").each(function(){
				$(this).click(function(){
					t.bPopup().close();
				});
			});
		});

	}
	$.fn.shiftDialog_kandai = function(attach,aFail,remove,rFail,oFail,expired) {
		function dialogAction(dialogObject){
			dialogObject.bPopup({
				closeClass:"cancel",
				modalColor:"#ffffff",
				opacity: 0.5,
				transitionClose: "fadeIn",
				zIndex: 110
			});
			dialogObject.find(".affirm, .delete").click(function(){
				dialogObject.bPopup().close();
			});
		};
		var root = this;
		var td = root.find("td");
		td.each(function(){
			var t = $(this),
				d		= $(this).data("shift"),
				dayno	= $(this).data("dayno"),
				dow		= $(this).data("dow");
			t.bind("click", function(e){
			e.preventDefault();
			if(t.hasClass("expired")){
				dialogAction(expired);
//			}else if(t.hasClass("attached") && t.hasClass("terminal")){
			}else if(t.hasClass("attached")){
				//削除する
				remove.find(".shiftData").text(d);
				remove.find(".dayno").val(dayno);
				remove.find(".dow").val(dow);
				dialogAction(remove);
//			}else if(t.hasClass("attached") && !t.hasClass("terminal")){
//					//削除できない中間セルの警告を出す
//					dialogAction(rFail);
//			}else if(t.hasClass("restricted")){
//					//同じ曜日で連続したセルを一つに制限
//					dialogAction(aFail);
//			}else if(t.hasClass("limit")){
//					//予約個数オーバー
//					dialogAction(oFail);
			}else if(t.hasClass("outofrange")){
					//outofrangeセルはアクションを起こさない
					//※運営管理者の選択学期外データ
					return false;
			}else if(t.hasClass("inactive")){
					//inactiveセルはアクションを起こさない
					return false;
			}else{
					//一つも指定のない曜日 or 既存指定の隣接セル
					attach.find(".shiftData").text(d);
					attach.find(".dayno").val(dayno);
					attach.find(".dow").val(dow);
					dialogAction(attach);
				}
			});
		});
	}

	$.fn.shiftDialog = function(attach,aFail,remove,rFail,oFail,expired) {
		function dialogAction(dialogObject){
			dialogObject.bPopup({
				closeClass:"cancel",
				modalColor:"#ffffff",
				opacity: 0.5,
				transitionClose: "fadeIn",
				zIndex: 110
			});
			dialogObject.find(".affirm, .delete").click(function(){
				dialogObject.bPopup().close();
			});
		};
		var root = this;
		var td = root.find("td");
		td.each(function(){
			var t = $(this),
				d		= $(this).data("shift"),
				dayno	= $(this).data("dayno"),
				dow		= $(this).data("dow");
			t.bind("click", function(e){
			e.preventDefault();
			if(t.hasClass("expired")){
				dialogAction(expired);
			}else if(t.hasClass("attached") && t.hasClass("terminal")){
				//削除する
				remove.find(".shiftData").text(d);
				remove.find(".dayno").val(dayno);
				remove.find(".dow").val(dow);
				dialogAction(remove);
			}else if(t.hasClass("attached") && t.hasClass("inter")){
				//削除する
				remove.find(".shiftData").text(d);
				remove.find(".dayno").val(dayno);
				remove.find(".dow").val(dow);
				dialogAction(remove);
			}else if(t.hasClass("inactive")){
					//inactiveセルはアクションを起こさない
					return false;
			}else{
					//一つも指定のない曜日 or 既存指定の隣接セル
					attach.find(".shiftData").text(d);
					attach.find(".dayno").val(dayno);
					attach.find(".dow").val(dow);
					dialogAction(attach);
				}
			});
		});
	}
	
	$.fn.workShiftDialog_kandai = function(attach,aFail,remove,rFail,oFail,uFail) {
		function dialogAction(dialogObject){
			dialogObject.bPopup({
				closeClass:"cancel",
				modalColor:"#ffffff",
				opacity: 0.5,
				transitionClose: "fadeIn",
				zIndex: 110
			});
			dialogObject.find(".affirm, .delete").click(function(){
				dialogObject.bPopup().close();
			});
		};
		var root = this;
		var td = root.find("td");
		td.each(function(){
			var t = $(this),
				d				= $(this).data("shift"),
				dayno			= $(this).data("dayno"),
				dow				= $(this).data("dow"),
				reservecnt		= $(this).find('div.reservecount').html(),
				staffcnt_html	= $(this).find('div.reservecount_right').html();
			
			// 学期の境目で「-」が表示される部分には作用しない
			if(staffcnt_html != undefined)
			{
				t.bind("click", function(e){
				e.preventDefault();
				
				staffcnt 		= staffcnt_html.slice(staffcnt_html.indexOf('(') + 1, -1);
				
				if(t.hasClass("attached") && reservecnt == staffcnt){
					dialogAction(uFail);
//				}else if(t.hasClass("attached") && t.hasClass("terminal")){
				}else if(t.hasClass("attached")){
					//削除する
					remove.find(".shiftData").text(d);
					remove.find(".dayno").val(dayno);
					remove.find(".dow").val(dow);
					dialogAction(remove);
//				}else if(t.hasClass("attached") && !t.hasClass("terminal")){
//						//削除できない中間セルの警告を出す
//						dialogAction(rFail);
//				}else if(t.hasClass("restricted")){
//						dialogAction(aFail);
//				}else if(t.hasClass("limit")){
//						//予約個数オーバー
//						dialogAction(oFail);
				}else if(t.hasClass("outofrange")){
						//outofrangeセルはアクションを起こさない
						return false;
				}else if(t.hasClass("inactive")){
						//inactiveセルはアクションを起こさない
						return false;
				}else{
						//一つも指定のない曜日 or 既存指定の隣接セル
						attach.find(".shiftData").text(d);
						attach.find(".dayno").val(dayno);
						attach.find(".dow").val(dow);
						dialogAction(attach);
					}
				});
			}
		});
	}

	$.fn.workShiftDialog = function(attach,aFail,remove,rFail,oFail,uFail) {
		function dialogAction(dialogObject){
			dialogObject.bPopup({
				closeClass:"cancel",
				modalColor:"#ffffff",
				opacity: 0.5,
				transitionClose: "fadeIn",
				zIndex: 110
			});
			dialogObject.find(".affirm, .delete").click(function(){
				dialogObject.bPopup().close();
			});
		};
		var root = this;
		var td = root.find("td");
		td.each(function(){
			var t = $(this),
				d		= $(this).data("shift"),
				dayno	= $(this).data("dayno"),
				dow		= $(this).data("dow"),
				circles = $(this).find('.circles'),
				sClass	= $('#facility').prop('value');
			t.bind("click", function(e){
			e.preventDefault();
			// 日本語ライティングの場合
			if(t.hasClass("attached") && sClass == '1,2' 
				&& ( (circles.last().data('red') > 0 && circles.last().data('reserve') == circles.last().data('count'))	// 就職予約があるなら赤丸の数だけで判断
						|| (circles.last().data('red') == 0 && ( ( Number(circles.first().data('reserve')) + Number(circles.last().data('reserve')) ) == ( Number(circles.first().data('count')) + Number(circles.last().data('count')) ) )))){
				dialogAction(uFail);
			// アカデミックの場合、アカデミックと就職両方の受入数の合算値でチェックする
			}else if(t.hasClass("attached") && sClass == '1' && ( ( Number(circles.first().data('reserve')) + Number(circles.last().data('reserve')) ) == ( Number(circles.first().data('count')) + Number(circles.last().data('count'))) )){
				dialogAction(uFail);
			}else if(t.hasClass("attached") && sClass == '3' && circles.data('reserve') == circles.first().data('count')){
				dialogAction(uFail);
			}else if(t.hasClass("attached") && t.hasClass("terminal")){
				//削除する
				remove.find(".shiftData").text(d);
				remove.find(".dayno").val(dayno);
				remove.find(".dow").val(dow);
				dialogAction(remove);
			}else if(t.hasClass("attached") && t.hasClass("inter")){
				//削除する
				remove.find(".shiftData").text(d);
				remove.find(".dayno").val(dayno);
				remove.find(".dow").val(dow);
				dialogAction(remove);
			}else if(t.hasClass("inactive")){
					//inactiveセルはアクションを起こさない
					return false;
			}else{
					//一つも指定のない曜日 or 既存指定の隣接セル
					attach.find(".shiftData").text(d);
					attach.find(".dayno").val(dayno);
					attach.find(".dow").val(dow);
					dialogAction(attach);
				}
			});
		});
	}

 	//通常の確認ダイアログ
 	$.fn.addReserveDialog = function(t) {
		this.bind("click", function(e) {
			e.preventDefault();
			t.bPopup({
				closeClass:"cancel",
				modalColor:"#ffffff",
				opacity: 0.5,
				transitionClose: "fadeIn",
				zIndex: 110,
				onClose: function() {
					//closing function
				}
			});
			t.find(".affirm").click(function(e){
				/*
				t.hide();
				e.preventDefault();
				comp.bPopup({
					escClose: false,
					modalClose:false,
					modalColor:"#eeeeee",
					opacity: 0.4,
					transitionClose: "fadeIn",
					zIndex: 115,
				});
				*/
			});
		});
	}
 	
 	//駆け込み予約用ダイアログ
 	$.fn.addRunReserveDialog = function(t) {
		this.bind("click", function(e) {
			e.preventDefault();
			t.bPopup({
				position	: ['auto',0],
				follow		: [true,false],
				closeClass:"cancel",
				modalColor:"#ffffff",
				opacity: 0.5,
				transitionClose: "fadeIn",
				zIndex: 110,
				onClose: function() {
					//closing function
				}
			});
		});
	}
})( jQuery );

$(function(){
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