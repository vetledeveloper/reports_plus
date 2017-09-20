
{title}{lang}Reports Plus Prints : {$tab_selected}{/lang}{/title}
<div class="reports_plus_main_container">
	<div class="reports_plus_main">
		<div id="reports_plus_controls" class="reports_plus_controls">
			
			<div id="reports_tpl_container" class="reports_tpl_container">
			{if $tab_selected == 'projects'}
				{include file=get_view_path('_projects_tab', 'reports_plus', $smarty.const.REPORTS_PLUS_MODULE, 'default')}
			<!-- {elseif $tab_selected == 'peoples'} -->
				<!-- {include file=get_view_path('_peoples_tab', 'reports_plus', $smarty.const.REPORTS_PLUS_MODULE, 'default')} -->
			<!-- {else $tab_selected == 'companies'} -->
				<!-- {include file=get_view_path('_companies_tab', 'reports_plus', $smarty.const.REPORTS_PLUS_MODULE, 'default')} -->
			{/if}
			</div>
	</div>
</div>


	

