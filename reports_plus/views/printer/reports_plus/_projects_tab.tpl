    <div id="reports_plus_reports_container"  class="reports_plus_reports_container">
        <div id="print_summary_container" class="print_summary_container">
        <div id="print_title" class="print_summary_title">{lang}Project Report{/lang}</div>
        <div id="print_info_labels" class="print_summary_details"></div>
        <div id="print_info_dates" class="print_summary_details"></div>
        </div>
        
        <div class="reports_plus_projects_details">
            <div id="projects_details_velocity_data_container" class="projects_details_velocity_data_container"></div>
            <div id="projects_task_in_progress" class="projects_task_in_progress"></div>
            <div id="projects_at_glance" class="projects_at_glance"></div>
        </div>

        <div class="reports_plus_charts_container" >
            <div class="projects_chart_velocity">
                <div class="header_style">{lang}Burn Down{/lang}</div>
                <div id="reports_burn_down_chart" class="display_charts"></div>
                <div id="reports_burn_down_chart_legend" class="reports_chart_legend"></div>
            </div>
            <div class="projects_chart_hours">
                <div class="header_style">{lang}Hours{/lang}</div>
                <div id="reports_hours_chart" class="display_charts"></div>
                <div id="reports_hours_chart_legend" class="reports_chart_legend"></div>
            </div>
            <div id="projects_details_most_active" class="projects_details_most_active"></div>
            <div id="projects_details_grid" class="projects_details_grid"></div>
            <!-- <div class="projects_chart_open"></div> -->
        </div>

    </div>


