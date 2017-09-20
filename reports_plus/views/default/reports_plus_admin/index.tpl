{title}Reports Plus Configuration{/title}
{add_bread_crumb}Reports{/add_bread_crumb}

  <div class="content_stack_wrapper">
      <!-- Permissions -->
    {form action=$permissions_url method=post}    
      <div class="content_stack_element">
        <div class="content_stack_element_info">
          <h3>{lang}Permissions{/lang}</h3>
        </div>
        <div id="" class="content_stack_element_body">
			    <table class="module_role_permissions">
			      <tr>
			        <th class="role_name">{lang}Role{/lang}</th>
        			<th class="permission">{lang}See Reports?{/lang}</th>
			        <th class="permission">{lang}People Reports?{/lang}</th>
              <th class="permission">{lang}Companies Reports?{/lang}</th>
              {if AngieApplication::isModuleLoaded ( 'tracking' )}
                <th class="permission">{lang}Time Reports?{/lang}</th>
              {/if}
			      </tr>
			    {foreach from=$roles item=role}
			      <tr class="{cycle values='odd,even'}">
			          <td class="role_name">{$role|clean}</td>
                <td class="permission wide">
                  {if $role == 'Administrator'}
                    {checkbox_field name="roles_permissions[$role][can_use_reports]" checked=checked class="enabling_disabling_chx" disabled="disabled" }
                  {else}
                    {checkbox_field name="roles_permissions[$role][can_use_reports]" checked=$roles_permissions.$role.can_use_reports class="enabling_disabling_chx" }
                  {/if}
                </td>
                <td class="permission wide">
                  {if $role == 'Administrator'}
                    {checkbox_field name="roles_permissions[$role][can_see_reports_people]"  class="enabling_disabling_chx" checked=checked disabled="disabled"}
                  {else}
                    {checkbox_field name="roles_permissions[$role][can_see_reports_people]"  class="enabling_disabling_chx" checked=$roles_permissions.$role.can_see_reports_people}
                  {/if}
                </td>
                <td class="permission wide">
                  {if $role == 'Administrator'}
                    {checkbox_field name="roles_permissions[$role][can_see_reports_companies]"  class="enabling_disabling_chx" checked=checked disabled="disabled"}
                  {else}
                    {checkbox_field name="roles_permissions[$role][can_see_reports_companies]"  class="enabling_disabling_chx" checked=$roles_permissions.$role.can_see_reports_companies}
                  {/if}
                </td>
              {if AngieApplication::isModuleLoaded ( 'tracking' )}
                <td class="permission wide">
                  {if $role == 'Administrator'}
                    {checkbox_field name="roles_permissions[$role][can_see_time_reports]"  class="enabling_disabling_chx" checked=checked disabled="disabled"}
                  {else}
                    {checkbox_field name="roles_permissions[$role][can_see_time_reports]"  class="enabling_disabling_chx" checked=$roles_permissions.$role.can_see_time_reports}
                  {/if}
                </td>
              {/if}
			      </tr>
			    {/foreach}
			    </table>
			    <p class="details">
			    {lang}Unchecking "See Reports?" disables access to Reports completely.{/lang}   
			    </p>
          {wrap_buttons}
      {submit}Save Permissions{/submit}
    {/wrap_buttons}
        </div>
      </div>
  {/form}
  
  {form action=$settings_url method=post}
	
    <div class="content_stack_element">
        <div class="content_stack_element_info">
          <h3>{lang}Settings{/lang}</h3>
        </div>
        <div class="content_stack_element_body">
        
	        {wrap field=date_format}
	          {label for=date_format}Date Format{/label}
	          <select class="required" name="reports[date_format]" id='date_format' value="$reports_data.date_format">
	          {foreach from=$date_format_values item=date_format_value}
	            {if $date_format_value == $reports_data.date_format}
	            <option value="{$date_format_value}" selected="selected">{$date_format_value}</option>
	            {else}
	            <option value="{$date_format_value}">{$date_format_value}</option>
	            {/if}
	          {/foreach}
	          </select>
	        {/wrap}
	        
	        <div class="clear"></div>
        
        {wrap field=reports_update_on_access}
          {label for=reports_update_on_access}Update data on every access{/label}
          {yes_no name='reports[reports_update_on_access]' id='reports_update_on_access' value=$reports_data.reports_update_on_access}
          <p class="details">{lang}Enabling this will update this week's reporting data every time someone accesses Reports - in addition to daily updates. Useful if you always want latest data or if Daily Scheduled Tasks are not working.{/lang}</p>
        {/wrap}

      <div class="clear"></div>
      
        {wrap field=num_recent_items}
         {label for=num_recent_items}Recent Items to show{/label}
          <select class="required" name="reports[num_recent_items]" id='num_recent_items' value="$reports_data.num_recent_items">
          {foreach from=$num_recent_items_values item=num_recent_items_value}
            {if $num_recent_items_value == $reports_data.num_recent_items}
            <option value="{$num_recent_items_value}" selected="selected">{$num_recent_items_value}</option>
            {else}
            <option value="{$num_recent_items_value}">{$num_recent_items_value}</option>
            {/if}
          {/foreach}
          </select>
          <p class="details">{lang}How many Recent Items (Activities / Timerecords / Upcoming) would you like to see in People reports?{/lang}</p>
        {/wrap}

      <div class="clear"></div>


        {wrap field=num_weeks_for_average_velocity}
          {label for=num_weeks_for_average_velocity}Average Velocity Weeks{/label}
          <select class="required" name="reports[num_weeks_for_average_velocity]" id='num_weeks_for_average_velocity' value="$reports_data.num_weeks_for_average_velocity">
          {foreach from=$weeks_for_average_velocity_values item=weeks_for_average_velocity_value}
            {if $weeks_for_average_velocity_value == $reports_data.num_weeks_for_average_velocity}
            <option value="{$weeks_for_average_velocity_value}" selected="selected">{$weeks_for_average_velocity_value}</option>
            {else}
            <option value="{$weeks_for_average_velocity_value}">{$weeks_for_average_velocity_value}</option>
            {/if}
          {/foreach}
          </select>
          <p class="details">{lang}Number of recent weeks to include while calculating Average Adjusted Velocity.{/lang}</p>
        {/wrap}
      </div>

  </div>

  <div class="content_stack_element">
        <div class="content_stack_element_info">
          <h3>{lang}Leader Board Settings{/lang}</h3>
        </div>

        <div class="content_stack_element_body">
          {wrap field=reports_enable_leader_board}
            {label for=reports_enable_leader_board}Enable Leader Board{/label}
            {yes_no name='reports[reports_enable_leader_board]' id='reports_enable_leader_board' value=$reports_data.reports_enable_leader_board}
          {/wrap}

            {wrap field=leader_board_period}

              {label for=leader_board_period}Leader Board Period{/label}
              <p class="details">{lang}What period do you want to consider for calculating Leader Board scores?{/lang}</p>
              <input type="radio" name="reports[leader_board_period][key]"  value="0" class="leader_board_period" id="leader_board_period_0" class="inline"
              {if $reports_data.leader_board_period.key ==  0} checked = "checked" {/if}/>
              {lang}Current Week{/lang}
              <br />
              
              <input type="radio" name="reports[leader_board_period][key]"  value="1" class="leader_board_period" id="leader_board_period_1" class="inline"
              {if $reports_data.leader_board_period.key ==  1} checked = "checked" {/if}/>
              {lang}Current Week + Previous Week{/lang}
              <br />

              <input type="radio" name="reports[leader_board_period][key]"  value="2" class="leader_board_period" id="leader_board_period_2" class="inline"
              {if $reports_data.leader_board_period.key ==  2} checked = "checked" {/if}/>
              {lang}Current Month{/lang}
              <br />

              <input type="radio" name="reports[leader_board_period][key]"  value="3" class="leader_board_period" id="leader_board_period_3" class="inline"
              {if $reports_data.leader_board_period.key ==  3} checked = "checked" {/if}/>
              {lang}Current Month + Previous 2 Months{/lang}
              <br />

              <input type="radio" name="reports[leader_board_period][key]"  value="4" class="leader_board_period" id="leader_board_period_4" class="inline"
              {if $reports_data.leader_board_period.key ==  4} checked = "checked" {/if}/>
              {lang}Current Month + Previous 11 Months{/lang}
              <br />

              <input type="radio" name="reports[leader_board_period][key]"  value="5" class="leader_board_period" id="leader_board_period_5" class="inline"
              {if $reports_data.leader_board_period.key ==  5} checked = "checked" {/if}/>
              {lang}A Specific Date Range{/lang}
              <div class="leader_board_date_range">
                {select_date name="reports[leader_board_period][start_date]" value=$reports_data.leader_board_period.start_date skip_days_off=false label='' class='starts_on'} 
                <div> -- </div>
                {select_date name="reports[leader_board_period][end_date]" value=$reports_data.leader_board_period.end_date skip_days_off=false label='' class='starts_on'}
              </div>
              <br />
            {/wrap}

            {wrap field=leader_board_companies}
              {label for=leader_board_period}Leader Board Companies{/label}
              {select_company_multiple name="reports[company_multiple][]" optional=true value=$reports_data.company_multiple style="width:170px;"}
              <p class="details">{lang}Select companies to allow their users to be on Leader Board.{/lang}</p>
            {/wrap}
        </div>
  </div>

	
	<div class="content_stack_element">
        <div class="content_stack_element_info">
          <h3>{lang}Calculations{/lang}</h3>
        </div>
        <div class="content_stack_element_body">
        
	      <h2>{lang}Activity Score - People{/lang}</h2>
	      <p class="details">{lang}Activity score for a person uses 3 parameters - hours logged, items completed and items created. Specify weight multiplier for each parameter here.<br /><strong><code>score = (hours logged * hours weight) + (# of items completed * completed weight) + (# of items created * creation weight)</code></strong>{/lang}</p>
		  <div class="col">
		  	{wrap field=formula_weight_person_score_hours}
	          {label for=formula_weight_person_score_hours required=yes}Hours Weight{/label}
	          {text_field name='reports[formula_weight_person_score_hours]' value=$reports_data.formula_weight_person_score_hours id=formula_weight_person_score_hours class='required validate_minlength 1'}
	          <p class="details">{lang}Default:{/lang} 1</p>
	        {/wrap}
	      </div>
		  <div class="col">
		  	{wrap field=formula_weight_person_score_completed}
	          {label for=formula_weight_person_score_completed required=yes}Completed Weight{/label}
	          {text_field name='reports[formula_weight_person_score_completed]' value=$reports_data.formula_weight_person_score_completed id=formula_weight_person_score_completed class='required validate_minlength 1'}
	          <p class="details">{lang}Default:{/lang} 3</p>
	        {/wrap}
	      </div>
	       <div class="col">
	       	{wrap field=formula_weight_person_score_created}
	          {label for=formula_weight_person_score_created required=yes}Creation Weight{/label}
	          {text_field name='reports[formula_weight_person_score_created]' value=$reports_data.formula_weight_person_score_created id=formula_weight_person_score_created class='required validate_minlength 1'}
	          <p class="details">{lang}Default:{/lang} 2</p>
	        {/wrap}
	      </div>

      <div class="clear"></div>
      
      <h2>{lang}Activity Score - Tasks{/lang}</h2>
      <p class="details">{lang}Most Active &amp; Open list uses 3 parameters to calculate activity score. Specify weight multiplier for each parameter here.<br /><strong><code>score = (# of comments * comment weight) + (hours logged * hours weight) + (# of updates to item * updates weight)</code></strong>{/lang}</p>
	  <div class="col">
        {wrap field=formula_weight_most_active_comments}
          {label for=formula_weight_most_active_comments required=yes}Comment Weight{/label}
          {text_field name='reports[formula_weight_most_active_comments]' value=$reports_data.formula_weight_most_active_comments id=formula_weight_most_active_comments class='required validate_minlength 1'}
          <p class="details">{lang}Default:{/lang} 2</p>
        {/wrap}
      </div>
	  <div class="col">
        {wrap field=formula_weight_most_active_hours}
          {label for=formula_weight_most_active_hours required=yes}Hours Weight{/label}
          {text_field name='reports[formula_weight_most_active_hours]' value=$reports_data.formula_weight_most_active_hours id=formula_weight_most_active_hours class='required validate_minlength 1'}
          <p class="details">{lang}Default:{/lang} 1</p>
        {/wrap}
      </div>
       <div class="col">
        {wrap field=formula_weight_most_active_version}
          {label for=formula_weight_most_active_version required=yes}Updates Weight{/label}
          {text_field name='reports[formula_weight_most_active_version]' value=$reports_data.formula_weight_most_active_version id=formula_weight_most_active_version class='required validate_minlength 1'}
          <p class="details">{lang}Default:{/lang} 0.25</p>
        {/wrap}
      </div>
      
	  </div>

      {wrap_buttons}
  	  {submit}Save Changes{/submit}
    {/wrap_buttons}
</div>

{/form}
<script type="text/javascript">
  if($('#leader_board_period_5').attr('checked') == 'checked'){
    $('div.leader_board_date_range').show();
  }else{
    $('div.leader_board_date_range').hide();
  }
  $(document).ready(function(){
    $('.leader_board_period').change(function(){
      if($('#leader_board_period_5').attr('checked') == 'checked'){
       $('div.leader_board_date_range').show();
      }else{
        $('div.leader_board_date_range').hide();
      }   
    });

  });

</script>