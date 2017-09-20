<?php

/**
 * Reports module definition
 *
 * @package custom.modules.reports_plus
 */
class ReportsPlusModule extends AngieModule {
	
	/**
	 * Plain module name
	 *
	 * @var string
	 */
	protected $name = 'reports_plus';
	
	/**
	 * Module version
	 *
	 * @var string
	 */
	protected $version = '4.2.5';
	
	// ---------------------------------------------------
	//  Events and Routes
	// ---------------------------------------------------
	

	
	/**
	 * Define module routes
	 *
	 * @return null
	 */
	function defineRoutes() {
		Router::map ( 'reports_plus', 'reports_plus', array ('controller' => 'reports_plus', 'action' => 'index' ), array () );
		Router::map ( 'reports_plus_html', 'reports_plus/html', array ('controller' => 'reports_plus', 'action' => 'index' ), array ('view' => '\w+' ) );
		//Router::map ( 'reports_plus_api', 'reports_plus/api', array ('controller' => 'reports_plus', 'action' => 'api' ), array () );
		Router::map ( 'reports_plus_admin', 'admin/others/reports_plus', array ('controller' => 'reports_plus_admin', 'action' => 'index' ), array ('form' => '\w+' )   );
		// Router::map ( 'reports_plus_admin_permissions', 'admin/others/reports_plus', array ('controller' => 'reports_plus_admin', 'action' => 'index' ), array ('form' => 'permissions' )  );
		//      Router::map('invoice_notify', 'invoices/:invoice_id/notify', array('controller' => 'invoices', 'action' => 'notify'), array('invoice_id' => Router::MATCH_ID));
		//      Router::map('reports_print', 'reports_plus/print', array('controller' => 'reports_plus', 'action' => 'reports_print'));
		Router::map ( 'reports_mobile_filters', 'reports_plus/filters', array ('controller' => 'reports_plus', 'action' => 'reports_mobile_filters' ) );
		Router::map ( 'reports_plus_api_html_req', 'reports_plus/api_html_req', array ('controller' => 'reports_plus', 'action' => 'api_html_req' ), array () );
		Router::map ( 'reports_plus_inline', 'people/:people_id/users/:user_id/reports', array ('controller' => 'reports_plus', 'action' => 'inline_view' ), array ('people_id' => Router::MATCH_ID, 'user_id' => Router::MATCH_ID ) );
		Router::map ( 'reports_plus_leader_board', 'people/:people_id/users/:user_id/leader-board', array ('controller' => 'reports_plus', 'action' => 'leader_board' ), array ('people_id' => Router::MATCH_ID, 'user_id' => Router::MATCH_ID ) );

        // Router::map('people', 'people', array('controller' => 'rp_people', 'action' => 'index'));
		// Router::map ( 'reports_people_leader_board', 'people/leader-board', array ('controller' => 'rp_people', 'action' => 'people_leader_board' ), array ('people_id' => Router::MATCH_ID, 'user_id' => Router::MATCH_ID ) );
		Router::map ( 'reports_people_leader_board', 'reports_plus/leader-board', array ('controller' => 'reports_plus', 'action' => 'people_leader_board' ) );
		Router::map ( 'reports_user_score_update', 'reports_plus/score-update', array ('controller' => 'reports_plus', 'action' => 'user_score_update' ) );
		
		//Time Reports Plus related routes
		Router::map ( 'time_reports_plus', 'reports/timereportsplus', array ('controller' => 'time_reports_plus', 'action' => 'index' ) );
		Router::map ( 'time_reports_plus_add', 'reports/timereportsplus/add', array ('controller' => 'time_reports_plus', 'action' => 'add' ) );
		Router::map ( 'time_reports_plus_edit', 'reports/timereportsplus/:tracking_report_id/edit', array ('controller' => 'time_reports_plus', 'action' => 'edit' ), array ('tracking_report_id' => '\d+' ) );
		Router::map ( 'time_report_plus_run', 'timereportsplus/run', array ('controller' => 'time_reports_plus', 'action' => 'run' ) );
		Router::map ( 'time_report_plus_mass_edit', 'reports/timereportsplus/mass-edit', array ('controller' => 'time_reports_plus', 'action' => 'mass_edit' ) );
		Router::map ( 'project_tracking', 'projects/:project_slug/tracking', array ('controller' => 'project_tracking_plus', 'action' => 'log' ) );
		Router::map ( 'project_tracking_tab', 'projects/:project_slug/tracking_tab', array ('controller' => 'project_tracking_plus', 'action' => 'render_tab' ) );
		// Tracked Time & Expenses

		// Invoicing
		if (AngieApplication::isModuleLoaded ( 'invoicing' )) {
			AngieApplication::getModule ( 'invoicing' )->defineInvoiceRoutesFor ( 'tracking_report', 'tracking-report', 'time_reports_plus', REPORTS_PLUS_MODULE, array ('tracking_report_id' => Router::MATCH_ID ) );
		} // if

		//Hijacked Route
		Router::map ( 'am_reports_panel', 'am_reports', array ('controller' => 'am_reports_panel', 'action' => 'reports_panel' ) );
	
	} // defineRoutes
	

