function fillZero(num)
{
	return ('0' + num).slice(-2);
}

function prepareFormat(tabnum)
{
	for(var i = 0; i <= 6; i++)
	{
		var max		= 0;
		
		for(var j = mintime; j <= maxtime; j++)
		{
			var main			= $('#t' + tabnum + '_cl' + i + '_' + j);
			
			main.find('.item').each(function(e)
			{
				var num			= $(this).attr('data-num');
				
				if(num > max)
					max	= num;
			});
		}
		for(var j = mintime; j <= maxtime; j++)
		{
			var main			= $('#t' + tabnum + '_cl' + i + '_' + j);
			
			main.find('.item').each(function(e)
			{
				$(this).attr('data-max', max);
			});
		}
	}
}

function insideForeach(tabnum, infoid, info, startdate, enddate, flg, locale)
{
	startdate	= new Date(startdate.replace('-', '/', 'g'));
	
	var w		= startdate.getDay();	// 曜日
	var sdate	= startdate.getFullYear() + '/' + fillZero(startdate.getMonth() + 1) + '/' + fillZero(startdate.getDate());
	var stime	= fillZero(startdate.getHours()) + ':' + fillZero(startdate.getMinutes());
	
	var shour	= startdate.getHours();		// 開始時
	var smin	= startdate.getMinutes();	// 開始分
	
	var dateStr = getDowArray();
	
	var vdate	= sdate + '(' + dateStr[w] + ')';
	
	enddate		= new Date(enddate.replace('-', '/', 'g'));
	
	if(!isNaN(enddate))
	{
		var edate = enddate.getFullYear() + '/' + fillZero(enddate.getMonth() + 1) + '/' + fillZero(enddate.getDate());
		var etime	= fillZero(enddate.getHours()) + ':' + fillZero(enddate.getMinutes());
		
		var ehour	= enddate.getHours();		// 開始時
		var emin	= enddate.getMinutes();		// 開始分
	}
	else
	{
		var edate = 0;
	}
	
	
	if(shour < mintime)
	{
		var vshour	= mintime;
		var vsmin	= 0;
	}
	else
	{
		var vshour	= shour;
		var vsmin	= smin;
	}
	
	if(maxtime <= ehour)
	{
		var vehour	= maxtime;
		var vemin	= 0;
	}
	else
	{
		var vehour	= ehour;
		var vemin	= emin;
	}
	
	if(shour == 0)
		shour = mintime;
	
	if(ehour == 0)
		ehour = maxtime;
	
	if(flg == 0)
	{
		var id = 't' + tabnum + '_cl' + w + '_' + vshour;
	}
	else
	{
		var id = 't' + tabnum + '_cl' + w + '_all';
	}
	
	
	// 複数日に跨る予定の場合
	if(edate != 0 && sdate != edate)
	{
		var weektop 		= $('#indexjs').data('weektop');
		var weektop_time	= Date.parse(weektop);
		var sdate_time 		= Date.parse(sdate);
		var edate_time 		= Date.parse(edate);
		
		var vedate	= edate + '(' + dateStr[w] + ')';
		var vdate = vdate + ' ' + stime + ' - ' + vedate + ' ' + etime;
		
		flg = 9;
		
		for(var i = 0; i <= 6; i++)
		{
			var tmp_stime = weektop_time + 86400000 * i;
			
			var isset = 0;
			
			if(sdate_time == tmp_stime)		// 開始日
			{
				var tmpid	= 't' + tabnum + '_cl' + w + '_' + vshour;
				var duration = maxtime * 60 - (vshour * 60 + vsmin);		// 全体の時間（分）
				
				isset = 1;
			}
			else if(edate_time == tmp_stime)	// 終了日
			{
				var tmpid	= 't' + tabnum + '_cl' + i + '_' + mintime;
				var duration = (vehour * 60 + vemin) - mintime * 60;		// 全体の時間（分）
				
				isset = 1;
			}
			else if(sdate_time < tmp_stime && tmp_stime < edate_time)	// 間の日
			{
				// 終日の予定
				var tmpid		= 't' + tabnum + '_cl' + i + '_' + mintime;
				var duration	= maxtime * 60 - mintime * 60;
				
				isset = 1;
			}
			
			if(isset === 1)
			{
				replaceThis(info, tmpid, vdate, stime, smin, edate, etime, duration, i, infoid, flg);
				setNum(tmpid, infoid, i, duration, tabnum);
			}
		}
	}
	else
	{
		var duration = (vehour * 60 + vemin) - (vshour * 60 + vsmin);		// 全体の時間（分）
		
		replaceThis(info, id, vdate, stime, vsmin, edate, etime, duration, w, infoid, flg);
		
		if(flg == 0)
			setNum(id, infoid, w, duration, tabnum);
	}
}

