<?php

/**
 * on_homescreen_tab_types event handler
 * 
 * @package custom.modules.reports_plus
 * @subpackage handlers
 */

/**
 * Handle on_homescreen_tab_types event
 * 
 * @param unknown_type $types
 */
function reports_plus_handle_on_homescreen_tab_types(&$types, &$user) {
	if (AngieApplication::isModuleLoaded ( 'tracking' ) && ($user->isAdministrator() || $user->isFinancialManager() || $user->getSystemPermission('can_see_time_reports'))) {
		$types [] = new TimeReportsPlusHomescreenTab ();
	}
} // reports_plus_handle_on_homescreen_tab_types