<script type="text/javascript" language="javascript">
    
var what = 'getProjectStats';
var search_by = 'project';
var at_glance_ids = '';
var start = '';
var end = '';
var	search_by_values = { 'projects' : 'project', 'project_groups'  : 'category', 'companies' : 'company' };
var labels_data = reports_enum.labels;


var project_tab = function () {
		
		var all_projects = projects_data[0]['data'];
		var reports_filter_types = reports_enum.filter_types.projects;
		var date_range_list = reports_enum.dates.projects;
		var date_today = new Date();
			
		var	project_tab_filters_html = '<div class="load_controls">';
			project_tab_filters_html += 	'<button id="button_load_projects" class="button_common" type="button">' + App.lang('Show') + '</button>';
			project_tab_filters_html += 	'<button id="button_previous_projects" class="button_common button_previous" type="button"><img src="'+ App.Wireframe.Utils.assetUrl('icon/prev.gif','reports_plus', 'images') + '" alt="" class="button_common_icon"></button>';
			project_tab_filters_html += 	'<button id="button_next_projects" class="button_common button_next" type="button"><img src="'+ App.Wireframe.Utils.assetUrl('icon/next.gif','reports_plus', 'images') + '" alt="" class="button_common_icon"></button>';
			project_tab_filters_html += '</div>';

			project_tab_filters_html += '<div class="select_date_range">';
			project_tab_filters_html += '<div class="div_round_controls">'; 
			project_tab_filters_html += 	'<div id="date_range_start" class="date_range_start"><input class="input_text" autocomplete="off" /></div>';
			project_tab_filters_html += 	'<div class="div_inner_text">' + App.lang('to') + '</div>';
			project_tab_filters_html += 	'<div id="date_range_end" class="date_range_end"><input class="input_text" autocomplete="off" /></div>';
			project_tab_filters_html += 	'<div id="date_range_list_container" title="' + App.lang('Date Range') + '"><select id="date_range_list"></select></div>';
			project_tab_filters_html += '</div>';
			project_tab_filters_html += '</div>';

			project_tab_filters_html += '<div class="select_reports_filters">';
			project_tab_filters_html += '<div class="div_round_controls">';
			project_tab_filters_html += 	'<div id="filter_type_container" class="filter_type_container custom_select_container"><select id="reports_filter_type" class="custom_select"></select></div>';
			project_tab_filters_html += 	'<div id="filter_list_container" class="filter_list_container custom_select_container"><select id="reports_filter_list" class="custom_select"></select></div>';
			project_tab_filters_html += '</div>';
			project_tab_filters_html += '</div>';

		$('#filter_control_container').empty();
		$('#filter_control_container').append(project_tab_filters_html);
		
		$('#date_range_start input').datepicker(date_options);
		$('#date_range_end input').datepicker(date_options);
		
		//Change the datepicker icons. Make it gray-scale..
		$('#date_range_start img').attr("src", App.Wireframe.Utils.assetUrl('icon/calendar_grayscale.png','reports_plus', 'images'));
		$('#date_range_end img').attr("src", App.Wireframe.Utils.assetUrl('icon/calendar_grayscale.png','reports_plus', 'images'));
		/*END*/
		
		$('#date_range_end input').datepicker('setDate', date_today);
		date_today.setMonth(date_today.getMonth()-3);
		$('#date_range_start input').datepicker('setDate', date_today);
		
		var project_tab_html =  '<div id="reports_plus_reports_container"  class="reports_plus_reports_container">';

			project_tab_html +=	'<div class="reports_plus_projects_details">';
			project_tab_html +=		'<div id="projects_details_velocity_data_container" class="projects_details_velocity_data_container"></div>';
			project_tab_html +=		'<div id="projects_task_in_progress" class="projects_task_in_progress"></div>';
			project_tab_html +=		'<div id="projects_at_glance" class="projects_at_glance"></div>';
			project_tab_html +=	'</div>';
		
			project_tab_html +=	'<div class="reports_plus_charts_container" >';
			project_tab_html +=	'<div class="projects_chart_velocity">';
			project_tab_html +=		'<div class="header_style">' + App.lang("Burn Down") + '<span class="tooltip_date"></span></div>';
			project_tab_html +=		'<div id="reports_burn_down_chart_tooltip" class="tooltip_main_container"></div>';
			project_tab_html +=		'<div id="reports_burn_down_chart" class="display_charts"></div>';
			// project_tab_html +=		'<div id="reports_burn_down_chart_legend" class="reports_chart_legend"></div>';
			project_tab_html +=		'<div id="reports_burn_down_chart_axis" class="reports_chart_axis"></div>';
			project_tab_html +=	'</div>';
			project_tab_html +=	'<div class="projects_chart_hours">';
			project_tab_html +=		'<div class="header_style">' + App.lang("Hours") + '<span class="tooltip_date"></span></div>';
			project_tab_html +=		'<div id="reports_hours_chart_tooltip" class="tooltip_main_container"></div>';
			project_tab_html +=		'<div id="reports_hours_chart" class="display_charts"></div>';
			// project_tab_html +=		'<div id="reports_hours_chart_legend" class="reports_chart_legend"></div>';
			project_tab_html +=		'<div id="reports_hours_chart_axis" class="reports_chart_axis"></div>';
			project_tab_html +=	'</div>';
			project_tab_html +=		'<div id="projects_details_most_active" class="projects_details_most_active"></div>';
			project_tab_html +=		'<div id="projects_details_grid" class="projects_details_grid"></div>';
			project_tab_html +=	'</div>';
			
			project_tab_html +=	'</div>';

		$('#reports_tpl_container').empty();
		$('#reports_tpl_container').append(project_tab_html);
		for (i=0; i < reports_filter_types.length; i++){
			// If the user dont have permission to see companies, skip it.
			if(reports_filter_types[i]['data'] == 'companies' && $.inArray('companies', available_reports_tabs) == -1){
				continue;
			}
			$('<option/>').val(reports_filter_types[i]['data']).html(reports_filter_types[i]['label']).appendTo('#reports_filter_type');
		}
		
		for (i=0; i < projects_data.length; i++){
			if(projects_data[i]['status'] == 'completed'){
				continue;
			}
			$('<option/>').val(projects_data[i]['data']).html(projects_data[i]['label']).appendTo('#reports_filter_list');
		}
		for (i=0; i< date_range_list.length; i++){
			$('<option/>').val(date_range_list[i]['data']).html(date_range_list[i]['label']).appendTo('#date_range_list');
		}

		$('#date_range_list option[value="these3months"]').attr('selected','selected');

		 $.ajax({
			'url' : App.extendUrl( api_url, { 'async' : 1, 'what' : what, 'projects' : all_projects }),
                        'type' : 'get', 
                        'success' : function(response) {

                                       if(response.data.primary.iterations.length > 0){
                                              project_stats_panel(response.data);
                                              burn_down_chart(response.data.primary.iterations);
                                              hours_summary_chart(response.data.primary.iterations);
                                       }else{
                                              App.Wireframe.Flash.error('No Data Found');
                                       }
                                }, 
                        'error' : function() {
                          App.Wireframe.Flash.error('No Data Found');
                        }
                        });

		get_company_data(all_projects, '', 'project');

		 // Project : Load Button  
		 $('#button_load_projects').click(function(){
			 get_project_data(all_projects);
		 });

		 $('#button_next_projects').click(function(){
			 $('#reports_filter_list option:selected').next('option').attr('selected', 'selected');
			 	get_project_data(all_projects);
		});
		 
		$('#button_previous_projects').click(function(){
			$('#reports_filter_list option:selected').prev('option').attr('selected', 'selected');
				get_project_data(all_projects);
		});

		$('#reports_filter_type').on('change',function(){
			var reports_filter_list = [];
			search_by = search_by_values[$(this).val()];

	 		$('#reports_filter_list').empty();
	 		reports_filter_list = reports_enum[$(this).val()];
	 		for (i = 0; i < reports_filter_list.length; i++){
	 			if(reports_filter_list[i]['status'] != undefined && reports_filter_list[i]['status'] == 'completed'){
					continue;
				}
				$('<option/>').val(reports_filter_list[i]['data']).html(reports_filter_list[i]['label']).appendTo('#reports_filter_list');
			}
			// selecting first option of the drop down
			$($('#reports_filter_list option').get(0)).attr('selected','selected');


			// Add Export url Button
			if($(this).val() == 'companies' && export_reports_url != null){
				App.Wireframe.PageTitle.addAction('export', {
                 'url' : App.extendUrl( export_reports_url, { 'status': 'all', 'companies' : $('#reports_filter_list').val() }),
                 'text' : ' ',
                 'icon' : App.Wireframe.Utils.assetUrl('icon/csv_icon.png', 'reports_plus', 'images', 'default'),
                 'onclick' : function () {
                   			 }
			    }, 'reports_export');
			    $('#page_action_export a').attr('title' , App.lang('Export Reports'));
		        $('#page_action_export a').attr('target','_blank');

			}else{
				$('#page_action_export').remove();
			}
		});

		// Changing dates according to the Date Ranges Provided
		$('#date_range_list').change(function(){
			var date_range = reports_enum.dates.projects[$(this).get(0).selectedIndex];
			$('#date_range_start input').datepicker('setDate', $.jsDate.strftime(date_range.start, default_date_format));
			$('#date_range_end input').datepicker('setDate', $.jsDate.strftime(date_range.end, default_date_format));
		});
             
	} //project tab


	//get data for Project tab
	var get_project_data = function (all_projects){
		
//		var what = 'getProjectStats';
		var projects_arr = '';
		var projects_load = '';
		var match_found = true;
		start = $('#date_range_start input').val();
		end = $('#date_range_end input').val();
		
		if($('#reports_filter_type option:selected').val() == 'projects' ){
			projects_load = $('#reports_filter_list option:selected').val();
		}else if ($('#reports_filter_list option:selected').val().length != 0 && $('#reports_filter_list option:selected').val().indexOf(",") == -1){
			if ($('#reports_filter_type option:selected').val() == 'project_groups' ){
				for (i = 0; i < projects_data.length; i++){
					if(projects_data[i]['group_id'] == $('#reports_filter_list option:selected').val() && projects_data[i]['status'] == 'active' ){
						projects_arr += projects_data[i]['data'] + ',';
					}
				}
			}else if ($('#reports_filter_type option:selected').val() == 'companies' ){
				for (i = 0; i < projects_data.length; i++){
					if(projects_data[i]['company_id'] == $('#reports_filter_list option:selected').val() && projects_data[i]['status'] == 'active' ){
						projects_arr += projects_data[i]['data'] + ',';
					}
				}
			}
			if((projects_arr == "")){
				match_found = false;
			}else{
				projects_load =  ( projects_arr.substr(projects_arr.length -1 ,1) == ',') ? projects_arr.substr(0, projects_arr.length -1) : projects_arr ;
				match_found = true;
			}
			}else {
				projects_load = all_projects;
		}
	
		if(match_found){
			 $.ajax({
	            'url' : App.extendUrl( api_url, { 'async' : 1, 'what' : what, 'projects' : projects_load, 'start' : start, 'end' : end }),
	            'type' : 'get', 
	            'success' : function(response) {
	                          if(response.data.primary.iterations.length > 0){
	                                  project_stats_panel(response.data);
	                                  burn_down_chart(response.data.primary.iterations);
	                                  hours_summary_chart(response.data.primary.iterations);
	                          }else{
	                                  App.Wireframe.Flash.error('No Data Found');
	                          }
	                    }, 
	            'error' : function() {
	              App.Wireframe.Flash.error('No Data Found');
	            }
	        });
		}else {
			App.Wireframe.Flash.error('No Match Found');
		}
		at_glance_ids = (search_by == 'company') ? $('#reports_filter_list option:selected').val() : projects_load;

		$('#projects_at_glance').empty();
        $("#projects_details_grid").empty();

		if(($($('#reports_filter_type').get(0)).val() == 'projects' && $('#reports_filter_list').get(0).selectedIndex == 0) ){
			get_company_data(at_glance_ids, '', search_by);
		}
		if($($('#reports_filter_type').get(0)).val() != 'projects'){
			get_company_data(at_glance_ids, '', search_by);
		}
		
		// Change URL of the Print   
		// extend_urls(at_glance_ids, '', search_by);
		// var print_url = App.extendUrl( html_url, {  'print' : 1, 'tab_selected' : tab_selected, 'what' : what, 'projects' : projects_load, 'start' : start, 'end' : end });
		 // $('.reports_print').attr('href', print_url);
		
	
	}//get data for Project tab
	
	//Projects stats
	var project_stats_panel = function (data){
	
		var projects_summary = data.summary;
		var projects_most_active = data.secondary.active;
		// var projects_longest_open = data.secondary.longest;
		var projects_longest_due_on = data.secondary.due_on;
		var task_in_progress = data.secondary.task_in_progress;
		var velocity = data.primary.iterations[data.primary.iterations.length -1]['velocity'];
		var hours_this_week = data.primary.iterations[data.primary.iterations.length -1]['hours_in_iteration'];
		var forecasted = (data.primary.forecast.forecasted == '-1' ) ? App.lang('Never') : $.jsDate.strftime(data.primary.forecast.forecasted, default_date_format);
		var project_name = ( $('#reports_filter_list option:selected').html().length  > 30 ) ? $('#reports_filter_list option:selected').html().substr(0, 30) + '...' : $('#reports_filter_list option:selected').html();
		var projects_most_active_html = '';
		
		var project_stats_html = '<ul class="list">';

			project_stats_html += '<li>';
			project_stats_html += '<div class="project_name_label">';
			project_stats_html += (projects_summary.url != undefined) ? '<a target="_blank" class="quick_view_item" href="' + projects_summary.url + '">' + project_name + '</a>' : project_name ;
			project_stats_html += '</div>';
			project_stats_html += '<div class="clear" /></div>';
			project_stats_html += '</li>';

			project_stats_html += '<li>';
			project_stats_html += '<div class="velocity_forcast_container"><div class="state_value">' + velocity + '</div><div class="state_label">' + App.lang('Velocity') + '</div><div class="clear" /></div></div>';
			project_stats_html += '<div class="hours_forcast_container"><div class="state_value" title="' + hours_this_week + '">' + Math.floor(hours_this_week) + '</div><div class="state_label">' + App.lang('Hours') + '</div><div class="clear" /></div></div>';
			project_stats_html += '<div class="clear" /></div>';
			project_stats_html += '</li>';
			
			project_stats_html += '<li>';
            project_stats_html += '<div class="project_progress_completed_label">' + (projects_summary.total_tasks_count - projects_summary.open_tasks_count) + ' ' + App.lang('of') + ' ' + projects_summary.total_tasks_count + ' ' + App.lang('items done') + '</div>';
			project_stats_html += '<div class="project_progress_bar"><div class="project_percent_progress_bar"><div class="project_percent_bar_chart_container"><div class="project_percent_bar_chart" style="width:' + projects_summary.percent_done + '%"></div></div></div><div class="project_percent_progress_value">' + projects_summary.percent_done + '%</div><div class="clear"></div></div>';
			project_stats_html += '<div class="clear" /></div>';
			project_stats_html += '</li>';

			if(forecasted){
				project_stats_html += '<li>';
				project_stats_html += '<div class="project_fotecasted_date_value">' + forecasted + '</div>';
				project_stats_html += '<div class="project_fotecasted_date_label">' + App.lang('Forecasted Completion Date') + '</div>';
				project_stats_html += '<div class="clear" /></div>';
				project_stats_html += '</li>';
			}

			if (projects_summary.leader_name != undefined){ 
				project_stats_html += '<li>';
	        	// leader_name = (projects_summary.leader_name.indexOf('@') != -1) ? projects_summary.leader_name.slice(1,projects_summary.leader_name.indexOf('@')-1) : projects_summary.leader_name;
				project_stats_html += 	'<div class="project_leader"><img src="'+ App.Wireframe.Utils.assetUrl('icon/assignee_icon.gif','reports_plus', 'images') + '" alt="" class="small_icon"><span class="project_list_value"> ' + projects_summary.leader_name + '</span></div>';			
				project_stats_html += '<div class="clear" /></div>';
				project_stats_html += '</li>';
			}
			if ( projects_summary.next_milestone_days != undefined && projects_summary.next_milestone_days != 'TBD'){
				 	var next_milestone_due_label = '';
				    var next_milestone_days = parseInt(projects_summary.next_milestone_days);
				    if(next_milestone_days == 0) {
				        next_milestone_due_label = 'Today';
				    } else if(next_milestone_days > 0) {
				        next_milestone_due_label = App.lang('due in') + ' ' + next_milestone_days + ' ' + App.lang('days');
				    } else {
				        next_milestone_due_label = (next_milestone_days * -1) + ' ' + App.lang('days late');
				    }

					project_stats_html += '<li>';
					project_stats_html += '<div class="project_next_milestone"><img src="'+ App.Wireframe.Utils.assetUrl('icon/next_milestone_16.png','reports_plus', 'images') + '" alt="" class="small_icon"><span class="milestone_label" title="'+ App.lang('Next:') + ' ' + projects_summary.next_milestone + ','  + next_milestone_due_label + '">' + App.lang('Next:') + ' ' + projects_summary.next_milestone  + '</span></div>';
					project_stats_html += '<div class="clear" /></div>';
					project_stats_html += '</li>';
			}
					project_stats_html += '</ul>';
		
		$('#projects_details_velocity_data_container').html(project_stats_html);
		
		var listHTML = '<ul class="list"><li class="header_style">' + App.lang('Most Active & Open') + '</li>';
		if(projects_most_active){
			$.each(projects_most_active, function(i, item) {
				var item_name = ( item.name.length > 100 ) ? item.name.substr(0,92) + '...' : item.name;
				listHTML += '<li class ="most_active_task_name"><a class="quick_view_item" href="' + item.url + '" >' + item_name  + '</a></li>';
			});
		}else{
			listHTML += '<li class ="no_data_msg">' + App.lang('-- No Data --') + '</li>';
		}
		listHTML += '</ul>';

		listHTML += '<ul class="list"><li class="header_style">' + App.lang('Longest Overdue') + '</li>';
		if(projects_longest_due_on){
			$.each(projects_longest_due_on, function(i, item) {
				var item_name = ( item.name.length > 100 ) ? item.name.substr(0,92) + '...' : item.name;
				listHTML += '<li class ="most_active_task_name"><a class="quick_view_item" href="' + item.url + '" >' + item_name  + '</a></li>';
			});
		}else{
			listHTML += '<li class ="no_data_msg">' + App.lang('-- No Data --') + '</li>';
		}
		listHTML += '</ul><br class="clear" />';

		$('#projects_details_most_active').html(listHTML);
		

		listHTML = '<ul class="list"><li class="header_style">' + App.lang('Tasks Progress') + '</li>';

		if(task_in_progress && labels_data){
			$.each(task_in_progress, function(i, item) {
				listHTML += '<li class =""><div class="list_value">' + item.no_tasks + '</div><div class="list_label_container"><span class="pill" style="background-color:' + labels_data[item.label_id]['bg_color'] + ';color:' + labels_data[item.label_id]['fg_color'] + ';">' + labels_data[item.label_id]['name'] + '</span></div><div class="clear"></div></li>';
			});
		}else{
			listHTML += '<li class ="no_data_msg">' + App.lang('-- No Data --') + '</li>';
		}
		listHTML += '</ul>';

		$('#projects_task_in_progress').html(listHTML);
		var reports_filter_types = reports_enum.filter_types.projects;
		var filter_id = $('#reports_filter_type ').get(0).selectedIndex;
		var reports_selected_filter_label = reports_enum.filter_types.projects[filter_id]['label'];
		reports_enum.filter_types.projects[filter_id]['label'];

	    extend_urls(at_glance_ids, '', 'project', start, end);

		// extend_urls(at_glance_ids, '', search_by);
		// var print_url = App.extendUrl( $('.reports_print').attr('href'), { 'project_name_label' : $('.reports_plus_projects_details .project_name_label a').html(), 'reports_filter_type' : reports_selected_filter_label });
		//  $('.reports_print').attr('href', print_url);	
	}// Projects stats
	
	// TODO :: can be another logic for this or may be optimized one
	var gb_burn_down_data = [];
	var gb_weekly_hours_actual = gb_total_hours_actual = gb_total_hours_estimated = [];	

	var burn_down_chart = function (stats){
        
		var burn_down_data = [];
		for(var i=0; i < stats.length; i++) {
			burn_down_data[i] = [];
			burn_down_data[i][0]= Date.parse(stats[i]['date']).toString('dd-MMM-yy');
			burn_down_data[i][1]= parseInt(stats[i]['open']);
			burn_down_data[i][2]= parseInt(stats[i]['velocity']);
			burn_down_data[i][3]= parseInt(stats[i]['total']);
			burn_down_data[i][4]= parseInt(stats[i]['closed']);
			burn_down_data[i][5]= parseInt(stats[i]['created_in_iteration']);
			burn_down_data[i][6]= parseInt(stats[i]['closed_in_iteration']);
			burn_down_data[i][7]= parseInt(stats[i]['reopened_in_iteration']);
			burn_down_data[i][8]= parseInt(stats[i]['deleted_in_iteration']);
			burn_down_data[i][9]= Date.parse(stats[i]['date']).toString('dd MMM, yyyy');
		}
		gb_burn_down_data = burn_down_data;

		$('#reports_burn_down_chart').empty();
		var burn_down_legend_labels = [ App.lang('Open Items')];
		var series_colors = [ "#1B95D9" ];

		//TODO :: check the graph settings remove which are not required
		var burn_down = $.jqplot('reports_burn_down_chart', [burn_down_data],  {
			animate: true,
			animateReplot : true,
			seriesDefaults: {
	        lineWidth: 3,	
	        rendererOptions: {
		        	animation: {
					speed: 1800
					},
		          smooth: true
		        },
	        markerRenderer: $.jqplot.MarkerRenderer,
	            markerOptions: {
	            show: true,
	            size: 8,
	            shadow: false
	        	}
	    	},
	    	grid: { backgroundColor: 'transparent', drawBorder: false, shadow: false, gridLineColor:'#EEEEEE'
			}, 
	    	series: [
					{ xaxis: 'xaxis', yaxis: 'yaxis', shadow: false }, 
					
					], 
			seriesColors: series_colors,
			axesDefaults: {
				tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
				tickOptions: {
		        	show:false,
					fontFamily: 'Lucida Grande, Arial, sans-serif',
					fontSize: '7pt',
				 		textColor: 'black'
				},
				rendererOptions: {
					drawBaseline : false
				}
			},
			axes: {
				xaxis: {
					renderer: $.jqplot.CategoryAxisRenderer,
					tickOptions: {
						showGridline : false	
					}
				},
				yaxis: {
					min: 0,
					numberTicks:5
					
				}
			},
			highlighter: {
				show: true,
				showTooltip: false
			},
			cursor: {
				show: false
			}
		});
        $('#reports_burn_down_chart_tooltip').html(burn_down_tool_tip_html(burn_down_data, burn_down_data.length-1));
        
		$('#reports_burn_down_chart').off('jqplotDataMouseOver');
		$('#reports_burn_down_chart').on('jqplotDataMouseOver', function (ev, seriesIndex, pointIndex, data, seriesColors ) {
	        $('#reports_burn_down_chart_tooltip').html(burn_down_tool_tip_html(burn_down_data, pointIndex));
	        $('#reports_hours_chart_tooltip').html(hours_tool_tip_html(gb_weekly_hours_actual, gb_total_hours_actual, gb_total_hours_estimated, pointIndex));
	        $('.reports_plus_reports_container .tooltip_main_container').addClass('tooltip_main_container_highlight');
		});

        $('#reports_burn_down_chart').off('jqplotDataUnhighlight');
        $('#reports_burn_down_chart').on('jqplotDataUnhighlight', function(ev) {
            $('#reports_burn_down_chart_tooltip').html(burn_down_tool_tip_html(burn_down_data, burn_down_data.length-1));
	        $('#reports_hours_chart_tooltip').html(hours_tool_tip_html(gb_weekly_hours_actual, gb_total_hours_actual, gb_total_hours_estimated, gb_weekly_hours_actual.length-1 ));
	        $('.reports_plus_reports_container .tooltip_main_container').removeClass('tooltip_main_container_highlight');

         });
			
	}// Burn_down Chart

	var stats_difference = function(data, index, kpi_index){
        var prev_data = data[index-1] || null;
        var span_diff_html = '';
    	var stats_diff = (prev_data) ? Number(data[index][kpi_index]) - Number(prev_data[kpi_index]) : null;
    	if(stats_diff && stats_diff != 0){
        	span_diff_html +=    '<span class="prev_state_value">';
        	span_diff_html +=    (stats_diff > 0 ) ? '+' : '';
        	span_diff_html +=    stats_diff + '</span>';
    	}
    	return span_diff_html;
	}
	var burn_down_tool_tip_html = function(data, index){
        $('.projects_chart_velocity .tooltip_date').html(App.lang(' of week starting from ')  + data[index][9]);
       	var tool_tip_div = '<div class="tooltip_div chart_tooltip_stats_container">';
        	tool_tip_div += 	'<div class="chart_tooltip_stats"><div class="state_value">' + stats_difference(data, index, 2) + data[index][2] + '</div><div class="state_label">' + App.lang('Velocity ') + '</div></div> ';
        	tool_tip_div += 	'<div class="chart_tooltip_stats"><div class="state_value">' + stats_difference(data, index, 3) + data[index][3] + '</div><div class="state_label">' + App.lang('Total') + '</div></div> ';
        	tool_tip_div += 	'<div class="chart_tooltip_stats"><div class="state_value">'  + stats_difference(data, index, 4) + data[index][4] + '</div><div class="state_label">' + App.lang('Completed') + '</div></div> ';
        	tool_tip_div += 	'<div class="chart_tooltip_stats" style="color:#1B95D9"><div class="state_value">' + stats_difference(data, index, 1) + data[index][1] + '</div><div class="state_label">' + App.lang('Open') + '</div></div> ';
        	tool_tip_div += 	'<div class="clear"></div>';
        	tool_tip_div += '</div>';
        	tool_tip_div += '<div class="tooltip_div tooltip_iteration_stats">';
        	tool_tip_div += App.lang('During this week:');
        	tool_tip_div += data[index][5] + ' ' + App.lang('added') + ', ';
        	tool_tip_div += data[index][6] + ' ' + App.lang('completed') + ', ';
        	tool_tip_div += data[index][7] + ' ' + App.lang('reopened') + ', ';
        	tool_tip_div += data[index][8] + ' ' + App.lang('deleted/moved');
        	tool_tip_div += '</div>';
        	tool_tip_div += 	'<div class="clear"></div>';

  	    return tool_tip_div;
	}

	
	// Hours Chart
	var hours_summary_chart = function (stats){
		 	
        var weekly_hours_actual = [];
        var total_hours_actual = [];
        var total_hours_estimated = [];
		for(var i=0; i < stats.length; i++) {
			weekly_hours_actual[i] = [];
			total_hours_actual[i] = [];
			total_hours_estimated[i] = [];
			
			weekly_hours_actual[i][0] = total_hours_actual[i][0] = total_hours_estimated[i][0] = Date.parse(stats[i]['date']).toString('dd-MMM-yy');
			weekly_hours_actual[i][2] = total_hours_actual[i][2] = total_hours_estimated[i][2] = Date.parse(stats[i]['date']).toString('dd MMM, yyyy');
			weekly_hours_actual[i][1] = parseInt(stats[i]['hours_in_iteration']);
			total_hours_actual[i][1]= parseInt(stats[i]['actual_hours']);
			total_hours_estimated[i][1]= parseInt(stats[i]['estimated_hours']);
		}
		// var bar_width = 35 / (weekly_hours_actual.length == 0 ? 1 : weekly_hours_actual.length);

		gb_weekly_hours_actual =  weekly_hours_actual;
		gb_total_hours_actual =  total_hours_actual;
		gb_total_hours_estimated = total_hours_estimated;

		$('#reports_hours_chart').empty();
		// var hours_legend_labels = [ App.lang('Weekly Hours - Actual'), App.lang('Total Hours - Actual'), App.lang('Total Hours - Estimated')];
		var series_colors = [ "#A5BC4E", "#E48701"];
		// var series_colors = ["#1B95D9", "#A5BC4E", "#E48701"];

		//TODO :: check the graph settings remove which are not required
		var hours_chart = $.jqplot('reports_hours_chart', [total_hours_actual, total_hours_estimated], {
			animate: true,
			animateReplot : true,
			grid: { backgroundColor: 'transparent', drawBorder: false, shadow: false, gridLineColor:'#EEEEEE'},
			seriesDefaults: {
	            lineWidth: 3,
	            rendererOptions: {
	            	animation: {
						speed: 1800
					},
		          smooth: true
		        },
	            markerRenderer: $.jqplot.MarkerRenderer,
	                markerOptions: {
	                show: true,
	                size: 8,
	                shadow: false
	            	}
	        	}, 
			series: [
					{ xaxis: 'xaxis', yaxis: 'yaxis', shadow: false }, 
					{ xaxis: 'xaxis', yaxis: 'yaxis', shadow: false }
					],
			seriesColors: series_colors,		
			axesDefaults: {
		        tickRenderer: $.jqplot.CanvasAxisTickRenderer,
		        tickOptions: {
		        	show:false,
					fontFamily: 'Lucida Grande, Arial, sans-serif',
			 		fontSize: '7pt',
			 		textColor: 'black'
				},
				rendererOptions: {
					drawBaseline : false
				}
		    },
			axes: {
				xaxis: {
					renderer: $.jqplot.CategoryAxisRenderer,
					autoscale: true,
					tickOptions: {
							showGridline : false
				    },
		            borderWidth: 0
				},
				
				yaxis: {
					min: 0
				},
				x2axis: {
					autoscale: true,
					tickOptions : {
	                	showGridline : false
					}
				}
				},
				highlighter: {
					show: true,
					showTooltip: false
				}
			
			});
	    $('#reports_hours_chart_tooltip').html(hours_tool_tip_html(weekly_hours_actual, total_hours_actual, total_hours_estimated, weekly_hours_actual.length-1));
		
		//Tool Tip for Hours
		$('#reports_hours_chart').off('jqplotDataMouseOver');
		$('#reports_hours_chart').on('jqplotDataMouseOver', function (ev, seriesIndex, pointIndex, data) {
	        $('#reports_hours_chart_tooltip').html(hours_tool_tip_html(weekly_hours_actual, total_hours_actual, total_hours_estimated, pointIndex));
	        $('#reports_burn_down_chart_tooltip').html(burn_down_tool_tip_html(gb_burn_down_data, pointIndex));
	        $('.reports_plus_reports_container .tooltip_main_container').addClass('tooltip_main_container_highlight');


	    });
		
		$('#reports_hours_chart').off('jqplotDataUnhighlight');
		$('#reports_hours_chart').on('jqplotDataUnhighlight', function(ev) {
	        $('#reports_hours_chart_tooltip').html(hours_tool_tip_html(weekly_hours_actual, total_hours_actual, total_hours_estimated, weekly_hours_actual.length-1 ));
	        $('#reports_burn_down_chart_tooltip').html(burn_down_tool_tip_html(gb_burn_down_data, gb_burn_down_data.length-1));
	        $('.reports_plus_reports_container .tooltip_main_container').removeClass('tooltip_main_container_highlight');
	    }); 
			
	} // Hours chart

	// Tooltip Html 
	var hours_tool_tip_html = function(weekly_actual, total_actual, total_estimated, index){
        $('.projects_chart_hours .tooltip_date').html(App.lang(' of week starting from ') + weekly_actual[index][2]);

        var tool_tip_div = '<div class="tooltip_div chart_tooltip_stats_container">';
	        tool_tip_div += 	'<div class="chart_tooltip_stats" ><div class="state_value">' + stats_difference(weekly_actual, index, 1) + weekly_actual[index][1] + '</div><div class="state_label">' + App.lang('Weekly - Actual') + '</div></div> ';
	        tool_tip_div += 	'<div class="chart_tooltip_stats" style="color: #A5BC4E"><div class="state_value">' + stats_difference(total_actual, index, 1) +  total_actual[index][1] + '</div><div class="state_label">' + App.lang('Total - Actual') + '</div></div> ';
	        tool_tip_div += 	'<div class="chart_tooltip_stats" style="color: #E48701"><div class="state_value">' + stats_difference(total_estimated, index, 1) + total_estimated[index][1] + '</div><div class="state_label">' + App.lang('Total - Estimated') + '</div></div> ';
	        tool_tip_div += 	'<div class="clear"></div>';
	        tool_tip_div += '</div>';
  	    return tool_tip_div;
	}


	var extend_urls = function (ids, status, search_by, start, end ){
		if($('#reports_filter_type option:selected').val() == 'companies' ){
	         var export_url =  App.extendUrl( export_reports_url, { 'status': 'all', 'companies' : ids});
	         $('#page_action_export a').attr('href', export_url);
		}

		 var project_name_label = ($('.reports_plus_projects_details .project_name_label a').html()) ? $('.reports_plus_projects_details .project_name_label a').html() : $('.reports_plus_projects_details .project_name_label').html();
		 var reports_filter_type = reports_enum.filter_types.projects[$('#reports_filter_type ').get(0).selectedIndex]['label'];
		 var reports_filter_val = reports_enum.filter_types.projects[$('#reports_filter_type ').get(0).selectedIndex]['data'];
         var print_url= App.extendUrl( html_url, { 'search_by' : search_by, 
                                                  'print' : 1, 
                                                  'what' : what, 
                                                  'start' : start, 
                                                  'end' : end,
                                                  'projects' : ids, 
                                                  'status': status, 
                                                  'project_name_label' : project_name_label,
                                                  'reports_filter_type' : reports_filter_type , 
                                                  'reports_filter_val' : reports_filter_val
                                                }
                                    );
         $('.reports_print').attr('href', print_url);
    }


    var get_company_data = function (ids, status, search_by){

		var what = 'getProjectStatstics';
		//For Now we are gettting stats of active projects only 
		status = 'active';
		$.ajax({
			    'url' : App.extendUrl( api_url, { 'async' : 1, 'what' : what, 'ids' : ids, 'status': status, search_by: search_by}),
                'type' : 'get', 
                'success' : function(response) {
	 		          company_stats_panel(response.data);
                }, 
                'error' : function() {
                  App.Wireframe.Flash.error('No Data Found');
                }
		  });	
         // extend_urls(ids, status, search_by);
	}

    // Company stats
	var company_stats_panel = function (data){
            var hours = (data.hours) ? data.hours : 0;
            var files = (data.files != undefined) ? data.files : 0;
            var fsize = 0;
            var unit = '';
            var Discussion_open = (data.project_objects != undefined && data.project_objects.Discussion != undefined) ? ((data.project_objects.Discussion.open != undefined) ? data.project_objects.Discussion.open : 0 ) : 0; 
            
            var company_stats_html = 		'<ul class="list"><li class="header_style">' + App.lang('Projects at Glance') + '</li>';
            	company_stats_html += 		'<li><span class="list_value">' + data.projects +  '</span><span class="list_label">' + App.lang('Projects') + '</span></li>';
                if(data.project_objects != undefined &&  data.project_objects.Task != undefined ){
                        var Task_open = ( data.project_objects.Task.open != undefined) ? data.project_objects.Task.open : 0;
                        var Task_completed = (data.project_objects.Task.completed != undefined) ? data.project_objects.Task.completed : 0;
                        company_stats_html += 		'<li><span class="list_value">' + Task_open + '</span><span class="list_label">' + App.lang('Tasks: Open ')  + '</span></li>';
                        company_stats_html += 		'<li><span class="list_value">' + Task_completed + '</span><span class="list_label">' + App.lang('Tasks: Completed')  + '</span></li>';
                }
                company_stats_html += 		'<li><span class="list_value">' + Discussion_open + '</span><span class="list_label">' + App.lang('Discussions') +  '</span></li>';
                company_stats_html += 		'<li><span class="list_value">' + files + '</span><span class="list_label">' + App.lang('Files') + '</span></li>';
                if(data.filesize && data.filesize['fsize']){
                    fsize = data.filesize['fsize'];
                    unit = ' (' + data.filesize['unit'] + ')';
                }
                company_stats_html +=       '<li><span class="list_value">' + fsize + '</span><span class="list_label">' + App.lang('File Size') + unit + '</span></li>';
                company_stats_html += 		'<li><span class="list_value">' + data.users + '</span><span class="list_label">' + App.lang('Users') + '</span></li>';

            if(data.project_objects != undefined ){

                    if( data.project_objects.TodoList != undefined ){
                            var TodoList_open = ( data.project_objects.TodoList.open != undefined) ? data.project_objects.TodoList.open : 0;
                            var TodoList_completed = (data.project_objects.TodoList.completed != undefined) ? data.project_objects.TodoList.completed : 0;
                            company_stats_html += 		'<li><span class="list_value">' + TodoList_open + ' / ' + TodoList_completed + '</span><span class="list_label">' + App.lang('Todo Lists: Open / Completed') +  '</span></li>';
                    }
                    if( data.project_objects.Notebook != undefined ){
                            var Notebook_open = (data.project_objects.Notebook.open != undefined) ? data.project_objects.Notebook.open : 0;
                            var Notebook_completed = (data.project_objects.Notebook != undefined && data.project_objects.Notebook.completed != undefined) ? data.project_objects.Notebook.completed : 0;
                            company_stats_html += 		'<li><span class="list_value">' + Notebook_open + ' / ' + Notebook_completed + '</span><span class="list_label">' + App.lang('NoteBooks: Open / Completed') +  '</span></li>';
                    }
            }
            company_stats_html += '</ul>';
            
            // $('#projects_at_glance').empty();
            $('#projects_at_glance').html(company_stats_html);

            var projects_summary_data = data.project_details;
            $('#projects_details_grid').empty();
            $("#projects_details_grid").append('<ul class="list"></ul>');
			$("#projects_details_grid ul").append('<li class="li_list_header">' +
				'<div class="project_details_list_label">' + App.lang('Projects Overview') + '</div>' +
				'</li>' );
            if(projects_summary_data != undefined ){
                $.each(projects_summary_data, function(i, projects_summary) {
                        project_detail_div(i, projects_summary);
                });// each 
            }else{
				$("#projects_details_grid ul").append('<li class ="no_data_msg">' + App.lang('-- No Data --') + '</li>');
            }
	}// Company stats

	// TODO :: implement logic for and according to stripe image, when created
	var preogress_iamge_url = function(percentage){
        var img_url = '';
        if(percentage <= 10) {
          img_url =  '<img src="' + App.Wireframe.Utils.imageUrl('progress/progress-mono-0.png', 'complete') + '">';
        } else if(percentage <= 20) {
          img_url =  '<img src="' + App.Wireframe.Utils.imageUrl('progress/progress-mono-10.png', 'complete') + '">';
        } else if(percentage <= 30) {
          img_url =  '<img src="' + App.Wireframe.Utils.imageUrl('progress/progress-mono-20.png', 'complete') + '">';
        } else if(percentage <= 40) {
          img_url =  '<img src="' + App.Wireframe.Utils.imageUrl('progress/progress-mono-30.png', 'complete') + '">';
        } else if(percentage <= 50) {
          img_url =  '<img src="' + App.Wireframe.Utils.imageUrl('progress/progress-mono-40.png', 'complete') + '">';
        } else if(percentage <= 60) {
          img_url =  '<img src="' + App.Wireframe.Utils.imageUrl('progress/progress-mono-50.png', 'complete') + '">';
        } else if(percentage <= 70) {
          img_url =  '<img src="' + App.Wireframe.Utils.imageUrl('progress/progress-mono-60.png', 'complete') + '">';
        } else if(percentage <= 80) {
          img_url =  '<img src="' + App.Wireframe.Utils.imageUrl('progress/progress-mono-70.png', 'complete') + '">';
        } else if(percentage <= 90) {
          img_url =  '<img src="' + App.Wireframe.Utils.imageUrl('progress/progress-mono-80.png', 'complete') + '">';
        } else if(percentage < 100){
          img_url =  '<img src="' + App.Wireframe.Utils.imageUrl('progress/progress-mono-90.png', 'complete') + '">';
        }else if(percentage = 100){
          img_url =  '<img src="' + App.Wireframe.Utils.imageUrl('progress/progress-mono-100.png', 'complete') + '">';
        } // if
        return img_url;
	}

	//Project Details div for company tab
	var project_detail_div = function (count, projects_summary){
		var project_name = ( projects_summary.name.length > 45 ) ? projects_summary.name.substr(0,42) + '...' : projects_summary.name;	
            var project_stats_html = '<div class="project_progress_indiacator">' + preogress_iamge_url(projects_summary.percent_done) + '</div>';
                project_stats_html += '<div class="project_details_list_label">' +  '<a target="_blank" class="quick_view_item" href="' +projects_summary.url + '">' + project_name + '</a></div>';
            if (projects_summary.leader_name != undefined){ 
                project_stats_html += 	'<div class="project_leader">' + projects_summary.leader_name + '</div></div>';			
            }
            project_stats_html += '<div class="project_percent"><div class=" project_percent_progress_label" >' + projects_summary.percent_done  + App.lang('%') + '</div></div>';
            project_stats_html += '<div class="open_completed_contianer">' + (projects_summary.total_tasks_count - projects_summary.open_tasks_count)  + '<span>' + ' ' + App.lang('of') + ' ' + projects_summary.total_tasks_count + ' ' + App.lang('done') + '</span></div>';
            project_stats_html += 	'<div class="project_hours"><div class =""> ' + projects_summary.hours + App.lang('h') +'</div></div></div>';
            if ( projects_summary.next_milestone_days != undefined && projects_summary.next_milestone_days != 'TBD'){
                var next_milestone_due_label = '';
                var next_milestone_days = parseInt(projects_summary.next_milestone_days);
                if(next_milestone_days == 0) {
                    next_milestone_due_label = 'Today';
                } else if(next_milestone_days > 0) {
                    next_milestone_due_label = App.lang('due in') + ' ' + next_milestone_days + ' ' + App.lang('days');
                } else {
                    next_milestone_due_label = (next_milestone_days * -1) + ' ' + App.lang('days late');
                }
                project_stats_html += '<div class="project_next_milestone "><div class="milestone_label" title="' + App.lang('Next:') + ' ' + projects_summary.next_milestone + ', ' + next_milestone_due_label + '">' + App.lang('Next:') + ' ' + projects_summary.next_milestone + '</div></div>';
            }
            project_stats_html += 	'<br class="clear" />';
		$('<li class="reports_projects_details_row">' + project_stats_html + '</li>').appendTo("#projects_details_grid ul");
	}//Project Details div

	$(document).ready(function (){
		project_tab();
	}); 

	
	
</script>