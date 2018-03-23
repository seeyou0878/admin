<!-- @default_order_uid -->
<span class="small">{type_id}</span><br>{order_uid}<br>{cdate}
<!-- @default_order_uid -->

<!-- @store_src -->
<span class="small">{src_id}<br>繳費代碼: {src_text}<br>{src_status}</span>
<!-- @store_src -->

<!-- @store_tar -->
<span class="small">{tar_id}{tar_text}<br>{tar_status}<br>餘額: {tar_value}</span>
<!-- @store_tar -->

<!-- @bonus_src -->
<span class="small">{src_id}<br>說明: {src_text}</span>
<!-- @bonus_src -->

<!-- @bonus_tar -->
<span class="small">{tar_id}{tar_text}<br>{tar_status}<br>餘額: {tar_value}</span>
<!-- @bonus_tar -->

<!-- @transfer_src -->
<span class="small">{src_id}{src_text}<br>{src_status}<br>餘額: {src_value}</span>
<!-- @transfer_src -->

<!-- @transfer_tar -->
<span class="small">{tar_id}{tar_text}<br>{tar_status}<br>餘額: {tar_value}</span>
<!-- @transfer_tar -->

<!-- @withdraw_total -->
<div style="direction: rtl">
<table class="table small" style="background-color: transparent; margin: 0; direction: ltr"><tr>
<td style="border: none; min-width: 60px">原金額<br>手續費<br>實際額<br></td>
<td style="border: none; min-width: 60px; text-align: right">{total}<br>{extra}<br>{actual}</td>
</tr></table>
</div>
<!-- @withdraw_total -->

<!-- @withdraw_src -->
<span class="small">{src_id}{src_text}<br>{src_status}<br>餘額: {src_value}</span>
<!-- @withdraw_src -->

<!-- @withdraw_tar -->
<span class="small">{tar_id}<br>{tar_text}</span>
<!-- @withdraw_tar -->

<!-- @member -->
<span class="small">
<span title="代理: {agent_account}({agent_name})">代理: {agent_account}({agent_name})</span><br>
<span title="會員: {member_account}({member_name})">會員: <a href="#" onclick="$('#main').loadTab('', {m: 'order_integrate', search: '{member_account}'}, '整合查詢')"> {member_account}({member_name})</a></span><i class="fa fa-exclamation-triangle text-danger {icon}"></i><br>
<span class="text-danger" title="{remark}">{remark}</span><br>
</span>
<!-- @member -->

<!-- @status -->
<span class="small">
<span class="label {class}">{status_id}</span><br>{last}<br>{udate}
</span>
<!-- @status -->

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
.ckb-hidden{
	visibility: hidden;
}
</style>
<script>
$(document).off('click', '.main');
$(document).on('click', '.main', function () {
	var uid = $(this).closest('.panel').attr('id').split('_')[0];
	$('#' + uid + '_search_area').find('table.review').trigger('refresh', { type: 'review' });
});
$('#{unique_id}_panel').find('table.review').on('refresh', function () {
	var f = $('#{unique_id}_panel');
	var s = $('#{unique_id}_search_area');
	var pdata = {};
	pdata['data'] = s.serialize();

	f.find('.td-cred').text('--');
	$.ajax({
		url: '{url}',
		type: 'POST',
		data: { jdata: JSON.stringify({ pdata: pdata, method: 'exec' }) },

		success: function (re) {
			var jdata = JSON.parse(re);
			if (jdata.code) {
				// fail
			} else {
				var d = jdata.data;

				//put data to display
				function format(str) {
					var rtn = parseInt(str).toLocaleString();
					return (isNaN(str) || null == str) ? '--' : '$' + rtn;
				}
				for (var i in d) {
					f.find('.store').text(format(d['store_tday']));
					f.find('.withdraw').text(format(d['withdraw_tday']));
					f.find('.bonus').text(format(d['bonus_tday']));
				}
			}

			customAlert(jdata);
		}
	});
});

$(function(){
	var search = $('{search}');
	var credits = $('{credits}');
	var f = $('#{unique_id}_panel');
	var g = $('#{unique_id}_search_group');
	
	f.find('.btn-group.toollist').before(search);
	f.find('.btn-group.toollist').after(credits);

	f.find('.toollist .main').click(function(){
		f.find('table.review').trigger('refresh', { type: 'review' });
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
	<div class="input-group form-inline">
		<input class="form-control" name="search_sdate" placeholder="起始時間" style="width: 100px">
		<span style="position: absolute; left: 51px; z-index: 2; font-size: 10px; bottom: -7px;">12:00:00</span>
		<input class="form-control" name="search_edate" placeholder="結束時間" style="width: 100px">
		<span style="position: absolute; left: 153px; z-index: 2; font-size: 10px; bottom: -7px;">11:59:59</span>
		<input class="form-control" name="search_account" placeholder="帳號" title="請輸入完整帳號，不支援模糊搜尋" style="width: 100px" value="{search}">

		<div class="input-group">
			<select class="form-control" name="search_status" title="訂單狀態">
				<option value="" disabled>狀態</option>
				<option value="">全部</option>
				<option value="1">待處理</option>
				<option value="2" selected>已處理</option>
				<option value="3">未完成</option>
				<option value="4">取消</option>
			</select>
		</div>

		<div class="input-group">
			<select class="form-control" name="search_type" title="訂單類型">
				<option value="" disabled selected>類型</option>
				<option value="">全部</option>
				<option value="1">儲值</option>
				<option value="2">轉移</option>
				<option value="3">提領</option>
				<option value="4">紅利</option>
			</select>
		</div>
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

<div class="btn-group">
	<table class="table table-striped table-bordered integrate-tcreds">
		<tr>
			<td>儲值</td>
			<td>提領</td>
			<td>紅利</td>
		</tr>
		<tr>
			<td class="td-cred store">--</td>
			<td class="td-cred withdraw">--</td>
			<td class="td-cred bonus">--</td>
		</tr>
	</table>
</div>
<style>
	.integrate-tcreds{
		margin: 3px;
	    font-size: small;
	    text-align: center;
	}
	.integrate-tcreds.table>tbody>tr>td{
		padding: 0 5px;
		width: 140px;
	}
	.td-cred{
		font-weight: bold;
		color: #5898d0;
	}
</style>
<!-- @search_date -->
