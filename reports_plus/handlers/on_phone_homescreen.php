<?php

/**
 * on_phone_homescreen event handler
 * 
 * @package custom.modules.reports_plus
 * @subpackage handlers
 */

/**
 * Handle on_phone_homescreen event
 * 
 * @param NamedList $items
 * @param IUser $user
 */
function reports_plus_handle_on_phone_homescreen(NamedList &$items, IUser &$user) {
	if ($user->isAdministrator () || $user->getSystemPermission('can_use_reports')) {
		$items->add ( 'reports_plus', array ('text' => lang ( 'Reports Plus' ), 'url' => Router::assemble ( 'reports_plus_html', array ('view' => 'html' ) ), 'icon' => AngieApplication::getImageUrl ( 'icons/homescreen/reports.png', REPORTS_PLUS_MODULE, AngieApplication::INTERFACE_PHONE ) ) );
	} // if
} // reports_plus_handle_on_phone_homescreen