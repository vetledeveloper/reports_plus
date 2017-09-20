<?php

// Build on top of reports module
AngieApplication::useController ( 'reports', REPORTS_FRAMEWORK_INJECT_INTO );

/**
 * Reports Plus controller
 *
 * @package custom.modules.reports_plus
 * @subpackage controllers
 */
class ReportsPlusController extends ReportsController {
	
	/**
	 * Active module
	 *
	 * @var string
	 */
	protected $active_module = REPORTS_PLUS_MODULE;
	protected $project_options = array ();
	
	/**
	 * Actions that are exposed through API
	 *
	 * @var array
	 */
	protected $api_actions = array ('index' );
	
	var $check_reports_access_permissions = false;
	/**
	 * Prepare controller
	 */
	function __before() {
		parent::__before ();
		
		if ($this->logged_user->isAdministrator () || $this->logged_user->canUseReports () || $this->logged_user ->getSystemPermission('can_use_reports')) {
			$this->wireframe->print->disable ();
		} else {
			$this->response->forbidden ();
		} // if
	} // __construct
	

	/**
	 * Show Reports index page
	 */
	function index() {
		// If we need to update reports on every access, do that now
		$update_on_access = (ConfigOptions::exists ( 'reports_update_on_access' )) ? ( boolean ) ConfigOptions::getValue ( 'reports_update_on_access' ) : false;
		if ($update_on_access === true) {
			$reports = new ReportsPlus ();
			$reports->dailyHandler ();
		}
		// Serve request made with web browser
		if ($this->request->isWebBrowser () || $this->request->isPrintCall () || $this->request->isPhone ()) {
			
			if ($this->request->isPhone ()) {
				$this->wireframe->breadcrumbs->remove ( 'reports' );
				$this->wireframe->breadcrumbs->add ( 'reports', lang ( 'Reports & Filters' ), Router::assemble ( 'reports_plus_html', array ('view' => 'html' ) ) );
			}
			
			$this->wireframe->setPageTitle ( lang ( "Reports Plus" ) );
			$view_type = $this->getValueFromRequest ( 'view' );
			$reports_enum = array ();
			
			//for Html View
			if ($view_type == 'html') {
				
				$tab_selected = ($this->getValueFromRequest ( 'tab_selected' ) != '') ? ($this->getValueFromRequest ( 'tab_selected' )) : 'projects';
				$search_by = ($this->getValueFromRequest ( 'search_by' ) != '') ? ($this->getValueFromRequest ( 'search_by' )) : 'project';
				
				$user = $this->logged_user;
				if (! ($reports instanceof ReportsPlus)) {
					$reports = new ReportsPlus ();
				}
				
				// Available reports
				$available_reports_tabs = array ();
				$available_reports_tabs [] = 'projects';
				
				if ($user->isAdministrator () || ( boolean ) $user->getSystemPermission('can_see_reports_companies')) {
					$available_reports_tabs [] = 'companies';
				}
				if ($user->isFinancialManager ()) {
					$available_reports_tabs [] = 'finance';
				}
				
				if ($this->request->isPhone ()) {
					if (in_array ( 'companies', $available_reports_tabs ) || in_array ( 'finance', $available_reports_tabs )) {
						$this->wireframe->actions->add ( 'dashboard', lang ( 'Dashboard' ), Router::assemble ( 'reports_plus_html', array ('view' => 'html', 'tab_selected' => 'projects' ) ), array ('icon' => AngieApplication::getImageUrl ( 'icon/dashboard_48x48.png', REPORTS_PLUS_MODULE, AngieApplication::INTERFACE_PHONE ) ) );
					}
					
					if (in_array ( 'companies', $available_reports_tabs )) {
						$this->wireframe->actions->add ( 'project', lang ( 'Project' ), Router::assemble ( 'reports_plus_html', array ('view' => 'html', 'tab_selected' => 'companies' ) ), array ('icon' => AngieApplication::getImageUrl ( 'icon/projects_48x48.png', REPORTS_PLUS_MODULE, AngieApplication::INTERFACE_PHONE ) ) );
					}
					if (in_array ( 'finance', $available_reports_tabs )) {
						$this->wireframe->actions->add ( 'finance', lang ( 'Finance' ), Router::assemble ( 'reports_plus_html', array ('view' => 'html', 'tab_selected' => 'finance' ) ), array ('icon' => AngieApplication::getImageUrl ( 'icon/finance_48x48.png', REPORTS_PLUS_MODULE, AngieApplication::INTERFACE_PHONE ) ) );
					}
					
					switch ($tab_selected) {
						case 'projects' :
							$filter_url = Router::assemble ( 'reports_mobile_filters', array ('view' => 'html', 'tab_selected' => 'projects' ) );
							break;
						case 'people' :
							$filter_url = Router::assemble ( 'reports_mobile_filters', array ('view' => 'html', 'tab_selected' => 'people' ) );
							break;
						case 'companies' :
							$filter_url = Router::assemble ( 'reports_mobile_filters', array ('view' => 'html', 'tab_selected' => 'companies' ) );
							break;
					}
				}

				$labels_map = Labels::getIdDetailsMap('AssignmentLabel');
				if(is_foreachable($labels_map)){
					$labels_map[0] = array( 'name' => lang('No Label'),
										          'fg_color' => '#000',
										          'bg_color' => '#FFFFFF',
										          'always_uppercase' => $always_uppercase);
				}

				$result = array (); 
				$reports_enum ['projects'] = $reports->getProjects ( $user );
				$reports_enum ['filter_types'] = $reports->getFilterTypes ();
				$reports_enum ['companies'] = $reports->getCompanies ( $user );
				$reports_enum ['labels'] =  $labels_map;
				$reports_enum ['people'] = $reports->getPeople ( $user, $options );
				$reports_enum ['roles'] = $reports->getRoles ();
				$reports_enum ['dates'] = $reports->getDates ( $user );
				$reports_enum ['project_groups'] = $reports->getProjectGroups ();

				// TODO :: delete this , reports_user_score_update

				$default_params = array ('date_format' => ConfigOptions::getValue ( "reports_date_format" ), 'api_url' => Router::assemble ( 'reports_plus_api_html_req' ), 'tab_selected' => $tab_selected, 'view' => $view_type );
				if ($this->request->isPrintCall ()) {
					switch ($search_by) {
						case 'project' :
							// $params = array_merge ( $default_params, array ('what' => $this->getValueFromRequest ( 'what' ), 'start' => $this->getValueFromRequest ( 'start' ), 'end' => $this->getValueFromRequest ( 'end' ), 'projects' => $this->getValueFromRequest ( 'projects' ), 'project_name_label' => $this->getValueFromRequest ( 'project_name_label' ), 'reports_filter_type' => $this->getValueFromRequest ( 'reports_filter_type' ) ) );
							$params = array_merge ( $default_params, array ('start' => $this->getValueFromRequest ( 'start' ), 'end' => $this->getValueFromRequest ( 'end' ), 'projects' => $this->getValueFromRequest ( 'projects' ), 'project_name_label' => $this->getValueFromRequest ( 'project_name_label' ), 'reports_filter_type' => $this->getValueFromRequest ( 'reports_filter_type' ), 'reports_filter_val' => $this->getValueFromRequest ( 'reports_filter_val' ), 'labels_map' => $labels_map, 'search_by' => $search_by ) );
						
							break;
						// case 'people' :
						// 	break;
						case 'companies' :
							$params = array_merge ( $default_params, array ('what' => $this->getValueFromRequest ( 'what' ), 'companies' => $this->getValueFromRequest ( 'companies' ), 'reports_company_label' => $this->getValueFromRequest ( 'reports_company_label' ), 'reports_company_status_label' => $this->getValueFromRequest ( 'reports_company_status_label' ), 'status' => $this->getValueFromRequest ( 'status' ) ) );
							break;
					}
				} elseif ($this->request->isPhone ()) {
					$params = array_merge ( $default_params, array ('reports_enum' => $reports_enum, 
																	'html_url' => Router::assemble ( 'reports_plus_html', array ('view' => 'html' ) ),
																	'available_reports_tabs' => $available_reports_tabs )
																	// 'reports_filters' => is_foreachable($this->getValueFromRequest('reports_filters')) ? $this->getValueFromRequest('reports_filters') : null,
					 );
				
				} else {
					$params = array_merge ( $default_params, array ('reports_enum' => $reports_enum,
																	'html_url' => Router::assemble ( 'reports_plus_html', array ('view' => 'html' ) ), 
																	'export_reports_url' => ($this->logged_user->canUseReports()) ? Router::assemble ( 'reports_plus_html', array ('export_reports' => 1 ) ) : null, 
																	'available_reports_tabs' => $available_reports_tabs ) );
				}
				
				$this->response->assign ( $params );
			
			} elseif ($this->getValueFromRequest('export_reports') == true) {
				$export_params = array();
				if($this->getValueFromRequest('companies') != ''){
					$export_params['companies'] = $this->getValueFromRequest('companies');
				}
				if($this->getValueFromRequest('status') != ''){
					$export_params['status'] = $this->getValueFromRequest('status');
				}
				$content = ReportsPlus::getExportData($this->logged_user, $export_params);
				$this->response->respondWithContentDownload (array_to_csv($content), BaseHttpResponse::CSV, 'projects_report.csv' );	
			}
		} elseif ($this->request->isApiCall ()) {
			
		}
		 // if

	} // index
	
