<?php

// Build on top of reports module
AngieApplication::useController ( 'reports', REPORTS_FRAMEWORK_INJECT_INTO );

/**
 * TimeReportsPlus controller implementation
 *
 * @package custom.modules.time_report_plus
 * @subpackage controllerslIST
 * 
 * 
 */
class TimeReportsPlusController extends ReportsController {
	
	/**
	 * Active tracking report
	 *
	 * @var TimeReportsPlus
	 */
	protected $active_tracking_report;
	protected $check_reports_access_permissions = false;
	protected $active_project;
	
	/**
	 * Invoice controller delegate
	 * 
	 * @var InvoiceController
	 */
	protected $invoice_delegate;
	
	var $projects_map = array ();
	var $companies_map = array ();
	var $users_map = array ();
	var $categories_map = array ();
	var $billing_array = array ();
	var $job_types_array = array ();
	
	/**
	 * Construct controller
	 *
	 * @param Request $parent
	 * @param mixed $context
	 */
	function __construct($parent, $context = null) {
		parent::__construct ( $parent, $context );
		if (AngieApplication::isModuleLoaded ( 'invoicing' ) && $this->getControllerName () == 'time_reports_plus') {
			$this->invoice_delegate = $this->__delegate ( 'invoice_based_on', INVOICING_MODULE, 'tracking_report' );
		} // if
	}
	
	// __construct
	

	/**
	 * Prepare controller
	 */
	function __before() {
		parent::__before ();
		// if($this->logged_user->isProjectManager() || $this->logged_user->isPeopleManager()) {
		$report_id = $this->request->getId ( 'tracking_report_id' );
		if ($report_id) {
			$this->active_tracking_report = TimeReportsPlus::findById ( $report_id );
		} // if
		

		if ($this->active_tracking_report instanceof TrackingReportPlus) {
			$this->wireframe->breadcrumbs->add ( 'time_reports_plus', $this->active_tracking_report->getName (), $this->active_tracking_report->getViewUrl () );
		} else {
			$this->active_tracking_report = new TrackingReportPlus ();
			if($this->active_tracking_report instanceof TrackingReportPlus){
				
			}
		} // if
		
		if (AngieApplication::isModuleLoaded ( 'invoicing' ) && $this->invoice_delegate instanceof InvoiceBasedOnController) {
			$this->invoice_delegate->__setProperties ( array ('active_object' => &$this->active_tracking_report ) );
		} // if


		$this->response->assign ( 'active_tracking_report', $this->active_tracking_report );
		/* } else {
          $this->response->forbidden();
          } // if */
	}
	
	// __construct
	

	/**
	 * Show tracking report form and options
	 */
	function index() {
		
		$this->wireframe->setPageTitle ( lang ( "Time Reports Plus" ) );
		$this->wireframe->breadcrumbs->add ( 'filter_time', 'Filter Time' );

		$condition = '';
		$companies = $all_companies = Companies::getIdNameMap ( null, STATE_VISIBLE );
		$categories = $all_categories = Categories::getIdNameMap ( null, 'ProjectCategory' );

		if (! $this->logged_user->canUseReports ()) {
			$visible_ids = TrackingReportPlus::getVisibleProjectIds ( $this->logged_user );
			
			if (count ( $visible_ids )) {
				$condition = DB::prepare ( '(' . TABLE_PREFIX . 'projects.id IN (?))', $visible_ids );
				$visible_companies_ids = TimeReportsPlus::getCompanyIdsByProjectIds($visible_ids);
				$visible_category_ids =  TimeReportsPlus::getCategoryIdsByProjectIds($visible_ids);
			}
			$categories = is_foreachable ( $visible_category_ids ) ? array_intersect_key ( $all_categories, array_flip ( $visible_category_ids ) ) : null;
			$companies =  is_foreachable ( $visible_companies_ids ) ? array_intersect_key ( $all_companies, array_flip ( $visible_companies_ids ) ) : null;
		}
		$projects = Projects::getIdNameMap ( $this->logged_user, STATE_VISIBLE, null, $condition, true );
		
       // creating a Map for the users
		$users = Users::getForSelect($this->logged_user);
	      if(is_foreachable($users)) {
	        foreach($users as $k => $v) {
	          $users[$k] = JSON::valueToMap($v);
	        } // foreach
	      } // if

		$this->response->assign ( array ('time_reports_plus_run' => Router::assemble ( 'time_report_plus_run' ), 
			'time_reports_plus' =>  TimeReportsPlus::findByUser ( 'TrackingReport', $this->logged_user ),
			'users' => $users, 
			'companies' => $companies, 
			'projects' => $projects, 
	        'active_projects' => Projects::getIdNameMap($this->logged_user, STATE_VISIBLE, null, null, true), // We need this so we can group projects in the report
			'project_categories' => $categories, 
			'job_types' => JobTypes::getIdNameMap (),
			'expense_categories' => ExpenseCategories::getIdNameMap (),
			// 'restrict_employee' => $restrict_employee, 
	        // 'currencies' => Currencies::getIdDetailsMap(),
			'can_use_reports' => $this->logged_user->canUseReports () 
			));
		
		$this->response->assign ( array ('export_csv_url' => extend_url ( Router::assemble ( 'time_report_plus_run' ), array ('export_format' => 'csv' ) ), 'export_html_url' => extend_url ( Router::assemble ( 'time_report_plus_run' ), array ('export_format' => "html" ) ) ) );
		if (AngieApplication::isModuleLoaded ( 'invoicing' ) && Invoices::canAdd ( $this->logged_user )) {
			$this->response->assign ( 'invoice_based_on_url', $this->active_tracking_report->invoice ()->getUrl () );
		} else {
			$this->response->assign ( 'invoice_based_on_url', false );
		} // if
	}
	