function replaceThis(info, id, vdate, stime, smin, edate, etime, duration, i ,infoid, flg)
{
	var td = document.getElementById(id);
	
	var a = document.createElement('div');
	a.setAttribute('class', 'item item' + i);
	a.setAttribute('id', 'item_' + i + '_' + infoid);
	
	smin = smin * 0.67;
	
	a.setAttribute('data-num', 0);
	a.setAttribute('style', 'top: ' + smin + 'px;');	// 分px
	
	if(flg == 0)
		a.setAttribute('title', vdate + ' / ' + stime + '-' + etime + ' / ' + info);
	else
		a.setAttribute('title', vdate + ' / ' + info);

	var span1 = document.createElement('span');
	span1.setAttribute('class', 'cover');
	//span1.setAttribute('id', 'cover4');
	
	duration = duration * 0.67;
	if(isNaN(duration))
		duration = 50;
	
	span1.setAttribute('style', 'height: ' + duration + 'px;'); // 分px

	var span2 = document.createElement('span');
	
	if(info.length >= 15){
		info2 = info.substr(0,12);
		info2 += "...";
	}else{
		info2 = info;
	}
	span2.appendChild(document.createTextNode(info2));

	span1.appendChild(span2);

	a.appendChild(span1);
	td.appendChild(a);
}

function toDoubleDigits(num)
{
	num += "";
	if(num.length === 1)
	{
		num = "0" + num;
	}
	return num;
}

function getElementPosition(elem)
{
	var position=elem.getBoundingClientRect();
	return position.top;
}

function setNum(id, infoid, w, duration, loop)
{
	var cnt				= 0;
	var cnt_arr			= [];
	var main			= $('#item_' + w + '_' + infoid);
	var mainLength		= Math.floor(duration * 0.67);
	
	var active			= $('#indexjs').data('active');
	if(active == undefined)
		active = 1;
	
	var mainDummy		= $('#' + id.replace('t' + loop, 't' + active));
	
	if(!isNaN(main.css('top')))
		var mainPosition	= Math.floor(getElementPosition(main.get(0)) + 1 + main.css('top').slice(0,-2));
	else
		var mainPosition	= Math.floor(getElementPosition(main.get(0)) + 1);
	
//	console.log(duration);
//	console.log(id);
//	console.log(infoid);

	for(var j = mintime; j <= maxtime; j++)
	{
		var sub			= $('#t' + loop + '_cl' + w + '_' + j);
		
		var n			= 0;
		sub.find('.item').each(function()
		{
			if($(this).attr('id') == ('item_' + w + '_' + infoid))	return true;
			
			var subItem		= $(this);
			var subLength	= Math.floor(Number(subItem.find('span').css('height').slice(0,-2)));
			var subNum		= subItem.attr('data-num');
			
			var subDummy		= $('#t' + active + '_cl' + w + '_' + j);
			
			if(!isNaN(subItem.css('top')))
				var subPosition		= Math.floor(getElementPosition(subItem.get(0))+ subItem.css('top').slice(0,-2));
			else
				var subPosition		= Math.floor(getElementPosition(subItem.get(0)));
			
//			console.log(subItem.attr('title'));
//			console.log(subItem.attr('id'));
//			console.log(subItem.css('top'));
//			
//			console.log(mainPosition);
//			console.log(subPosition);
//			console.log(mainPosition+mainLength);
//			console.log(subPosition+subLength);
			
			if(mainPosition == subPosition
					|| (mainPosition < subPosition && mainPosition+mainLength > subPosition)
						|| (mainPosition > subPosition && mainPosition < subPosition+subLength))
			{
//				console.log('overlap');
//				console.log('cnt:' + cnt);
//				console.log('data:' + subItem.attr('data-num'));
				
				if(subItem.attr('data-num') == cnt)
				{
					cnt_arr.push(cnt);
//					console.log('cnt:' + cnt);
				}
				else
				{
					cnt_arr.push(Number(subItem.attr('data-num')));
//					console.log('not cnt:' + Number(subItem.attr('data-num')));
//					console.log(cnt_arr[0]);
				}
				cnt++;
			}
		});
	}
		
	var cnt_flag = false;
	cnt_arr.sort();
	for(var n = 0; n < cnt_arr.length; n++)
	{
//		console.log(n + ':' + cnt_arr[n]);
		
		if(cnt_arr[n] != n)
		{
			main.attr('data-num', n);
			cnt_flag = true;
			
			break;
		}
	}
	if(cnt == 0)
	{
		main.attr('data-num', 0);
//		console.log('not arr: 0');
	}
	else if(!cnt_flag)
	{
		main.attr('data-num', cnt_arr[cnt_arr.length-1] + 1);
//		console.log('arr:' + (Number(cnt_arr[cnt_arr.length-1]) + 1));
	}
//	console.log('**********************');
}