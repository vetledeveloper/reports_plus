<?php

/**
 * Reports Plus module on_project_overview_sidebars event handler
 *
 * @package custom.modules.reports_plus
 * @subpackage handlers
 */

/**
 * Add sidebars to project overview page
 *
 * @param array $sidebars
 * @param Project $project
 * @param User $user
 */
function reports_plus_handle_on_project_overview_sidebars(&$sidebars, Project &$project, User &$user) {
	if (AngieApplication::isModuleLoaded ('tracking') && ($user->canUseReports() || ($user->getSystemPermission('can_see_time_reports') && $project->isLeader($user))) ) {
		$report = new TrackingReport ();
		$report->setTypeFilter ( TrackingReport::TYPE_FILTER_TIME );
		$project_ids [] = $project->getId ();
		$report->filterByProjects ( $project_ids );
		// Removing Trashed from the project users.
		$trashed_user_ids = array_var((Users::getTrashedMap($user)), 'user');
		$user_ids = array_diff(array_keys(Users::getIdNameMap(null, false)), ($trashed_user_ids != null) ? $trashed_user_ids : array());
		$report->filterByUsers($user_ids);

		$report->setSumByUser ( true );
		$records = $report->run($user);
		
		if (is_foreachable ( $records ['all'] ['records'] )) {
			$view = SmartyForAngie::getInstance ()->createTemplate ( AngieApplication::getViewPath ( 'tracking_report', 'time_reports_plus', REPORTS_PLUS_MODULE, AngieApplication::INTERFACE_DEFAULT ) );
			$view->assign ( array ('user' => $user, 'report' => $report, 'records' => $records ) );
			$sidebars [] = array ('label' => lang ( 'Time Tracked on This Project' ), 'is_important' => false, 'id' => 'project_time_record', 'body' => $view->fetch () );
		}
	}

} // reports_plus_handle_on_project_overview_sidebars