<!-- @search_date -->
<style>
.nav-xxs{
	position: absolute;
	width: 250px;
	z-index: 2;
	top: -15px;
}
.nav-xxs>li>a{
	padding: 0 3px;
	font-size: 10px;
}

/*XinXin*/
.mid {
    color: #1ea061;
    font-family: "微軟正黑體","蘋果儷黑體";
}
.wg_io, .wg_con {
    color: #bf343b;
    font-weight: bold;
}
.p_font_gr {
    color: #7e7e7e;
}
.bet_str {
    color: #008aff;
    font-weight: bold;
}
.tm_set {
	white-space: pre-wrap;
	font-size: xx-small;
	float: left;
}
</style>
<script>
$(function(){
	var search = $('{search}');
	var f = $('#{unique_id}_panel');
	var g = $('#{unique_id}_search_group');
	
	f.find('.toollist .main').click(function(){
		f.find('table.review').trigger('refresh',{type: 'review'});
	});
	
	var type = {type}; // type and offset 1.default 2.cross half 3.sport with tomorrow
	
	switch(type){
		default:
			g.find('span').remove();
			g.find('li').eq(0).remove();
			break;
		case 2:
			g.find('li').eq(0).remove();
			break;
		case 3:
			g.find('span').remove();
			break;
	}
	
	g.find('[name=search_sdate]').datepicker();
	g.find('[name=search_edate]').datepicker();
	g.find('a').click(function(){
		var type = {type};
		switch(type){
			default:
				o = 0;
				break;
			case 2:
				o = 1;
				break;
		}
	
		var now = new Date();
		var y = now.getFullYear();
		var m = now.getMonth();
		var d = now.getDate();
		var w = now.getDay();
		var sdate = eval($(this).attr('sdate'));
		var edate = eval($(this).attr('edate'));
		g.find('[name=search_sdate]').datepicker('setDate', sdate);
		g.find('[name=search_edate]').datepicker('setDate', edate);
	});
	g.find('a').eq(0).trigger('click');
})
</script>
<div id="{unique_id}_search_group" class="btn-group">
	<ul class="nav nav-pills nav-xxs">
		<li><a href="#" sdate="new Date(y, m, d+1)" edate="new Date(y, m, d+1 +o)">明日</a></li>
		<li><a href="#" sdate="new Date(y, m, d)" edate="new Date(y, m, d +o)">本日</a></li>
		<li><a href="#" sdate="new Date(y, m, d-1)" edate="new Date(y, m, d-1 +o)">昨日</a></li>
		<li><a href="#" sdate="new Date(y, m, d -w +1)" edate="new Date(y, m, d -w +7 +o)">本週</a></li>
		<li><a href="#" sdate="new Date(y, m, d -w -7 +1)" edate="new Date(y, m, d -w +o)">上週</a></li>
		<li><a href="#" sdate="new Date(y, m, 1)" edate="new Date(y, m+1, 0 +o)">本月</a></li>
		<li><a href="#" sdate="new Date(y, m-1, 1)" edate="new Date(y, m, 0 +o)">上月</a></li>
	</ul>
	<div class="input-group">
		<input class="form-control" name="search_sdate" placeholder="起始時間" style="width: 100px">
		<span style="position: absolute; left: 51px; z-index: 2; font-size: 10px; bottom: -7px;">12:00:00</span>
		<input class="form-control" name="search_edate" placeholder="結束時間" style="width: 100px">
		<span style="position: absolute; left: 153px; z-index: 2; font-size: 10px; bottom: -7px;">11:59:59</span>
		<input class="form-control" name="search" placeholder="帳號" style="width: 100px">
	</div>
</div>
<div class="btn-group toollist">
	<button type="button" class="btn btn-default main">搜尋</button>
	<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		<span class="caret"></span>
		<span class="sr-only">Toggle Dropdown</span>
	</button>
	<ul class="dropdown-menu toollist"></ul>
</div>
<!-- @search_date -->

<!-- @default_uid -->
<span class="small">{bet_date}<br>{report_uid}</span>
<!-- @default_uid -->

<!-- @default_info -->
<span class="small">{account}<br>{game_type}</span>
<!-- @default_info -->

