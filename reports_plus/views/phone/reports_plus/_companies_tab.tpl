{add_bread_crumb}Projects{/add_bread_crumb}

    <div id="companies_tab_mobile" >
        <div id="company_project_detail_grid" class="company_project_detail_grid"></div>
    </div>
	
	
	<script type="text/javascript" language="javascript">

	 //Project Details div for company tab
	var project_detail_div = function (count, projects_summary){
		var project_stats_html = '';
        var open_comp = [[App.lang('completed'), projects_summary.total_tasks_count - projects_summary.open_tasks_count], [App.lang('open'), projects_summary.open_tasks_count]];
		var project_name  = '';
		var small_pie_chart_id = "small_pie_chart_" + count;
		var reports_company_projects_list_id = "reports_company_projects_list_" + count;
		var reports_project_details_id = "reports_project_details_" + count;
		project_name = ( projects_summary.name.length > 45 ) ? projects_summary.name.substr(0,42) + '...' : projects_summary.name;
		
		project_stats_html += '<div id="'+ reports_project_details_id +'"class="reports_project_details">';
		project_stats_html += '<div class="small_pie_company_container"><div id="'+ small_pie_chart_id + '" class="small_pie_company" ></div></div>';
        project_stats_html += '<div id="'+ reports_company_projects_list_id + '"class="reports_company_projects_list">';
        project_stats_html += '<div class="project_name_label">' +  project_name + '</div>';
		project_stats_html += 	'<div class="project_hours_percent">';
		project_stats_html += 	'<div class="project_hours"><img src="'+ App.Wireframe.Utils.assetUrl('icon/clock.gif','reports_plus', 'images') + '" alt="" class="small_icon"><div class ="info_label_bold"> ' + projects_summary.hours + App.lang('h') +'</div></div><div class="project_percent"><img src="'+ App.Wireframe.Utils.assetUrl('icon/checked.gif','reports_plus', 'images') + '" alt="" class="small_icon"><div class="info_label_bold">' + projects_summary.percent_done  + App.lang('% done') + ' </div></div><br class="clear"/></div>';
		if (projects_summary.leader_name != undefined){ 
			project_stats_html += 	'<div class="project_leader_container"><div class="project_leader"><img src="'+ App.Wireframe.Utils.assetUrl('icon/assignee_icon.gif','reports_plus', 'images') + '" class="small_icon"><div class ="info_label_bold"> ' + projects_summary.leader_name  + '</div></div></div>';			
		} 
		if ( projects_summary.next_milestone_days != undefined && projects_summary.next_milestone_days != 'TBD'){
			project_stats_html += '<div class="project_next_milestone info_label_bold">' + App.lang('Next : ') + projects_summary.next_milestone + ', <span class="">' + parseInt(projects_summary.next_milestone_days) * -1 + App.lang(' days late') + '</span></div>';
		}
		project_stats_html += 	'</div>';
		project_stats_html += '<br class="clear"/></div>';
                project_stats_html += '<br class="clear"/>';
		project_stats_html += '</div>';
		
		$("#company_project_detail_grid").append('<div class="reports_projects_details_container">' + project_stats_html + '</div>');
                
        // Small Pie Chart
        small_pie_chart(small_pie_chart_id,open_comp);
        
        $('#'+ reports_project_details_id).height($('#'+reports_company_projects_list_id).height() );
	
	}//Project Details div

	//get data for company tab
	var get_company_data = function (companies){
            var what = 'getCompanyStats';
            var status = (reports_filters != null) ? reports_filters['project_status'] : '';
            $.ajax({
                      'url' : App.extendUrl( api_url, { 'async' : 1, 'what' : what, 'companies' : companies, 'status': status }),
              'type' : 'get', 
              'success' : function(response) {
                                    company_pojects_panel(response.data);
                      }, 
              'error' : function() {
                              display_error('No Data Found', '#company_mobile_detail_container');
              }
              });	
	}



        // Company stats
	var company_pojects_panel = function (data){
        
		var projects_summary_data = data.project_details;
		$('#company_project_detail_grid').empty();
		if(projects_summary_data != undefined ){
			$.each(projects_summary_data, function(i, projects_summary) {
				project_detail_div(i, projects_summary, 'company_tab');
			});// each 
		}else{
			App.Wireframe.Flash.error('No Projects Found for the Company.');
		}

	}// Company stats

	// Comnpany Tab
        var company_tab = function (){
            get_company_data();
        }// company Tab

        $(document).ready(function (){
            company_tab();
        }); 

	</script>