	// index
	

	/**
	 * Create new report
	 */
	function add() {
		if (($this->request->isAsyncCall () || $this->request->isApiCall ()) && $this->request->isSubmitted ()) {
			if (TimeReportsPlus::canAdd ( $this->logged_user )) {
				try {
					$filter_data = $this->request->post ( 'filter' );
					
					$this->active_tracking_report->setAttributes ( $filter_data );
					$this->active_tracking_report->setType ( 'TrackingReport' );
					// acHack : Set summarize paramenter
					// TODO: Needs to Check why this values not set from the set attributes funciton
					$this->active_tracking_report->setAdditionalProperty ( 'sum_by_1', $filter_data ['sum_by_1'] );
					$this->active_tracking_report->setAdditionalProperty ( 'sum_by_2', $filter_data ['sum_by_2'] );
					$this->active_tracking_report->setAdditionalProperty ( 'sum_by_3', $filter_data ['sum_by_3'] );
					$this->active_tracking_report->setAdditionalProperty ( 'show_time_records', $filter_data ['show_time_records'] );
					// acHack :Over
					

					$this->active_tracking_report->save ();
					$this->response->respondWithData ( $this->active_tracking_report, array ('as' => 'report' ) );
				} catch ( Exception $e ) {
					$this->response->exception ( $e );
				} // try
			} else {
				$this->response->forbidden ();
			} // if
		} else {
			$this->response->notFound ();
		} // if
	}
	
	// add
	

	/**
	 * Update existing report
	 */
	function edit() {
		if (($this->request
			->isAsyncCall () || $this->request
			->isApiCall ()) && $this->request
			->isSubmitted ()) {
			if ($this->active_tracking_report
				->isLoaded ()) {
				if ($this->active_tracking_report
					->canEdit ( $this->logged_user )) {
					try {
						
						$filter_data = $this->request
							->post ( 'filter' );
						$this->active_tracking_report
							->setAttributes ( $filter_data );
						
						// acHack : Set summarize paramenter
						// TODO: Needs to Check why this values not set from the setAttributes funciton
						$this->active_tracking_report
							->setAdditionalProperty ( 'sum_by_1', $filter_data ['sum_by_1'] );
						$this->active_tracking_report
							->setAdditionalProperty ( 'sum_by_2', $filter_data ['sum_by_2'] );
						$this->active_tracking_report
							->setAdditionalProperty ( 'sum_by_3', $filter_data ['sum_by_3'] );
						$this->active_tracking_report
							->setAdditionalProperty ( 'show_time_records', $filter_data ['show_time_records'] );
						// acHack :Over
						

						$this->active_tracking_report
							->save ();
						
						$this->response
							->respondWithData ( $this->active_tracking_report, array ('as' => 'report' ) );
					} catch ( Exception $e ) {
						$this->response
							->exception ( $e );
					} // try
				} else {
					$this->response
						->forbidden ();
				} // if
			} else {
				$this->response
					->notFound ();
			} // if
		} else {
			$this->response
				->badRequest ();
		} // if
	}
	
	// edit
	

