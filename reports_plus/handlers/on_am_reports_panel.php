<?php

/**
 * on_reports_panel event handler
 * 
 * @package custom.modules.reports_plus
 * @subpackage handlers
 */

/**
 * Handle on_reports_panel event
 * 
 * @param ReportsPanel $panel
 * @param IUser $user
 */
function reports_plus_handle_on_am_reports_panel(ReportsPanel &$panel, IUser &$user) {
	if ($user->isAdministrator() || $user->isFinancialManager() || $user->getSystemPermission('can_use_reports')) {
		$panel->addToGeneral ( 'reports_plus_html', lang ( 'Reports Plus' ), Router::assemble ( 'reports_plus_html', array ('view' => 'html' ) ), AngieApplication::getImageUrl ( 'module.png', REPORTS_PLUS_MODULE, AngieApplication::INTERFACE_DEFAULT ) );
	} // if
	
	if(AngieApplication::isModuleLoaded ( 'tracking' ) && ($user->isAdministrator() || $user->isFinancialManager() || $user->getSystemPermission('can_see_time_reports'))){
		$panel->addToGeneral ( 'tracking_time_reports_plus', lang ( 'Time Reports Plus' ), Router::assemble ( 'time_reports_plus' ), AngieApplication::getImageUrl ( 'time_reports_plus_module.png', REPORTS_PLUS_MODULE, AngieApplication::INTERFACE_DEFAULT ) );
	}
	$rp = new ReportsPlus();
    if($rp->canAccessLeaderBoard()){
		$panel->addToGeneral ( 'reports_plus_leader_board', lang ( 'Leader Board' ), Router::assemble ( 'reports_people_leader_board'), AngieApplication::getImageUrl ( 'leader_board.png', REPORTS_PLUS_MODULE, AngieApplication::INTERFACE_DEFAULT ) );
    }
} // reports_plus_handle_on_reports_panel