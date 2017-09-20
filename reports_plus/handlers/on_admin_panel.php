<?php

/**
 * on_admin_panel event handler
 * 
 * @package custom.modules.reports_plus
 * @subpackage handlers
 */

/**
 * Handle on_admin_panel event
 * 
 * @param AdminPanel $admin_panel
 */
function reports_plus_handle_on_admin_panel(AdminPanel &$admin_panel) {
	$admin_panel->addToProjects ( 'reports_plus_admin', lang ( 'Reports Plus' ), Router::assemble ( 'reports_plus_admin' ), AngieApplication::getImageUrl ( 'module.png', REPORTS_PLUS_MODULE ) );
} // reports_plus_handle_on_admin_panel