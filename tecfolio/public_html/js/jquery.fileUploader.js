(function( $ )
{
	$.fn.fileUploader = function()
	{
		$(this).each(function()
		{
			var target = $(this);
			var txt = $(this).find('.txt');
			var btn = $(this).find('.btn');
			var uploader = $(this).find('.uploader');
			
			var preview = $(this).siblings('.preview');
			var thumb = preview.find('.thumbnail');
			
			// 変更時
			uploader.bind('change',function()
			{
				// IEではファイルパスが取得されるが、強制的にファイル名のみを抜き出す
				var last = $(this).val().lastIndexOf('\\') + 1;
				txt.val($(this).val().slice(last));
				
				if(target.find('p').attr('class') == undefined)
				{
					// 削除ボタン
					var p = document.createElement('p');
					p.setAttribute('class', 'delete');
					p.innerHTML = "";
					txt.before($(p));
				}
				else
				{
					p = target.find('p').get(0);
				}
				
				$(p).off('click');
				$(p).on('click',function(){
					// 表示テキスト削除
					txt.val('');
					// セッション情報削除
					$(this).find('input:hidden').val('');
					
					thumb.addClass('hidden');
					
					// <input type="file">のリセット処理
					resetFormElement($(this));
					
					// 自身を削除
					$(this).remove();
				});
				
				if(thumb.attr('src') == undefined)
				{
					preview.empty();
					preview.append('<img src="" class="thumbnail hidden" />');
					thumb = preview.find('.thumbnail');
				}
				
				var baseUrl = $('#baseurl').prop('value');
				var controllerName = $('#controllerName').prop('value');
				
				// 画像登録のため、formを生成
				var elmf = document.createElement('form');
				elmf.setAttribute('method', 'POST');
				elmf.setAttribute('action', baseUrl + '/' + controllerName + '/uploadimg');
				elmf.setAttribute('name', 'uploadDummy');
				elmf.setAttribute('id', 'uploadDummy');
				elmf.setAttribute('enctype', 'multipart/form-data');
				elmf.setAttribute('target', 'postIframe');
				
				$('form').before($(elmf));
				
				// 元のIDを退避
				var ori_name = $(this).attr('name');
				
				// 選択されたinput type="file"を画像登録用form内に移動($.cloneではIEで動作しない)
				// この際の各idとnameは固定とする
				//$(elmf).append($(this).clone(true).attr('id', 'file').attr('name', 'file'));

				$(elmf).append($(this).prop('id', 'file').prop('name', 'file'));
				
				// POST対象となるiframeを生成
				var fra = document.createElement('iframe');
				fra.setAttribute('name', 'postIframe');
				fra.setAttribute('id', 'postIframe');
				$('form').not('#uploadDummy').first().before($(fra));
				
				// 以前の画像タグを非表示、ローディング画像を生成
				thumb.addClass('hidden');
				thumb.css('opacity', 0);
				
				var loading = document.createElement('img');
				loading.setAttribute('src', '/images/loading.gif');
				loading.setAttribute('id', 'loading');
				preview.append($(loading));
				
				var ori = $(this);
				// 生成したフォームのサブミットイベント
				$(elmf).submit(function()
				{
					$(fra).unbind().bind('load', function()
					{
						var response = $(fra).contents().find('body').html().replace(/[\\"]/g,'');
						
						if(response.indexOf('ERROR:') !== -1)
						{
							response = response.slice(6);	// ERROR: の文字切り取り
							// 選択されたinput type="file"をリセット、
							// サムネイルをクリア、代替のWarningテキストを挿入
							p.click();
							thumb.remove()
							thumb = document.createElement('div');
							thumb.setAttribute('class', 'thumbnail error');
							thumb.innerHTML = response;
							thumb = $(thumb);
							preview.append(thumb);
						}
						else if(response != 'error')
						{
							// 画像の差し替え
							thumb.attr('src', response);
							$('#image_main_hidden').val(response);
						}
						else
						{
							// 選択されたinput type="file"をリセット、
							// サムネイルをクリア、代替のWarningテキストを挿入
							thumb.remove();
							thumb = document.createElement('div');
							thumb.setAttribute('class', 'thumbnail error');
							thumb.innerHTML = 'ファイルのアップロードに失敗しました';
							thumb = $(thumb);
							preview.append(thumb);
						}
						
						// アップロードした画像を表示、ローディング画像を削除、生成したフォームを削除
						$('#loading').remove();
						thumb.removeClass('hidden');
						thumb.animate({opacity:'1'}, 500);
						
						// 選択されたinput type="file"を元の位置に戻す
						btn.after(ori.prop('id', ori_name).prop('name', ori_name));
						// 生成したiframeを削除
						$(fra).remove();
						
						// TEC追加
						// <input type="file">のリセット処理
						resetFormElement($('#image_main'));
					});
				});
				
				// 生成したフォームをサブミット
				$(elmf).submit();
			});
			
			// 初期表示されている場合の削除ボタンイベント
			$(this).find('p.delete').off('click');
			$(this).find('p.delete').on('click',function() {
				// 表示テキスト削除
				txt.val('');
				// セッション情報削除
				target.find('input:hidden').val('');
				
				thumb.addClass('hidden');
				
				// <input type="file">のリセット処理
				resetFormElement($(this).siblings('.uploader'))
				
				$(this).remove();
			});
			
			// ボタンのイベントを無効にする
			btn.bind('click',function(event){
				event.preventDefault();
				return false;
			});
			
			uploader.bind('mouseover',function(){
				btn.css('color','#999');
			});
			uploader.bind('mouseout click',function(){
				btn.css('color','#000');
			});
		});
		
		function resetFormElement(e)
		{
			e.wrap('<form>').closest('form').get(0).reset();
			e.unwrap();
		}
	};
})( jQuery );