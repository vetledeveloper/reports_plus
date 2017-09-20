
<div id="global_time">
{if is_array($output_rows) && count($output_rows) > 0}
	 {include file=get_view_path('_report_output', 'time_reports_plus', 'reports_plus', 'default')}
{else}
  <p class="empty_page">{lang}This report is empty{/lang}</p>
{/if}
</div>
