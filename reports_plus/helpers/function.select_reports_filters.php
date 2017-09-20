<?php

/**
 * select_expense_category helper implementation
 * 
 * @package custom.modules.reports_plus
 * @subpackage helpers
 */

/**
 * Render select expense category box
 * 
 * @param array $params
 * @param Smarty $smarty
 * @return string
 */
function smarty_function_select_reports_filters($params, &$smarty) {
	
	$name = array_required_var ( $params, 'name', true );
	$value = array_var ( $params, 'value', null, true );
	$option_data = array_var ( $params, 'option_data', null, true );
	
	if (is_foreachable ( $option_data )) {
		foreach ( $option_data as $filter ) {
			$value [] = $filter ['data'];
			$label [] = $filter ['label'];
		}
	}
	
	return HTML::selectFromPossibilities ( $name, $label, $value, $params );
} // smarty_function_select_reports_filters