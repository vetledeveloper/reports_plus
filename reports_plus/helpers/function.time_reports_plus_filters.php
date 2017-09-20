<?php

/**
 * time_reports_filters helper implementation
 * 
 * @package cunstom.modules.time_reports_plus
 * @subpackage helpers
 */

/**
 * Render time reports plus filters
 * 
 * @param array $params
 * @param Smarty $smarty
 */
function smarty_function_time_reports_plus_filters($params, &$smarty) {
	$user = array_required_var ( $params, 'user', null, 'User' );
	$filter = array_var ( $params, 'filter', null, true );
	$view = $smarty->createTemplate ( AngieApplication::getViewPath ( 'index', 'time_reports_plus', REPORTS_PLUS_MODULE, AngieApplication::INTERFACE_DEFAULT ) );

	$condition = '';
	$companies = $all_companies = Companies::getIdNameMap ( null, STATE_VISIBLE );
	$categories = $all_categories = Categories::getIdNameMap ( null, 'ProjectCategory' );

	if (! $user->canUseReports ()) {
			$visible_ids = TrackingReportPlus::getVisibleProjectIds ( $user);
			if (count ( $visible_ids )) {
				$condition = DB::prepare ( '(' . TABLE_PREFIX . 'projects.id IN (?))', $visible_ids);
				$visible_companies_ids = TimeReportsPlus::getCompanyIdsByProjectIds($visible_ids);
				$visible_category_ids =  TimeReportsPlus::getCategoryIdsByProjectIds($visible_ids);
			}
			$categories = is_foreachable ( $visible_category_ids ) ? array_intersect_key ( $all_categories, array_flip ( $visible_category_ids ) ) : null;
			$companies =  is_foreachable ( $visible_companies_ids ) ? array_intersect_key ( $all_companies, array_flip ( $visible_companies_ids ) ) : null;
	}
	$projects = Projects::getIdNameMap ( $user, STATE_VISIBLE, null, $condition, true );
	$condition = '';
	$companies = Companies::getIdNameMap ( null, STATE_VISIBLE );
	
	$view->assign(array('export_csv_url' => extend_url ( Router::assemble ( 'time_report_plus_run' ), array ('export_format' => 'csv', 'from_home_scren_tab' => true ) ), 
						'export_html_url' => extend_url ( Router::assemble ( 'time_report_plus_run' ), array ('export_format' => "html", 'from_home_scren_tab' => true ) ), 
						'time_reports_plus_run' => extend_url ( Router::assemble ( 'time_report_plus_run' ), array ('from_home_scren_tab' => true ) ), 
						'time_reports_plus' => TimeReportsPlus::findByUser ( 'TrackingReport', $user ),
						'pre_select_filter' => $filter, 
						'new_filter_url' => TimeReportsPlus::canAdd ( $user ) ? Router::assemble ( 'time_reports_plus_add' ) : null, 
						'companies' => $companies, 'users' => Users::getForSelect ( $user ), 
						'projects' => $projects, 'project_categories' => $categories, 
						'job_types' => JobTypes::getIdNameMap (), 'expense_categories' => ExpenseCategories::getIdNameMap (), 
						'can_use_reports' => $user->canUseReports () ) );
	
	return $view->fetch ();
} // smarty_function_time_reports_plus_filters
