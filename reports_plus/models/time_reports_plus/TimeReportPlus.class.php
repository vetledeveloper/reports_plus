<?php

/**
 * TimeReportPlus class
 *
 * @package custom.modules.time_reports_plus
 * @subpackage models
 */
class TimeReportPlus extends TrackingReport {
	
	/**
	 * Run report
	 *
	 * @param User $user
	 * @return array
	 */
	// function run(IUser $user, $additional = null)
	function run(User $user) {
		if ($user instanceof User) {
			$conditions = $this->prepareConditions ( $user );
			return $conditions;
		
		//$list = $this->runList($user, $conditions);
		//return $list;
		}
	}
	
	function runList($user, $conditions) {
		$time_records_table = TABLE_PREFIX . 'time_records';
		$expenses_table = TABLE_PREFIX . 'expenses';
		$project_objects_table = TABLE_PREFIX . 'project_objects';
		$project_table = TABLE_PREFIX . 'projects';
		
		$query = "SELECT tr.id as id,
			      'TimeRecord' as type,
				  CASE WHEN tr.parent_type = 'Project' THEN 0 ELSE tr.parent_id END AS parent_id,
				  tr.parent_type as parent_type,
				  tr.summary as summary,
				  tr.job_type_id as group_id,
				  tr.user_id as integer_field_1,
				  tr.record_date as date_field_1,
				  tr.user_name as user_name,
				  tr.user_email as email,
				  tr.value as hours,
				  CASE WHEN tr.parent_type = 'Task' THEN tr.parent_id ELSE 0 END AS tcp_id,
				  CASE WHEN tr.parent_type = 'Task' THEN po.name ELSE '' END AS tcp_name,
				  CASE WHEN tr.parent_type = 'Project' THEN tr.parent_id ELSE po.project_id END AS project_id,
				  CASE WHEN tr.parent_type = 'Project' THEN 0 ELSE po.milestone_id END AS milestone_id,
				  CASE WHEN tr.parent_type = 'Task' THEN 'Task' ELSE '' END AS tcp_type,
				  tr.billable_status as billable_status
			      FROM $time_records_table tr LEFT JOIN $project_objects_table po ON tr.parent_id = po.id AND tr.parent_type = 'Task' 
			      ORDER BY project_id, tr.user_id, tr.record_date";
		
		$rows = DB::execute ( $query );
		
		$time_records = array ();
		if ($rows) {
			$rows->setCasting ( array ('id' => DBResult::CAST_INT, 'parent_id' => DBResult::CAST_INT, 'project' => DBResult::CAST_INT, 'group_id' => DBResult::CAST_INT, 'user_id' => DBResult::CAST_INT, 'date_field_1' => DBResult::CAST_DATE, 'billable_status' => DBResult::CAST_INT, 'hours' => DBResult::CAST_FLOAT ) );
			
			$time_records = $rows->toArray ();
		}
		
		return $time_records;
	
	} //class 
	

	function fixReportConditions(&$conditions, &$project_objects_table, &$report_filter_data) {
		$conditions = ' ' . $conditions;
		$search_subject = ' type =';
		$search_pos = strpos ( $conditions, $search_subject );
		if ($search_pos !== false) {
			$conditions = substr_replace ( $conditions, " $project_objects_table.type =", strpos ( $conditions, $search_subject ), strlen ( $search_subject ) );
		}
		$search_subject = ' project_id =';
		$search_pos = strpos ( $conditions, $search_subject );
		if ($search_pos !== false) {
			$conditions = substr_replace ( $conditions, " $project_objects_table.project_id =", strpos ( $conditions, $search_subject ), strlen ( $search_subject ) );
		}
		
		// Add the client condition - we should move this to a new model class later on, extending TimeReport class
		if ($report_filter_data ['company_id'] > 0) {
			$conditions .= db_prepare_string ( ' AND p.company_id = ? ', array ($report_filter_data ['company_id'] ) );
		}
		
		$conditions = str_replace ( $project_objects_table . '.', 'po1.', $conditions );
	}
	
	/**
	 * Return run report URL
	 *
	 * @return string
	 */
	function getViewUrl() {
		if ($this->isLoaded ()) {
			$params = array ('tracking_report_id' => $this->getId () );
		} else {
			$params = $this->getReportParams ();
		} // if

		return Router::assemble ( 'tracking_reports', $params );
	} // getViewUrl


}