	// api Function for HTML calls   
	function api_html_req() {
		$what = $this->getValueFromRequest ( 'what' );
		try {
			$reports = new ReportsPlus ();
			$data = array ('action' => $what, 'result' => 'error', 'message' => 'Please specify a valid action' );
			$options = array ();
			$visibility_condition = '';
			if ($this->getValueFromRequest ( 'start' ) != '') {
				$options ['start_date'] = $reports->parseFilterDate ( $this->getValueFromRequest ( 'start' ) );
			}
			if ($this->getValueFromRequest ( 'end' ) != '') {
				$options ['end_date'] = $reports->parseFilterDate ( $this->getValueFromRequest ( 'end' ) );
			}
			
			// Prepare visibility condition
			$activeModules = AngieApplication::getEnabledModuleNames ();
			if (in_array ( 'confidential_items', $activeModules )) {
				$visibility_condition = "AND " . ConfidentialItems::addVisibilityCondition ( null, null, $this->logged_user, 'po' );
			} else {
				$can_see_private = $this->logged_user->isProjectManager () || ( boolean ) $this->logged_user->canSeePrivate();
				$visibility_condition = "AND po.visibility >= " . (($can_see_private === true) ? VISIBILITY_PRIVATE : VISIBILITY_NORMAL);
			}
			
			$options ['visibility_condition'] = $visibility_condition;
			
			$data = array ('action' => $what, 'result' => 'error', 'message' => 'Please specify a valid action' );
			switch ($what) {
				case 'getLabelsAndSettings' :
					$result = $reports->getLabelsAndSettings ( $this->logged_user );
					break;
				case 'ping' :
					$result = array ('result' => 'success' );
					break;
				case 'getProjects' :
					$result = $reports->getProjects ( $this->logged_user );
					break;
				case 'getEnums' :
					$result = array ();
					$result ['filter_types'] = $reports->getFilterTypes ();
					$result ['companies'] = $reports->getCompanies ( $this->logged_user );
					$result ['people'] = $reports->getPeople ( $this->logged_user, $options );
					$result ['roles'] = $reports->getRoles ();
					$result ['dates'] = $reports->getDates ( $this->logged_user );
					$result ['project_groups'] = $reports->getProjectGroups ();
					break;
				case 'getPeople' :
					$options ['sort'] = $this->getValueFromRequest ( 'sort' );
					$result = $reports->getPeople ( $this->logged_user, $options );
					break;
				case 'getProjectStats' :
					$projects = $this->getValueFromRequest ( 'projects' );
					$result = $reports->getProjectStats ( $projects, $options, $this->logged_user );
					break;
				case 'getCompanyStats' :
					if (! $this->logged_user->isAdministrator () && ! $this->logged_user->getSystemPermission('can_see_reports_companies')) {
						$this->httpError ( HTTP_ERR_FORBIDDEN );
					}
					$companies = $this->getValueFromRequest ( 'companies' );
					$options ['status'] = $this->getValueFromRequest ( 'status' );
					$result = $reports->getCompanyStats ( $companies, $this->logged_user, $options );
					break;

				case 'getProjectStatstics' :
					$ids = $this->getValueFromRequest ( 'ids' );
					$search_by = $this->getValueFromRequest ( 'search_by' );
					$options ['status'] = $this->getValueFromRequest ( 'status' );
					$result = $reports->getProjectStatstics ( $ids, $search_by, $this->logged_user, $options );
					break;
				case 'getPersonStats' :
					if (! $this->logged_user->isAdministrator () && ! $this->logged_user->getSystemPermission('can_see_reports_people')) {
						$this->httpError ( HTTP_ERR_FORBIDDEN );
					}
					$id = $this->getValueFromRequest ( 'id' );
					$result = $reports->getPersonStats ( $id, $options, $this->logged_user );
					break;
				case 'getPersonActivity' :
					if (! $this->logged_user->isAdministrator () && ! $this->logged_user->getSystemPermission('can_see_reports_people')) {
						$this->httpError ( HTTP_ERR_FORBIDDEN );
					}
					$id = $this->getValueFromRequest ( 'id' );
					$result = $reports->getPersonActivity ( $id, $options, $this->logged_user );
					break;
				
				case 'sendReminder' :
					$to = $this->getValueFromRequest ( 'to' );
					$subject = $this->getValueFromRequest ( 'subject' );
					$body = $this->getValueFromRequest ( 'body' );
					$result = $reports->sendReminder ( $this->logged_user, $to, $subject, $body );
					break;
				
				case 'invoiceStats' :
					$result = $reports->getFinanceStats ( $options, $this->logged_user );
					break;
				case 'invoiceSummary' :
					$result = $reports->getFinanceSummary ( $options, $this->logged_user );
					break;
				
				default :
					$result = array ();
					break;
			}
			
			$data = array ('action' => $what, 'result' => 'success', 'data' => $result );
			
			if (is_array ( $options )) {
				$data ['options'] = $options;
			}
		} catch ( Exception $e ) {
			$data = array ('action' => $what, 'result' => 'error', 'message' => $e->getMessage () );
		}
		// Following headers solve IE7/8's problem with HTTPS and Flex
		// References:
		// http://faindu.wordpress.com/2008/04/18/ie7-ssl-xml-flex-error-2032-stream-error/
		// http://www.judahfrangipane.com/blog/?p=87
		header ( "Cache-Control: no-store,max-age=0,must-revalidate" );
		header ( "Pragma: public" );
		header ( "Pragma: public_no_cache" );
		$this->response->respondWithData ( $data, array ('format' => FORMAT_XML ) );
		//		$this->response->assign('data', $data);
		die ();
	}
	