	/**
	 * Prepare and run a report based on attributes provided
	 */
	function run() {
		$this->wireframe->hidePrintButton ();
		$filter_data = $this->request->get ( 'filter' );
		$date_format = ConfigOptions::getValue ( "reports_date_format" ) == 'MM/DD/YY' ? '%m/%d/%Y' : '%d/%m/%Y' ;

		// we have removed Assigned To filter, setting value for the USer
		// Only if he has no permission for using reports and is not a Leader of the project
		if (! $this->logged_user->canUseReports () && ! $this->request->get ( 'project_id' )) {
			$filter_data ['user_filter'] = 'logged_user';
		}
		
		$not_visible_project_ids = null;
		if ($this->request->get ( 'project_id' )) {
			$filter_data ['project_filter'] = 'selected';
			$filter_data ['project_ids'] = array ($this->request->get ( 'project_id' ) );
		} elseif ($this->request->get ( 'from_home_scren_tab' ) || ! $this->logged_user->canUseReports ()) {
			if ($filter_data ['project_filter'] == 'any') {
				$filter_data ['project_ids'] = Projects::findIdsByUser ( $this->logged_user, true );
			} elseif ($filter_data ['project_filter'] == 'active') {
				$filter_data ['project_ids'] = Projects::findIdsByUser ( $this->logged_user, true, 'completed_on IS NULL' );
			} elseif ($filter_data ['project_filter'] == 'completed') {
				$filter_data ['project_ids'] = Projects::findIdsByUser ( $this->logged_user, true, 'completed_on IS NOT NULL' );
			} elseif ($filter_data ['project_filter'] == 'category') {
				$filter_data ['project_ids'] = Projects::findIdsByUser ( $this->logged_user, true, 'category_id = ' . $filter_data ['project_category_id'] );
			} elseif ($filter_data ['project_filter'] == 'client') {
				$filter_data ['project_ids'] = Projects::findIdsByUser ( $this->logged_user, true, 'company_id = ' . $filter_data ['project_client_id'] );
			}
			
			$visible_project_ids = TrackingReportPlus::getVisibleProjectIds ( $this->logged_user );
			$not_visible_project_ids = (! $this->logged_user->canUseReports ()) ? array_diff ( $filter_data ['project_ids'], $visible_project_ids ) : null;
			if (count ( $visible_project_ids ) && ! $this->logged_user->canUseReports ()) {
				$filter_data ['project_ids'] = array_intersect ( $visible_project_ids, $filter_data ['project_ids'] );
			}
		
		}
		unset ( $_SESSION ['for_ids'] );
		
		$show_time_records = (isset ( $filter_data ['show_time_records'] ) && $filter_data ['show_time_records']) ? true : false;
		$summary_options = array_unique ( array ('sum_by_1' => $filter_data ['sum_by_1'], 'sum_by_2' => $filter_data ['sum_by_2'], 'sum_by_3' => $filter_data ['sum_by_3'] ) );
		$summarize_by_array = array ();
		foreach ( $summary_options as $value ) {
			if ($value != 'all') {
				$summarize_by_array [] = $value;
			}
		}
		$level = count ( $summarize_by_array );
		$report = new TrackingReportPlus ();
		if ($this->request->isPrintCall () || $this->request->isWebBrowser ()) {
			$flag = true;
			
			try {
				$report->setAttributes ( $filter_data );
			} catch ( Exception $e ) {
				$flag = false;
			}
			//Printer
			if ($this->request->isPrintCall () || ($this->request->isWebBrowser ()) && $this->request->isAsyncCall ()) {
				try {
					//============================== WebCall =============================
					//Prepare order by string - we order by the same order that we summarize
					if ($level > 0) {
						$summarize_by_str = trim ( implode ( ',', $summarize_by_array ), ',' );
					} else {
						$show_time_records = true;
					}
					
					//Ordered fields
					$order_fields = array ('date_field_1', 'integer_field_1', 'project_id' );
					$order_fields = array_unique ( array_merge ( $summarize_by_array, $order_fields ) );
					$order_by = implode ( ", ", $order_fields );
					
					//Prepare condition
					

					$time_records = array ();
					if ($flag && $this->logged_user instanceof User) {
						$time_records = $report->run ( $this->logged_user, $filter_data, $order_by );
					}
					$skip_estimate_heading = array ('date_field_1', 'integer_field_2', 'integer_field_1', 'job_type_id' );
					$show_estimate = (count ( array_intersect ( $skip_estimate_heading, $summary_options ) ) > 0) ? 'false' : 'true';
					$all_headings = $this->check_module_loaded ( array ('date_field_1' => lang ( 'Date' ), 'integer_field_1' => lang ( 'Person' ), 'project_id' => lang ( 'Project' ), 'milestone_id' => lang ( 'Milestone' ), 'tcp_id' => lang ( 'Tasks / Page / Todo' ), 'task_id' => lang ( 'Task' ), 'summary' => lang ( 'Summary' ), 'integer_field_2' => lang ( 'Status' ), 'estimate' => '', 'hours' => lang ( 'Hours' ), 'company_id' => lang ( 'Client' ), 'job_type_id' => lang ( 'Job type' ) ) );
					
					// CSS class for the headings / cells
					$headings_class = array ('id' => 'Id', 'group_id' => 'Group Id', 'date_field_1' => 'Date', 'parent_type' => 'Parent Type', 'parent_id' => 'Parent Id', 'integer_field_1' => 'User', 'company_id' => 'Client Name', 'tcp_id' => 'Task', 'project_id' => 'Project', 'summary' => 'Desc', 'estimate' => 'Estimate', 'hours' => 'Hours', 'task_category_id' => 'Task Category', 'project_category_id' => 'Project Category', 'integer_field_2' => 'Billing Status', 'job_type_id' => 'Job type' );
					
					$this->billable_array = array (BILLABLE_STATUS_NOT_BILLABLE => lang ( 'Non Billable' ), BILLABLE_STATUS_BILLABLE => lang ( 'Billable' ), BILLABLE_STATUS_PENDING_PAYMENT => lang ( 'Pending' ), BILLABLE_STATUS_PAID => lang ( 'Paid' ) );
					
					// Deleting records which are not visible for the user
					if ($not_visible_project_ids != null && is_foreachable ( $time_records )) {
						foreach ( $time_records as $key => $record ) {
							if (in_array ( $record ['project_id'], $not_visible_project_ids )) {
								unset ( $time_records [$key] );
								continue;
							}
						}
					}
					
					
					// If we got some records..
					if (is_foreachable ( $time_records )) {
						
						$projects = array ();
						$milestones = array ();
						$users = array ();
						$task_categories = array ();
						$project_categories = array ();
						$companies = array ();
						foreach ( $time_records as $key => $record ) {
							
							/* if($not_visible_project_ids != null && in_array($record['project_id'], $not_visible_project_ids)){
                                    unset($time_records[$key]);
                                    continue;
                            }*/
							if ($record ['parent_type'] == 'Task') {
								$task_id_for_estimate [] = $record ['parent_id'];
							}
							if (! in_array ( $record ['project_id'], $projects )) {
								$projects [] = $record ['project_id'];
							}
							
							if (! in_array ( $record ['integer_field_1'], $users )) {
								$users [] = $record ['integer_field_1'];
							}
							
							if (! in_array ( $record ['company_id'], $companies )) {
								$companies [] = $record ['company_id'];
							}
							
							if (! is_null ( $record ['task_category_id'] ) && ! (in_array ( $record ['task_category_id'], $task_categories ))) {
								$task_categories [] = $record ['task_category_id'];
							}
							
							if (! is_null ( $record ['project_category_id'] ) && ! (in_array ( $record ['project_category_id'], $project_categories ))) {
								$project_categories [] = $record ['project_category_id'];
							}
						}
						$task_id_estimate_map = TrackingReportPlus::getIdEstimateMap ( $task_id_for_estimate );
						
						$query = ' SELECT id, name, slug, leader_id FROM ' . TABLE_PREFIX . 'projects  WHERE id IN( ? ) ';
						$results = DB::execute ( $query, $projects );
						$results = $results->toArray ();
						$this->projects_map = array ();
						foreach ( ( array ) $results as $row ) {
							$this->projects_map [$row ['id']] ['name'] = $row ['name'];
							$this->projects_map [$row ['id']] ['url'] = Router::assemble ( 'project', array ('project_slug' => $row ['slug'] ) );
							$projects_map [$row ['id']] ['leader_id'] = $row ['leader_id'];
							$dummy_project = new Project ();
							$dummy_project->setId ( $row ['id'] );
							$dummy_project->setAttributes ( $projects_map [$row ['id']] );
							$this->projects_map [$row ['id']] ['object'] = $dummy_project;
						}
						
						// Create a map for users
						$query = ' SELECT id, company_id, first_name, last_name, email FROM ' . TABLE_PREFIX . 'users WHERE id IN ( ? )';
						$results = DB::execute ( $query, $users );
						$this->users_map = array ();
						foreach ( $results->toArray () as $row ) {
							if ($row ['first_name'] == '' && $row ['last_name'] == '') {
								$this->users_map [$row ['id']] ['name'] = $row ['email'];
							} else {
								$this->users_map [$row ['id']] ['name'] = $row ['first_name'] . ' ' . $row ['last_name'];
							}
							$this->users_map [$row ['id']] ['url'] = Router::assemble ( 'people_company_user', array ('company_id' => $row ['company_id'], 'user_id' => $row ['id'] ) );
						}
						
						// Create a map for job type
						$this->job_types_array = JobTypes::getIdNameMap ();
						
						// Create a map for categories		
						

						$this->categories_map [0] ['name'] = 'ZZ' . lang ( "No Category" );
						
						$categories = array_merge ( ( array ) $task_categories, ( array ) $project_categories );
						
						if (is_foreachable ( $categories )) {
							$query = 'SELECT id, name FROM ' . TABLE_PREFIX . 'categories WHERE id IN ( ? )';
							$results = DB::execute ( $query, $categories );
							
							foreach ( $results as $row ) {
								$this->categories_map [$row ['id']] ['name'] = $row ['name'];
							}
						}
						
						// Create a map for companies		
						$this->companies_map = array ();
						$this->companies_map [0] ['name'] = lang ( "No Client" );
						$query = 'SELECT id, name FROM ' . TABLE_PREFIX . 'companies WHERE id IN ( ? ) ';
						$results = DB::execute ( $query, $companies );
						foreach ( $results->toArray () as $row ) {
							$this->companies_map [$row ['id']] ['name'] = $row ['name'];
						}
						foreach ( $summarize_by_array as $value ) {
							$summary_labels [] = $all_headings [$value];
						}
						$display_headings = array_diff ( array_flip ( $all_headings ), ( array ) $summarize_by_array );
						
						// If we have some grouping, we need to bring their headings forward in headings list
						// else leave them as it is
						if (is_array ( $summarize_by_array )) {
							foreach ( $summarize_by_array as $summarized ) {
								$headings [$summarized] = $all_headings [$summarized];
							}
							
							$final_headings = array_merge ( $headings, $all_headings );
							
							if (isset ( $headings ) && ($show_time_records == true)) {
								$final_export_heading = array_unique ( array_merge ( $headings, $headings_class ) );
							} elseif (isset ( $headings ) && ($show_time_records == false)) {
								$final_export_heading = $headings;
								array_push ( $final_export_heading, "Hours" );
							} else {
								$final_export_heading = $headings_class;
							}
						} else {
							$final_headings = $all_headings;
						}

						$sorting_key_array = array ('project_category_id', 'task_category_id', 'job_type_id' );
						if (count ( array_intersect ( $sorting_key_array, $summarize_by_array ) ) > 0) {
							foreach ( $time_records as $idx => $record ) {
								
								if (in_array ( 'project_category_id', $summarize_by_array )) {
									$time_records [$idx] ['project_category_id'] = $this->categories_map [$record ['project_category_id']] ['name'];
								}
								
								if (in_array ( 'task_category_id', $summarize_by_array )) {
									$time_records [$idx] ['task_category_id'] = $this->categories_map [$record ['task_category_id']] ['name'];
								}
								
								if (in_array ( 'job_type_id', $summarize_by_array )) {
									$time_records [$idx] ['job_type_id'] = $this->job_types_array [$record ['job_type_id']];
								}

								foreach ( ( array ) $order_fields as $key ) {
									$value = $time_records [$idx] [$key];
									$sorting_helper [$idx] .= '' . $value;
									$string .= $sorting_helper [$idx] . '</br>';
								}
								
							}
							array_multisort ( $sorting_helper, $time_records );
						}
						unset ( $sorting_key_array );
						
						
						// We never display milestone_id, category, tcp_id and task_id, job_type_id in the output atleast for now, 
						// so remove them from the $display_headings array
						$display_headings = array_diff ( $display_headings, array ('milestone_id', 'task_id', 'tcp_id', 'category_id', 'company_id', 'job_type_id' ) );
						$count_of_columns = count ( $display_headings );

						$previous_values = array ();
						$total_hours = array ();
						
						foreach ( $summarize_by_array as $value ) {
							$previous_values [$value] ['value'] = '';
							$previous_values [$value] ['hours'] = 0;
							$previous_values [$value] ['estimate'] = 0;
							$previous_values [$value] ['rowIdx'] = 0;
						}
						// Loop over all records and group them based on selected summary options
						$rowIdx = 0;
						$output_rows = array ();
						foreach ( $time_records as $idx => $row ) {
							
							//adding estimates
							if ($task_id_estimate_map [$row ['parent_id']] && (! in_array ( $row ['parent_id'], $pre_added ))) {
								$pre_added [] = $row ['parent_id'];
								$row ['estimate'] = $task_id_estimate_map [$row ['parent_id']];
							} else {
								$row ['estimate'] = 0;
							}
							
							// Grouping logic
							foreach ( $summarize_by_array as $current_level => $value ) {

								$row_value = $row [$value];
								$prev_value = $previous_values [$value] ['value'];

								// checking Date as strings
								if($value == 'date_field_1'){
									$row_value = ($row [$value] instanceof DateValue) ? strftime ($date_format, $row [$value]->getTimestamp()) : '';
									$prev_value = ($previous_values [$value] ['value'] instanceof DateValue) ? strftime ($date_format, $previous_values [$value] ['value']->getTimestamp()) : '';
								}
								
								// If current heading value is not the same as previous, we need to add a new heading
								// if ($previous_values [$value] ['value'] != $row [$value]) {
								if ($prev_value !== $row_value) {
									
									// Update previous value
									foreach ( $final_headings as $key => $heading ) {
										
										if ($key == $value) {
											$nameURLs = $this->getNameURLValues ( $key, $row );
											$output_rows [$rowIdx] ['heading'] = $nameURLs ['heading'];
											$output_rows [$rowIdx] ['heading_url'] = $nameURLs ['heading_url'];
											
											
											if (isset ( $nameURLs ['heading_estimate'] )) {
												$output_rows [$rowIdx] ['heading_estimate'] = $nameURLs ['heading_estimate'];
											}
											$output_rows [$rowIdx] ['heading_name'] = $key;
										}
										$output_rows [$rowIdx] ['colspan'] = $count_of_columns - 1;
										$output_rows [$rowIdx] ['current_level'] = $current_level;
										/***code added by jyoti**/
										$output_rows[$rowIdx]['status_filter'] = $filter_data ['billable_status_filter'];
									//	if($filter_data ['billable_status_filter']=='billable'){
										//for progress
										if($output_rows [$rowIdx] ['current_level']=='0'){
										$output_rows [$rowIdx] ['project_id'] = $row ['project_id'];
										$query_total_tasks ="SELECT count(*) as cnt  FROM `acx_project_objects` WHERE `module` LIKE 'tasks' AND `project_id` = '".$row ['project_id']."' and state!=1  ";
											$results_total_tasks = DB::execute ($query_total_tasks);
											$results_total_tasks = $results_total_tasks->toArray ();
											
										$query_completed_tasks ="SELECT count(*) as cnt  FROM `acx_project_objects` WHERE `module` LIKE 'tasks' AND `project_id` = '".$row ['project_id']."' and state!=1 and completed_on is not null ";
											$results_completed_tasks = DB::execute ($query_completed_tasks);
											$results_completed_tasks = $results_completed_tasks->toArray ();
											$output_rows [$rowIdx] ['project_progress'] = 0;
											if($results_completed_tasks[0]['cnt']!=0 && $results_total_tasks[0]['cnt']!=0){
											$output_rows [$rowIdx] ['project_progress'] =floor((100*$results_completed_tasks[0]['cnt'])/$results_total_tasks[0]['cnt']);
											}
											}
										//end of progress
										if($output_rows [$rowIdx] ['current_level']=='1'){
											$output_rows [$rowIdx] ['parent_id'] = $row ['parent_id'];
											$output_rows [$rowIdx] ['parent_type'] = $row ['parent_type'];
											$query_logged ="SELECT sum(value) as logged_hours FROM `acx_time_records` where parent_id='".$row ['parent_id']."' and parent_type='". $row ['parent_type']."' and state!='1' group by parent_id ";
										   $results_logged = DB::execute ( $query_logged  );
											if( $results_logged!=NULL ){
											$results_logged = $results_logged->toArray ();
											$output_rows [$rowIdx] ['logged_hours'] = $results_logged[0]['logged_hours'];
											}
										}
										//}
										/**code ended by jyoti**/
									}
									
									$previous_values [$value] ['value'] = $row [$value];
									$previous_values [$value] ['hours'] = 0;
									$previous_values [$value] ['estimate'] = 0;
									$previous_values [$value] ['rowIdx'] = $rowIdx;
									$rowIdx ++;
									
									for($j = $current_level + 1; $j < $level; $j ++) {
										$previous_values [$summarize_by_array [$j]] ['value'] = '';
										$previous_values [$summarize_by_array [$j]] ['hours'] = 0;
										$previous_values [$summarize_by_array [$j]] ['estimate'] = 0;
										$previous_values [$summarize_by_array [$j]] ['rowIdx'] = 0;
									}
								}
								
								$previous_values [$value] ['hours'] += $row ['hours'];
								$previous_values [$value] ['estimate'] += $row ['estimate'];
								
								if ($current_level != $level) {
									$output_rows [$previous_values [$value] ['rowIdx']] ['hours'] = sprintf ( '%01.2f', $previous_values [$value] ['hours'] );
									$output_rows [$previous_values [$value] ['rowIdx']] ['estimate'] = sprintf ( '%01.2f', $previous_values [$value] ['estimate'] );
								}
							}

							// Headings done for this time record, now take it's actual values
							if ($show_time_records === true || ! $show_time_records) {
								foreach ( $row as $key => $value ) {
									
									$output_rows [$rowIdx] ["heading"] = '';
									
									if (! in_array ( $key, $summarize_by_array )) {
										
										$nameURLs = $this->getNameURLValues ( $key, $row );
										$output_rows [$rowIdx] [$key] = $nameURLs ['heading'];
										if ($nameURLs ['heading_url'] != '') {
											$output_rows [$rowIdx] [$key . '_url'] = $nameURLs ['heading_url'];
										}
										if (isset ( $nameURLs ['heading_estimate'] )) {
											$output_rows [$rowIdx] [$key . '_estimate'] = $nameURLs ['heading_estimate'];
										}
									}
									if ($key == 'project_id') {
										$dummy_project = $this->projects_map [$value] ['object'];
										
										if ($this->logged_user->isAdministrator () || $this->logged_user->isProjectManager () || (($this->logged_user->projects ()->getPermission ( 'tracking', $dummy_project )) >= ProjectRole::PERMISSION_MANAGE)) {
											$output_rows [$rowIdx] ['can_manage'] = 1;
											$editable_time_records_count ++;
										} else {
											$output_rows [$rowIdx] ['can_manage'] = 0;
										}
									}
								}
								$output_rows [$rowIdx] ['colspan'] = 0;
								if ($output_rows [$rowIdx] ['can_manage'] == 1) {
									$permission [] = 1;
									$output_rows [$rowIdx] ['current_level'] = $level;
								} else {
									$permission [] = 0;
									$output_rows [$rowIdx] ['current_level'] = $level;
								}
								$rowIdx ++;
							}
							// Total at the bottom of the report...				
							$total_time += $row ['hours'];
							$total_estimate_time += $row ['estimate'];
						}
						// Calculations over.. now display the report
						$summarized_array = array_merge ( array ('' => lang ( '-- None --' ) ), ( array ) $all_headings );
						unset ( $summarized_array ['summary'] );
						unset ( $summarized_array ['value'] );
						
						if ($this->active_project) {
							$this->active_project = Projects::findById ( $project_id );
							$report_url = Router::assemble ( 'project_time_report', array ('project_id' => $this->request->getId ( $active_project ), 'report_id' => $report->getId () ) );
							unset ( $summarized_array ['project_id'] );
							unset ( $summarized_array ['company_id'] );
						} else {
							$time_report_plus_mass_edit = Router::assemble ( 'time_report_plus_mass_edit' );
						}
						
						$this->wireframe->javascriptAssign ( 'summarized_array', $summarized_array );
						
						$export_format = $this->request->get ( 'export_format' );
						if ($export_format == 'csv') {
							
						/*
                         * Export time records in CSV 
                         */
							$previous_level = 0;
							$previous_headings = array ();
							$csv_content = array ();
							$csvIdx = 0;
							$flag = 0;
							$csv_content [$csvIdx ++] = $final_export_heading;
							
							if ($show_time_records == true) {
								unset ( $csv_content [0] ['estimate'] );
							} else {
								if ($show_estimate == 'true')
									$csv_content [0] [] = "Estimate";
							}
						
							foreach ( $output_rows as &$row ) {
								
								if ($row ['heading'] != '') {
									if ($show_estimate == 'false') {
										unset ( $row ['estimate'] );
									}
									// Fix for date..
									if ($row ['heading_name'] == 'date_field_1') {
										$row ['heading'] = strftime ($date_format, $row ['heading']->getTimestamp());
									}
									
									$flag = 1;
									// When level goes down, we have a new set of headings...
									if ($row ['current_level'] <= $previous_level && $previous_level != 0) {
										for($i = 0; $i < $row ['current_level']; $i ++) {
											$csv_content [$csvIdx] [] = $previous_headings [$i];
										}
									}
									// process on heading part
									$csv_content [$csvIdx] [] = $row ['heading'];
									$previous_level = $row ['current_level'];
									$previous_headings [$row ['current_level']] = $row ['heading'];
									unset ( $final_export_heading [$row ['heading_name']] );
									if ($show_time_records === false && $row ['current_level'] == $level - 1) {
										$csv_content [$csvIdx] [] = $row ['hours'];
										$csv_content [$csvIdx] [] = $row ['estimate'];
										$csvIdx ++;
									}
								} else {
									if ($show_time_records === true) {
										if ($flag != 1) {
											foreach ( $previous_headings as $th ) {
												$csv_content [$csvIdx] [] = $th;
											}
										}
										$flag = 0;
										// process on data part
										foreach ( $final_export_heading as $key => $value ) {
											switch ($key) {
												case 'date_field_1' :
													$csv_content [$csvIdx] [] = strftime ($date_format, $row ['date_field_1']->getTimestamp());
													break;
												case 'parent_summary' :
													if ($row ['parent_type'] == 'Task') {
														$csv_content [$csvIdx] [] = $row ['task_name'];
													} else {
														$csv_content [$csvIdx] [] = $row ['tcp_name'];
													}
													break;
												case 'estimate' :
													
													break;
												default :
													$csv_content [$csvIdx] [] = $row [$key];
													
													break;
											}
										}
										$csvIdx ++;
									}
								}
							}
							$content = array_to_csv ( $csv_content );
							
							$this->response->respondWithContentDownload ( $content, BaseHttpResponse::CSV, 'Time_Reports.csv' );
						} else {
							
							$filter_map = TimeReportsPlus::getFilterDataMap ();
							$view = SmartyForAngie::getInstance ()->createTemplate ( get_view_path ( 'run', 'time_reports_plus', REPORTS_PLUS_MODULE, AngieApplication::INTERFACE_DEFAULT ) );
							$assign_array = array ('summarized_array' => $summarized_array, 'level' => $level, 'colspan' => $count_of_columns, 'output_rows' => $output_rows, 'display_headings' => $display_headings, 'all_headings' => $all_headings, 'global_time_report_mass_edit_url' => $time_report_plus_mass_edit, 'summarize' => $summary_options, 'editable_time_records_count' => $editable_time_records_count, 'report_url' => $report_url, //'grouped_reports' => TimeReports::findGrouped(),
							'grouped_reports' => array (), 'total_time' => sprintf ( '%01.2f', $total_time ), 'total_estimate_time' => sprintf ( '%01.2f', $total_estimate_time ), 'parents' => $parents, 'headings_class' => $headings_class, 'parents_map' => $parents_map, 'show_time_records' => $show_time_records, 'show_project' => ($this->active_project == null) ? true : false, 'filter_data' => $filter_data, 'filter_map' => $filter_map, 'time_report_plus_run' => Router::assemble ( 'time_report_plus_run' ), 'job_types' => JobTypes::getIdNameMap (), 'permission' => $permission, 'show_estimate' => $show_estimate );
							
							if ($this->request->isPrintCall ()) {
								$css_files = array ("main.css" );
								$print_content = array ();
								foreach ( $css_files as $file ) {
									$file_path = REPORTS_PLUS_MODULE_PATH . '/assets/default/stylesheets/backend/' . $file;
									if (file_exists ( $file_path )) {
										$print_content [] = file_get_contents ( $file_path );
									}
								}
								$assign_array ['print_content'] = implode ( "\r\n", $print_content );
							} else if ($export_format == 'html') {
								$css_files = array ("main.css" );
								$css_content = array ();
								foreach ( $css_files as $file ) {
									$file_path = REPORTS_PLUS_MODULE_PATH . '/assets/default/stylesheets/backend/' . $file;
									if (file_exists ( $file_path )) {
										$css_content [] = file_get_contents ( $file_path );
									}
								}
								$assign_array ['css_content'] = implode ( "\r\n", $css_content );
							}
							
							
							$view->assign ( $assign_array );
							$fetch = $view->fetch ();
							
							
							if ($this->request->isPrintCall ()) {
								$this->response->assign ( $assign_array );
							} elseif ($export_format == 'html') {
								$this->response->respondWithContentDownload ( $fetch, BaseHttpResponse::HTML, 'Time_Reports.html' );
							} else {
								$this->response->respondWithMap ( ( array ) $fetch );
							}
						}
					}
				} catch ( Exception $e ) {
					$this->response->exception ( $e );
				} // try
			} // if
		} else {
			$this->response->badRequest ();
		} // if
	}
	
