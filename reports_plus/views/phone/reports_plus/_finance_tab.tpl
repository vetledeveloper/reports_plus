{add_bread_crumb}Finance{/add_bread_crumb}

    <div id="finance_tab_mobile">
        <div id="invoice_details_container" class="invoice_details_container"></div>
        <div id="finance_kpi_container" class="finance_kpi_container"></div>
    </div>

<script type="text/javascript" language="javascript">
    
        var invoice_data_all = {}; //global array to hold data
        var currency_sign = '$'; //global variable for currency symbol..
        
		var abbrevate_number = function (number, decPlaces) {
		    decPlaces = Math.pow(10,decPlaces);
		    var abbrev = [ "k", "m", "b", "t" ];
		    for (var i=abbrev.length-1; i>=0; i--) {
		        var size = Math.pow(10,(i+1)*3);
		        if(size <= number) {
		             number = Math.round(number*decPlaces/size)/decPlaces;
		             if((number == 1000) && (i < abbrev.length - 1)) {
		                 number = 1;
		                 i++;
		             }
		             number += abbrev[i];
		             break;
		        }
		    }
		    return number;
		}
		
        //Function to format the currency values..
        var format_currency = function(value, max_length) {
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
			if(((String(value).length) + counter) > max_length) {
				var formatted_value = abbrevate_number(value, 2);
        	}
    		return String(formatted_value);
		}
        
	var finance_tab = function () {
	} //finance_tab
	
        var invoice_kpi = function(response) {

            var invoice_kpi_response = response.data;
            var year_to_date_change = (Number(invoice_kpi_response.year_to_date.current) - Number(invoice_kpi_response.year_to_date.previous));
            var month_to_date_change = (Number(invoice_kpi_response.month_to_date.current) - Number(invoice_kpi_response.month_to_date.previous));
            var paid_on_time_change = (Number(invoice_kpi_response.paid_on_time.value) - Number(invoice_kpi_response.paid_on_time.value_prev));
            
            var finance_tab_html =  '<div class="general_stats_container" id="general_stats_container">';
                finance_tab_html +=     '<div class="kpi_cell_container"><div class="kpi_cell">';			
                finance_tab_html +=         '<span class="kpi_heading"><span class="kpi_label">' + App.lang('Year To Date') + '</span></span>';
                finance_tab_html +=         '<div class="amount_kpi_underline"></div>';
                finance_tab_html +=         '<span class="kpi_current"><span class="kpi_current_value">'+ currency_sign + abbrevate_number(invoice_kpi_response.year_to_date.current, 2) + '</span></span>';
                if(year_to_date_change != 0){
                    finance_tab_html +=         '<span class="kpi_difference"><img src="' + ((year_to_date_change > 0)? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images')) + '">';
                    finance_tab_html +=         '<span class="kpi_difference_value">'+ currency_sign + abbrevate_number(Math.abs(year_to_date_change), 2) + '</span></span>';
                }
                finance_tab_html +=	'</div></div>';
                
                finance_tab_html +=     '<div class="kpi_cell_container"><div class="kpi_cell">';			
                finance_tab_html +=         '<span class="kpi_heading"><span class="kpi_label">' + App.lang('Month To Date') + '</span></span>';
                finance_tab_html +=         '<div class="amount_kpi_underline"></div>';
                finance_tab_html +=         '<span class="kpi_current"><span class="kpi_current_value">'+ currency_sign + abbrevate_number(invoice_kpi_response.month_to_date.current, 2) + '</span></span>';
                if(month_to_date_change != 0){
                    finance_tab_html +=         '<span class="kpi_difference"><img src="' +  ((month_to_date_change > 0)? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images')) + '">';
                    finance_tab_html +=         '<span class="kpi_difference_value">'+ currency_sign + abbrevate_number(Math.abs(month_to_date_change), 2) + '</span></span>';
                }
                finance_tab_html +=	'</div></div>';
                
                finance_tab_html +=     '<div class="kpi_cell_container kpi_cell_last"> <div class="kpi_cell">';			
                finance_tab_html +=         '<span class="kpi_heading"><span class="kpi_label ">' + App.lang('Paid On Time') + '</span></span>';
                finance_tab_html +=         '<div class="amount_kpi_underline"></div>';
                finance_tab_html +=         '<span class="kpi_current"><span class="kpi_current_value">'+ abbrevate_number(invoice_kpi_response.paid_on_time.value, 2) + '%' + '</span></span>';
                if(paid_on_time_change != 0){
                    finance_tab_html +=         '<span class="kpi_difference"><img src="' + ((paid_on_time_change > 0)? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images')) + '">';
                    finance_tab_html +=         '<span class="kpi_difference_value">'+ abbrevate_number(Math.abs(paid_on_time_change), 2) + '%' + '</span></span>';
                }
                finance_tab_html +=	'</div></div>';
                finance_tab_html += '</div>';
                
                $("#finance_kpi_container").empty();
                $("#finance_kpi_container").append(finance_tab_html);
        }

      var invoice_summary = function(current_stats, previous_stats) {
      
                var issued_invoices_diff = Math.round((Number(current_stats.total_amount) - Number(previous_stats.total_amount)));
                var paid_invoices_diff = Math.round((Number(current_stats.paid_amount) - Number(previous_stats.paid_amount)));
                var outstanding_invoices_diff = Math.round((Number(current_stats.outstanding_amount) - Number(previous_stats.outstanding_amount)));
                var overdue_invoices_diff = Math.round((Number(current_stats.overdue_amount) - Number(previous_stats.overdue_amount)));
                
                var finance_tab_html = '<div class="invoice_summary_container" id="invoice_summary_container">';
                    
                    finance_tab_html +=	'<div class="invoice_summary_cell issued_details">';
                    finance_tab_html += '<div class="amount_kpi_heading heading_issued"><span class="amount_kpi_label">' + App.lang('Issued') + '</span></div>';
                    finance_tab_html += '<div class="amount_kpi_underline label_issued"></div>';
                    finance_tab_html += '<span class="kpi_current"><span class="kpi_current_value">'+ currency_sign + abbrevate_number(Math.round(Number(current_stats.total_amount)), 2) + '</span></span>';
                    if(issued_invoices_diff != 0){
                        finance_tab_html += '<span class="kpi_difference"><img src="' +  ((issued_invoices_diff > 0)? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images')) + '">';
                        finance_tab_html += '<span class="kpi_difference_value">' + currency_sign + abbrevate_number(Math.round(Number(issued_invoices_diff)), 2)+ '</span></span>';
                    }
                    finance_tab_html += '</div>';
                    
                    finance_tab_html +=	'<div class="invoice_summary_cell paid_details">';
                    finance_tab_html += '<div class="amount_kpi_heading heading_paid"><span class="amount_kpi_label">' + App.lang('Paid') + '</span></div>';
                    finance_tab_html += '<div class="amount_kpi_underline label_paid"></div>';
                    finance_tab_html += '<span class="kpi_current"><span class="kpi_current_value">'+ currency_sign +  abbrevate_number(Math.round(Number(current_stats.paid_amount)), 2) + '</span></span>';
                    if(paid_invoices_diff != 0){
                        finance_tab_html += '<span class="kpi_difference"><img src="' + ((paid_invoices_diff > 0)? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images')) + '">';
                        finance_tab_html += '<span class="kpi_difference_value">'+ currency_sign + abbrevate_number(Math.round(Number(paid_invoices_diff)), 2)+ '</span></span>';
                    }
                    finance_tab_html += '</div>';
                    
                    finance_tab_html +=	'<div class="invoice_summary_cell outstanding_details">';
                    finance_tab_html += '<div class="amount_kpi_heading heading_outstanding"><span class="amount_kpi_label">' + App.lang('Outstanding') + '</span></div>';
                    finance_tab_html += '<div class="amount_kpi_underline  label_outstanding"></div>';
                    finance_tab_html += '<span class="kpi_current"><span class="kpi_current_value">'+ currency_sign +  abbrevate_number(Math.round(Number(current_stats.outstanding_amount)), 2) + '</span></span>';
                    if(outstanding_invoices_diff != 0){
                        finance_tab_html += '<span class="kpi_difference"><img src="' + ((outstanding_invoices_diff > 0)? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images')) + '">';
                        finance_tab_html += '<span class="kpi_difference_value">'+ currency_sign + abbrevate_number(Math.round(Number(outstanding_invoices_diff)), 2)+ '</span></span>';
                    }
                    finance_tab_html += '</div>';
                    
                    finance_tab_html +=	'<div class="invoice_summary_cell overdue_details">';
                    finance_tab_html += '<div class="amount_kpi_heading heading_overdue"><span class="amount_kpi_label">' + App.lang('Overdue') + '</span></div>';
                    finance_tab_html += '<div class="amount_kpi_underline  label_overdue"></div>';
                    finance_tab_html += '<span class="kpi_current"><span class="kpi_current_value">'+ currency_sign +  abbrevate_number(Math.round(Number(current_stats.overdue_amount)), 2) + '</span></span>';
                    if(overdue_invoices_diff != 0){
                        finance_tab_html += '<span class="kpi_difference"><img src="' +  ((overdue_invoices_diff > 0)? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images')) + '">';
                        finance_tab_html += '<span class="kpi_difference_value">'+ currency_sign + abbrevate_number(Math.round(Number(overdue_invoices_diff)), 2)+ '</span></span>';
                    }
                    finance_tab_html += '</div>';
                    finance_tab_html +=	'</div>';
             
                    $("#invoice_details_container").empty();
                    $("#invoice_details_container").append(finance_tab_html);
        }
        
        var get_invoice_data = function(){
            var what = 'invoiceStats';
            var start = '';
            var end = '';
            $.ajax({
                    'url' : App.extendUrl( api_url, { 'async' : 1, 'what' : what, 'start' :start , 'end' : end}),
                    'type' : 'get',
                    'success' : function(response) {
                                    if(response.data.all != undefined ){
                                          invoice_summary(response.data.all, response.data.prev_invoice_stats);

                                     }else{
                                        App.Wireframe.Flash.error('No Invoices Found');
                                     }
                                },
                    'error' : function() {
                            App.Wireframe.Flash.error('No Data Found');
                    }
            }); 

            $.ajax({
                    'url' : App.extendUrl( api_url, { 'async' : 1, 'what' : 'invoiceSummary'}),
                    'type' : 'get', 
                    'success' : function(response) {
                                invoice_kpi(response);
                            }, 
                    'error' : function() {
                            App.Wireframe.Flash.error('No Data Found');
                    }
            });
        }
        
        $(document).ready(function (){
//            finance_tab();
            get_invoice_data();
            
	});
  
  
</script>	