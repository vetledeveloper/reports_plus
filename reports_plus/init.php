<?php
/**
 * Init reports plus module
 *
 * @package custom.modules.reports_plus
 */

define ( 'REPORTS_PLUS_MODULE', 'reports_plus' );
define ( 'REPORTS_PLUS_MODULE_PATH', CUSTOM_PATH . '/modules/reports_plus' );

//define('TIME_REPORTS_PLUS_MODULE', 'time_reports_plus');
//define('TIME_REPORTS_PLUS_MODULE_PATH', CUSTOM_PATH . '/modules/time_reports_plus');

AngieApplication::setForAutoload (array ('ReportsPlus' => REPORTS_PLUS_MODULE_PATH . '/models/ReportsPlus.class.php', 
	'TimeReportsPlus' => REPORTS_PLUS_MODULE_PATH . '/models/time_reports_plus/TimeReportsPlus.class.php',
	'TrackingReportPlus' => REPORTS_PLUS_MODULE_PATH . '/models/time_reports_plus/TrackingReportPlus.class.php', 
	'TimeReportsPlusHomescreenTab' => REPORTS_PLUS_MODULE_PATH . '/models/homescreen_tabs/TimeReportsPlusHomescreenTab.class.php' ) );

if(!AngieApplication::isModuleLoaded('tracking')){
	$homescreen_tab_table = TABLE_PREFIX . 'homescreen_tabs';
	if(DB::tableExists ( $homescreen_tab_table )){
		DB::execute ( "DELETE FROM $homescreen_tab_table WHERE type IN ('TimeReportsPlusHomescreenTab') " );
	} 
}

// For upgrade
if (! DB::tableExists ( TABLE_PREFIX . 'rep_finance_summary' )) {
	ReportsPlus::updateFinancialSummary ();
}

//Uninstall Time Reports Plus module if Time Reports Plus module is already installed
if (AngieApplication::isModuleLoaded ( 'time_reports_plus' )) {
	DB::execute ( ' DELETE FROM ' . TABLE_PREFIX . 'modules WHERE name =  ? ', 'time_reports_plus' );
	AngieApplication::cleanModuleAssets ( 'time_reports_plus' );
}
 