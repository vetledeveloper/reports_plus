<?php

/**
 * Reports Plus module on_main_menu event handler
 *
 * @package custom.modules.reports_plus
 * @subpackage handlers
 */

/**
 * Handle on_main_menu event
 *
 */
function reports_plus_handle_on_main_menu(MainMenu &$menu, User &$user) {
	if (!$user->canUseReports()) {
		$menu->addBefore ( 'am_reports', lang ( 'Reports & Filters' ), Router::assemble ( 'am_reports_panel' ), AngieApplication::getImageUrl ( 'reports.png', REPORTS_FRAMEWORK, AngieApplication::INTERFACE_DEFAULT ), null, 'admin' );
	} // if
} // reports_plus_on_main_menu