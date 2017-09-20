<?php

/**
 * ReportsPlus module on_inline_tabs event handler
 *
 * @package custom.modules.reports_plus
 * @subpackage handlers
 */

/**
 * Handle on inline tabs event
 *
 * @param NamedList $tabs
 * @param ApplicationObject $object
 * @param User $logged_user
 * @param string $interface
 * @return null
 */

function reports_plus_handle_on_inline_tabs(&$tabs, &$object, &$logged_user, $interface) {
	// populate user inline tabs
	if ($object instanceof User && (($logged_user->getSystemPermission('can_use_reports')  && ($logged_user->getId() == $object->getId())) || $logged_user->canUseReports())) {
		$tabs->beginWith ( 'user_leader_board', array ('title' => lang ( 'Reports' ), 'url' => Router::assemble ( 'reports_plus_inline', array ('people_id' => $logged_user->getId (), 'user_id' => $object->getId () ) ) ), true );
	}
} // reports_plus_handle_on_inline_tabs


?> 