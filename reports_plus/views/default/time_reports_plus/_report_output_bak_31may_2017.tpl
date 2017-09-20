<table class="global_time_record" id="global_time_report_records">
        <tr id="select_all_row">
          {if $level > 0}
            <td class="leagends_td" colspan="{if $show_time_records}6{else}7{/if}" >
                <span class="leagends estimate">{lang}Estimate{/lang}</span>
                <span class="leagends actual"> {lang}Actual{/lang} </span>
            </td>
        {/if}
        {if $editable_time_records_count > 0 && !$is_export && $show_time_records}
            <td {if $level == 0}colspan="7" {/if} class="select_all_row_label">{lang}Select All{/lang}</td>
             <td class="report_chb">{checkbox_field name="all_time_record_ids[]" id="all_time_record_ids"}</td> 
        {/if}
        </tr>
   


    {if $level == 0}
        <tr>
            {foreach from=$display_headings item=field}
                <th>{$all_headings.$field}</th>
            {/foreach}
            <th>&nbsp;</th>
        </tr>
    {/if}
	{*<pre>
{$output_rows|@print_r}*}

    {assign var=count value=0}
    {foreach from=$output_rows item=row key=key}

        {if $row.heading != ''}
            <tr class="heading{$row.current_level}">
                <td colspan="5">
    				{if $row.heading_url != ''}<a class="quick_view_item" href="{$row.heading_url}">{/if}{if $row.heading_name == 'date_field_1'}{$row.heading|date:0}{else}{$row.heading}{/if}{if $row.heading_url != ''}</a>{/if}

    				{* code added by jyoti *}
					{*{if $row.status_filter=='billable'}*}
					&nbsp;&nbsp;
					{assign var="logged_hours" value=0}
					{assign var="budget_color" value="#A5BC4E"}
					
					{if $row.current_level eq 0}
						{assign var="index_key" value=$key+1}
						{while $output_rows[$index_key].current_level neq 0}
						{assign var="logged_hours" value=$logged_hours+$output_rows[$index_key].logged_hours}
						{assign var="index_key" value=$index_key+1}
						{/while}
					{/if}
					
    				{if $row.current_level eq 1}
					       {assign var="logged_hours" value=$row.logged_hours}
					{/if}
					{if $row.current_level eq 0 || $row.current_level eq 1}
							{if $row.estimate lt $logged_hours}
								{assign var="budget_color" value="#FF0000"}
							{/if}
							{assign var="estimate_hours" value=$row.estimate+0}
							{assign var="logged_hours" value=$logged_hours+0}
							{if $row.parent_id != 'project'}
								<font style='color:{$budget_color}'>{$estimate_hours}</font>
								/<font style='color:#2D4685;'>{$logged_hours}</font>
							{/if}
				             {if $row.current_level eq 0}
								 {* {if $row.budget > 0}*}
										{if $row.cost_so_far_perc > 100}
											{assign var="budget_perc" value=$row.cost_over_budget_perc}
										{else if $row.cost_so_far_perc > 90}
										  {assign var="budget_perc" value=$row.cost_so_far_perc}
										 {else}
										{assign var="budget_perc" value=$row.cost_so_far_perc}
									 {/if}
									 {if !($row.budget)}
									      {assign var="budget_perc" value=0}
									 {/if}
                                  <font style='color:#FF0000'>({$budget_perc}% over)</font>{/if}
                                  {*{/if}*}
				   {/if}
				   {*{/if}*}
    				{* code ended by jyoti *}
    				
    			 </td>
                <td class="totalEstimate{$row.current_level}">{if $show_estimate == 'true'}{$row.estimate}{else}&nbsp;{/if}</td>
                <td class="totalHours{$row.current_level}"> {$row.hours}</td>

{if $row.current_level == 0}
    {assign_var name=countlevel0}{$countlevel0+1}{/assign_var}
{/if}

{if $row.current_level == 1}
    {assign_var name=countlevel1}{$countlevel1+1}{/assign_var}
{/if}

{if $row.current_level ==2}
    {assign_var name=countlevel2}{$countlevel2 + 1}{/assign_var}
{/if}

