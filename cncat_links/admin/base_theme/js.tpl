{TEMPLATE js_calendar}
<script type="text/javascript">
var month=Array({$CNCAT[lang][calendar_months]});
var minday=0;
var minmonth=0;
var minyear=0;

function compareDates(d1,m1,y1,d2,m2,y2) 
{
	var dt1 = new Date(); 
	var dt2 = new Date(); 
	dt1.setDate(d1); 
	dt1.setMonth(m1-1); 
	dt1.setYear(y1); 
	dt2.setDate(d2); 
	dt2.setMonth(m2-1);
	dt2.setYear(y2); 

	if (dt1>=dt2) return 0; else return 1; 
}

function IsLeapYear(Year) 
{
	return ((Year % 4) == 0) && (((Year % 100) != 0) || ((Year % 400) == 0))
}

function CalendarSD(y,m,d,input,div) 
{
	var ed=document.getElementById(input);
	var edc=document.getElementById(input+"c");
	var e=document.getElementById(div);

	if (!ed || !e) return;

	if (m<10) m='0'+m;
	if (d<10) d=' 0'+d; else d=' '+d;

	if (input=='ed') hours='23:59'; else hours='00:00';
	ed.value=hours+d+'.'+m+'.'+y;
	e.style.visibility='hidden';
    edc.checked=1;
}

function ShowCalendarE(div, input, nohide) 
{
  var el=document.getElementById(input);
	y=parseInt(el.value.substring(12,16));
	m=el.value.substring(9,11);
	if (m=="01") m=1;if (m=="02") m=2;if (m=="03") m=3;
	if (m=="04") m=4;if (m=="05") m=5;if (m=="06") m=6;
	if (m=="07") m=7;if (m=="08") m=8;if (m=="09") m=9;

	ShowCalendar(y,m,div, input, nohide);
}

function ShowCalendar(y,m,div, input, nohide) 
{
    var mdays_noleap=Array(31,28,31,30,31,30,31,31,30,31,30,31);
    var mdays_leap=Array(31,29,31,30,31,30,31,31,30,31,30,31);
	
	var e=document.getElementById(div);
	if (!e) return;

	var el=document.getElementById(input);
	d=el.value.substring(6,8);
	if (d=="01") d=1;if (d=="02") d=2;if (d=="03") d=3;
	if (d=="04") d=4;if (d=="05") d=5;if (d=="06") d=6;
	if (d=="07") d=7;if (d=="08") d=8;if (d=="09") d=9;
	day=parseInt(d);

	if (nohide==0) if (e.style.visibility=='visible') {e.style.visibility='hidden';return;}
	
	if (e.style.width!='210px') {
		e.style.left=e.offsetLeft-35;
		e.style.width='210px';
		}
	e.style.visibility='visible';

	if (!IsLeapYear(y)) mdays=mdays_noleap; else mdays=mdays_leap;

	var dnow=new Date();
	dnow_d=dnow.getDate();
	dnow_m=dnow.getMonth()+1;
	dnow_y=dnow.getFullYear();

	var d='';
	var fday=new Date(y,m-1,1);
	var dow=fday.getDay();
	

	if (dow==0) dow=7;

	m=parseInt(m);
	if (m==12) {mn=1;yn=y+1;} else {mn=m+1;yn=y;}
	if (m== 1) {mp=12;yp=y-1;} else {mp=m-1;yp=y;}

	d+='<table class="ctable"><tr class="ctbl0">';
	d+='<td style="width:33%;"><a href="javascript:ShowCalendar('+yp+','+mp+',\''+div+'\',\''+input+'\',1);">&lt;&lt;&nbsp;'+month[mp-1]+'</a></td>';
	d+='<td style="width:34%; text-align: center;">'+month[m-1]+'&nbsp;'+y+'</td>';
	d+='<td style="width:33%; text-align: right;"><a href="javascript:ShowCalendar('+yn+','+mn+',\''+div+'\',\''+input+'\',1);">'+month[mn-1]+'&nbsp;&gt;&gt;</a></td>';
	d+='</tr></table>';

	d+='<table class="ctable"><tr class="ctbl2">';
	if (dow!=1) d+='<td colspan="'+(dow-1)+'">&nbsp;</td>'
	var i=1;
	do {
		if (i==day) style='style="background: #9bc871;"'; else style='';
		if (compareDates(i,m,y,minday,minmonth,minyear)==0 && compareDates(dnow_d,dnow_m,dnow_y,i,m,y)==0) 
			d+='<td align="right" '+style+' onClick="CalendarSD('+y+','+m+','+i+',\''+input+'\',\''+div+'\');"><div><a href="javascript:CalendarSD('+y+','+m+','+i+',\''+input+'\',\''+div+'\');">'+i+"</a></div></td>";
		else 
			d+='<td align="right">'+i+"</td>";
			
		i++;
		dow++;
		if (dow>7) {d+='</tr><tr class="ctbl2">';dow=1;}
		} while (i<=mdays[m-1]);
	
	if (dow!=0) d+='<td colspan="'+(8-dow)+'">&nbsp;</td>';

	d+='</tr></table>';

	if (e) e.innerHTML=d;
}
</script>
{/TEMPLATE}
