<?php

/**
 * select_time_reports_plus_filter helper implementation
 * 
 * @package custom.modules.time_reports_plus
 * @subpackage helpers
 */

/**
 * Render time_reports_plus filter picker
 * 
 * @param array $params
 * @param Smarty $smarty
 * @return string
 */
function smarty_function_select_time_reports_plus_filter($params, &$smarty) {
	$user = array_required_var ( $params, 'user', true, 'User' );
	$name = array_required_var ( $params, 'name' );
	$value = array_var ( $params, 'value', null, true );
	
	if (array_var ( $params, 'optional', true, true )) {
		return HTML::optionalSelectFromPossibilities ( $name, TrackingReportPlus::getIdNameMap (), $value, $params );
	} else {
		return HTML::selectFromPossibilities ( $name, TrackingReportPlus::getIdNameMap (), $value, $params );
	} // if
} // smarty_function_select_time_reports_plus_filter