	/**
	 * Define event handlers
	 *
	 * @return null
	 */
	function defineHandlers() {
		EventsManager::listen ( 'on_daily', 'on_daily' );
		EventsManager::listen ( 'on_reports_panel', 'on_reports_panel' );
		EventsManager::listen ( 'on_am_reports_panel', 'on_am_reports_panel' );
		EventsManager::listen ( 'on_system_permissions', 'on_system_permissions' );
		EventsManager::listen ( 'on_admin_panel', 'on_admin_panel' );
		EventsManager::listen ( 'on_main_menu', 'on_main_menu' );
		EventsManager::listen ( 'on_inline_tabs', 'on_inline_tabs' );
		EventsManager::listen ( 'on_phone_homescreen', 'on_phone_homescreen' );
		EventsManager::listen ( 'on_custom_user_permissions', 'on_custom_user_permissions' );
		EventsManager::listen ( 'on_initial_javascript_assign', 'on_initial_javascript_assign' );
		// EventsManager::listen ( 'on_wireframe_updates', 'on_wireframe_updates' );
		// EventsManager::listen ( 'on_shutdown', 'on_shutdown' );
		
		//Time Reports Plus
		EventsManager::listen ( 'on_homescreen_tab_types', 'on_homescreen_tab_types' );
		EventsManager::listen ( 'on_project_overview_sidebars', 'on_project_overview_sidebars' );
	} // defineHandlers
	

	/**
	 * Install module
	 *
	 * @param void
	 * @return boolean
	 */
	function install() {

		if (! defined ( 'REPORTS_PLUS_MODULE' )) {
			define ( 'REPORTS_PLUS_MODULE', 'reports_plus' );
		}	
		
		$engine = defined ( 'DB_CAN_TRANSACT' ) && DB_CAN_TRANSACT ? 'ENGINE=InnoDB' : '';
		$charset = defined ( 'DB_CHARSET' ) && DB_CHARSET == 'utf8' ? 'DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci' : '';
		
		// Get master categories
		// quick fix for the ticket_categories not available : start
		$master_categories = array ();
		try {
			$master_categories = ( array ) ConfigOptions::getValue ( 'task_categories' );
			/*
			if (count($pages_categories) > 0) {
				$master_categories = array_values(array_unique(array_merge($master_categories, $pages_categories)));
			}
			*/
		} catch ( ConfigOptionDnxError $e ) {
			//throw $e;	
		}
		
		// Don't drop the table if it exists - this can be an upgrade
		// DB::execute("DROP TABLE IF EXISTS `" . TABLE_PREFIX . "rep_tickets_snapshot`");

		// Snapshot table query
		$query = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "rep_tickets_snapshot` (
					`date` date NOT NULL,
					`project_id` smallint(5) NOT NULL,
					`iteration` smallint(5) NOT NULL,
					`total` int(6) NOT NULL default '0',
					`open` int(6) NOT NULL default '0',
					`closed` int(6) NOT NULL default '0',
					`created_in_iteration` int(6) NOT NULL default '0',
					`closed_in_iteration` int(6) NOT NULL default '0',
					`reopened_in_iteration` int(6) NOT NULL default '0',
					`deleted_in_iteration` int(6) NOT NULL default '0',
					`estimated_hours` double(10,2) NOT NULL default '0',
					`actual_hours` double(10,2) NOT NULL default '0',
					`hours_in_iteration` double(10,2) NOT NULL default '0',
					`others_opened` smallint(5) NOT NULL default '0',
					`others_closed` smallint(5) NOT NULL default '0',";
		// Create fields for master categories
		if (is_foreachable ( $master_categories )) {
			$count = count ( $master_categories );
			for($i = 0; $i < $count; $i ++) {
				$query .= " `cat" . $i . "_opened` smallint(5) NOT NULL default '0',
					`cat" . $i . "_closed` smallint(5) NOT NULL default '0', ";
			} // for
		} // if
		

		$query .= "PRIMARY KEY  (`date`,`project_id`),
			KEY `iteration` (`iteration`)
			) $engine $charset COMMENT='Iteration wise tasks count snapshot';";
		// Create snapshot table
		DB::execute ( $query );
		// Configuration Options
		try {
			
			ConfigOptions::addOption ( 'reports_iteration_days', REPORTS_PLUS_MODULE, 7 );
			ConfigOptions::addOption ( 'reports_update_frequency', REPORTS_PLUS_MODULE, 'D' ); // Possible options - W: weekly, D: Daily

			$date_format_values = array ("DD/MM/YY", "MM/DD/YY" );
			ConfigOptions::addOption ( 'reports_date_format_values', REPORTS_PLUS_MODULE, $date_format_values );
			ConfigOptions::addOption ( 'reports_date_format', REPORTS_PLUS_MODULE, $date_format_values [0] );
			ConfigOptions::addOption ( 'reports_num_recent_items', REPORTS_PLUS_MODULE, 6 );
			ConfigOptions::addOption ( 'reports_num_weeks_for_average_velocity', REPORTS_PLUS_MODULE, 3 );
			ConfigOptions::addOption ( 'reports_formula_weight_most_active_comments', REPORTS_PLUS_MODULE, 2 );
			ConfigOptions::addOption ( 'reports_formula_weight_most_active_hours', REPORTS_PLUS_MODULE, 1 );
			ConfigOptions::addOption ( 'reports_formula_weight_most_active_version', REPORTS_PLUS_MODULE, 0.25 );
			ConfigOptions::addOption ( 'reports_formula_weight_person_score_hours', REPORTS_PLUS_MODULE, 1 );
			ConfigOptions::addOption ( 'reports_formula_weight_person_score_completed', REPORTS_PLUS_MODULE, 3 );
			ConfigOptions::addOption ( 'reports_formula_weight_person_score_created', REPORTS_PLUS_MODULE, 2 );
			ConfigOptions::addOption ( 'reports_reminder_copy_self', REPORTS_PLUS_MODULE, true );
			ConfigOptions::addOption ( 'reports_update_on_access', REPORTS_PLUS_MODULE, false );
			
			// Remove "Others" from Master Categories if it exists
			$master_categories = array_diff ( $master_categories, array ("Others", "others" ) );
			ConfigOptions::addOption ( 'reports_master_categories', REPORTS_PLUS_MODULE, $master_categories );
			
			//Store current version of a reports plus module for upgrade process in future.
			ConfigOptions::addOption ( 'previous_reports_plus_module_version', REPORTS_PLUS_MODULE, '3.3' );

	        ConfigOptions::addOption( 'reports_permissions',REPORTS_PLUS_MODULE, array());
	        ConfigOptions::addOption( 'reports_enable_leader_board', REPORTS_PLUS_MODULE, true);
	        ConfigOptions::addOption('reports_leader_board_period', REPORTS_PLUS_MODULE, array('key' => 0));
			ConfigOptions::addOption ( 'companies_in_ranking_game', REPORTS_PLUS_MODULE, array() );
			ConfigOptions::addOption ( 'reports_snapshot_taken_on', REPORTS_PLUS_MODULE, '' );

		} catch ( Exception $e ) {
			//throw $e;	
		}
		
