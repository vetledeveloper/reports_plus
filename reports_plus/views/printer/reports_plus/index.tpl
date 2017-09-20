{if $view == 'html'}

	<style type="text/css">
		body {
		    background-color: #ffffff !important; 
		}
		#wireframe_content{
			background-color: #ffffff !important;
			background-image: none !important;
		}
	</style>
	
	<script type="text/javascript" >

	/* Var Declaration */
	var reports_enum = {$reports_enum|json nofilter};
	var api_url = {$api_url|json nofilter};
	var tab_selected = {$tab_selected|json nofilter};
	var date_format = {$date_format|json nofilter};
	var labels_data = {$labels_map|json nofilter};
	var	search_by_values = { 'projects' : 'project', 'project_groups'  : 'category', 'companies' : 'company' };
	
	
	if(date_format == 'MM/DD/YY'){
		var jqplot_tooltip_date_format = 'MM/dd/yy';
		var default_date_format =  '%m/%d/%y';
		var jqplot_axis_date_format = '%m/%d';
	}else{
		var jqplot_tooltip_date_format = 'MM/dd/yy';
		var default_date_format = '%d/%m/%y';
		var jqplot_axis_date_format = '%d/%m';
	}
	
	var project_name_label = {$project_name_label|json nofilter};
	var reports_filter_type = {$reports_filter_type|json nofilter};
	var reports_company_label = {$reports_company_label|json nofilter};
	var reports_company_status_label = {$reports_company_status_label|json nofilter};
        
	</script>
	
	{title}{lang}Reports Plus{/lang}{/title}
            <div class="reports_plus_main_container">
                <div class="reports_plus_main">
                    <div id="reports_tpl_container" class="reports_tpl_container">
                        {if $tab_selected == 'projects'}
                            {include file=get_view_path('_projects_tab', 'reports_plus', $smarty.const.REPORTS_PLUS_MODULE)}
                        {elseif $tab_selected == 'people'}
                            {include file=get_view_path('_people_tab', 'reports_plus', $smarty.const.REPORTS_PLUS_MODULE)}
                        {else $tab_selected == 'companies'}
                            {include file=get_view_path('_companies_tab', 'reports_plus', $smarty.const.REPORTS_PLUS_MODULE)}
                        {/if}
                    </div>
                </div>
            </div>
{/if}