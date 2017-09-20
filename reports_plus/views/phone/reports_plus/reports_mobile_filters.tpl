{title}Reports Filters{/title}
{add_bread_crumb}Reports Filters{/add_bread_crumb}

<div id="reports_filters">

  {form action=$filter_url }
	{if $tab_selected == 'companies'}

		    {wrap field=project_status}
		    	{select_reports_filters name='reports_filters[project_status]' option_data=$reports_enum.filter_types.companies_project_statuses id=reports_project_status label='Project Status'}
		    {/wrap}
		{else}
			{wrap field=project_filter_type}
		    	{select_reports_filters name='reports_filters[project_filter_type]' option_data=$reports_enum.filter_types.projects id=reports_project_filter_type label='Project Filter Type'}
		    {/wrap}
			{wrap field=start}
		    	{select_date name='reports_filters[start]' value=$dates.start label='Start' id=reports_filters_start }
		    {/wrap}
		    
		    {wrap field=end}
		    	{select_date name='reports_filters[end]' value=$dates.end label='End' id=reports_filters_end }
		    {/wrap}
		    
		    {wrap field=date_range	}
                        {select_reports_filters name='reports_filters[date_range]' option_data=$reports_enum.dates.projects id=reports_filters_date_range label='Date Range'}
		    {/wrap}
	{/if}
	
    {wrap_buttons}
      {submit id=button_mobile_load_reports}Load Reports{/submit}
    {/wrap_buttons}
  {/form}
</div>


<script type="text/javascript">

	var reports_enum = {$reports_enum|json nofilter};
	var tab_selected = {$tab_selected|json nofilter};
	
	$(document).ready(function() {
		App.Wireframe.DateBox.init();
		App.Wireframe.SelectBox.init();
	});
    
	
	$('#reports_filters_date_range').change(function(){
		var date_range = reports_enum.dates.projects[$(this).get(0).selectedIndex];
		$('#reports_filters_start').val(Date.parse(date_range.start).toString(date_picker_format));
		$('#reports_filters_end').val(Date.parse(date_range.end).toString(date_picker_format));

	});
	
	
	
</script>