		parent::install ();
		
		// Create initial snapshot now
		require_once CUSTOM_PATH . "/modules/reports_plus/models/ReportsPlus.class.php";
		// Turn off debugging while we take initial snapshot to save memory
		$appmode = APPLICATION_MODE;
		define ( APPLICATION_MODE, AngieApplication::IN_PRODUCTION );
		ReportsPlus::createMissingActivityLogItems ();
		ReportsPlus::dailyHandler ();
		define ( APPLICATION_MODE, $appmode );
		
		return true;
	} // install

		/**
     * Enable this module
     */

	function enable() {
		parent::enable ();
	}

	/**
     * Disable this module
     */
    function disable() {
    	
    	//Time Reports Plus
		$homescreen_tab_table = TABLE_PREFIX . 'homescreen_tabs';
		if (DB::tableExists ( $homescreen_tab_table )) {
			DB::execute ( "DELETE FROM $homescreen_tab_table WHERE type IN ('TimeReportsPlusHomescreenTab') " );
		} //if

		parent::disable();
    } // disable

	/**
	 * Uninstall this module
	 *
	 * @param void
	 * @return boolean
	 */
	function uninstall() {
		DB::execute ( "DROP TABLE IF EXISTS `" . TABLE_PREFIX . "rep_tickets_snapshot`" );
		DB::execute ( "DROP TABLE IF EXISTS `" . TABLE_PREFIX . "rep_finance_summary`" );
		
		//Time Reports Plus
		$homescreen_tab_table = TABLE_PREFIX . 'homescreen_tabs';
		if (DB::tableExists ( $homescreen_tab_table )) {
			DB::execute ( "DELETE FROM $homescreen_tab_table WHERE type IN ('TimeReportsPlusHomescreenTab') " );
		} //if

		return parent::uninstall ();
	} // uninstall
	

	/**
	 * Get module display name
	 *
	 * @return string
	 */
	function getDisplayName() {
		return lang ( 'Reports Plus' );
	} // getDisplayName
	

	/**
	 * Return module description
	 *
	 * @param void
	 * @return string
	 */
	function getDescription() {
		return lang ( 'Useful, actionable and extensive reports about projects, people, companies, finance and system usage. Go to Administration > Reports for configuration. Also, generate custom time reports with summarized by different parameters.' );
	} // getDescription
	

	/**
	 * Return module uninstallation message
	 *
	 * @param void
	 * @return string
	 */
	function getUninstallMessage() {
		return lang ( 'Module will be deactivated. All reporting data generated using it will be deleted.' );
	} // getUninstallMessage


}