<td class="report_chb">
    {if $show_time_records === true}
        {if $editable_time_records_count > 0}
            {if $row.current_level == 0 && $permission[$count]==1} 
                {checkbox_field  name="group_time_record_ids[]" id="heading_{$countlevel0}" style="float:left!important;"}
                {assign_var name=class_for_records}heading_{$countlevel0}{/assign_var}
            {elseif $row.current_level == 1 && $permission[$count]==1}
                {checkbox_field class="heading_{$countlevel0}"  name="group_time_record_ids[]" id="heading_{$countlevel0}_{$countlevel1}" style="float:left!important;"}
                {assign_var name=class_for_records}heading_{$countlevel0} heading_{$countlevel0}_{$countlevel1}{/assign_var}
            {elseif $row.current_level == 2 && $permission[$count]==1}
                {checkbox_field class="heading_{$countlevel0} heading_{$countlevel0}_{$countlevel1}" name="group_time_record_ids[]" id="heading_{$countlevel0}_{$countlevel1}_{$countlevel2}" style="float:left!important;"}
                {assign_var name=class_for_records}heading_{$countlevel0} heading_{$countlevel0}_{$countlevel1} heading_{$countlevel0}_{$countlevel1}_{$countlevel2} {/assign_var}
            {/if}
        {/if}
    {else}
        &nbsp;
    {/if}
</td>

</tr>
{elseif $show_time_records === true}
    {assign var=count value=$count+1}
    <tr class="{cycle values='odd,even'}">
        {assign_var name=idx}0{/assign_var}
        {assign_var name=indent}{$level*30}{/assign_var}
        {foreach from=$display_headings item=field}
            {if substr($items, -3) == '_id'}
                {assign_var name=url}{$field}_url{/assign_var}
                {assign_var name=name}{$field}_name{/assign_var}
            {else}
                {assign_var name=url} {/assign_var}
                {assign_var name=name} {/assign_var}
            {/if}

            <td class="{$headings_class.$field}" {if $idx == 0}style="padding-left:{$indent}px"{/if} >
                {if $field == 'date_field_1'}{$row.$field|date:0}
                {elseif $field == 'integer_field_1'}<a class="quick_view_item" href="{$row.integer_field_1_url}" title="{$row.$field}">{$row.$field}</a>
                {elseif $field == 'project_id'}<a class="quick_view_item" href="{$row.project_id_url}" title="{$row.$field}">{$row.$field}</a>
                {elseif $field == 'integer_field_2' || $field == 'hours'  || $field == 'heading_estimate'}{$row.$field}
                {elseif $field == 'summary'} 
                    {if $row.parent_id != ''}
                        {if $row.parent_type == 'Task'}
                            <a class="quick_view_item" href="{$row.tcp_id_url}">{$row.tcp_name|excerpt:50}</a>
                        {else}	

                        {/if}
                        {if $row.$field != '' }	
                            &mdash;
                        {/if}
                    {/if}
                    {$row.$field}
                {/if}
            </td>
            {assign_var name=idx}{$ids+1}{/assign_var}
        {/foreach}

        <td class="report_chb">
            {if $row.can_manage }
                {checkbox_field class="report_chb_checkbox {$class_for_records}" name="time_record_ids[]" value=$row.id }
            {else}
                <span class="empty_span" >&nbsp;</span>
            {/if}  
        </td>
    </tr>
{/if}
{/foreach}

<tr id="records_summary" class="global_total_hours">
    <!-- <td colspan="{if $show_estimate == 'true'}5{else}6{/if}">{lang}Total{/lang}:</td> -->
    <td colspan="5">{lang}Total{/lang}:</td>
    <td class="totalEstimate">{if $show_estimate == 'true' && $level > 0}{$total_estimate_time}{else}&nbsp;{/if}</td>
    <td class={if $level > 0}"totalHours"{else}"totalHours_padding"{/if}> {$total_time}</td>
    <td class="report_chb">&nbsp;</td>
</tr>

