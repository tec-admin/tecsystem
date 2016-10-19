// hasClassのnameプロパティバージョン
$.fn.hasName = function(name) {
    return this.attr('name') == name;
};
// 上記正規表現を用いる場合
$.fn.hasNameRegExp = function(patern) {
    return this.attr('name').match(patern)
};

function bytesToSize(bytes)
{
	if(bytes == 0 || bytes == 1) return bytes + ' Byte';
	var k = 1000;
	var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
	var i = Math.floor(Math.log(bytes) / Math.log(k));

	if(i > 0)
		return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
	else
		return bytes + ' ' + sizes[i];
}

function imageLoader(url)
{
	var img = new Image();
	img.src = url;
}

function resetForm(obj)
{
	obj.find("textarea, :text").val("").end().find(":checked").prop("checked", false);
	obj.find("select").attr("selected",true);
}

// ajaxによるSubmit処理
// @param obj			イベントを起こしたオブジェクト(Form)
// @param event			objが起こしたイベント
// @param succeeded		成功時処理のコールバック
// @param failed		失敗時処理のコールバック
// @param translate		trueならjquery.localizeする
// @return				なし
function ajaxSubmit(obj, event, succeeded, failed, translate)
{
	event.preventDefault();	// 本来のsubmit処理をキャンセル

	var $form = $(obj);
	var fd = new FormData($form[0]);

	$.ajax({
		async: false,
		url: $form.attr('action'),
		type: $form.attr('method'),
		timeout: 600000,

		// 以下、ファイルアップロードに必須
		data: fd,
		processData: false,
		contentType: false,

		// 各種処理
		beforeSend: function(xhr, settings) {
		},
		success: function(data, textStatus, jqXHR) {
			//alert(data);
			try
			{
				var response = JSON.parse(data);
				if (response['error'] !== undefined)
				{	// 論理エラー
					error(failed, response);
				}
				else
				{	// 成功
					succeeded(response);
				}
			}
			catch(e)
			{
				dispError('処理に失敗しました。ページを更新してもう一度お試しください。');
			}
		},
		error: function(jqXHR, textStaus, errorThrown) {
			alert("error");
		},
		complete: function(jqXHR, textStatus) {
			if(translate)
				jqTranslate();
		}
	});
}

// ajaxによるGET処理
// @param url	宛先URL
function ajaxSubmitUrl(url, succeeded, failed, translate)
{
	$.ajax({
		async: false,
		url: url,
		type: 'GET',
		timeout: 600000,

		// 各種処理
		beforeSend: function(xhr, settings) {
		},
		success: function(data, textStatus, jqXHR) {
			//alert(data);
			try
			{
				var response = JSON.parse(data);
				if (response['error'] !== undefined)
				{	// 論理エラー
					error(failed, response);
				}
				else
				{	// 成功
					succeeded(response);
				}
			}
			catch(e)
			{
				dispError('処理に失敗しました。ページを更新してもう一度お試しください。');
			}
		},
		error: function(jqXHR, textStaus, errorThrown) {
			alert("error");
		},
		complete: function(jqXHR, textStatus) {
			if(translate)
				jqTranslate();
		}
	});
}

// 色々指定したい場合(殆ど使わないため別枠)
function ajaxSubmitEx(obj, event, async, before, succeeded, failed, complete)
{
	event.preventDefault();	// 本来のsubmit処理をキャンセル

	var $form = $(obj);
	var fd = new FormData($form[0]);

	$.ajax({
		async: async,
		url: $form.attr('action'),
		type: $form.attr('method'),
		timeout: 600000,

		// 以下、ファイルアップロードに必須
		data: fd,
		processData: false,
		contentType: false,

		// 各種処理
		beforeSend: function(xhr, settings) {
			if(before != undefined)
				before();
		},
		success: function(data, textStatus, jqXHR) {
			//alert(data);
			try
			{
				var response = JSON.parse(data);
				if (response['error'] !== undefined)
				{	// 論理エラー
					error(failed, response)
				}
				else
				{	// 成功
					succeeded(response);
				}
			}
			catch(e)
			{
				dispError('処理に失敗しました。ページを更新してもう一度お試しください。');
			}
		},
		error: function(jqXHR, textStaus, errorThrown) {
			alert("error");
		},
		complete: function(jqXHR, textStatus) {
			if(complete != undefined)
				complete();
		}
	});
}

// 失敗時処理→共通のエラーダイアログ→Javascriptのalertの順で、
// 呼び出し可能ないずれかを呼び出す
function error(failed, response)
{
	if(failed != undefined)
	{
		failed(response);
	}
	else
	{
		dispError(response['error']);
	}
}

// ダイアログ、あるいはアラートを出す
// JSON.parse時の例外と、parse後のユーザー定義エラー時の処理
function dispError(str)
{
	if($('#errorDialog').get(0) != undefined)
	{
		var txt = str;
		txt		= txt.replace(/\r\n/g, "<br />");
		txt		= txt.replace(/(\n|\r)/g, "<br />");
		$('#errorContents').html(txt);
		$('#errorDialog').bPopup();

		$('.loading').each(function(){
			if(!$(this).hasClass('hidden'))
				$(this).addClass('hidden');
		});
	}
	else
	{
		alert(str);
	}
}
