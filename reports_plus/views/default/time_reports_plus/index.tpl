

{AngieApplication::useWidget('ui_date_picker' , 'environment')}
{AngieApplication::useWidget('filter_criteria' , 'reports')}
<div id="tracking_reports_plus" class="filter_criteria">
  <form action="{$time_reports_plus_run}" method="get">
  
    <!-- Filter Picker -->
    <div class="filter_criteria_head">
      <div class="filter_criteria_head_inner">
        <div class="filter_criteria_picker">
          {lang}Filter{/lang}: 
          <select>
            <option value="">{lang}Custom{/lang}</option>
          </select>
        </div>
        
        <div class="filter_criteria_run">{button type="submit" class="default"}Run{/button}</div>
        <div class="filter_criteria_options" style="display: none"></div>
      </div>
    </div>
    
    <div class="filter_criteria_body"></div>
  </form>
<div id="tracking_reports_plus_results" class="filter_results" style="background:#FFFFFF;"></div>
</div>

<script type="text/javascript">
	App.Wireframe.Events.bind('create_invoice_from_tracking_report.single', function (event, invoice) {
	  if (invoice['class'] == 'Invoice') {
	    	App.Wireframe.Flash.success(App.lang('New invoice created.'));
               App.Wireframe.Content.setFromUrl(invoice['urls']['view']);
	    } // if
  	});

  $('#tracking_reports_plus').each(function() {
	  var wrapper = $(this);
	    
        wrapper.filterCriteria({
        'pre_select_filter_id' : {if $pre_select_filter instanceof TrackingReport}{$pre_select_filter->getId()|json nofilter}{else}null{/if}, 
         'filter_type' : 'TimeReports',
          'options' : {
            'show_time_records' : {
                  'label' : App.lang('Show Time Records'), 
                  'selected' : true
                }
      },

      'on_result_links' : function(response, data, links) {

    	  links.push({
				'text' : App.lang('Export CSV'),
				'url' : App.extendUrl({$export_csv_url|json nofilter}, { async : 1 }),
				'download' : true
				 		
		  });
			
	      links.push({
	          'text' : App.lang('Export HTML'),
	          'url' : App.extendUrl({$export_html_url|json nofilter}, { async : 1 }),
	          'download' : true
	
	      });
	  
    	  	{if $invoice_based_on_url}
			links.push({
				'text' : App.lang('Create Invoice'), 
				'url' : {$invoice_based_on_url|json nofilter},
				'init' : function() {
					$(this).flyoutForm({
				      'title' : App.lang('Create Invoice based on Time Report'),
      				  'success_event' : 'create_invoice_from_tracking_report'
    				});
				}
			});
			
			{/if}
		},
  		'criterions' : {
  		  'type_filter' : {
  			  'label' : App.lang('Show'), 
  				'choices' : {
  					'time' : App.lang('Time Only')
  				}
  			}, 
  			'job_type_filter' : {
  			  'label' : App.lang('Job Type'), 
  			  'choices' : {
  			    'any' : App.lang('Any'), 
    				'selected' : {
    				  'label' : App.lang('Selected Types ...'), 
    					// 'prepare' : prepare_select_job_type
    					'prepare' : function(submit_as, criterion, filter, data) {
			                if(data['job_types']) {
			                  var selected_job_type_ids = typeof(filter) == 'object' && filter && filter['job_type_ids'] ? filter['job_type_ids'] : null;
			                  var select_html = '<div class="time_report_' + criterion + '">';

			                  App.each(data['job_types'], function(job_type_id, job_type_name) {
			                    var id = 'time_report_' + criterion + '_' + job_type_id;
			                    var checked = jQuery.isArray(selected_job_type_ids) && selected_job_type_ids.indexOf(parseInt(job_type_id)) >= 0 ? 'checked' : '';

			                    select_html += '<div class="job_type"><input type="checkbox" name="' + submit_as + '[job_type_ids][]" value="' + job_type_id + '" id="' + id + '" ' + checked + '> <label for="' + id + '">' + App.clean(job_type_name) + '</label></div>';
			                  });

			                  $(this).append(select_html + '</div>');
			                } else {
			                  $(this).text(App.lang('There are no job types to select from'));
			                } // if
			              }
    				}
  				}
  			},
  			/*
  			'expense_category_filter' : {
  			  'label' : App.lang('Expense Category'), 
  			  'choices' : {
  			    'any' : App.lang('Any'), 
    				'selected' : {
    				  'label' : App.lang('Selected Categories ...'), 
    					//'prepare' : prepare_select_expense_categories
    					'prepare' : function(submit_as, criterion, filter, data) {
			                if(data['expense_categories']) {
			                  var selected_category_ids = typeof(filter) == 'object' && filter && filter['expense_category_ids'] ? filter['expense_category_ids'] : null;
			                  var select_html = '<div class="time_report_' + criterion + '">';

			                  App.each(data['expense_categories'], function(category_id, category_name) {
			                    var id = 'time_report_' + criterion + '_' + category_id;
			                    var checked = jQuery.isArray(selected_category_ids) && selected_category_ids.indexOf(parseInt(category_id)) >= 0 ? 'checked' : '';

			                    select_html += '<div class="expense_category"><input type="checkbox" name="' + submit_as + '[expense_category_ids][]" value="' + category_id + '" id="' + id + '" ' + checked + '> <label for="' + id + '">' + App.clean(category_name) + '</label></div>';
			                  });

			                  for(var category_id in data['expense_categories']) {

			                  } // for

			                  $(this).append(select_html + '</div>');
			                } else {
			                  $(this).text(App.lang('There are no expense categories to select from'));
			                } // if
			              }
    				}
				  }
  			}, 
  			*/
  			{if $can_use_reports || $hide_project_filter} 
  			'user_filter' : {
  			  'label' : App.lang('Assigned To'), 
  			  'choices' : {
  			    'anybody' : App.lang('Anybody'), 
  			    'logged_user' : App.lang('Person Accessing This Report'), 
  			    'company' : {
    					'label' : App.lang('Member of a Company ...'), 
    					// 'prepare' : prepare_select_company
    					'prepare' : App['Wireframe']['Utils']['dataFilters']['prepareSelectCompany'],
                        'get_name' : function(c) {
                          return 'company_id';
                        },
                        'get_value' : function(f, c) {
                          return typeof(f) == 'object' && f ? f['company_id'] : null;
                        }
    				},
    				'selected' : {
    				  'label' : App.lang('Selected Users ...'), 
    					// 'prepare' : prepare_select_users
    					'prepare' : App['Wireframe']['Utils']['dataFilters']['prepareSelectUsers'],
                        'get_name' : function(c) {
                          return 'user_ids';
                        },
                        'get_value' : function(f, c) {
                          return typeof(f) == 'object' && f ? f['user_ids'] : null;
                        }

    				}
  			  }
  			}, 
  			{/if}
  			'date_filter' : {
  			  'label' : App.lang('For Day'), 
  			  'choices' : {
    				'any' : App.lang('Any Day'), 
    				'last_month' : App.lang('Last Month'), 
    				'last_week' : App.lang('Last Week'), 
    				'yesterday' : App.lang('Yesterday'), 
    				'today' : App.lang('Today'), 
    				'this_week' : App.lang('Week'), 
    				'this_month' : App.lang('This Month'), 
    				'selected_date' : {
    					'label' : App.lang('Selected Date ...'), 
    					// 'prepare' : prepare_date
    					'prepare' : App['Wireframe']['Utils']['dataFilters']['prepareSelectDate'],
                        'get_name' : function() {
                          return 'date_on';
                        },
                        'get_value' : function(f) {
                          return typeof(f) =='object' && f ? f['date_on'] : null;
                        }
    				}, 
    				'selected_range' : {
    					'label' : App.lang('Selected Date Range ...'), 
    					// 'prepare' : prepare_date_range
    					'prepare' : App['Wireframe']['Utils']['dataFilters']['prepareSelectDateRange'],
		                // Added while correcting sorting
		                'get_name' : function() {
		                  return 'date';
		                },
		                'get_value' : function(f) {
		                  return typeof(f) =='object' && f ? [f['date_from'], f['date_to']] : null;
		                }
    				}
  				}
  			},

  			{if !$hide_project_filter} 
  			'project_filter' : {
  			  'label' : App.lang('Projects'), 
  			  'choices' : {
  				  'any' : App.lang('Any Project'), 
  				  'active' : App.lang('Active Projects'), 
  				  'completed' : App.lang('Completed Projects'), 
  				  'category' : {
  					  'label' : App.lang('From Category ...'), 
		              'prepare' : App['Wireframe']['Utils']['dataFilters']['prepareSelectProjectCategory']
  					  // 'prepare' : preapre_select_project_category
  					}, 
  				  'client' : {
  					  'label' : App.lang('For Client ...'), 
  					  // 'prepare' : prepare_select_company
		              'prepare' : App['Wireframe']['Utils']['dataFilters']['prepareSelectCompany'],
		              // Added while correcting sorting
		              'get_name' : function(c) {
		                return 'project_client_id';
		              },
		              'get_value' : function(f, c) {
		                return typeof(f) == 'object' && f ? f['project_client_id'] : null;
		              }

  					},  
  				  'selected' : {
  					  'label' : App.lang('Selected Projects ...'),
		              'prepare' : App['Wireframe']['Utils']['dataFilters']['prepareSelectProjects']
  					  // 'prepare' : prepare_select_projects 
  					} 
  				}
  			},
  			{/if}

  			 
  			'billable_status_filter' : {
  			  'label' : App.lang('Status'), 
  			  'choices' : {
  			    'all' : App.lang('Any'), 
  			    'not_billable' : App.lang('Non-Billable'), 
  			    'billable' : App.lang('Billable'), 
  			    'pending_payment' : App.lang('Pending Payment'), 
  			    'billable_not_paid' : App.lang('Not Yet Paid (Billable or Pending Payment)'), 
  			    'billable_paid' : App.lang('Already Paid'), 
  			  }
  			}, 
  			'sum_by_1' : {
  			  'label' : App.lang('Summarized By'), 
  			  'id' : 'sum_by_1',
  			  'choices' : {
  			    'all' : App.lang("Don't Group"), 
  			    'date_field_1' : App.lang('by Date'), 
  			    'project_id' : App.lang('by Project'),
    			'milestone_id' : App.lang('by Milestone'),
                        'integer_field_2' : App.lang('by Billing Status'),
                        'task_category_id' : App.lang('by Task Category'),
                        'project_category_id' : App.lang('by Project Category'),
    			'integer_field_1' : App.lang('by User'), 
  			    'company_id' : App.lang('by Client'), 
  			    'tcp_id' : App.lang('by Task'), 
  			    'job_type_id' : App.lang('by job Type'), 
			    }
  			},
  			'sum_by_2' : {
  			  'label' : App.lang('Then By'),
    			'id' : 'sum_by_2', 
  			  'choices' : {
  			    'all' : App.lang("Don't Group"), 
    			'date_field_1' : App.lang('by Date'), 
  			    'project_id' : App.lang('by Project'), 
    			'milestone_id' : App.lang('by Milestone'),
                'integer_field_2' : App.lang('by Billing Status'),
                'task_category_id' : App.lang('by Task Category'),
                'project_category_id' : App.lang('by Project Category'),
    			'integer_field_1' : App.lang('by User'),
  			    'company_id' : App.lang('by Client'), 
    			'tcp_id' : App.lang('by Task'),
    			'job_type_id' : App.lang('by job Type'),
			    }
  			},
  			'sum_by_3' : {
  			  'label' : App.lang('And Then By'),
    			'id'  : 'sum_by_3', 
  			    'choices' : {
  			    'all' : App.lang("Don't Group"), 
    			'date_field_1' : App.lang('by Date'), 
  			    'project_id' : App.lang('by Project'),
    			'milestone_id' : App.lang('by Milestone'),
                'integer_field_2' : App.lang('by Billing Status'),
                'task_category_id' : App.lang('by Task Category'),
                'project_category_id' : App.lang('by Project Category'),
    			'integer_field_1' : App.lang('by User'), 
  			    'company_id' : App.lang('by Client'),
    			'tcp_id' : App.lang('by Task'),
    			'job_type_id' : App.lang('by Job Type'), 
			    }
  			}
  	  
  	  }, 
  	  'filters' : {$time_reports_plus|map nofilter},
  		'new_filter_url' : '{assemble route=time_reports_plus_add}', 
  		'export_url' : '{assemble route=tracking_reports_export}', 
  		'on_show_results' : function(response, data, form_data) {
                        var result = response[0].__v;
                        var results_wrapper = $(this);
                        results_wrapper.append(result);
  	  },
  	  'data' : {
  	    'companies' : {$companies|map nofilter}, 
  	    'users' : {$users|map nofilter}, 
  	    'projects' : {$projects|map nofilter}, 
  	    'project_categories' : {$project_categories|map nofilter}, 
        'active_projects' : {$active_projects|map nofilter},
  	    'job_types' : {$job_types|map nofilter}, 
  	    'expense_categories' : {$expense_categories|map nofilter}
        // 'currencies' : {$currencies|json nofilter}
      }

    });
  });
  
  {if !$can_use_reports} 
        $('.filter_criteria_picker').remove();
        $('.filter_save_button').remove();
  {/if}
</script>