{if $editable_time_records_count gt 0 && $show_time_records}
    <tr id="mass_edit_globaltimereports">
        <td colspan = "99" style="text-align: right; padding-top: 5px">
            <div id ="globaltime_mass_edit">
                <select name="with_selected" id="timereports_action" class="auto" disabled="disabled">
                    <option value="">{lang}With Selected ...{/lang}</option>
                    <option value=""></option>

                    <option value="round_to_5">{lang count=5}Round to :count Minutes{/lang}</option>
                    <option value="round_to_15">{lang count=15}Round to :count Minutes{/lang}</option>
                    <option value="round_to_30">{lang count=30}Round to :count Minutes{/lang}</option>
                    <option value="adjust_to_percent">{lang}Adjust to %{/lang}</option>

                    <option value=""></option>
                    <option value="mark_as_billable">{lang}Mark as{/lang} {lang}Billable{/lang}</option>
                    <option value="mark_as_not_billable">{lang}Mark as{/lang} {lang}Non-Billable{/lang}</option>
                    <option value=""></option>
                    <option value="mark_as_billed">{lang}Already{/lang} {lang}Paid{/lang}</option>
                    <option value="mark_as_not_billed">{lang}Not yet{/lang} {lang}Paid{/lang}</option>

                    {if is_foreachable($job_types)}
                        <option value=""></option>
                        <optgroup label="{lang}Change Job Types{/lang}">
                            {foreach $job_types as $job_type_id => $job_type_name}
                                <option value="job_type_change_to_{$job_type_id}">{lang}{$job_type_name}{/lang}</option>
                            {/foreach}	
                        </optgroup>
                    {/if}

                    <option value=""></option>
                    <option value="move_to_trash">{lang}Move to{/lang} {lang}Trash{/lang}</option>

                </select>

                {submit id="globaltimerecord_submit"}Submit{/submit}
            </div></td> 
    </tr>
{/if}	
</table>

