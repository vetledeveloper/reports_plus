<?php

/**
 * TimeReportsPlus class
 *
 * @package custom.modules.time_reports_plus
 * @subpackage models
 */
//class TimeReportsPlus extends TrackingReports {
class TimeReportsPlus extends DataManager {
	
	static function getInstanceClassName() {
      return 'TrackingReportPlus';
    } // getInstanceClassName
    
    static function getModelName($underscore = false) {
      return $underscore ? 'tracking_report_plus' : 'TrackingReportPlus';
    } // getModelName
    
    /**
     * Return name of the table where system will persist model instances
     *
     * @param boolean $with_prefix
     * @return string
     */
    static function getTableName($with_prefix = true) {
      return $with_prefix ? TABLE_PREFIX . 'data_filters' : 'data_filters';
    } // getTableName

     static function getInstanceClassNameFrom() {
      return DataManager::CLASS_NAME_FROM_TABLE;
    } // getInstanceClassNameFrom

     /**
     * Return name of the field from which we will read instance class
     *
     * @return string
     */
    static function getInstanceClassNameFromField() {
      return '';
    } // getInstanceClassNameFrom

    /**
     * Return name of this model
     *
     * @return string
     */
    static function getDefaultOrderBy() {
      return '';
    } // getDefaultOrderBy


	/**
	 * Do a SELECT query over database with specified arguments
	 * 
	 * This static function can return single instance or array of instances that match 
	 * requirements provided in $arguments associative array
	 *
	 * @param array $arguments Array of query arguments. Fields:
	 * 
	 * - one        - select first row
	 * - conditions - additional conditions
	 * - order      - order by string
	 * - offset     - limit offset, valid only if limit is present
	 * - limit      - number of rows that need to be returned
	 * 
	 * @return mixed
	 * @throws DBQueryError
	 */
	// static function find($arguments = null) {
	// 	if ($arguments === null) {
	// 		$arguments = array ('order' => 'name' );
	// 	} else {
	// 		if (! isset ( $arguments ['order'] )) {
	// 			$arguments ['order'] = 'name';
	// 		} // if
	// 	} // if      
	// 	//return DataManager::find($arguments, TABLE_PREFIX . 'tracking_reports', DataManager::CLASS_NAME_FROM_TABLE, 'TrackingReportPlus', '');
	// 	return parent::find ( $arguments );
	// }
	
	/**
	 * Return filters of given type that $user can see
	 *
	 * @param string $type
	 * @param User $user
	 * @return DataFilter[]
	 */
	static function findByUser($type, User $user) {
		return parent::find ( array ('conditions' => array ('type = ? AND (is_private = ? OR (created_by_id = ? AND is_private = ?))', $type, false, $user->getId (), true ), 'order' => 'name'));
	} // findByUser
	

	// find
	

	/**
	 * Return array of objects that match specific SQL
	 *
	 * @param string $sql
	 * @param array $arguments
	 * @param boolean $one
	 * @return mixed
	 */
	// static function findBySQL($sql, $arguments = null, $one = false) {
	// 	return DataManager::findBySQL ( $sql, $arguments, $one, TABLE_PREFIX . 'data_filters', DataManager::CLASS_NAME_FROM_TABLE, 'TrackingReportPlus', '' );
	// }
	
	// findBySQL
	

	/**
	 * Return object by ID
	 *
	 * @param mixed $id
	 * @return TrackingReport
	 */
	// static function findById($id) {
	// 	return DataManager::findById ( $id, TABLE_PREFIX . 'data_filters', DataManager::CLASS_NAME_FROM_TABLE, 'TrackingReportPlus', '' );
	// }
	
	// findById
	

	/**
	 * Return paginated result
	 * 
	 * This static function will return paginated result as array. First element of 
	 * returned array is array of items that match the request. Second parameter 
	 * is Pager class instance that holds pagination data (total pages, current 
	 * and next page and so on)
	 *
	 * @param array $arguments
	 * @param integer $page
	 * @param integer $per_page
	 * @return array
	 * @throws DBQueryError
	 */
	// static function paginate($arguments = null, $page = 1, $per_page = 10) {
	// 	return DataManager::paginate ( $arguments, $page, $per_page, TABLE_PREFIX . 'data_filters', DataManager::CLASS_NAME_FROM_TABLE, 'TrackingReportPlus', '' );
	// }
	
	// paginate
	