	function getValueFromRequest($param) {
		$value = array_var ( $_POST, $param, NULL );
		if (is_null ( $value )) {
			$value = array_var ( $_GET, $param, NULL );
		}
		return $value;
	}
	
	function inline_view() {
		$options = array ();
		$active_user_id = $this->request->get ('user_id');
		$leader_board_url = Router::assemble ( 'reports_plus_leader_board', array ('people_id' => $this->request->get ( 'people_id' ), 'user_id' => $active_user_id ) );
		
		$response_data = array ('leader_board_url' => $leader_board_url);
		$this->smarty->assign ( $response_data );
	}
	
	function leader_board() {
		$report = new ReportsPlus ();
		$options = array ();
		
		$options ['start_date'] = ($this->getValueFromRequest ( 'start' ) != '') ? $report->parseFilterDate ( $this->getValueFromRequest ( 'start' ) ) : date ( 'Y-m-d', strtotime ( '-3 months' ) );
		$options ['end_date'] = ($this->getValueFromRequest ( 'end' ) != '') ? $report->parseFilterDate ( $this->getValueFromRequest ( 'end' ) ) : date ( 'Y-m-d', time () );
		
		// Prepare visibility condition
		if (AngieApplication::isModuleLoaded ( 'confidential_items' )) {
			$visibility_condition = "AND " . ConfidentialItems::addVisibilityCondition ( null, null, $this->logged_user, 'po' );
		} else {
			$can_see_private = $this->logged_user->isProjectManager () || ( boolean ) $this->logged_user->canSeePrivate();
			$visibility_condition = "AND po.visibility >= " . (($can_see_private === true) ? VISIBILITY_PRIVATE : VISIBILITY_NORMAL);
		}
		$options ['visibility_condition'] = $visibility_condition;
		
		$active_user_id = $this->request->get ( 'user_id' );
		$active_user = Users::FindById ( $active_user_id );
		$result = $report->getPersonStats ( $active_user_id, $options, $this->logged_user );
		
		$projects_map = Projects::getIdNameMapByIds ( $result ['projects'] ['ids'] );
		$series_colors = array ("#0571B0", "#5E3C99", "#008837", "#A6DBA0", "#A6611A", "#92C5DE", "#B2ABD2", "#E66101", "#FDB863", "#F4A582", "#DFC27D", "#CA0020", "#F3CBBF", "#54DB4F", "#E83B01", "#04C0F0", "#BA55D3", "#6A5ACD", "#FFD700", "#86813C" );
		$series_colors_length = count ( $series_colors );
		
		//Create a 2D array with the project names and series color
		$projects_label_color_mapping = array ();
		
		//Loop on the number of id's recieved in the result array..
		$projects_ids = $result ['projects'] ['ids'];
		if (($projects_ids != null) && (count ( $projects_ids ) > 0)) {
			for($i = 0; $i < count ( $projects_ids ); $i ++) {
				$project_label = $projects_map [$projects_ids [$i]];
				$projects_label_color_mapping [$project_label] = $series_colors [$i % ($series_colors_length - 1)];
			}
		}
		
		//Person Summary..
		$person_summary ['Items Worked'] = (isset ( $result ['items'] ['worked'] ) && ($result ['items'] ['worked'] != '') && ($result ['items'] ['worked'] != null)) ? $result ['items'] ['worked'] : 0;
		$person_summary ['Hours'] = (isset ( $result ['hours'] ) && ($result ['hours'] != '') && ($result ['hours'] != null)) ? $result ['hours'] : 0;
		$person_summary ['Activity Score'] = (isset ( $result ['score'] ) && ($result ['score'] != '') && ($result ['score'] != null)) ? $result ['score'] : 0;
		
		//Project Responsibilities..
		$project_responsibilities ['Projects - Leading'] = (isset ( $result ['projects'] ['leading'] ) && ($result ['projects'] ['leading'] != '') && ($result ['projects'] ['leading'] != null)) ? $result ['projects'] ['leading'] : 0;
		$project_responsibilities ['Projects - Assigned'] = (isset ( $result ['projects'] ['assigned'] ) && ($result ['projects'] ['assigned'] != '') && ($result ['projects'] ['assigned'] != null)) ? $result ['projects'] ['assigned'] : 0;
		$project_responsibilities ['Items - Leading'] = (isset ( $result ['items'] ['leading'] ) && ($result ['items'] ['leading'] != '') && ($result ['items'] ['leading'] != null)) ? $result ['items'] ['leading'] : 0;
		$project_responsibilities ['Items - Assigned'] = (isset ( $result ['items'] ['assigned'] ) && ($result ['items'] ['assigned'] != '') && ($result ['items'] ['assigned'] != null)) ? $result ['items'] ['assigned'] : 0;
		
		//Project Activities..
		$project_activities ['Items - Created'] = (isset ( $result ['items'] ['created'] ) && ($result ['items'] ['created'] != '') && ($result ['items'] ['created'] != null)) ? $result ['items'] ['created'] : 0;
		$project_activities ['Items - Completed'] = (isset ( $result ['items'] ['completed'] ) && ($result ['items'] ['completed'] != '') && ($result ['items'] ['completed'] != null)) ? $result ['items'] ['completed'] : 0;
		$project_activities ['Items - Worked'] = (isset ( $result ['items'] ['worked'] ) && ($result ['items'] ['worked'] != '') && ($result ['items'] ['worked'] != null)) ? $result ['items'] ['worked'] : 0;
		$project_activities ['Items - Overdue'] = (isset ( $result ['items'] ['overdue'] ) && ($result ['items'] ['overdue'] != '') && ($result ['items'] ['overdue'] != null)) ? $result ['items'] ['overdue'] : 0;
		$project_activities ['Comments'] = (isset ( $result ['items'] ['created_types'] [1] ['total'] ) && ($result ['items'] ['created_types'] [1] ['total'] != '') && ($result ['items'] ['created_types'] [1] ['total'] != null)) ? $result ['items'] ['created_types'] [1] ['total'] : 0;
		$project_activities ['Time Records'] = (isset ( $result ['time_entries'] ) && ($result ['time_entries'] != '') && ($result ['time_entries'] != null)) ? $result ['time_entries'] : 0;
		
		//To show the percentage values in the tool-tip, we need the total of hours and assignments
		$total_hours = $result ['hours'];
		$total_assignments = 0;
		
		$projects_hours = $result ['projects'] ['hours'];
		$projects_items_assigned = $result ['projects'] ['items_assigned'];
		$projects_map = Projects::getIdNameMapByIds ( $result ['projects'] ['ids'] );
		
		//Associative arrays to compare the keys of the two arrays..
		if (is_foreachable ( $projects_hours )) {
			foreach ( $projects_hours as $project_hours ) {
				$hours [] = array ($projects_map [$project_hours ['id']], floatval ( $project_hours ['total'] ), round ( (($project_hours ['total'] / $total_hours) * 100), 1 ) );
			}
		}
		
		//Calculate the total_assignments total value to use it in calculating the percentage..
		if (is_foreachable ( $projects_items_assigned )) {
			foreach ( $projects_items_assigned as $project_items_assigned ) {
				$total_assignments += $project_items_assigned ['total'];
			}
		}
		
		if (is_foreachable ( $projects_items_assigned )) {
			foreach ( $projects_items_assigned as $project_items_assigned ) {
				$items_assigned [] = array ($projects_map [$project_items_assigned ['id']], floatval ( $project_items_assigned ['total'] ), round ( (($project_items_assigned ['total'] / $total_assignments) * 100), 1 ) );
			}
		}
		
		//Get the project-names for corresponding project-id's..
		$projects_info = $report->getProjects ( $this->logged_user );
		
		$javascripts = array ('jquery.jqplot.min.js', 'jqplot.donutRenderer.min.js' );
		
		foreach ( $javascripts as $javascript ) {
			$javascript_urls [] = AngieApplication::getAssetUrl ( $javascript, REPORTS_PLUS_MODULE, 'images', 'foundation' );
		}
		
		// Handling Null values
		if (! is_foreachable ( $hours )) {
			$hours = array ("dummy", 0, 0 );
		}
		
		if (! is_foreachable ( $items_assigned )) {
			$items_assigned = array ("dummy", 0, 0 );
		}
		
		$api_url = Router::assemble ( 'reports_plus_inline', array ('people_id' => $this->request->get ( 'people_id' ), 'user_id' => $active_user_id ) );
		$leader_board_url = Router::assemble ( 'reports_plus_leader_board', array ('people_id' => $this->request->get ( 'people_id' ), 'user_id' => $active_user_id ) );
		$load = ($this->request->get ( 'load' )) ? 1 : 0;
		$response_data = array ('javascript_urls' => $javascript_urls, 'date_format' => ConfigOptions::getValue ( "reports_date_format" ), 'hours' => $hours, 'items_assigned' => $items_assigned, 'projects_info' => $projects_info, 'api_url' => $api_url, 'leader_board_url' => $leader_board_url, 'person_summary' => $person_summary, 'project_responsibilities' => $project_responsibilities, 'project_activities' => $project_activities, 'projects_label_color_mapping' => $projects_label_color_mapping, 'load' => $load, 'start_date' => $options ['start_date'], 'end_date' => $options ['end_date'] );
		
		if ($this->request->get ( 'load' )) {
			$view = SmartyForAngie::getInstance ()->createTemplate ( get_view_path ( 'leader_board', 'reports_plus', REPORTS_PLUS_MODULE, AngieApplication::INTERFACE_DEFAULT ) );
			$view->assign ( $response_data );
			$fetch = $view->fetch ();
			$this->response->respondWithData ( ( object ) $fetch );
		} else {
			$this->smarty->assign ( $response_data );
		}
	}
	
	function user_score_update(){
		try{
			$reports = new ReportsPlus ();
		    $result = $reports->getLeaderBoardData($this->logged_user);
		    $score = $result['logged_user_rank'];

			$this->response->respondWithData ( $score);
        } catch(Exception $e) {
	          $this->response->exception($e);
		}
	}

	/**
     * Pepople getting data for leaderboard
     */
    function people_leader_board(){
      $reports = new ReportsPlus ();
      $result = $reports->getLeaderBoardData($this->logged_user);
      $this->response->assign ($result);
    } 
}