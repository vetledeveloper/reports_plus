<style type="text/css">
.inline_tabs .inline_tabs_content_wrapper {
	min-width: 590px !important;
}
</style>


{if $smarty.const.IS_AC_GREATER_THAN_33}
    {AngieApplication::useWidget('ui_date_picker' , 'environment')}
{/if}

<div id="reports_plus_leader_board" class="reports_plus_leader_board">
		<div class="people_select_date_range">
			<div class="load_controls">
				<button id="people_button_load_reports" class="button_common" type="button">{lang}Show{/lang}</button>
			</div>
			<div class="people_div_round_controls">
				<div id="people_date_range_start" class="date_range_start"><input class="input_text" autocomplete="off" /></div>
				<div class="people_div_inner_text">{lang}to{/lang}</div>
				<div id="people_date_range_end" class="date_range_end"><input class="input_text" autocomplete="off" /></div>
			</div>
		</div>
 </div>

<script type="text/javascript">
		if(!$(".jqplot-event-canvas").get(0)) {
			var start = ($('#people_date_range_start input').val() == undefined ) ? '' : $('#people_date_range_start input').val();
			var end = ($('#people_date_range_end input').val() == undefined )? '' : $('#people_date_range_end input').val();
			var leader_board_url = '{$leader_board_url}';
		 	$.ajax({
			   'url' : App.extendUrl( leader_board_url, { 'async' : 1 ,  'start' : start, 'end' : end, 'load' : 1 }),
		       'type' : 'get',
		       'success' : function(response) {
		 			$('#reports_plus_leader_board').empty();
					$('#reports_plus_leader_board').append(response.scalar);
			  }, 
		      'error' : function() {
		        App.Wireframe.Flash.error('No data found');
		      }
	  		});
		}

	 	var getPeopleData = function () {
			var start_date = $('#people_date_range_start input').val();
			var end_date = $('#people_date_range_end input').val();
			 $.ajax({
				   'url' : App.extendUrl( leader_board_url, { 'async' : 1 ,  'start' : start_date, 'end' : end_date, 'load' : 1 }),
		           'type' : 'get',
		           'success' : function(response) {
						//$('.main_container').html(response.scalar);
						$('#reports_plus_leader_board').empty();
						$('#reports_plus_leader_board').append(response.scalar);
				  }, 
		          'error' : function() {
		            App.Wireframe.Flash.error('No data found');
		          }
			  });
		};
		
		$('#people_button_load_reports').live('click', function () {
			getPeopleData();
		});
</script>




