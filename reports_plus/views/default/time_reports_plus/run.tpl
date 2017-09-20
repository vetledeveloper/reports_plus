<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>

<style type="text/css">

    
{if $css_content}
	{literal}
            #select_all_row ,
            .report_chb,.select_all_row_label, 
            #select_all_row  , 
            #timereports_action, 
            #globaltimerecord_submit ,
            #mass_edit_globaltimereports {
                    display: none;
             }
		    .common{
			    margin-left: 55px;
		    }
	{/literal}		
	{$css_content}
	{literal}
	{/literal}
{/if}	
</style>

{if $css_content}
	<table class="common" cellspacing="10">
	<tbody>
		{if $filter_data.show_time_records == '1'}
			<tr class="report_select criterion_type_filter">
				<td class="report_select_label">Show Time Records</td>
				<td>{$filter_map['show_time_records'][$filter_data.show_time_records]}</td>
			</tr>
		{/if}
			<tr class="report_select criterion_job_type_filter">
				<td class="report_select_label">Job Type</td>
				<td class="report_select_select">{$filter_map['job_type_filter'][$filter_data.job_type_filter]}</td>
				</tr>
				<tr class="report_select criterion_user_filter">
				<td class="report_select_label">Assigned To</td>
				<td class="report_select_select">{$filter_map['user_filter'][$filter_data.user_filter]}</td>
			</tr>

			<tr class="report_select criterion_date_filter">
				<td class="report_select_label">For Day</td>
				<td class="report_select_select">{$filter_map['date_filter'][$filter_data.date_filter]}</td>
			</tr>
			
			<tr class="report_select criterion_project_filter">
				<td class="report_select_label">Projects</td>
				<td class="report_select_select">{$filter_map['project_filter'][$filter_data.project_filter]}</td>
			</tr>

			<tr class="report_select criterion_billable_status_filter">
				<td class="report_select_label">Status</td>
				<td class="report_select_select">{$filter_map['billable_status_filter'][$filter_data.billable_status_filter]}</td>
			</tr>
			
			<tr class="report_select criterion_sum_by_1">
				<td class="report_select_label">Summarized By</td>
				<td class="report_select_select">{$filter_map['sum_by_1'][$filter_data.sum_by_1]}</td>
			</tr>
			
			<tr class="report_select criterion_sum_by_2">
				<td class="report_select_label">Then By</td>
				<td class="report_select_select">{$filter_map['sum_by_1'][$filter_data.sum_by_2]}</td>
			</tr>

			<tr class="report_select criterion_sum_by_3">
				<td class="report_select_label">And Then By</td>
				<td class="report_select_select">{$filter_map['sum_by_1'][$filter_data.sum_by_3]}</td>
			</tr>
	</tbody>
	</table>
{/if}	
{include file = get_view_path('report', 'time_reports_plus', 'reports_plus', 'default')}