	static function getFilterDataMap() {
		
		$filter_map ['job_type_filter'] ['any'] = lang ( 'Any' );
		$filter_map ['job_type_filter'] ['selected'] = lang ( 'Selected Types ...' );
		
		$filter_map ['user_filter'] ['anybody'] = lang ( 'Anybody' );
		$filter_map ['user_filter'] ['logged_user'] = lang ( 'Person Accessing This Report' );
		$filter_map ['user_filter'] ['company'] = lang ( 'Member of a Company ...' );
		$filter_map ['user_filter'] ['selected'] = lang ( 'Selected Users ...' );
		
		$filter_map ['date_filter'] ['any'] = lang ( 'Any Day' );
		$filter_map ['date_filter'] ['last_month'] = lang ( 'Last Month' );
		$filter_map ['date_filter'] ['last_week'] = lang ( 'Last Week' );
		$filter_map ['date_filter'] ['yesterday'] = lang ( 'Yesterday' );
		$filter_map ['date_filter'] ['today'] = lang ( 'Today' );
		$filter_map ['date_filter'] ['this_week'] = lang ( 'Week' );
		$filter_map ['date_filter'] ['this_month'] = lang ( 'This Month' );
		$filter_map ['date_filter'] ['selected_date'] = lang ( 'Selected Date ...' );
		$filter_map ['date_filter'] ['selected_range'] = lang ( 'Selected Date Range ...' );
		
		$filter_map ['project_filter'] ['any'] = lang ( 'Any Project' );
		$filter_map ['project_filter'] ['active'] = lang ( 'Active Projects' );
		$filter_map ['project_filter'] ['completed'] = lang ( 'Completed Projects' );
		$filter_map ['project_filter'] ['category'] = lang ( 'From Category ...' );
		$filter_map ['project_filter'] ['client'] = lang ( 'For Client ..' );
		$filter_map ['project_filter'] ['selected'] = lang ( 'Selected Projects ...' );
		
		$filter_map ['billable_status_filter'] ['all'] = lang ( 'Any' );
		$filter_map ['billable_status_filter'] ['not_billable'] = lang ( 'Non-Billable' );
		$filter_map ['billable_status_filter'] ['billable'] = lang ( 'Billable' );
		$filter_map ['billable_status_filter'] ['pending_payment'] = lang ( 'Pending Payment' );
		$filter_map ['billable_status_filter'] ['billable_not_paid'] = lang ( 'Not Yet Paid (Billable or Pending Payment)' );
		$filter_map ['billable_status_filter'] ['billable_paid'] = lang ( 'Already Paid' );
		
		$filter_map ['sum_by_1'] ['all'] = lang ( "Don't Group" );
		$filter_map ['sum_by_1'] ['date_field_1'] = lang ( 'by Date' );
		$filter_map ['sum_by_1'] ['project_id'] = lang ( 'by Project' );
		$filter_map ['sum_by_1'] ['milestone_id'] = lang ( 'by Milestone' );
		$filter_map ['sum_by_1'] ['integer_field_2'] = lang ( 'by Billing Status' );
		$filter_map ['sum_by_1'] ['task_category_id'] = lang ( 'by Task Category' );
		$filter_map ['sum_by_1'] ['project_category_id'] = lang ( 'by Project Category' );
		$filter_map ['sum_by_1'] ['integer_field_1'] = lang ( 'by User' );
		$filter_map ['sum_by_1'] ['company_id'] = lang ( 'by Client' );
		$filter_map ['sum_by_1'] ['tcp_id'] = lang ( 'by Task' );
		$filter_map ['sum_by_1'] ['job_type_id'] = lang ( 'by job Type' );
		
		return $filter_map;
	}
	
	/**
	 * Returns true if $user can create new tracking reports
	 *
	 * @param User $user
	 * @return boolean
	 */
	static function canAdd(User $user) {
		return $user->isProjectManager ();
	} // canAdd

	static function getCompanyIdsByProjectIds($project_ids) {
		
		$company_ids = array();
		if(is_foreachable($project_ids)) {
			$company_ids = DB::executeFirstColumn ("SELECT DISTINCT company_id FROM " . TABLE_PREFIX . "projects where id IN (?)", $project_ids);
		}

		return $company_ids;
	}
	
	static function getCategoryIdsByProjectIds($project_ids) {
		
		$category_ids = array();
		if(is_foreachable($project_ids)) {
			$category_ids = DB::executeFirstColumn ("SELECT DISTINCT category_id FROM " . TABLE_PREFIX . "projects where id IN (?)", $project_ids);
		}

		return $category_ids;
	}

}