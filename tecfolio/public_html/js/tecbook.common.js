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

$(function(){
	$('.miniMenu').each(function(){
		setMenu($(this));
	});
});