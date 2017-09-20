<!--Financial Reports Structure-->

<script type="text/javascript" language="javascript">
    
    
        var invoice_data_all = {}; //global array to hold data
        var currency_sign = '$'; //global variable for currency symbol..
        
		//Function for abbreviating the values, if they are too long..
		var abbrevate_number = function (number, decPlaces) {

		    // 2 decimal places => 100, 3 => 1000, etc
		    decPlaces = Math.pow(10,decPlaces);
		
		    // Enumerate number abbreviations
		    var abbrev = [ "k", "m", "b", "t" ];
		
		    // Go through the array backwards, so we do the largest first
		    for (var i=abbrev.length-1; i>=0; i--) {
		
		        // Convert array index to "1000", "1000000", etc
		        var size = Math.pow(10,(i+1)*3);
		
		        // If the number is bigger or equal do the abbreviation
		        if(size <= number) {
		             // Here, we multiply by decPlaces, round, and then divide by decPlaces.
		             // This gives us nice rounding to a particular decimal place.
		             number = Math.round(number*decPlaces/size)/decPlaces;
		
		             // Handle special case where we round up to the next abbreviation
		             if((number == 1000) && (i < abbrev.length - 1)) {
		                 number = 1;
		                 i++;
		             }
		
		             // Add the letter for the abbreviation
		             number += abbrev[i];
		
		             // We are done... stop
		             break;
		        }
		    }
		    return number;
		}
		
        //Function to format the currency values..
        var format_currency = function(value, max_length) {
			
        	//Convert the number to currency format, with commas(,)..
	    	part = String(value).split('.');
		    part1 = part[0];
		    part2 = part.length > 1 ? '.' + part[1] : '';
		    
		    var rgx = /(\d+)(\d{3})/;
		    var counter = 0;
		    while ( rgx.test(part1) ) {
		        part1 = part1.replace( rgx, '$1' + ',' + '$2' );
		        counter ++;
		    }
		    var formatted_value = part1 + part2;

		   	//Check if we need to convert it a shorter form.. 	
			if(((String(value).length) + counter) > max_length) {
				var formatted_value = abbrevate_number(value, 2);
        	}
    		return String(formatted_value);
		}
        
        
	var finance_tab = function () {
        
//            var what = 'invoiceStats';
            var start = '';
            var end = '';
            var date_today = new Date();
            var date_range_list = reports_enum.dates.finance;

            var finance_tab_filters_html = '<div class="load_controls">';
                finance_tab_filters_html += 	'<button id="button_load_finance" class="button_common" type="button">' + App.lang('Show') + '</button>'	
                finance_tab_filters_html += '</div>';

                //Date Ranges..
                finance_tab_filters_html += '<div class="select_date_range_finance select_date_range">';
                finance_tab_filters_html +=  '<div class="div_round_controls">';
                finance_tab_filters_html +=		'<div id="date_range_start" class="date_range_start"><input class="input_text" autocomplete="off" /></div>';
                finance_tab_filters_html +=		'<div class="div_inner_text">' + App.lang('to') + '</div>';
                finance_tab_filters_html +=		'<div id="date_range_end" class="date_range_end"><input class="input_text" autocomplete="off" /></div>';
//                finance_tab_filters_html +=		'<div id="date_range_list_container" title="' + App.lang('Date Range') + '"><select id="date_range_list"></select></div>';
                finance_tab_filters_html +=  '</div>';
                finance_tab_filters_html += '</div>';

                $('#filter_control_container').empty();
                $('#filter_control_container').append(finance_tab_filters_html);
                
                for (i=0; i< date_range_list.length; i++){
			$('<option/>').val(date_range_list[i]['data']).html(date_range_list[i]['label']).appendTo('#date_range_list');
                        
		}

            //Setting the date range..    
            $('#date_range_start input').datepicker(date_options);
            $('#date_range_end input').datepicker(date_options);
            

			//Change the datepicker icons. Make it gray-scale..
			$('#date_range_start img').attr("src", App.Wireframe.Utils.assetUrl('icon/calendar_grayscale.png','reports_plus', 'images'));
			$('#date_range_end img').attr("src", App.Wireframe.Utils.assetUrl('icon/calendar_grayscale.png','reports_plus', 'images'));
			/*END*/

            $('#date_range_end input').datepicker('setDate', date_today);
            date_today.setMonth(date_today.getMonth()-12);
            $('#date_range_start input').datepicker('setDate', date_today);

            
		
	} //finance_tab
	
         var group_list = function(invoice_data){
            var monthly_invoices = invoice_data.data.invoice_stats;
            var list_items = ('<li id="month_all" class="month_list_items">' + invoice_data.data.all.label + '</li>');
            $.each(monthly_invoices, function(index, invoice_list) {
                if(index != Date.parse(invoice_data.data.params.invoice_start).toString('yyyy-MM')){
                    list_items += ('<li id="month_' + index + '">' + invoice_list.label + '</li>');
                }
            });
            
            $('#invoice_group_container').empty();
            $('#invoice_group_container').addClass('invoice_group_container');
            $('#invoice_group_container').append('<ul class="items_group_list" id="items_group_list">' + list_items + '</ul>');

        }
        
   
	
        var invoice_kpi = function(response) {

            var invoice_kpi_response = response.data;
            //1st Pane - Yearly/Monthly Stats...
            var finance_tab_html = 		'<div class="general_stats_container" id="general_stats_container">';
                finance_tab_html +=			'<table class="general_stats_cell">';			
                finance_tab_html += 			'<tr class="general_stats_label"><td>' + App.lang('Year To Date') + '</td></tr>';
                finance_tab_html += 			'<tr class="general_stats_value">';
                finance_tab_html +=					'<td>';
                finance_tab_html +=						'<span>' + currency_sign + '</span>';
                finance_tab_html +=						'<span>' + abbrevate_number(invoice_kpi_response.year_to_date.current, 2) + '</span>';
                finance_tab_html +=					'</td>';
                finance_tab_html +=				'</tr>';
            var year_to_date_change = (Number(invoice_kpi_response.year_to_date.current) - Number(invoice_kpi_response.year_to_date.previous));
                finance_tab_html += 			'<tr class="general_stats_changed">';
                finance_tab_html +=					'<td class="general_stats_changed_value">';
                if(year_to_date_change != 0) {
                	finance_tab_html +=						'<span class="up_down_indicator"><img src="' + ((year_to_date_change > 0)? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images')) + '"></span>';
                	finance_tab_html +=						'<span>' + currency_sign + '</span>';
                	finance_tab_html +=						'<span>' + abbrevate_number(Math.abs(year_to_date_change), 2) + '</span>';
                } else {
                	finance_tab_html += 					'<span>' + App.lang('no change') + '</span>';
                }    
                finance_tab_html +=					'</td>';
                finance_tab_html +=				'</tr>';
                finance_tab_html +=				'<tr><td></td></tr>';

                finance_tab_html += 			'<tr class="general_stats_label"><td>' + App.lang('Month To Date') + '</td></tr>';
                finance_tab_html += 			'<tr class="general_stats_value">';
                finance_tab_html +=					'<td>';
                finance_tab_html +=						'<span>' + currency_sign + '</span>';
                finance_tab_html +=						'<span>' + abbrevate_number(invoice_kpi_response.month_to_date.current, 2) + '</span>';
                finance_tab_html +=					'</td>';
                finance_tab_html +=				'</tr>';
            var month_to_date_change = (Number(invoice_kpi_response.month_to_date.current) - Number(invoice_kpi_response.month_to_date.previous));
                finance_tab_html += 			'<tr class="general_stats_changed">';
                finance_tab_html +=					'<td class="general_stats_changed_value">';
                if(month_to_date_change != 0) {
//                	finance_tab_html +=						'<span class="up_down_indicator"><img src="' + ((month_to_date_change != 0) ? ((month_to_date_change > 0)? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images')) : "") + '"></span>';
                	finance_tab_html +=						'<span class="up_down_indicator"><img src="' + ((month_to_date_change > 0)? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images')) + '"></span>';
                	finance_tab_html +=						'<span>' + currency_sign + '</span>';
                	finance_tab_html +=						'<span>' + abbrevate_number(Math.abs(month_to_date_change), 2) + '</span>';	
                } else {
                	finance_tab_html += 					'<span>' + App.lang('no change') + '</span>';
                }
                finance_tab_html +=					'</td>';
                finance_tab_html +=				'</tr>';
                finance_tab_html +=				'<tr><td></td></tr>';

                finance_tab_html += 			'<tr class="general_stats_label"><td>' + App.lang('Paid On Time') + '</td></tr>';
                finance_tab_html += 			'<tr class="general_stats_value">';
                finance_tab_html +=					'<td>';
                finance_tab_html +=						'<span>' + invoice_kpi_response.paid_on_time.value + '%' + '</span>';
                finance_tab_html +=					'</td>';
                finance_tab_html +=				'</tr>';
            var paid_on_time_change = (Number(invoice_kpi_response.paid_on_time.value) - Number(invoice_kpi_response.paid_on_time.value_prev));
                finance_tab_html += 			'<tr class="general_stats_changed">';
                finance_tab_html +=					'<td class="general_stats_changed_value">';
                if(paid_on_time_change != 0) {
                	finance_tab_html +=						'<span class="up_down_indicator"><img src="' + ((paid_on_time_change > 0)? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images')) + '"></span>';
                	finance_tab_html +=						'<span>' + Math.abs(paid_on_time_change.toFixed(2)) + '%' + '</span>';
                } else {
                	finance_tab_html += 					'<span>' + App.lang('no change') + '</span>';
                }
                finance_tab_html +=					'</td>';
                finance_tab_html +=				'</tr>';
                finance_tab_html += 		'</table>';
                finance_tab_html +=			'</div>';
                //END: 1st Pane - Yearly/Monthly Stats...

                var unpaid_invoices = invoice_kpi_response.un_paid;

                //1st Pane - Unpaid since 0-30, 30-60 days...
                finance_tab_html += 		'<div class="unpaid_invoices_stats_container" id="unpaid_invoices_stats_container">';
                finance_tab_html += 		'<div class="horizontal_rule"></div>';
                finance_tab_html +=			'<table>';
                finance_tab_html +=				'<tr><td class="invoice_stats_header" colspan="2">' + App.lang('Unpaid') + '</td></tr>';

                //Not checking if data is null or undefined b'coz, it is already handled in the database part.
                //We will not get null/undefined here..

                //Increment by 30, coz array keys are 0, 30, 60, 90...
                for(var i = 0; i < Object.size(unpaid_invoices); i++) {
                    finance_tab_html += 			'<tr class="invoice_stats_cell">';
                    finance_tab_html += 				'<td class="invoice_stats_label">' + unpaid_invoices[i*30].label + '</td>';
                    finance_tab_html += 				'<td class="invoice_stats_value"><span>' + currency_sign + '</span><span>' + abbrevate_number((unpaid_invoices[i*30].value) , 2) + '</span></td>';
                    finance_tab_html +=				'</tr>';
                }
                finance_tab_html +=			'</table>';
                finance_tab_html +=			'</div>';
                //END: 1st Pane - Unpaid since 0-30, 30-60 days...

                //1st Pane - Top 5 Clients...
                var top_five_clients = invoice_kpi_response.top_five_client;

                finance_tab_html += 		'<div class="top_clients_stats_container" id="top_clients_stats_container">';
                finance_tab_html += 		'<div class="horizontal_rule"></div>';
                finance_tab_html +=			'<table>';
                finance_tab_html +=				'<tr><td class="invoice_stats_header" colspan="2">' + App.lang('Top Clients') + '</td></tr>';	
                if((top_five_clients != null) && (top_five_clients != undefined)) {
                    for(var i = 0; i < Object.size(top_five_clients); i++) {
                        finance_tab_html += 			'<tr class="invoice_stats_cell">';
                        finance_tab_html += 				'<td class="invoice_stats_label" title="' + top_five_clients[i].name + '">' +  (((top_five_clients[i].name).length > 12) ? (top_five_clients[i].name).substring(0, 10) + ".." : top_five_clients[i].name) + '</td>';
                        finance_tab_html += 				'<td class="invoice_stats_value"><span>' + currency_sign + '</span><span>' + abbrevate_number(top_five_clients[i].amount, 2) + '</span></td>';
                        finance_tab_html +=				'</tr>';
                    }
                }
                finance_tab_html +=			'</table>';
                finance_tab_html +=			'</div>';

                //END: 1st Pane - Top 5 Clients...
                $("#invoice_stats_container").empty();
                $("#invoice_stats_container").addClass('invoice_stats_container');
                $("#invoice_stats_container").append(finance_tab_html);
                $("#vertical_rule").addClass('vertical_rule');
        }


      var invoice_summary = function(current_stats, previous_stats) {
      
                var issued_invoices_diff = Math.round((Number(current_stats.total_amount) - Number(previous_stats.total_amount)));
                var paid_invoices_diff = Math.round((Number(current_stats.paid_amount) - Number(previous_stats.paid_amount)));
                var outstanding_invoices_diff = Math.round((Number(current_stats.outstanding_amount) - Number(previous_stats.outstanding_amount)));
                var overdue_invoices_diff = Math.round((Number(current_stats.overdue_amount) - Number(previous_stats.overdue_amount)));

                var finance_tab_html =		'<div class="invoice_summary_container" id="invoice_summary_container">';
                    finance_tab_html +=		'<table><tr>';
                    finance_tab_html +=			'<td class="invoice_summary_cell">';
                    finance_tab_html +=			'<div class="invoices_kpi_container" id="issued_invoices_container">';							
                    finance_tab_html +=				'<table>';
                    finance_tab_html +=					'<tr class="kpi_label_box">';
                    finance_tab_html +=						'<td class="issued_hr">';
                    finance_tab_html +=							'<span class="kpi_label">' + App.lang('Issued') + '</span>';
                    finance_tab_html +=							'<span class="kpi_invoice_count">' + "" + '</span>';
                    finance_tab_html +=						'</td>';
                    finance_tab_html +=					'</tr>';					
                    finance_tab_html +=					'<tr class="kpi_value"><td>';
                    finance_tab_html +=						'<span class="currency_symbol">' + currency_sign + '</span>';
                    finance_tab_html +=						'<span>' + abbrevate_number(Math.round(Number(current_stats.total_amount)), 2) + '</span>';
                    finance_tab_html +=					'</td></tr>';
                    finance_tab_html +=					'<tr class="kpi_stats_changed">';
                    finance_tab_html +=						'<td class="kpi_stats_changed_value">';
                    
                    if(issued_invoices_diff != 0) {
                		finance_tab_html +=							'<span class="up_down_indicator"><img src="' + ((issued_invoices_diff > 0)? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images')) + '"></span>';
                    	finance_tab_html +=							'<span class="currency_symbol">' + currency_sign + '</span>'; 
                    	finance_tab_html +=							'<span>' + abbrevate_number(Math.abs(issued_invoices_diff), 2) + '</span>';
	                } else {
	                	finance_tab_html += 						'<span>' + App.lang('no change') + '</span>';
	                }
                    finance_tab_html +=						'</td>';
                    finance_tab_html +=					'</tr>';
                    finance_tab_html +=				'</table>';
                    finance_tab_html +=			'</div>';
                    finance_tab_html +=			'</td>';

                    finance_tab_html +=			'<td class="invoice_summary_cell">';
                    finance_tab_html +=			'<div class="invoices_kpi_container" id="paid_invoices_container">';							
                    finance_tab_html +=				'<table>';
                    finance_tab_html +=					'<tr class="kpi_label_box">';
                    finance_tab_html +=						'<td class="issued_hr">';
                    finance_tab_html +=							'<span class="kpi_label">' + App.lang('Paid') + '</span>';
                    finance_tab_html +=							'<span class="kpi_invoice_count">' + "" + '</span>';
                    finance_tab_html +=						'</td>';
                    finance_tab_html +=					'</tr>';
                    finance_tab_html +=					'<tr class="kpi_value"><td>';
                    finance_tab_html +=						'<span class="currency_symbol">' + currency_sign + '</span>';
                    finance_tab_html +=						'<span>' + abbrevate_number(Math.round(Number(current_stats.paid_amount)), 2) + '</span>';
                    finance_tab_html +=					'</td></tr>';
                    finance_tab_html +=					'<tr class="kpi_stats_changed">';
                    finance_tab_html +=						'<td class="kpi_stats_changed_value">';
                    
                    if(paid_invoices_diff != 0) {
                		finance_tab_html +=							'<span class="up_down_indicator"><img src="' + ((paid_invoices_diff > 0)? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images')) + '"></span>';
                    	finance_tab_html +=							'<span class="currency_symbol">' + currency_sign + '</span>'; 
                    	finance_tab_html +=							'<span>' + abbrevate_number(Math.abs(paid_invoices_diff), 2) + '</span>';
	                } else {
	                	finance_tab_html += 						'<span>' + App.lang('no change') + '</span>';
	                }
                    finance_tab_html +=						'</td>';
                    finance_tab_html +=					'</tr>';
                    finance_tab_html +=				'</table>';
                    finance_tab_html +=			'</div>';
                    finance_tab_html +=			'</td>';

                    finance_tab_html +=			'<td class="invoice_summary_cell">';
                    finance_tab_html +=			'<div class="invoices_kpi_container" id="outstanding_invoices_container">';							
                    finance_tab_html +=				'<table>';
                    finance_tab_html +=					'<tr class="kpi_label_box">';
                    finance_tab_html +=						'<td class="issued_hr">';
                    finance_tab_html +=							'<span class="kpi_label">' + App.lang('Outstanding') + '</span>';
                    finance_tab_html +=							'<span class="kpi_invoice_count">' + "" + '</span>';
                    finance_tab_html +=						'</td>';
                    finance_tab_html +=					'</tr>';
                    finance_tab_html +=					'<tr class="kpi_value"><td>';
                    finance_tab_html +=						'<span class="currency_symbol">' + currency_sign + '</span>';
                    finance_tab_html +=						'<span>' + abbrevate_number(Math.round(Number(current_stats.outstanding_amount)), 2) + '</span>';
                    finance_tab_html +=					'</td></tr>';
                    finance_tab_html +=					'<tr class="kpi_stats_changed">';
                    finance_tab_html +=						'<td class="kpi_stats_changed_value">';
                    
                    if(outstanding_invoices_diff != 0) {
                		finance_tab_html +=							'<span class="up_down_indicator"><img src="' + ((outstanding_invoices_diff > 0)? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images')) + '"></span>';
                    	finance_tab_html +=							'<span class="currency_symbol">' + currency_sign + '</span>'; 
                    	finance_tab_html +=							'<span>' + abbrevate_number(Math.abs(outstanding_invoices_diff), 2) + '</span>';
	                } else {
	                	finance_tab_html += 						'<span>' + App.lang('no change') + '</span>';
	                }
                    finance_tab_html +=						'</td>';
                    finance_tab_html +=					'</tr>';
                    finance_tab_html +=				'</table>';
                    finance_tab_html +=			'</div>';
                    finance_tab_html +=			'</td>';

                    finance_tab_html +=			'<td class="invoice_summary_cell">';
                    finance_tab_html +=			'<div class="invoices_kpi_container" id="overdue_invoices_container">';							
                    finance_tab_html +=				'<table>';
                    finance_tab_html +=					'<tr class="kpi_label_box">';
                    finance_tab_html +=						'<td class="issued_hr">';
                    finance_tab_html +=							'<span class="kpi_label">' + App.lang('Overdue') + '</span>';
                    finance_tab_html +=							'<span class="kpi_invoice_count">' + "" + '</span>';
                    finance_tab_html +=						'</td>';
                    finance_tab_html +=					'</tr>';
                    finance_tab_html +=					'<tr class="kpi_value"><td>';
                    finance_tab_html +=						'<span class="currency_symbol">' + currency_sign + '</span>';
                    finance_tab_html +=						'<span>' + abbrevate_number(Math.round(Number(current_stats.overdue_amount)), 2) + '</span>';
                    finance_tab_html +=					'</td></tr>';
                    finance_tab_html +=					'<tr class="kpi_stats_changed">';
                    finance_tab_html +=						'<td class="kpi_stats_changed_value">';
                    
                    if(overdue_invoices_diff != 0) {
                		finance_tab_html +=							'<span class="up_down_indicator"><img src="' + ((overdue_invoices_diff > 0)? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images')) + '"></span>';
                    	finance_tab_html +=							'<span class="currency_symbol">' + currency_sign + '</span>'; 
                    	finance_tab_html +=							'<span>' + abbrevate_number(Math.abs(overdue_invoices_diff), 2) + '</span>';
	                } else {
	                	finance_tab_html += 						'<span>' + App.lang('no change') + '</span>';
	                }
                    finance_tab_html +=						'</td>';
                    finance_tab_html +=					'</tr>';
                    finance_tab_html +=				'</table>';
                    finance_tab_html +=			'</div>';
                    finance_tab_html +=			'</td>';
                    finance_tab_html +=		'</table></tr>';
                    finance_tab_html +=		'</div>';

                     //Search box to filter the results in the grid..
                    finance_tab_html +=		'<div class= "search_in_grid" id="search_in_grid">';
                    finance_tab_html += 		'<div class="search_filter" id="search_filter">';
                    finance_tab_html +=				'<img class="search_icon" src="' + App.Wireframe.Utils.assetUrl('icon/filter_icon.png', 'reports_plus', 'images') + '"/>';
                    finance_tab_html +=				'<input type="text" class="search_field" id="search_field" placeholder="' + App.lang('Filter') + '"/>';
                    finance_tab_html +=				'<img class="clear_search" id="clear_search" src="' + App.Wireframe.Utils.assetUrl('icon/clear_search_11x11.png', 'reports_plus', 'images') + '"/>';
                    finance_tab_html +=			'</div>';
                    finance_tab_html +=		'</div>';

                    finance_tab_html +=		'<div class="details_grid_container" id="details_grid_container">';
                    finance_tab_html +=		'</div>';
                    //end here..
                    
                    $("#invoice_details_container").empty();
                    $("#invoice_details_container").addClass('invoice_details_container');
                    $("#invoice_details_container").append(finance_tab_html);

                    //Fix: Highlighted/Selected month CSS was not proper..
                    $("#invoice_details_container").height($("#invoice_group_container").height());
        }
        
        
        var prepare_grid_data = function(response_data, month_selected) {
        	
               var metadata = [];
               var data = [];

        	metadata.push({ name: "fully_paid", label: App.lang('Paid'), datatype: "boolean", editable: false });
                metadata.push({ name: "invoice_num", label: App.lang("Invoice"), datatype: "string", editable: false });
                metadata.push({ name: "client_name", label: App.lang("Client"), datatype: "string", editable: false });
                metadata.push({ name: "project_name", label: App.lang("Project"), datatype: "string", editable: false });
                metadata.push({ name: "amount", label: App.lang("Amount"), datatype: "string", editable: false });
                metadata.push({ name: "due", label: " ", datatype: "string", editable: false });
                metadata.push({ name: "reminder", label: " ", datatype: "string", editable: false });
                
                var clients_map = response_data.data.clients;
                var projects_map = response_data.data.projects;
                
                if( month_selected != 'all'){
                    var invoice_data_rows = response_data.data.invoice_stats[month_selected];
                    // Getting a previous month 
                    var month_year = month_selected.split('-');
                    --month_year[1];
                    if(month_year[1] == 0){
                        month_year[1] = 12;
                        --month_year[0];
                    } 
                    
                    month = (month_year[1] < 10) ? '0' + String(month_year[1]) : month_year[1] ;
                    
                    var prev_month = month_year[0] + "-" + month;
                    invoice_summary(response_data.data.invoice_stats[month_selected], response_data.data.invoice_stats[prev_month]);
                    
                     if(invoice_data_rows.invoices) {
                        invoice_data_row = invoice_data_rows.invoices;
                        for(var i=0; i < Object.size(invoice_data_row); i++) {
                            data.push({ values: {
                                        		"fully_paid" 	: (invoice_data_row[i].status == 2) ? true : false,
                                                "invoice_num"	: invoice_data_row[i].number + "view_url" + invoice_data_row[i].view_url, 
                                                "client_name"	: (clients_map[(invoice_data_row[i].company_id)])? (clients_map[(invoice_data_row[i].company_id)]) : "-", 
                                                "project_name"	: (projects_map[(invoice_data_row[i].project_id)])? (projects_map[(invoice_data_row[i].project_id)]) : "-", 
                                                "amount"		: currency_sign + (Number(invoice_data_row[i].paid_amount)).toFixed(2) + " / " + currency_sign + (Number(invoice_data_row[i].total_amount)).toFixed(2), 
                                                "due"			: invoice_data_row[i].overdue_days,
                                                "reminder"		: (invoice_data_row[i].resend_email_url != 'null' ) ? invoice_data_row[i].resend_email_url : 'null' //TODO :: need to pass the resend URL here
                                            } 
                                        });
                        }
                        }
                    
                }else{
                    var invoice_data_rows = response_data.data.invoice_stats;
                    var invoice_data_length = Object.size(invoice_data_rows);

                    invoice_summary(response_data.data.all, response_data.data.prev_invoice_stats);

                    for(var key in invoice_data_rows) {
                        if((invoice_data_rows[key]).invoices && key != Date.parse(response_data.data.params.invoice_start).toString('yyyy-MM')) {
                            invoice_data_row = (invoice_data_rows[key]).invoices;
                            for(var i=0; i < Object.size(invoice_data_row); i++) {
                                data.push({ values: {
                                        	"fully_paid" 	: (invoice_data_row[i].status == 2) ? true : false,
                                                "invoice_num"	: invoice_data_row[i].number + "view_url" + invoice_data_row[i].view_url, 
                                                "client_name"	: (clients_map[(invoice_data_row[i].company_id)])? (clients_map[(invoice_data_row[i].company_id)]) : "-", 
                                                "project_name"	: (projects_map[(invoice_data_row[i].project_id)])? (projects_map[(invoice_data_row[i].project_id)]) : "-", 
                                                "amount"		: currency_sign + (Number(invoice_data_row[i].paid_amount)).toFixed(2) + " / " + currency_sign + (Number(invoice_data_row[i].total_amount)).toFixed(2), 
                                                "due"			: invoice_data_row[i].overdue_days, 
                                                "reminder"		: (invoice_data_row[i].resend_email_url != 'null' ) ? invoice_data_row[i].resend_email_url : 'null' 
                                        		} 
                                        });
                            }
                        }
                    }
                    
                
                }
                
                editable_grid = new EditableGrid ( "FinanceData" );
                editable_grid.load( { "metadata" : metadata , "data" : data } );
                editable_grid.setCellRenderer("invoice_num", new CellRenderer({ 
                        render: function (cell, value) { 
                                cell.innerHTML = "<a target='_blank' class='quick_view_item' href='"+ value.substring(value.indexOf('view_url') + 8) + "'>" + value.substring(0, value.indexOf('view_url')) + " </a>";
                                }
                }));

                // Show a green dot if the invoice is fully paid..
                editable_grid.setCellRenderer("fully_paid", new CellRenderer({ 
                        render: function (cell, value) { 
                                cell.innerHTML = (value == true) ? "<img src='" + App.Wireframe.Utils.assetUrl('icon/green_dot.png', 'reports_plus', 'images') + "'/>" : ""; }
                }));
                 
                 
                // Show a email icon, if the invoice is overdue..
                editable_grid.setCellRenderer("reminder", new CellRenderer({
                    render: function (cell, value) {
                        cell.innerHTML = (value !== 'null') ? "<a class='reminder_email' href='"+ value +"'><img src='" + App.Wireframe.Utils.assetUrl('icon/email_icon.png', 'reports_plus', 'images') + "'/></a>" : ""; }
                }));


                editable_grid.setCellRenderer("due", new CellRenderer({
                    render: function (cell, value) {
                        cell.innerHTML = ((value > 0)? (App.lang("by") + " " + Math.abs(value) + " " + App.lang("days")) : (App.lang("in") + " " + Math.abs(value) + " " + App.lang("days")));
                        cell.style.color = (value > 0) ?  "#CD0101" : "#525252";
                    }
                }));
                
               /*
                editable_grid.setHeaderRenderer("fully_paid", new CellRenderer({
                        render: function (cell, value) { 
                                cell.innerHTML = "<img src='" + App.Wireframe.Utils.assetUrl('icon/grey_dot.png', 'reports_plus', 'images') + "'/>";
                        }
                }));
                */
                //To turn off sorting on the last column..for now
                editable_grid.setHeaderRenderer("due", new CellRenderer({
                        render: function (cell, value) { 
                        cell.innerHTML = App.lang("Due");
                        cell.style.color = "#950000";
                        cell.style.fontWeight = "normal";
                        }
                }));
                
                editable_grid.renderGrid("details_grid_container", "finance_data_grid"); 
                
                //To filter/search using the search-keywords...
                var search_field = document.getElementById('search_field');
                search_field.addEventListener('keyup', function() {
                        editable_grid.filter(search_field.value);
                });
                                
                //On click of 'clear-search', clear the 'search_field' and also unset the 'editable_grid' filter property..
                $('#clear_search').click( function () {	
                        $('#search_field').val("");
                        editable_grid.filter("");
                });
                
                // Sending a reminder mail for the overdue invoices.
                $('.reminder_email').flyoutForm({
                        'width' : 400, 
                        'title' : App.lang('Resend Email'),
                        'href' : $(this).attr('href'),
                        'success_event' :'resend_email'
                    });
        }
        
        var reports_error = function(msg){
            $('#finance_main_container').html('<div class="reports_error_container"><div class="reports_error_class">'+ msg+'</div></div>');
        }
        
        var get_invoice_data = function(){

//            var what = 'invoiceStats';
            var start = $('#date_range_start input').val();
            var end = $('#date_range_end input').val();

            var finance_tab_html = '<div class="finance_main_container" id="finance_main_container">';

                finance_tab_html +=     '<div class="" id="invoice_stats_container">';
                finance_tab_html +=     '</div>';
                finance_tab_html +=     '<div class="" id="vertical_rule"></div>';
                
                finance_tab_html +=     '<div class="" id="invoice_group_container"></div>';
                
                finance_tab_html +=     '<div class="" id="invoice_details_container"></div>';
                finance_tab_html +=     '<div class="clear"></div>';

                finance_tab_html += '</div>';
            //end finance_main_container...

            $('#reports_tpl_container').html(finance_tab_html);


            $.ajax({
                    'url' : App.extendUrl( api_url, { 'async' : 1, 'what' : 'invoiceSummary'}),
                    'type' : 'get', 
                    'success' : function(response) {
                        if(response.data.empty == false){
                            invoice_kpi(response);
                        }else{
                            reports_error(App.lang('No Invoices Found !'));
                        }
                    }, 
                    'error' : function() {
                            // App.Wireframe.Flash.error('No Data Found');
                            reports_error(App.lang('No Data Found !'));
                    }
            });

            $.ajax({
                    'url' : App.extendUrl( api_url, { 'async' : 1, 'what' :'invoiceStats', 'start' :start , 'end' : end}),
                    'type' : 'get',
                    'success' : function(response) {
                                    if(response.data.all != undefined ){
                                        invoice_data_all = response; //  store data in Global array
                                        group_list(response);
                                        prepare_grid_data(response, 'all');

                                     }else{
                                        // App.Wireframe.Flash.error('No Invoices Found');
                                        reports_error(App.lang('No Invoices Found for this Date range.'));
                                     }
                                },
                    'error' : function() {
                            // App.Wireframe.Flash.error('No Data Found');
                            reports_error(App.lang('No Data Found !'));
                    }
            }); 
        }
        
        $('#button_load_finance').live('click', function (){
            get_invoice_data();
        });
        
        $(document).ready(function (){
            finance_tab();
            get_invoice_data();
            
	});
       
        // Changing dates according to the Date Ranges Provided
        $('#date_range_list').change(function(){
                var date_range = reports_enum.dates.finance[$(this).get(0).selectedIndex];
                $('#date_range_start input').datepicker('setDate', $.jsDate.strftime(date_range.start, default_date_format));
                $('#date_range_end input').datepicker('setDate', $.jsDate.strftime(date_range.end, default_date_format));
        }); 
        
        $('#items_group_list li').live("click", function() {
            prepare_grid_data(invoice_data_all, $(this).attr('id').substr(6) );
            $(this).parent().find('li').removeClass('month_list_items');
            $(this).addClass('month_list_items');

        });
        
  
</script>	