	// run    
	

	function check_module_loaded($array_for_check) {
		if (! AngieApplication::isModuleLoaded ( 'tasks' ) && ! AngieApplication::isModuleLoaded ( 'pages' ) && ! AngieApplication::isModuleLoaded ( 'todo' )) {
			unset ( $array_for_check ['tcp_id'] );
		}

		return $array_for_check;
	}
	
	function getNameURLValues($value, &$row) {
		
		$result = array ();
		switch ($value) {
			case 'project_id' :
				$result ['heading'] = $this->projects_map [$row [$value]] ['name'];
				$result ['heading_url'] = $this->projects_map [$row [$value]] ['url'];
				break;
			
			case 'milestone_id' :
				$result ['heading'] = $row ['milestone_name'];
				$result ['heading_estimate'] = $row ['milestone_estimate'];
				$result ['heading_url'] = ($row [$value] > 0) ? Router::assemble ( 'project_milestone', array ('project_slug' => $row ['project_slug'], 'milestone_id' => $row ['milestone_id'] ) ) : '';
				break;
			
			case 'tcp_id' :
				$result ['heading'] = $row ['tcp_name'];
				$result ['heading_estimate'] = $row ['tcp_estimate'];
				$path_info = 'project_' . strtolower ( $row ['tcp_type'] );
				$object_type_id = strtolower ( $row ['tcp_type'] ) . '_id';
				$result ['heading_url'] = ($row ['parent_local_id'] > 0) ? Router::assemble ( $path_info, array ('project_slug' => $row ['project_slug'], $object_type_id => $row ['parent_local_id'] ) ) : '';
				break;
			
			case 'task_id' :
				$result ['heading'] = $row ['task_name'];
				$result ['heading_estimate'] = $row ['task_estimate'];
				$result ['heading_url'] = ($row [$value] > 0) ? Router::assemble ( 'project_task', array ('project_id' => $row ['project_id'], 'task_id' => $row ['task_id'] ) ) : '';
				break;
			
			case 'integer_field_1' : // User
				if (key_exists ( $row [$value], $this->users_map )) {
					$result ['heading'] = $this->users_map [$row [$value]] ['name'];
					$result ['heading_url'] = $this->users_map [$row [$value]] ['url'];
				} else {
					$result ['heading'] = $row ['user_name'];
					$result ['heading_url'] = 'mailto:' . $row ['email'];
				}
				break;
			
			case 'integer_field_2' : // Billable Status
				$result ['heading'] = $this->billable_array [$row [$value]];
				break;
			
			case 'date_field_1' : // Date of time record
				$result ['heading'] =  $row [$value] ;
				break;
			
			case 'company_id' :
				$result ['heading'] = $this->companies_map [$row [$value]] ['name'];
				$result ['heading_url'] = ($row [$value] > 0) ? Router::assemble ( 'people_company', array ('company_id' => $row [$value] ) ) : '';
				break;
			
			case 'task_category_id' :
			case 'project_category_id' :
				if (key_exists ( $row [$value], $this->categories_map )) {
					$result ['heading'] = $this->categories_map [$row [$value]] ['name'];
				} else {
					$result ['heading'] = $row [$value];
				}
				
				if (substr ( $result ['heading'], 0, 2 ) == 'ZZ') {
					$result ['heading'] = substr ( $result ['heading'], 2 );
				}
				$result ['heading_url'] = '';
				break;
			
			case 'job_type_id' :
				if (key_exists ( $row [$value], $this->job_types_array )) {
					$result ['heading'] = $this->job_types_array [$row [$value]];
				} else {
					$result ['heading'] = $row [$value];
				}
				
				$result ['heading_url'] = '';
				break;
			
			default :
				$result ['heading'] = $row [$value];
				$result ['heading_url'] = '';
		}
		return $result;
	}
	