<!-- @allbet_bet_content -->
<span class="small">{bet_type}<br>{game_result}</span>
<!-- @allbet_bet_content -->

<!-- @salon_bet_content -->
<span class="small">{game_result}</span>
<!-- @salon_bet_content -->

<!-- @supersport_uid -->
<span class="small">{bet_date}<br>{set_date}<br>{report_uid}</span>
<!-- @supersport_uid -->

<!-- @supersport_bet_content -->
<span class="small">
{league}-{game_type}<br>
{bets}
</span>
<!-- @supersport_bet_content -->

<!-- @ninetynine_bet_content -->
<span class="small">{play}<br>{content}</span>
<!-- @ninetynine_bet_content -->

<!-- @globebet_bet_content -->
<span class="small">期號:{drawno}<br>玩法:{play}<br>下注項目:{content}</span>
<!-- @globebet_bet_content -->

<!-- @ss_vs_partial -->
{visit_team}
<span style="color: red">{chum_num}</span>
<span style="color: grey">VS</span>
<span style="color: blue">{main_team}</span> (主)
<span style="color: red">{chum_num2}</span>
<br>
<span style="color: red">{note}</span>
[{score2}:{score1}]<br>
<!-- @ss_vs_partial -->

<!-- @ss_note_partial -->
{main_team}<br>
<span style="color: red">{note}</span>
[{score1}]<br>
<!-- @ss_note_partial -->

<!-- @ss_status_partial -->
<span style="color: red">
	{status}
</span>
<!-- @ss_status_partial -->

<!-- @numbers -->
<span class="hidden-lg hidden-md hidden-sm small">{bet_amount}</span>
<span class="hidden-lg hidden-md hidden-sm small">{set_amount}</span>
<span class="{class} small">{win_amount}</span>
<span class="hidden-xs small" style="color: lightgray;">{hwin_amount}</span>
<!-- @numbers -->

<!-- @col-hid-amt -->
<span class="small">{num}<span style="color: lightgray;">{hid-amt}</span></span>
<!-- @col-hid-amt -->

<!-- @report-all-win-detail -->
<tr>
	<td class="td1"><span>{game}: </span></td>
	<td class="td2"><span class="{class}">{num}<span>{hid-amt}</span></span></td>
</tr>
<!-- @report-all-win-detail -->

<!-- @report-all-style -->
<style>
	.table-report-all .td1, .table-report-all .td2 {
		border: none; 
		min-width: 60px; 
		padding: 0;
	}
	.table-report-all .td2 {
		text-align: right; 
	}
	.td1>span, .td2>span {
		font-size: x-small;
	}
	.td2>span>span {
		color: lightgray;
	}
</style>
<!-- @report-all-style -->

<!-- @report-sums -->
<script> //used in report-all, report-agent, report-order
	$('#{unique_id}_tree_view_complete').change(function () {
		var list = $('#{unique_id}_panel').find('.datalist').not('.hidden'); // all visible td
		var cols = {cols}; //arr, columns that need sum
		var fix = {fix}; //int, round decimal place
		var html = $('#{unique_id}_panel').find('.table-alter:first()').clone(); //the table header
		html.find('th').css('visibility', 'hidden'); //hide all columns, make needed ones visible later

		cols.forEach(function (e) {
			var s = calc_sum(e);
			html.find('th[name=' + e + ']').html(s).css('visibility', 'visible').attr('style', 'border:none;');
		}, this);

		$('#{unique_id}_panel').find('.info').html(html);


		//extract the value of a cell
		function aggregate(num, cell) {
			var cell_val = parseFloat(cell.text().replace(/,/g, ''));
			if(isNaN(cell_val)) cell_val = parseFloat(cell.find('span:nth-child(3)').text().replace(/,/g, '')); //this is for report-agent where the third col has a combined value
			return num + cell_val;
		}

		//loop through a column and calculate the sum value
		function calc_sum(col) {
			var sum = 0;
			list.find('td[name=' + col + ']').each(function () {
				sum = aggregate(sum, $(this));
			});
			var txt = sum.toFixed(fix).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
			return '<span class="' + get_text_color(sum) + '">' + txt + '</span>';
		}

		function get_text_color(num) {
			return (num > 0) ? 'text-success' : 'text-danger';
		}
	});
</script>
<!-- @report-sums -->
