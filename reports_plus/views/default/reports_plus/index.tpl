{if $view == 'html'}

<!-- Loading jq plot library -->

{AngieApplication::useWidget('ui_date_picker' , 'environment')}

<style type="text/css">
#wireframe_content {
	background-color: #ffffff !important;
	background-image: none !important;
}
</style>

<script type="text/javascript">
	
	/* Var Declaration */
	var api_url = {$api_url|json nofilter};
  var html_url = {$html_url|json nofilter};
	var export_reports_url = {$export_reports_url|json nofilter};
	var reports_enum = {$reports_enum|json nofilter};
	var tab_selected = {$tab_selected|json nofilter};
  var date_format = {$date_format|json nofilter};
	var available_reports_tabs = {$available_reports_tabs|json nofilter};
  var projects_data = reports_enum.projects;
	
  
	//Highlight the selected tab..
        $("#button_" + tab_selected).addClass('button_selected_skin');
		
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
	
  //Add Reports Print Button 
  // Do not include Print functionlaity for this release in Finance Reports
  if(tab_selected != 'finance'){ 
      App.Wireframe.PageTitle.addAction('print', {
                 'url' : App.extendUrl(html_url, { 'print' : 1, 'tab_selected' : tab_selected }),
                 'text' : ' ',
                 'icon' : App.Wireframe.Utils.assetUrl('icon/print.gif', 'reports_plus', 'images', 'default'),
                 'onclick' : function () {
                   App.Wireframe.Print.doPrint($(this).attr('href'));
                   return false;
                   }
      }, 'reports_print');
   } 

	// setting a Current Tab Value	
    $('.button_selected').click(function(){
		  $('#tab_selected').val($(this).val());
	  });

    // Highlight the selected tab..
    $("#button_" + tab_selected).addClass('button_selected_skin');
	
    // need to Implement functionality here.
    $('#button_reminder').live('click', function(){
    });

	
  	$(document).ready(function (){
      $('#page_action_print a').attr('title' , App.lang('Print Reports'));
  		

  	});
	
</script>

{title}{lang}Reports Plus{/lang}{/title}
<div class="reports_plus_main_container">
<div class="reports_plus_main">
<div id="reports_plus_controls" class="reports_plus_controls">
{form action=$html_url method=post}
	<div class="reports_plus_common_buttons">
		<div class="button_common_container">
			{if {$available_reports_tabs|@count} > 1} 
				{if is_foreachable($available_reports_tabs)} 
					{foreach $available_reports_tabs as $report} 
						{if $report == 'projects' }
							<button id="button_projects" class="button_common button_selected" type="submit" value="projects"> <img src="{image_url name='icon/projects.gif' module=$smarty.const.REPORTS_PLUS_MODULE}" alt="" class="button_common_icon">{lang}Projects{/lang} </button>
						{elseif $report == 'finance'}
							<button id="button_finance" class="button_common button_selected" type="submit" value="finance"><img src="{image_url name='icon/finance.png' module=$smarty.const.REPORTS_PLUS_MODULE}" alt="" class="button_common_icon">{lang}Finance{/lang}</button>
						{elseif $report == 'people'}
							<button id="button_people" class="button_common button_selected" type="submit" value="people"><img src="{image_url name='icon/assignees.gif' module=$smarty.const.REPORTS_PLUS_MODULE}" alt="" class="button_common_icon">{lang}People{/lang}</button>
						{/if} 
					{/foreach} 
				{/if}
			{/if} 
		<input id="tab_selected" name="tab_selected" type="hidden" value="projects" /></div>
		<div class="button_print_container"></div>
	</div>
{/form}
<div id="filter_control_container" class="filter_control_container"></div>
</div>
<div id="reports_tpl_container" class="reports_tpl_container">
	{if $tab_selected == 'projects'}
		{include file=get_view_path('_projects_tab', 'reports_plus', $smarty.const.REPORTS_PLUS_MODULE)} 
	{elseif $tab_selected == 'people'}
		{include file=get_view_path('_people_tab', 'reports_plus', $smarty.const.REPORTS_PLUS_MODULE)}
	{else $tab_selected == 'finance'} 
		{include file=get_view_path('_finance_tab', 'reports_plus', $smarty.const.REPORTS_PLUS_MODULE)} 
	{/if}
</div>
<!-- <div id="reminder_dialog" class="reminder_dialog_container"> </div> -->
</div>
</div>

{/if}