	function mass_edit() {
		
		if ($this->request
			->isSubmitted ()) {
			
			$updated = 0; // number of successfully update records
			$message = lang ( 'No records were updated' );
			
			$action = $this->request->post ( 'with_selected' );
			
			$action_part = explode ( '_', $action );
			
			if ($action_part [0] == 'round') {
				$round_minutes = $action_part [2];
				$action = $action_part [0];
			} elseif ($action_part [0] == 'job') {
				$job_type_change_to = $action_part [4];
				$action = 'job_type_change';
			}
			$time_record_ids = $this->request
				->post ( 'time_record_ids' );
			
			if (is_foreachable ( $time_record_ids )) {
				$time_records = TimeRecords::findByIds ( $time_record_ids, STATE_VISIBLE, VISIBILITY_NORMAL );
				if (is_foreachable ( $time_records )) {
					DB::beginWork ( 'TimeReports Plus mass-update start' );
					switch ($action) {
						
						case 'round' :
							$message = ':count records round up by :round_minutes minutes';
							
							foreach ( $time_records as $time_record ) {
								$time = $time_record->getValue ();
								$rounded_time = self::round_minutes ( $round_minutes, $time );
								$time_record->setValue ( $rounded_time );
								$save = $time_record->save ();
								if ($save && ! is_error ( $save )) {
									$updated ++;
								} // if
							} // foreach
							

							break;
						
						case 'job_type_change' :
							
							foreach ( $time_records as $time_record ) {
								$time_record->setJobTypeId ( $job_type_change_to );
								$save = $time_record->save ();
								if ($save && ! is_error ( $save )) {
									$updated ++;
								} // if
							} // foreach
							

							break;
						
						case 'adjust_to_percent' :
							$adjust_to_percent = $this->request
								->post ( 'adjust_to_percent' );
							$message = ':count records adjusted to :adjust_to_percent%';
							
							if (! isset ( $adjust_to_percent ) || ! is_numeric ( $adjust_to_percent )) {
								$adjust_to_percent = 100;
							}
							
							foreach ( $time_records as $time_record ) {
								$time = $time_record->getValue ();
								$calculate_time = $time * ($adjust_to_percent / 100);
								$time_record->setValue ( $calculate_time );
								$save = $time_record->save ();
								if ($save && ! is_error ( $save )) {
									$updated ++;
								} // if
							} // foreach
							

							break;
						
						case 'mark_as_billable' :
							$message = ':count records marked as billable';
							
							foreach ( $time_records as $time_record ) {
								if ($time_record->canChangeBillableStatus ( $this->logged_user )) {
									$time_record->setBillableStatus ( BILLABLE_STATUS_BILLABLE );
									$save = $time_record->save ();
									if ($save && ! is_error ( $save )) {
										$updated ++;
									} // if
								} // if
							} // foreach
							

							break;
						
						// Mark as non-billable
						case 'mark_as_not_billable' :
							$message = ':count records marked as non-billable';
							
							foreach ( $time_records as $time_record ) {
								if ($time_record->canChangeBillableStatus ( $this->logged_user )) {
									$time_record->setBillableStatus ( BILLABLE_STATUS_NOT_BILLABLE );
									$save = $time_record->save ();
									if ($save && ! is_error ( $save )) {
										$updated ++;
									} // if
								} // if
							} // foreach
							

							break;
						
						// Mark as billed
						case 'mark_as_billed' :
							$message = ':count records marked as billed';
							
							foreach ( $time_records as $time_record ) {
								if ($time_record->canChangeBillableStatus ( $this->logged_user )) {
									$time_record->setBillableStatus ( BILLABLE_STATUS_PAID );
									$save = $time_record->save ();
									if ($save && ! is_error ( $save )) {
										$updated ++;
									} // if
								} // if
							} // foreach
							

							break;
						
						// Mark as not billed
						case 'mark_as_not_billed' :
							$message = ':count records marked as not billed';
							
							foreach ( $time_records as $time_record ) {
								if ($time_record->canChangeBillableStatus ( $this->logged_user )) {
									
									$time_record->setBillableStatus ( BILLABLE_STATUS_PENDING_PAYMENT );
									$save = $time_record->save ();
									if ($save && ! is_error ( $save )) {
										$updated ++;
									} // if
								} // if
							} // foreach
							

							break;
						
						// Move to trash
						case 'move_to_trash' :
							$message = ':count records moved to trash';
							foreach ( $time_records as $time_record ) {
								if ($time_record->canDelete ( $this->logged_user )) {
									$trash = $time_record->state ()
										->trash ();
									if ($trash && ! is_error ( $trash )) {
										$updated ++;
									} // if
								} // if
							} // foreach
							

							break;
					} // switch
					

					DB::commit ( 'Updated Successfully' );
				} // if
			} // if
			

			$this->response->ok ();
		} else {
			$this->response->forbidden ();
		} // if
		
		die();
	}
	
	function round_minutes($round_up, $time) {
		if (is_float ( $time )) {
			$time_dec = $time - floor ( $time );
			$hours = floor ( $time );
			$minutes = round ( $time_dec * 60 );
			if ($minutes % $round_up == 0) {
				$rounded_time = time_to_float ( $hours . ':' . $minutes );
			} else {
				$rounded_minutes = ($minutes + $round_up) - (($minutes + $round_up) % $round_up);
				$rounded_time = time_to_float ( $hours . ':' . $rounded_minutes );
			}
			
			return $rounded_time;
		} else {
			return $time;
		} // if
	}

}
