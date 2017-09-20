<div class="people_main_container">
	<div class="people_select_date_range">
		<div class="load_controls">
			<button id="people_button_load_reports" class="button_common" type="button">{lang}Show{/lang}</button>
		</div>
		<div class="people_div_round_controls">
			<div id="people_date_range_start" class="date_range_start"><input class="input_text" autocomplete="off" /></div>
			<div class="people_div_inner_text">{lang}to{/lang}</div>
			<div id="people_date_range_end" class="date_range_end"><input class="input_text" autocomplete="off" /></div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="main_container">
		<div id="person_stats_container" class="person_stats"></div>
		<div class="people_reports">
			<div id="projects_details_container" class="projects_details"></div>
			<div class="charts_container">
				<div id="projects_charts_container" class="projects_charts_container">
					<div class="assignments_chart_container">
						<div id="assignments_donut_chart" class="leader_board_charts jqplot-target"></div>
						<div id="people_assignments_legend" class="people_assignments_legend"></div>
					</div>
					<div class="hours_chart_container">
						<div id="hours_donut_chart" class="leader_board_charts jqplot-target"></div>
						<div id="people_hours_legend" class="people_hours_legend"></div>
					</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
</div>


<script type="text/javascript">

	var api_url = '{$api_url}';
	var load = '{$load}';
	var projects_hours = {$hours|json nofilter};		
	var projects_item_assigned = {$items_assigned|json nofilter};
	var date_format = {$date_format|json nofilter};
	var people_reports_legend_labels = {$people_reports_legend_labels|json nofilter};
	
	//Changes here..
	var person_summary = {$person_summary|json nofilter};
	var project_responsibilities = {$project_responsibilities|json nofilter};
	var project_activities = {$project_activities|json nofilter};

	var hours_donut_series_colors = new Array();
	var assignments_donut_series_colors = new Array();
	var projects_label_color_map = {$projects_label_color_mapping|json nofilter};
	
	var leaderboard_start_date = {$start_date|json nofilter};
	var leaderboard_end_date = {$end_date|json nofilter};
	
	//Generate series-color for hours..
	$.each(projects_hours, function(label, value) {
		hours_donut_series_colors.push(projects_label_color_map[value[0]]);
	});

	//Generate series-color for hours..
	$.each(projects_item_assigned, function(label, value) {
		assignments_donut_series_colors.push(projects_label_color_map[value[0]]);
	});
	
	
	/**********CREATING PERSON STATS****/
	
	var person_stats_html = '';
	person_stats_html += '<div id="person_summary" class="div_round_controls people_project_summary">';
	
	$.each(person_summary, function(label, value) {
		person_stats_html += '<div class="project_objects_cell"><div class="project_objects_value">' + value + '</div><div class="project_objects_label">' + App.lang(label) + '</div></div>';
	});
	
	person_stats_html += '</div>';
	/**********CREATING PERSON STATS - END****/
	
	
	/**********CREATING PROJECT RESPONSIBILITIES / ACTIVITIES*******/
	var projects_details_html = '';
	
	//Responsibilities..
	projects_details_html += '<div><div class="text_font_bold">' + App.lang('Responsibilities') + '</div><div class="div_padding_right"><table class="project_responsibilities projects_object_labels" >';

	$.each(project_responsibilities, function(label, value) {
		projects_details_html += '<tr><td class="text_font_bold td_width">' + value + '</td><td>' + App.lang(label) + '</td></tr>';
	});
			
	projects_details_html += '</table></div></div>';
	
	//Activities..
	projects_details_html += '<div><div class="text_font_bold">' + App.lang('Activities') + '</div><div class="div_padding_right"><table class="project_activities projects_object_labels">';

	$.each(project_activities, function(label, value) {
		projects_details_html += '<tr><td class="text_font_bold td_width">' + value + '</td><td>' + App.lang(label) + '</td></tr>';
	});	
	
	projects_details_html += '</table></div></div>';
	
		
	/**********CREATING PROJECT RESPONSIBILITIES / ACTIVITIES - END****/
	
	if(date_format == 'MM/DD/YY'){
		var date_options_date_format = "mm/dd/y";
	}else{
		var date_options_date_format = "dd/mm/y";
	}

	//Function to draw the charts(assignments and hours chart)
	var people_reports = function(projects_hours, projects_item_assigned, people_reports_legend_labels, start_date, end_date) {

		var date_today = new Date();
		var date_options = {
	        	dateFormat : date_options_date_format,
	      		showAnim : "blind",
	      		duration : 0,
	      		changeYear: true,
	  			showOn: "both",
	      		buttonImage: App.Wireframe.Utils.imageUrl('icons/16x16/calendar.png', 'system'),
	  			buttonImageOnly: true,
	            buttonText : App.lang('Select Date'),
	      		changeYear: true,
	      		hideIfNoPrevNext : true,
	  			yearRange: "2000:2050"
	    };

		$('#projects_details_container').empty();
		$('#projects_details_container').append(projects_details_html);
		
		$('#person_stats_container').empty();
		$('#person_stats_container').append(person_stats_html);

		
		$('#people_date_range_start input').datepicker(date_options);
		$('#people_date_range_end input').datepicker(date_options);
		
		//Change the datepicker icons. Make it gray-scale..
		$('#people_date_range_start img').attr("src", App.Wireframe.Utils.assetUrl('icon/calendar_grayscale.png','reports_plus', 'images'));
		$('#people_date_range_end img').attr("src", App.Wireframe.Utils.assetUrl('icon/calendar_grayscale.png','reports_plus', 'images'));
		/*END*/
		
		var start_date_to_string = leaderboard_start_date.replace(/-/g, ", ");
		var end_date_to_string = leaderboard_end_date.replace(/-/g, ", ");
		
		var reports_start_date = new Date(start_date_to_string);
		var reports_end_date = new Date(end_date_to_string);
		
		$('#people_date_range_end input').datepicker('setDate', reports_end_date);
		date_today.setMonth(date_today.getMonth()-3);
		$('#people_date_range_start input').datepicker('setDate', reports_start_date);
		
		//Adding dummy values, so that jqplot doesn't respond with an error.
		//Bcoz we are passing a blank array, if there are no values..
		if( (projects_item_assigned == null) || (projects_item_assigned.length == 0))	 {
			projects_item_assigned = [["dummy", 0, 0]];
		}
		

		var assignments_chart = $.jqplot('assignments_donut_chart', [projects_item_assigned], {
		  	title: App.lang('Assignments'),
		  	grid: { backgroundColor: 'transparent', drawBorder: false, shadow: false},
		  	series: [{ shadow: false, startAngle: -90, padding: 0}],
		  	seriesColors: assignments_donut_series_colors,
		    seriesDefaults:{
		      renderer:$.jqplot.DonutRenderer ,
		      rendererOptions:{
		        	innerDiameter: 48
		      }
	    	}
		});

		$("#assignments_donut_chart").bind('jqplotDataHighlight', 
			function(ev, seriesIndex, pointIndex, data) {
				var mouseX = (ev.pageX) + 1 ;
	            var mouseY = (ev.pageY) + 1 ;
            	var borderColor = assignments_donut_series_colors[pointIndex];
	            	
				var tool_tip_div = '<div class="tooltip_div">';
	            	tool_tip_div += '<div class="tooltip_div tooltip_font_bold">';
	                tool_tip_div += data[0] + ': ' + data[2] + '%';
	            	tool_tip_div += '</div>';
	            	tool_tip_div += '<div class="tooltip_div">';
	                tool_tip_div += '(' + data[1] + ')';
	            	tool_tip_div += '</div>';
	        		tool_tip_div += '</div>';
	        		
	        	$('#people_reports_assignments_chart_tooltip').remove();
	  	        $('#assignments_donut_chart').append('<div id="people_reports_assignments_chart_tooltip" class="tooltip_main_container"></div>');
	  	        $('#people_reports_assignments_chart_tooltip').html(tool_tip_div);
	  	        var cssObj = {
	  	              'left' : mouseX + 'px', //usually needs more offset here
	  	              'top' : mouseY + 'px',
	  	              'border' : '1px solid ' + borderColor,
	  	              'font-size': '7pt'
	  	        };
	  	        $('#people_reports_assignments_chart_tooltip').css(cssObj);
	  	});

		$("#assignments_donut_chart").bind('jqplotDataUnhighlight', function(ev) {
			$('#people_reports_assignments_chart_tooltip').empty().hide();
 		});

		
		//Adding dummy values, so that jqplot doesn't respond with an error.
		//Bcoz we are passing a blank array, if there are no values..
		if( (projects_hours == null) || (projects_hours.length == 0)) {
			projects_hours = [["dummy", 0, 0]];
		}

		
		//People Hours Chart
		var hours_chart = $.jqplot('hours_donut_chart', [projects_hours], {
			animate: true,
			animateReplot: true,
		  	title: App.lang('Hours'),
		  	grid: { backgroundColor: 'transparent', drawBorder: false, shadow: false},
		  	series: [{ shadow: false, startAngle: -90, padding: 0}],
		  	seriesColors: hours_donut_series_colors,
		    seriesDefaults:{
		      renderer:$.jqplot.DonutRenderer,
		      rendererOptions:{
		        	innerDiameter: 48
		      }
		    }
		});
		$("#hours_donut_chart").bind('jqplotDataHighlight', 
			function(ev, seriesIndex, pointIndex, data) {
				var mouseX = (ev.pageX) + 1  ;
	            var mouseY = (ev.pageY) + 1 ;
	            var borderColor = hours_donut_series_colors[pointIndex];;
	            		
				var tool_tip_div = '<div class="tooltip_div">';
	            	tool_tip_div += '<div class="tooltip_div tooltip_font_bold">';
	                tool_tip_div += data[0] + ': ' + data[2] + '%';
	            	tool_tip_div += '</div>';
	            	tool_tip_div += '<div class="tooltip_div">';
	                tool_tip_div += '(' + data[1] + ')';
	            	tool_tip_div += '</div>';
	        		tool_tip_div += '</div>';
	        		
	        	$('#people_reports_hours_chart_tooltip').remove();
	  	        $('#hours_donut_chart').append('<div id="people_reports_hours_chart_tooltip" class="tooltip_main_container"></div>');
	  	        $('#people_reports_hours_chart_tooltip').html(tool_tip_div);
	  	        var cssObj = {
	  	              'left' : mouseX + 'px', //usually needs more offset here
	  	              'top' : mouseY + 'px',
	  	              'border' : '1px solid ' + borderColor,
	  	              'font-size': '7pt'
	  	        };
	  	        $('#people_reports_hours_chart_tooltip').css(cssObj);
	  	});
	  	
		$("#hours_donut_chart").bind('jqplotDataUnhighlight', function(ev) {
			$('#people_reports_hours_chart_tooltip').empty().hide();
 		}); 
		//Display the legends..
 		var label = legend_html = '';
 		legend_html_even = legend_html_odd = '<ul>';

		// finding length of data object, For old browsers
 		var color_map_length=0;
 		$.each(projects_label_color_map, function(k, v) { color_map_length++; });
        if(projects_label_color_map != null) {
 			if (color_map_length > 0) {
 	 			var i = 0;
 				$.each(projects_label_color_map, function(label, color) {
 	 				label_name = ( label.length > 30 ) ? label.substr(0,25) + '...' : label;
 	 				if(i%2 == 0){
            			legend_html_even += '<li class="legend_list_item"> <span class="people_reports_legend_bullet" style="background-color:' + color + '"></span><span class="people_reports_legend_label">' + label_name + '</span>';
 	 				}else{
 	 					legend_html_odd += '<li class="legend_list_item"> <span class="people_reports_legend_bullet" style="background-color:' + color + '"></span><span class="people_reports_legend_label">' + label_name + '</span> ';
            		}
            		i++;
        		});// each
 			}
        }
        legend_html_even += '</ul>';
        legend_html_odd += '</ul>';

		$('#people_assignments_legend').append(legend_html_even);
		$('#people_hours_legend').append(legend_html_odd);
        
	};
	
	$(document).ready(function(){
		people_reports(projects_hours, projects_item_assigned, projects_label_color_map);
	});
	
	if(!$(".jqplot-event-canvas").get(0)) {
		getPeopleData();
	}
	
</script>