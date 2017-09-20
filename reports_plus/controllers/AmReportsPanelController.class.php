<?php

// Build on top of reports module
AngieApplication::useController ( 'reports', REPORTS_FRAMEWORK_INJECT_INTO );

/**
 * Reports Plus controller
 *
 * @package custom.modules.reports_plus
 * @subpackage controllers
 */
class AmReportsPanelController extends ReportsController {
	
	var $check_reports_access_permissions = false;
	/**
	 * Prepare controller
	 */
	
	function reports_panel() {
		
		$this->setView ( array ('view' => 'index', 'controller' => 'fw_reports', 'module' => REPORTS_FRAMEWORK ) );
		
		$reports_panel = new ReportsPanel ();
		$reports_panel->defineRow ( 'assignments', new ReportsPanelRow ( lang ( 'Assignments' ) ) );
		
		EventsManager::trigger ( 'on_am_reports_panel', array (&$reports_panel, &$this->logged_user ) );
		$this->smarty->assign ( 'reports_panel', $reports_panel );
	} // index
}