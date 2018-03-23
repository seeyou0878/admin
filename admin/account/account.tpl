<!-- @credits -->
<script>
$(function(){
	//query button
	var exec = $('<button class="col-xs-12 col-sm-2 btn btn-danger buttonLoading hidden-create" style="margin: 1px; vertical-align: top;">查詢會員點數</button>');
	//modal
	var m = $('#{unique_id}_Modal');

	//on modal open
	$('#{unique_id}_target_id').change(function(){
		//reset modal
		m.find('.td_col').html('--');
		m.find('.bc').remove();

		//level
		var id = $('#{unique_id}_target_id').val();
		$.ajax({
			url: '{url}',
			type: 'POST',
			data: { jdata: JSON.stringify({ pdata: {data: {id: id}}, method: 'get_level' }) },
			success: function(re){
				var jdata = JSON.parse(re);
				if(jdata.code){
					// fail
				}else{
					var d = jdata.data;
					$(exec).after($(d));
				}
				customAlert(jdata);
			}
		});
	});

	//display table
	var table = $('{table}');

	//append button & table to header
	m.find('#{unique_id}_home').prepend(exec);
	$(exec).after(table);

	//btn click
	$(exec).click(function(){
		var id = $('#{unique_id}_target_id').val();
		//disable button
		m.find('.btn').prop('disabled', true);

		//credit query
		m.find('.td_col').html('<i class="fa fa-spinner fa-pulse fa-1x fa-fw"></i>');
		$.ajax({
			url: '{url}',
			type: 'POST',
			data: { jdata: JSON.stringify({ pdata: {data: {id: id}}, method: 'exec' }) },
			success: function(re){
				var jdata = JSON.parse(re);
				if(jdata.code){
					// fail
				}else{
					var d = jdata.data;
					m.find('.td_col').html('');

					//put data to display
					for(var i in d){
						m.find('#' + i + '_col').text(d[i]);
					}

					//reset button
					m.find('.btn').prop('disabled', false);
				}
				customAlert(jdata);
			}
		});

	});
});
</script>
<!-- @credits -->

<!-- @table -->
<table class="table table-bordered table-striped table-credits hidden-create" style="text-align: center; margin-bottom: 5px;">
<tr>
	<!-- @th -->
	<td>{text}</td>
	<!-- @th -->
</tr>
<tr>
	<!-- @td -->
	<td class="td_col" id="{game}_col">--</td>
	<!-- @td -->
</tr>
</table>
<table class="table table-bordered table-striped table-credits hidden-create" style="text-align: center; margin-bottom: 0;">
<tr>
	<td>歷史儲值</td>
	<td>當日儲值</td>
	<td>歷史提領</td>
	<td>當日提領</td>
	<td>歷史紅利</td>
	<td>當日紅利</td>
</tr>
<tr>
	<td class="td_col" id="store_col"></td>
	<td class="td_col" id="store_tday_col"></td>
	<td class="td_col" id="withdraw_col"></td>
	<td class="td_col" id="withdraw_tday_col"></td>
	<td class="td_col" id="bonus_col"></td>
	<td class="td_col" id="bonus_tday_col"></td>
</tr>
</table>
<!-- @table -->

<!-- @level -->
<ul class="well bc hidden-create" style="margin: 0 10px 10px 10px; font-size: small;">
	<!-- @li -->
	<li class="bcli"><span>{level}<br>{name}</span></li>
	<li class="arrow"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></li>
	<!-- @li -->
</ul>
<style>
	.bcli {
	    display: inline-block;
	    text-align: center;
	    vertical-align: middle;
	    text-overflow: ellipsis;
	    overflow: hidden;
    	width: 61px;
	}
	.bc {
	    padding: 0px;
	    display: inline-block;
	}
	.arrow:last-child {
	    display: none;
	}
	.arrow{
	    display: inline-block;
	    text-align: center;
	    vertical-align: middle;
	}
</style>
<!-- @level -->

<!-- @search-ip -->
<script>
	$(document).off('click', '[bind-search]');
	$(document).on('click', '[bind-search]', function(){
	var uid = $(this).closest('.panel').attr('id').split('_')[0];
	$('#' + uid + '_search_area').find('[name=search]').val($(this).attr('bind-search')).trigger('input');
});
</script>
<!-- @search-ip -->