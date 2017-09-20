{add_bread_crumb}Dashboard{/add_bread_crumb}
  
<div id="project_tab_mobile" >
    <div class="projects_mobile_chart_container">
            <div id="projects_tracker_update" class="projects_tracker_update">
            </div>
            <div class="projects_chart_velocity">
                <div id="reports_burn_down_chart" class="reports_burn_down_chart"></div>
            </div>
                <div class="projects_tracker_update_footer"></div>
            <br class="clear"/>  
    </div>
    <div class="projects_mobile_detail_container">
                <div class="projects_mobile_stats_small_pie_container">
                    <div id="projects_mobile_stats_small_pie" class="projects_mobile_stats_small_pie"></div>
                </div>
                <div id="projects_mobile_stats_list" class="projects_mobile_stats_list">
                    <div id="projects_mobile_name_label" class="projects_mobile_name_label">{lang}All Projects{/lang}</div>
                    <div id= "project_hours_percent" class="project_hours_percent"></div>
                </div>
                <br class="clear"/>  
    </div>
    <div id="company_project_objects" class="company_project_objects"></div>
    
    
</div>
				


<script type="text/javascript" language="javascript">
    
    
     $.jqplot.LineRenderer.prototype.draw = function(ctx, gd, options, plot) {
     
        var i;
        // get a copy of the options, so we don't modify the original object.
        var opts = $.extend(true, {}, options);
        var shadow = (opts.shadow != undefined) ? opts.shadow : this.shadow;
        var showLine = (opts.showLine != undefined) ? opts.showLine : this.showLine;
        var fill = (opts.fill != undefined) ? opts.fill : this.fill;
        var fillAndStroke = (opts.fillAndStroke != undefined) ? opts.fillAndStroke : this.fillAndStroke;
        var xmin, ymin, xmax, ymax;
        ctx.save();
        if (gd.length) {
            if (showLine) {
                // if we fill, we'll have to add points to close the curve.
                if (fill) {
                    if (this.fillToZero) {
                        // have to break line up into shapes at axis crossings
                        var negativeColor = this.negativeColor;
                        if (! this.useNegativeColors) {
                            negativeColor = opts.fillStyle;
                        }
                        var isnegative = false;
                        var posfs = opts.fillStyle;

                        // if stoking line as well as filling, get a copy of line data.
                        if (fillAndStroke) {
                            var fasgd = gd.slice(0);
                        }
                        // if not stacked, fill down to axis
                        if (this.index == 0 || !this._stack) {

                            var tempgd = [];
                            var pd = (this.renderer.smooth) ? this.renderer._smoothedPlotData : this._plotData;
                            this._areaPoints = [];
                            var pyzero = this._yaxis.series_u2p(this.fillToValue);
                            var pxzero = this._xaxis.series_u2p(this.fillToValue);

                            opts.closePath = true;

                            if (this.fillAxis == 'y') {
                                tempgd.push([gd[0][0], pyzero]);
                                this._areaPoints.push([gd[0][0], pyzero]);

                                for (var i=0; i<gd.length-1; i++) {
                                    tempgd.push(gd[i]);
                                    this._areaPoints.push(gd[i]);
                                    // do we have an axis crossing?
                                    if (pd[i][1] * pd[i+1][1] < 0) {
                                        if (pd[i][1] < 0) {
                                            isnegative = true;
                                            opts.fillStyle = negativeColor;
                                        }
                                        else {
                                            isnegative = false;
                                            opts.fillStyle = posfs;
                                        }

                                        var xintercept = gd[i][0] + (gd[i+1][0] - gd[i][0]) * (pyzero-gd[i][1])/(gd[i+1][1] - gd[i][1]);
                                        tempgd.push([xintercept, pyzero]);

                                        // now empty temp array and continue
                                        tempgd = [[xintercept, pyzero]];
                                        // this._areaPoints = [[xintercept, pyzero]];
                                    }
                                }
                                if (pd[gd.length-1][1] < 0) {
                                    isnegative = true;
                                    opts.fillStyle = negativeColor;
                                }
                                else {
                                    isnegative = false;
                                    opts.fillStyle = posfs;
                                }
                                tempgd.push(gd[gd.length-1]);
                                this._areaPoints.push(gd[gd.length-1]);
                                                                tempgd.push([gd[gd.length-1][0], pyzero]);
                                this._areaPoints.push([gd[gd.length-1][0], pyzero]);
                                                            }
                                                            // now draw the last area.
                                                            if (shadow) {
                                this.renderer.shadowRenderer.draw(ctx, tempgd, opts);
                                                            }
                                this.renderer.shapeRenderer.draw(ctx, tempgd, opts);
                        }
                        // if stacked, fill to line below
                        else {
                            var prev = this._prevGridData;
                            for (var i=prev.length; i>0; i--) {
                                gd.push(prev[i-1]);
                                // this._areaPoints.push(prev[i-1]);
                            }
                            if (shadow) {
                                this.renderer.shadowRenderer.draw(ctx, gd, opts);
                            }
                            this._areaPoints = gd;
                            this.renderer.shapeRenderer.draw(ctx, gd, opts);
                        }
                    }
                    /////////////////////////
                    // Not filled to zero
                    ////////////////////////
                    else {
                        // if stoking line as well as filling, get a copy of line data.
                        if (fillAndStroke) {
                            var fasgd = gd.slice(0);
                        }
                        // if not stacked, fill down to axis
                        if (this.index == 0 || !this._stack) {
                            // var gridymin = this._yaxis.series_u2p(this._yaxis.min) - this.gridBorderWidth / 2;
                            var gridymin = ctx.canvas.height;
                            // IE doesn't return new length on unshift
                            gd.unshift([gd[0][0], gridymin]);
                            var len = gd.length;
                            gd.push([gd[len - 1][0], gridymin]);
                        }
                        // if stacked, fill to line below
                        else {
                            var prev = this._prevGridData;
                            for (var i=prev.length; i>0; i--) {
                                gd.push(prev[i-1]);
                            }
                        }
                        this._areaPoints = gd;

                        if (shadow) {
                            this.renderer.shadowRenderer.draw(ctx, gd, opts);
                        }

                        this.renderer.shapeRenderer.draw(ctx, gd, opts);
                    }
                    if (fillAndStroke) {
                        var fasopts = $.extend(true, {}, opts, { fill:false, closePath:false });
                        this.renderer.shapeRenderer.draw(ctx, fasgd, fasopts);
                        //////////
                        // TODO: figure out some way to do shadows nicely
                        // if (shadow) {
                        // this.renderer.shadowRenderer.draw(ctx, fasgd, fasopts);
                        // }
                        // now draw the markers
                        if (this.markerRenderer.show) {
                            if (this.renderer.smooth) {
                                fasgd = this.gridData;
                            }
                        // change to have only one marker, laste one
                        //                            for (i=0; i<fasgd.length; i++) {
                        // this.markerRenderer.draw(fasgd[i][0], fasgd[i][1], ctx, opts.markerOptions);
                        //                            }
                        var i1= fasgd.length - 1;
                        this.markerRenderer.draw(fasgd[i1][0], fasgd[i1][1], ctx, opts.markerOptions);

                        }
                    }
                }
                else {

                    if (this.renderer.bands.show) {
                        var bdat;
                        var bopts = $.extend(true, {}, opts);
                        if (this.renderer.bands.showLines) {
                            bdat = (this.renderer.smooth) ? this.renderer._hiBandSmoothedData : this.renderer._hiBandGridData;
                            this.renderer.shapeRenderer.draw(ctx, bdat, opts);
                            bdat = (this.renderer.smooth) ? this.renderer._lowBandSmoothedData : this.renderer._lowBandGridData;
                            this.renderer.shapeRenderer.draw(ctx, bdat, bopts);
                        }

                        if (this.renderer.bands.fill) {
                            if (this.renderer.smooth) {
                                bdat = this.renderer._hiBandSmoothedData.concat(this.renderer._lowBandSmoothedData.reverse());
                            }
                            else {
                                bdat = this.renderer._hiBandGridData.concat(this.renderer._lowBandGridData.reverse());
                            }
                            this._areaPoints = bdat;
                            bopts.closePath = true;
                            bopts.fill = true;
                            bopts.fillStyle = this.renderer.bands.fillColor;
                            this.renderer.shapeRenderer.draw(ctx, bdat, bopts);
                        }
                    }

                    if (shadow) {
                        this.renderer.shadowRenderer.draw(ctx, gd, opts);
                    }

                    this.renderer.shapeRenderer.draw(ctx, gd, opts);
                }
            }
            // calculate the bounding box
            var xmin = xmax = ymin = ymax = null;
            for (i=0; i<this._areaPoints.length; i++) {
                var p = this._areaPoints[i];
                if (xmin > p[0] || xmin == null) {
                    xmin = p[0];
                }
                if (ymax < p[1] || ymax == null) {
                    ymax = p[1];
                }
                if (xmax < p[0] || xmax == null) {
                    xmax = p[0];
                }
                if (ymin > p[1] || ymin == null) {
                    ymin = p[1];
                }
            }

            if (this.type === 'line' && this.renderer.bands.show) {
                ymax = this._yaxis.series_u2p(this.renderer.bands._min);
                ymin = this._yaxis.series_u2p(this.renderer.bands._max);
            }

            this._boundingBox = [[xmin, ymax], [xmax, ymin]];

            // now draw the markers
            if (this.markerRenderer.show && !fill) {
                if (this.renderer.smooth) {
                    gd = this.gridData;
                }
                
                 var i1= gd.length - 1;
                        this.markerRenderer.draw(gd[i1][0], gd[i1][1], ctx, opts.markerOptions);
//                for (i=0; i<gd.length; i++) {
//                    if (gd[i][0] != null && gd[i][1] != null) {
//                        this.markerRenderer.draw(gd[i][0], gd[i][1], ctx, opts.markerOptions);
//                    }
//                }
            }
        }

        ctx.restore();
    };
    
    
	
	var project_tab = function (){
            get_project_data(projects_data[0]['data']);
	}  //project tab

	var company_stats_panel = function (data){

		var company_stats_html ='';
		var hours = (data.hours) ? data.hours : 0;
		var files = (data.files != undefined) ? data.files : 0;
//		var filesize = (data.filesize != undefined) ? data.filesize : 0;
		var discussion_open = (data.project_objects != undefined && data.project_objects.Discussion != undefined) ? ((data.project_objects.Discussion.open != undefined) ? data.project_objects.Discussion.open : 0 ) : 0; 
		
		company_stats_html += 		'<div class="project_objects_cell"><div class="project_objects_value">' + data.projects + '</div><div class="project_objects_label">' + App.lang('Projects') + '</div></div>';
		company_stats_html += 		'<div class="project_objects_cell"><div class="project_objects_value">' + data.users + '</div><div class="project_objects_label">' + App.lang('Users') + '</div></div>';
		company_stats_html += 		'<div class="project_objects_cell"><div class="project_objects_value">' + discussion_open + '</div><div class="project_objects_label">' + App.lang('Discussion') + '</div></div>';
		company_stats_html += 		'<div class="project_objects_cell"><div class="project_objects_value">' + files + '</div><div class="project_objects_label">' + App.lang('Files') + '</div></div>';
//		company_stats_html += 		'<div class="project_objects_cell"><div class="project_objects_value">' + filesize + '</div><div class="project_objects_label">' + App.lang('File Size') + '</div></div>';
//		company_stats_html += 		'<div class="project_objects_cell"><div class="project_objects_value">' + hours + '</div><div class="project_objects_label">' + App.lang('Hours Logged') + '</div></div>';
		
		if(data.project_objects != undefined ){
				
			if( data.project_objects.Task != undefined ){
				var Task_open = ( data.project_objects.Task.open != undefined) ? data.project_objects.Task.open : 0;
				var Task_completed = (data.project_objects.Task.completed != undefined) ? data.project_objects.Task.completed : 0;
				company_stats_html += 		'<div class="project_objects_cell"><div class="project_objects_value">' + Task_open + ' / ' + Task_completed + '</div><div class="project_objects_label">' + App.lang('Tasks: Open / Completed') + '</div></div>';
			}
			if( data.project_objects.TodoList != undefined ){
				var TodoList_open = ( data.project_objects.TodoList.open != undefined) ? data.project_objects.TodoList.open : 0;
				var TodoList_completed = (data.project_objects.TodoList.completed != undefined) ? data.project_objects.TodoList.completed : 0;
				company_stats_html += 		'<div class="project_objects_cell"><div class="project_objects_value">' + TodoList_open + ' / ' + TodoList_completed +  '</div><div class="project_objects_label">' + App.lang('Todo Lists: Open / Completed') + '</div></div>';
			}
			if( data.project_objects.Notebook != undefined ){
				var Notebook_open = (data.project_objects.Notebook.open != undefined) ? data.project_objects.Notebook.open : 0;
				var Notebook_completed = (data.project_objects.Notebook != undefined && data.project_objects.Notebook.completed != undefined) ? data.project_objects.Notebook.completed : 0;
				company_stats_html += 		'<div class="project_objects_cell"><div class="project_objects_value">' + Notebook_open + ' / ' + Notebook_completed +  '</div><div class="project_objects_label">' + App.lang('NoteBooks: Open / Completed') + '</div></div>';
			}
		}
		company_stats_html += 	'<br class="clear"/>';

		$('#company_project_objects').html(company_stats_html);

	}// Company stats

	//get data for Project tab
	var get_project_data = function (projects_load){
		var what = 'getProjectStats';
		var projects_arr = '';
		var start = (reports_filters != null) ? Date.parse(reports_filters['start']).toString('dd/M/yy') : '';
		var end = (reports_filters != null) ? Date.parse(reports_filters['end']).toString('dd/M/yy') : '';

		if(projects_load != null){
		 $.ajax({
		  'url' : App.extendUrl( api_url, { 'async' : 1, 'what' : what, 'projects' : projects_load, 'start' : start, 'end' : end }),
	          'type' : 'get', 
	          'success' : function(response) {
		  		if(typeof response.data != undefined){
//		  			if(response.data.primary.iterations.length > 0 ) {
                                                project_stats_panel(response.data);
		  				burn_down_chart(response.data.primary.iterations);
			 			
//		  			} else {
//		  				display_error('No Data Found', '#projects_mobile_detail_container');
//		  			}
		  		}else{
		  			display_error('No Data Found', '#projects_mobile_detail_container');
		  		}
			  }, 
	          'error' : function() {
				  display_error('No Data Found', '#projects_mobile_detail_container');
	          }
		  });
		}else {
			display_error('No Match Found', '#projects_mobile_detail_container');
		}
                if($.inArray('companies', available_reports_tabs) > 0){
                
                    var what = 'getCompanyStats';
                    var status =  '';
                    $.ajax({
                       'url' : App.extendUrl( api_url, { 'async' : 1, 'what' : what, 'companies' : companies, 'status': status }),
                      'type' : 'get', 
                      'success' : function(response) {
                                            company_stats_panel(response.data);
                              }, 
                      'error' : function() {
                                      display_error('No Data Found', '#company_mobile_detail_container');
                      }
                      });	
               }
        
        
	}//get data for Project tab
	
	//Projects stats
	var project_stats_panel = function (data){
		var projects_summary = data.summary;
		var velocity = data.primary.iterations[data.primary.iterations.length -1]['velocity'];
                var velocity_current  = velocity;
		var velocity_previous = velocity_current - data.primary.iterations[data.primary.iterations.length -2]['velocity'];
		var hours_current = data.primary.iterations[data.primary.iterations.length -1]['hours_in_iteration'];
		var hours_previous = hours_current - data.primary.iterations[data.primary.iterations.length -2]['hours_in_iteration'];
		var forecasted = (data.primary.forecast.forecasted == '-1' ) ? App.lang('Never') : $.jsDate.strftime(data.primary.forecast.forecasted, default_date_format);
                
               
                var projects_tracker_html = '<div class="tracker_heading">' + App.lang('This Week') + '</div>'+
                    '<div class="projects_tracker_velocity"><span class="value_heading">'+ App.lang('Velocity') +'</span>'+
                    '<span class="current">'+ velocity_current+'</span>';
                
                    projects_tracker_html += (velocity_previous != 0) ? ('<span class="previous"><img src="' + ((velocity_previous > 0) ? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images'))  + '" class="small_icon">' + Math.abs(velocity_previous) +'</span>') : '';
                    projects_tracker_html += '</div>';
                    projects_tracker_html += '<div class="projects_tracker_hours"><span class="value_heading">'+ App.lang('Hours') +'</span><span class="current">'+ hours_current+'</span>';
                    projects_tracker_html += (hours_previous != 0) ?  ('<span class="previous"><img src="' + ((hours_previous > 0) ? App.Wireframe.Utils.assetUrl('icon/green_triangle_up.gif','reports_plus', 'images') : App.Wireframe.Utils.assetUrl('icon/red_triangle_down.gif','reports_plus', 'images'))  + '" class="small_icon">' + Math.abs(hours_previous)+'</span>') : '';
                    projects_tracker_html += '</div>'
                    $('#projects_tracker_update').html(projects_tracker_html);
                
                
                // Pie chart data
                var open_comp = [[App.lang('completed'), projects_summary.total_tasks_count - projects_summary.open_tasks_count], [App.lang('open'), projects_summary.open_tasks_count]];
                small_pie_chart('projects_mobile_stats_small_pie',open_comp);
		
                var project_stats_html = '';
		project_stats_html += 	'<div class="project_hours"><img src="'+ App.Wireframe.Utils.assetUrl('icon/clock.gif','reports_plus', 'images') + '" alt="" class="small_icon"><div class ="info_label_bold"> ' + projects_summary.hours + App.lang('h') +'</div></div><div class="project_percent"><img src="'+ App.Wireframe.Utils.assetUrl('icon/checked.gif','reports_plus', 'images') + '" alt="" class="small_icon"><div class="info_label info_label_bold">' + projects_summary.percent_done  + App.lang('% done') + ' </div></div></div>';
		if ( projects_summary.next_milestone_days != undefined && projects_summary.next_milestone_days != 'TBD'){
			project_stats_html += '<div class="project_next_milestone"><span class="info_label_bold">' + App.lang('Next') +': </span> <span class="info_label_bold">' + projects_summary.next_milestone + ', ' + parseInt(projects_summary.next_milestone_days) * -1 + App.lang(' days late') + '</span></div>';
		}
			
		$('#project_hours_percent').append(project_stats_html);
		
		$('#projects_details_velocity_container').html(
				'<div class="projects_details_velocity"><div id="project_velocity" class="project_velocity"><img src="'+ App.Wireframe.Utils.assetUrl('icon/launch.png','reports_plus', 'images') + '" class="small_icon"><div class="info_label">' + App.lang('Velocity: ') + velocity + 
				'</div></div> <div id="project_forecast" class="project_forecast"> <img src="'+ App.Wireframe.Utils.assetUrl('icon/clock.gif','reports_plus', 'images') + '" alt="" class="small_icon"><div class="info_label">' + forecasted + '</div></div><div>'
				);
		
	$('.projects_mobile_detail_container').height($('.projects_mobile_stats_small_pie_container').height());
			
	}// Projects stats

	
	// Burn_down Chart 
	var burn_down_chart = function (stats){
		var burn_down_data = [];
                if(stats.length <=  0){
                display_error('No Data Found', '#reports_burn_down_chart');
                    return;
                }
		for(var i=0; i < stats.length; i++) {
			burn_down_data[i] = [];
			burn_down_data[i][0]= Date.parse(stats[i]['date']).toString('dd-MMM-yy');
			burn_down_data[i][1]= parseInt(stats[i]['open']);
		}
                
                
		$('#reports_burn_down_chart').empty();
		var burn_down_legend_labels = [ App.lang('Open Items')];
		var series_colors = [ "#00355F" ];
		
		var burn_down = $.jqplot('reports_burn_down_chart', [burn_down_data],  {
			animate: true,
			animateReplot : true,

                        seriesDefaults: {
                               showTooltip:false,
                               linePattern: 'smooth',
                               rendererOptions: {
                                           smooth: false
                                           },
                               lineWidth: 3,
                               fill: true, //to fill the line area to the axes
                               fillAndStroke: true,
                               fillColor: '#C8E5FF',
                               showMarker:true,
                               showLine: true // shows the graph trend line
                           },
			seriesColors: series_colors,
                        
                        series: [
                               { xaxis: 'xaxis', yaxis: 'yaxis', showTicks: false, shadow: false, markerOptions: { style:"circle" } },
                           ],
                        
			axes: {
				xaxis: {
                                        showTicks: false,
                                        renderer: $.jqplot.CategoryAxisRenderer,
                                        tickOptions: {
                                                formatter: $.jqplot.DateTickFormatter,
                                                showGridline : false,
                                                show: false
                                        }
				},
				yaxis: {
                                        showTicks: false,
                                         tickOptions: {
                                                showGridline : false
                                        }
				}
			},
			
                        grid: {
                            backgroundColor: 'transparent',
                            drawBorder: false,
                            shadow: false
                        },
                        highlighter: {
                            show: true,
                            showTooltip: false, //turn off tooltip
                            showMarker: false  //turn of the marker, which shows up at each point
                        },
                       
                        axesDefaults: {
                            
                            rendererOptions: {
                                baselineWidth: 1.5,
                                drawBaseline: false // property to hide the axes from the graph
                            }

                        }
			
		});
	}// Burn_down Chart
	
	$(document).ready(function (){
		project_tab();
	}); 

	
</script>
