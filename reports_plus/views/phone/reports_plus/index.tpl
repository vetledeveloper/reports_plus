{if $view == 'html'}


{title}Reports{/title}

	<!--  white background for now -->
	<style type="text/css">
		.phone div.ui-page, .phone div.ui-content{
			background: none repeat scroll 0 0 #FFFFFF !important;
		}
	</style>
	<!-- Loading jq plot library -->
	
		
		<div id="reports_tpl_container" >
				{if $tab_selected == 'projects'}
					{include file=get_view_path('_projects_tab', 'reports_plus', $smarty.const.REPORTS_PLUS_MODULE)}
				{elseif $tab_selected == 'people'}
					{include file=get_view_path('_people_tab', 'reports_plus', $smarty.const.REPORTS_PLUS_MODULE)}
				{elseif $tab_selected == 'companies'}
					{include file=get_view_path('_companies_tab', 'reports_plus', $smarty.const.REPORTS_PLUS_MODULE)}
                                {else $tab_selected == 'finance'}
					{include file=get_view_path('_finance_tab', 'reports_plus', $smarty.const.REPORTS_PLUS_MODULE)}
				{/if}
		</div>

<script type="text/javascript" >
   
	
	/* Var Declaration */
	var api_url = {$api_url|json nofilter};
	var html_url = {$html_url|json nofilter};
	var tab_selected = {$tab_selected|json nofilter};
	var reports_enum = {$reports_enum|json nofilter};
	var reports_filters = {$reports_filters|json nofilter};
//	var date_format = {$date_format|json nofilter};
	var projects_data_all = reports_enum.projects;
        var available_reports_tabs = {$available_reports_tabs|json nofilter};
	var companies = reports_enum.companies;
	
        if(tab_selected == 'projects'){
                var project_filter_type = (reports_filters != null) ? reports_enum.filter_types.projects[reports_filters.project_filter_type]['data'] : 'projects';
                var projects_data =  reports_enum[project_filter_type] ; // need to simplify this
        }else if(tab_selected == 'companies'){
                var companies = reports_enum.companies;

        }
        /*
        if(date_format == 'MM/DD/YY'){
                var jqplot_tooltip_date_format = 'MM/dd/yy';
                var default_date_format =  '%m/%d/%y';
                var jqplot_axis_date_format = '%m/%d';
                var date_options_date_format = "mm/dd/y";
        }else{
                var jqplot_tooltip_date_format = 'dd/MM/yy';
                var default_date_format = '%d/%m/%y';
                var jqplot_axis_date_format = '%d/%m';
                var date_options_date_format = "dd/mm/y";
		}
          */      
        var small_pie_chart = function (chart_container_id, open_comp){
		 var small_pie = $.jqplot(chart_container_id, [open_comp], {
		 					 seriesColors: ["#00355F", "#4E8ABE"],
                                                         seriesDefaults: {
		                         renderer: jQuery.jqplot.PieRenderer,
                                        rendererOptions: {
                                        highlightMouseOver: false
                                        }
                                 }
		 });
		 
		 var pie_themes = { grid: { backgroundColor: 'transparent', drawBorder: false, shadow: false
		 							}, 
                                    series: [
                                                    { shadow: false, startAngle: -90, padding: 0 
                                                    }
                                                    ]		
                                    };
	
		small_pie.themeEngine.newTheme('pie_themes', pie_themes);
		small_pie.activateTheme('pie_themes');
	     
	 // Small Pie Chart
        }

        $("#projects_tab_mobile_button").click(function (){
                 $(this).attr('href', App.extendUrl( html_url, {  'tab_selected' : 'projects' }));

        });
        $("#people_tab_mobile_button").click(function (){
                $(this).attr('href', App.extendUrl( html_url, {  'tab_selected' : 'people' }));
        });
        $("#companies_tab_mobile_button").click(function (){
                $(this).attr('href', App.extendUrl( html_url, {  'tab_selected' : 'companies' }));
        });
        var display_error = function (err_msg, err_div){
                $(err_div).html();
                $(err_div).html('<div class="err_msg_div"><div class="err_msg_text_div div_round_controls">' + "! "  + err_msg + '</div></div>');
        }

		
	</script>
	
{/if}