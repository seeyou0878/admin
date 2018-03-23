<!-- @script -->
<script>
$(function(){
	var member_id = $('#{unique_id}_search_adv').val();
	var exec = $('{li}');
	$('#{unique_id}_panel').find('ul.toollist').append(exec);
	$('[game]').click(function(){
		var game_id = $(this).attr('game');
		
		if(confirm('{confirm}')){
			$.ajax({
				url: '{url}',
				type: 'POST',
				data: { jdata: JSON.stringify({ pdata: {data: {id: member_id, game_id: game_id}}, method: 'exec' }) },
				success: function(re){
					var jdata = JSON.parse(re);
					if(jdata.code){
						// fail
					}else{
						$('#{unique_id}_panel').find('table.review').trigger('refresh',{type: 'modify', id: jdata['data']});
					}
					customAlert(jdata);
				}
			});
		}
	});
});
</script>
<!-- @script -->

<!-- @li --><li><a href="#" game="{id}">{name}</a></li><!-- @li -->