<script type="text/javascript">
	
    $(document).ready(function() {

    //Removing Printing for now
    //$('#page_title_actions #page_action_print').remove();            
    var mass_edit_url = '{$global_time_report_mass_edit_url nofilter}';
    var time_report_plus_run = '{$time_report_plus_run nofilter}';
    var time_record_ids = new Array;
    var adjust_to_percent = 100;

    //adjustinng a colspan values in the view based on the different grouping    
    if({$level} != 0){
        var colspan_adjustment = {$level};   
        var limit = [0, 6, 5, 4]; 
        colspan_adjustment ++;
        colspan_adjustment -= {$colspan} - limit[{$level}];
        $('.Estimate').attr('colspan', colspan_adjustment);
    }
    
    //SelectAll Checkbox
    $('#all_time_record_ids').click(function() {
    if($(this).is(":checked")) {
        $("#global_time_report_records input:checkbox").each(function(){
            $(this).attr('checked', 'checked');
        });
    } else {
    $("#global_time_report_records input:checkbox").each(function(){
    $(this).removeAttr('checked');
    });
    }	 	
    });          
    //Group By SelectAll
    $("#global_time_report_records input:checkbox").click(function(){
    var id = ($(this).attr("id"));
    if($(this).is(":checked")){
    $('.' + id).attr('checked', 'checked');
    }else{      
    $('.' + id).removeAttr('checked');
    }    
    });  


    $("#global_time_report_records input:checkbox").click(function(){
    var time_record_ids = new Array;
                            
    			
    $("#global_time_report_records input:checkbox").each(function(){
    if($(this).is(":checked") && $(this).val() != 'checked'){
    time_record_ids.push($(this).val());
    }
    });
    			
    if(time_record_ids.length > 0){
    $('#globaltime_mass_edit').find('select').removeAttr('disabled');
    }else{
    $('#globaltime_mass_edit').find('select').attr('disabled', 'disabled'); 
    }
    prepareInvoiceUrl(time_record_ids);
    			
    });


    $('#timereports_action').change(function(){
    var selection = $("#timereports_action option:selected").val();
    if(selection == 'adjust_to_percent'){
    var setTo = setToPercent();
    if(setTo){
    massUpdate();
    //document.getElementById('globaltime_edit_form').submit();
    }
    }
    });


    			
    $('#globaltimerecord_submit').click(function(){

    var time_record_ids = new Array;
    $("#global_time_report_records input:checkbox").each(function(){
    if($(this).is(":checked") && $(this).val() != 'checked'){
    time_record_ids.push($(this).val());
    }
    });
    			
    if(time_record_ids.length <= 0) {
    alert(App.lang("Please select time records to update"));
    return;
    }		
    			
    massUpdate();

    });
    		

    // adjust timerecords with the percent
    var setToPercent = function () {
    var str = prompt(App.lang("Enter % value to apply"), '100%');
    var prepareHtml = '';
    if(str){
    var lastCharacter = str.substring(str.length - 1);
    if(lastCharacter == '%'){
    var str = str.replace('%', '');
    }
    				
    if(isNumeric(str)){
    adjust_to_percent = str;
    /*
    prepareHtml = '<input type="hidden" name="adjust_to_percent" value="' + str + '" />';
    $('#globaltime_mass_edit').find('button').attr('disabled', '').removeClass('button_disabled');
    */
    					
    }else{
    alert(App.lang('Please Enter valid % value'));
    setToPercent();
    }
    }else{
    document.getElementById('timereports_action').selectedIndex = 0;
    //$('#globaltime_mass_edit').find('button').attr('disabled', 'disabled').addClass('button_disabled');
    return false;
    }
    			
    $('#globaltime_mass_edit').append(prepareHtml);
    return true;
    };

    var fetchData = function () {
    			
    $.ajax({
    'url' : App.extendUrl(time_report_plus_run, { 'async' : 1 }),
    'type' : 'get',
    'data' : {
    'filter' : {$filter_data|json nofilter},
    },		
    'success' : function(r) {
    //$(".filter_results").html(r.scalar);
    $("#global_time").remove();
    var result = r[0].__v;
    $(".filter_results .filter_result_links").before(result);
    prepareInvoiceUrl();
    },
    'error' : function(response) {
    //App.Wireframe.Flash.success(App.lang(''));
    }		
    });
    };

    var massUpdate = function () {

    if($('#timereports_action').val() == '') {
    alert(App.lang('Please select an action'));
    return;
    }	
    var time_record_ids = new Array;
    $("#global_time_report_records input:checkbox").each(function(){
    if($(this).is(":checked") && $(this).val() != 'checked'){
    time_record_ids.push($(this).val());
    }
    });
    			
    $.ajax({
    'url' : App.extendUrl(mass_edit_url, { 'async' : 1 }), 
    'type' : 'post', 
    'data' : {
    'submitted' : 'submitted',
    'filter_data' : {$filter_data|json nofilter},
    'with_selected' : $('#timereports_action').val(),
    'time_record_ids': time_record_ids,
    'adjust_to_percent': adjust_to_percent,
    },
    'success' : function(response) {
    fetchData();
    App.Wireframe.Flash.success(App.lang('TimeRecords have been updated successfully'));
    }, 
    'error' : function(response) {
    App.Wireframe.Flash.error('Timerecords have not been updated successfully');
    }
    });

    };	

    var isNumeric = function (sText) {
    var ValidChars = "0123456789.";
    var IsNumber=true;
    var Char;

    for (i = 0; i < sText.length && IsNumber == true; i++) 
                               { 
    Char = sText.charAt(i); 
    if (ValidChars.indexOf(Char) == -1) 
                                     {
    IsNumber = false;
    }
    }
    return IsNumber;
    }

    var prepareInvoiceUrl = function (time_record_ids) {
        $('.filter_result_links ul li').each(function() {
            var anchor = $(this).find('a');
            var href = anchor.attr('href');
            if(href.toLowerCase().indexOf("invoice") >= 0) {
                if(typeof(time_record_ids) == 'object' && time_record_ids.length > 0) {
                    if(href.toLowerCase().indexOf("&for_ids") >= 0) {
                        href = href.substring(0, href.toLowerCase().indexOf("&for_ids"));
                    }
                    href = App.extendUrl(href, { 'for_ids' : time_record_ids.join(',')});
                } else {
                    $('#globaltime_mass_edit').find('select').attr('disabled', 'disabled');

                        if(href.toLowerCase().indexOf("&for_ids") >= 0) {
                            href = href.substring(0, href.toLowerCase().indexOf("&for_ids"));
                        }
                        href = App.extendUrl(href, { 'for_ids' : 'all' });
                }
            anchor.attr('href', href);		
            }	
        });

    }	
    
    // Tweaking a heading CSS to set the text aligned to right for the nos.
    $('table#global_time_report_records').find('th:contains("Hours")').css('text-align' , 'right');  
    });

</script>