<script type="text/javascript" language="javascript">

	var project_tab = function (){
			
			var what = 'getProjectStats';
			var start = {$start|json nofilter};
			var end = {$end|json nofilter};
            var projects = {$projects|json nofilter};
			var search_by = search_by_values[{$reports_filter_val|json nofilter}];

                        
			$.ajax({
				  'url' : App.extendUrl( api_url, { 'async' : 1, 'what' : what, 'projects' : projects, 'start' : start, 'end' : end }),
		          'type' : 'get', 
		          'success' : function(response) {
					 if(response.data.primary.iterations.length > 0){
						project_stats_panel(response.data);
                        burn_down_chart(response.data.primary.iterations);
                        hours_summary_chart(response.data.primary.iterations);
                                                
						//String for date - print summary..
						var print_summary_dates = App.lang("Period: ") + Date.parse(response.data.params.start).toString(jqplot_tooltip_date_format) + App.lang(" to ") + Date.parse(response.data.params.end).toString(jqplot_tooltip_date_format);
						$('#print_info_dates').html(print_summary_dates);
                                                
						//String for project labels and filter - print summary..
						var print_filter_labels = reports_filter_type + ": " + project_name_label;
						$('#print_info_labels').html(print_filter_labels);
						
					 }else{
				  		App.Wireframe.Flash.error('No Data Found');
				  	 }
				  }, 
		          'error' : function() {
		            App.Wireframe.Flash.error('No Data Found');
		          }
			 });

    // at_glance_ids = (search_by == 'company') ? $('#reports_filter_list option:selected').val() : projects;
        if((search_by == 'project' && project_name_label == App.lang('All Projects')) ){
            get_company_data(projects, '', search_by);
        }
        if(search_by != 'project'){
            get_company_data(projects, '', search_by);
        }
        // get_company_data(projects, '', search_by);
			
	} //project tab


	//Projects stats
	var project_stats_panel = function (data){
        var projects_summary = data.summary;
        var projects_most_active = data.secondary.active;
        var task_in_progress = data.secondary.task_in_progress;
        var projects_longest_due_on = data.secondary.due_on;
        // var projects_longest_open = data.secondary.longest; // TODO :: blank
        var velocity = data.primary.iterations[data.primary.iterations.length -1]['velocity'];
        var hours_this_week = data.primary.iterations[data.primary.iterations.length -1]['hours_in_iteration'];
        var forecasted = (data.primary.forecast.forecasted == '-1' ) ? App.lang('Never') : $.jsDate.strftime(data.primary.forecast.forecasted, default_date_format);
        var open_comp = [[App.lang('completed'), projects_summary.total_tasks_count - projects_summary.open_tasks_count], [App.lang('open'), projects_summary.open_tasks_count]];
		var project_name = ( project_name_label.length  > 30 ) ? project_name_label.substr(0, 30) + '...' : project_name_label;
		

        var project_stats_html = '<ul class="list">';

            project_stats_html += '<li>';
            project_stats_html += '<div class="project_name_label">';
            project_stats_html += (projects_summary.url != undefined) ? '<a target="_blank" class="quick_view_item" href="' + projects_summary.url + '">' + project_name + '</a>' : project_name ;
            project_stats_html += '</div>';
            project_stats_html += '<div class="clear" /></div>';
            project_stats_html += '</li>';

            project_stats_html += '<li>';
            project_stats_html += '<div class="velocity_forcast_container"><div class="state_value">' + velocity + '</div><div class="state_label">' + App.lang('Velocity') + '</div><div class="clear" /></div></div>';
            // project_stats_html += '<div class="hours_forcast_container"><div class="state_value" >' + Math.round(projects_summary.hours) + '</div><div class="state_label">' + App.lang('Hours') + '</div><div class="clear" /></div></div>';
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
                project_stats_html +=   '<div class="project_leader"><img src="'+ App.Wireframe.Utils.assetUrl('icon/assignee_icon.gif','reports_plus', 'images') + '" alt="" class="small_icon"><div class="project_list_value"> ' + projects_summary.leader_name + '</div></div>';         
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
                    project_stats_html += '<div class="project_next_milestone"><img src="'+ App.Wireframe.Utils.assetUrl('icon/next_milestone_16.png','reports_plus', 'images') + '" alt="" class="small_icon"><div class="milestone_label" >' + App.lang('Next:') + ' ' + projects_summary.next_milestone + ',' + '</div><span>' + next_milestone_due_label + '</span></div>';
                    project_stats_html += '<div class="clear" /></div>';
                    project_stats_html += '</li>';
            }
                    project_stats_html += '</ul>';
        
        $('#projects_details_velocity_data_container').html(project_stats_html);

                var listHTML = '<ul class="list"><li class="header_style">' + App.lang('Most Active & Open') + '</li>';
                if(projects_most_active){
                    $.each(projects_most_active, function(i, item) {
                        var item_name = ( item.name.length > 100 ) ? item.name.substr(0,92) + '...' : item.name;
                        listHTML += '<li class ="most_active_task_name"><a target="_blank" href="' + item.url + '" >' + item_name  + '</a></li>';
                    });
                }else{
                    listHTML += '<li class ="no_data_msg">' + App.lang('-- No Data --') + '</li>';
                }
                listHTML += '</ul>';

                listHTML += '<ul class="list"><li class="header_style">' + App.lang('Longest Overdue') + '</li>';
                if(projects_longest_due_on){
                    $.each(projects_longest_due_on, function(i, item) {
                        var item_name = ( item.name.length > 100 ) ? item.name.substr(0,92) + '...' : item.name;
                        listHTML += '<li class ="most_active_task_name"><a target="_blank" href="' + item.url + '" >' + item_name  + '</a></li>';
                    });
                }else{
                    listHTML += '<li class ="no_data_msg">' + App.lang('-- No Data --') + '</li>';
                }
                listHTML += '</ul><br class="clear" />';
                $('#projects_details_most_active').html(listHTML);

                listHTML = '<ul class="list"><li class="header_style">' + App.lang('Tasks Progress') + '</li>';

                if(task_in_progress && labels_data){
                    $.each(task_in_progress, function(i, item) {
                        listHTML += '<li class =""><div class="list_value">' + item.no_tasks + '</div><div class="list_label_container"><span class="pill pill_print" style="background-color:' + labels_data[item.label_id]['bg_color'] + ';color:' + labels_data[item.label_id]['fg_color'] + ';">' + labels_data[item.label_id]['name'] + '</span></div><div class="clear"></div></li>';
                    });
                }else{
                    listHTML += '<li class ="no_data_msg">' + App.lang('-- No Data --') + '</li>';
                }
                listHTML += '</ul>';

                $('#projects_task_in_progress').html(listHTML);
		 	
	}// Projects stats

    var get_company_data = function (ids, status, search_by){

        var what = 'getProjectStatstics';
        //For Now we are gettting stats of active projects only 
        status = 'active';
        $.ajax({
                'url' : App.extendUrl( api_url, { 'async' : 1, 'what' : what, 'ids' : ids, 'status': status, 'search_by': search_by}),
                'type' : 'get', 
                'success' : function(response) {
                      company_stats_panel(response.data);
                }, 
                'error' : function() {
                  App.Wireframe.Flash.error('No Data Found');
                }
          });   

    }

     // Company stats
    var company_stats_panel = function (data){
            var hours = (data.hours) ? data.hours : 0;
            var files = (data.files != undefined) ? data.files : 0;
            var fsize = 0;
            var unit = '';
            var Discussion_open = (data.project_objects != undefined && data.project_objects.Discussion != undefined) ? ((data.project_objects.Discussion.open != undefined) ? data.project_objects.Discussion.open : 0 ) : 0; 
            
            var company_stats_html =        '<ul class="list"><li class="header_style">' + App.lang('Projects at Glance') + '</li>';
                company_stats_html +=       '<li><span class="list_value">' + data.projects +  '</span><span class="list_label">' + App.lang('Projects') + '</span></li>';
                if(data.project_objects != undefined &&  data.project_objects.Task != undefined ){
                        var Task_open = ( data.project_objects.Task.open != undefined) ? data.project_objects.Task.open : 0;
                        var Task_completed = (data.project_objects.Task.completed != undefined) ? data.project_objects.Task.completed : 0;
                        company_stats_html +=       '<li><span class="list_value">' + Task_open + '</span><span class="list_label">' + App.lang('Tasks: Open ')  + '</span></li>';
                        company_stats_html +=       '<li><span class="list_value">' + Task_completed + '</span><span class="list_label">' + App.lang('Tasks: Completed')  + '</span></li>';
                }
                company_stats_html +=       '<li><span class="list_value">' + Discussion_open + '</span><span class="list_label">' + App.lang('Discussions') +  '</span></li>';
                company_stats_html +=       '<li><span class="list_value">' + files + '</span><span class="list_label">' + App.lang('Files') + '</span></li>';
                if(data.filesize && data.filesize['fsize']){
                    fsize = data.filesize['fsize'];
                    unit = ' (' + data.filesize['unit'] + ')';
                }
                company_stats_html +=       '<li><span class="list_value">' + fsize + '</span><span class="list_label">' + App.lang('File Size') + unit + '</span></li>';
                company_stats_html +=       '<li><span class="list_value">' + data.users + '</span><span class="list_label">' + App.lang('Users') + '</span></li>';
                // company_stats_html +=        '<li><span class="list_label">' + App.lang('Hours Logged') + '</span><span class="list_value">' + hours + '</span></li>';

            if(data.project_objects != undefined ){

                    if( data.project_objects.TodoList != undefined ){
                            var TodoList_open = ( data.project_objects.TodoList.open != undefined) ? data.project_objects.TodoList.open : 0;
                            var TodoList_completed = (data.project_objects.TodoList.completed != undefined) ? data.project_objects.TodoList.completed : 0;
                            company_stats_html +=       '<li><span class="list_value">' + TodoList_open + ' / ' + TodoList_completed + '</span><span class="list_label">' + App.lang('Todo Lists: Open / Completed') +  '</span></li>';
                    }
                    if( data.project_objects.Notebook != undefined ){
                            var Notebook_open = (data.project_objects.Notebook.open != undefined) ? data.project_objects.Notebook.open : 0;
                            var Notebook_completed = (data.project_objects.Notebook != undefined && data.project_objects.Notebook.completed != undefined) ? data.project_objects.Notebook.completed : 0;
                            company_stats_html +=       '<li><span class="list_value">' + Notebook_open + ' / ' + Notebook_completed + '</span><span class="list_label">' + App.lang('NoteBooks: Open / Completed') +  '</span></li>';
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

    //Project Details div 
    var project_detail_div = function (count, projects_summary){
        var project_name = ( projects_summary.name.length > 45 ) ? projects_summary.name.substr(0,42) + '...' : projects_summary.name;  
            var project_stats_html = '<div class="project_progress_indiacator">' + preogress_iamge_url(projects_summary.percent_done) + '</div>';
                project_stats_html += '<div class="project_details_list_label">' +  '<a target="_blank" class="quick_view_item" href="' +projects_summary.url + '">' + project_name + '</a></div>';
            if (projects_summary.leader_name != undefined){ 
                    project_stats_html +=   '<div class="project_leader">' + projects_summary.leader_name + '</div></div>';         
            }
            project_stats_html += '<div class="project_percent"><div class=" project_percent_progress_label" >' + projects_summary.percent_done  + App.lang('%') + '</div></div>';
            project_stats_html += '<div class="open_completed_contianer">' + (projects_summary.total_tasks_count - projects_summary.open_tasks_count)  + '<span>' + ' ' + App.lang('of') + ' ' + projects_summary.total_tasks_count + ' ' + App.lang('done') + '</span></div>';
            project_stats_html +=   '<div class="project_hours"><div class =""> ' + projects_summary.hours + App.lang('h') +'</div></div></div>';
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
                project_stats_html += '<div class="project_next_milestone "><div class="milestone_label" >' + App.lang('Next:') + ' ' + projects_summary.next_milestone + ',' + '</div><span>' + next_milestone_due_label + '</span></div>';
            }
            project_stats_html +=   '<br class="clear" />';
        
        $('<li class="reports_projects_details_row">' + project_stats_html + '</li>').appendTo("#projects_details_grid ul");

    }//Project Details div


	// Date formatter for chart
	$.jqplot.DateTickFormatter = function(format, val) {
	    format = jqplot_axis_date_format;
	    return $.jsDate.strftime(val, format);
	};


	// Burn_down Chart 
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
                    burn_down_data[i][9]= Date.parse(stats[i]['date']).toString(jqplot_tooltip_date_format);
            }
            $('#reports_burn_down_chart').empty();
            var burn_down_legend_labels = [ App.lang('Open Items')];
            var series_colors = [ "#E48701" ];
            var tick_angle = (burn_down_data.length <= 8  ? 0 : -45 );

            var burn_down = $.jqplot('reports_burn_down_chart', [burn_down_data],  {
                                seriesDefaults: {
                                    lineWidth: 3,
                                    rendererOptions: { smooth: true },
                                    markerRenderer: $.jqplot.MarkerRenderer,
                                        markerOptions: {
                                        show: true,
                                        size: 8,
                                        shadow: false
                                            },
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
                                                fontFamily: 'Lucida Grande, Arial, sans-serif',
                                                fontSize: '7pt',
                                                        textColor: 'black'
                                        }
                                },
                                axes: {
                                        xaxis: {
                                                renderer: $.jqplot.CategoryAxisRenderer,
                                                tickOptions: {
                                                        angle: tick_angle,
                                                        formatter: $.jqplot.DateTickFormatter,
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
                                },

            });

            // Adding Legends 
            var legend_html = '';
            $.each(burn_down_legend_labels, function(i, label) {
                    legend_html += '<div class="legend_cell"> <div class="legend_bullet" style="background-color:' + series_colors[i] + ';border: 2px solid' + series_colors[i] + '"></div><div class="legend_label">' + label + '</div> </div>';
            });// each 

            $('#reports_burn_down_chart_legend').html(legend_html);
            /* Css Fix for x-axis of Burn down chart   */
            $('#reports_burn_down_chart div.jqplot-xaxis').css('bottom', -5);
		
	}// Burn_down Chart

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
                    weekly_hours_actual[i][2] = total_hours_actual[i][2] = total_hours_estimated[i][2] = Date.parse(stats[i]['date']).toString(jqplot_tooltip_date_format);
                    weekly_hours_actual[i][1] = parseInt(stats[i]['hours_in_iteration']);
                    total_hours_actual[i][1]= parseInt(stats[i]['actual_hours']);
                    total_hours_estimated[i][1]= parseInt(stats[i]['estimated_hours']);
            }
            var bar_width = 35 / (weekly_hours_actual.length == 0 ? 1 : weekly_hours_actual.length);
            var tick_angle = (weekly_hours_actual.length <= 8  ? 0 : -45 );

            $('#reports_hours_chart').empty();
            var hours_legend_labels = [ App.lang('Weekly Hours - Actual'), App.lang('Total Hours - Actual'), App.lang('Total Hours - Estimated')];
            var series_colors = ["#1B95D9", "#A5BC4E", "#E48701"];
            var hours_chart = $.jqplot('reports_hours_chart', [weekly_hours_actual, total_hours_actual, total_hours_estimated], {
                    grid: { backgroundColor: 'transparent', drawBorder: false, shadow: false, gridLineColor:'#EEEEEE'},
                    seriesDefaults: {
                    lineWidth: 3,
                    rendererOptions: { smooth: true },
                    markerRenderer: $.jqplot.MarkerRenderer,
                    markerOptions: {
                    show: true,
                    size: 8,
                    shadow: false
                    },
                    }, 
                    series: [
                                    { renderer: $.jqplot.BarRenderer,rendererOptions: { barWidth: bar_width }, yaxis: 'y2axis', shadow: false }, 
                                    { xaxis: 'xaxis', yaxis: 'yaxis', shadow: false }, 
                                    { xaxis: 'xaxis', yaxis: 'yaxis', shadow: false }
                                    ],
                    seriesColors: series_colors,		
                    axesDefaults: {
                    tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                    tickOptions: {
                                    fontFamily: 'Lucida Grande, Arial, sans-serif',
                                    fontSize: '7pt',
                                    textColor: 'black'
                            }
                },
                    axes: {
                            xaxis: {
                                    renderer: $.jqplot.CategoryAxisRenderer,
                                    autoscale: true,
                                    tickOptions: {
                                            angle: tick_angle,
                                            formatter: $.jqplot.DateTickFormatter,
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
                            showGridline : true
                                    }
                            },
                            y2axis: {
                                    autoscale: true,
                                    tickOptions : {
                            showGridline : false,
                            show: false
                        },
                            borderWidth: 0,
                            borderColor: 'transparent'
                        } 
                            },
                            highlighter: {
                                    show: true,
                                    showTooltip: false
                            },

                    });

                    // Adding Legends 
            var legend_html = '';
            $.each(hours_legend_labels, function(i, label) {
                    legend_html += '<div class="legend_cell"> <div class="legend_bullet" style="background-color:' + series_colors[i] + ';border: 2px solid' + series_colors[i] + '"></div><div class="legend_label">' + label + '</div> </div>';
            });// each 
            $('#reports_hours_chart_legend').html(legend_html);

            /* Css Fix for x-axis of  hours chart   */
            $('#reports_hours_chart div.jqplot-xaxis').css('bottom', -5);
			
	} // Hours chart


	$(document).ready(function (){
		project_tab();
	}); 
	
</script>