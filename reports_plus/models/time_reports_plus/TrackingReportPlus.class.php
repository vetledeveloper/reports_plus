<?php

// AngieApplication::useModel('TrackingReport', TRACKING_MODULE);


/**
 * TrackingReport class
 *
 * @package custom.modules.reports_plus
 * @subpackage models
 */
class TrackingReportPlus extends TrackingReport {
	
	// Group
	const GROUP_BY_DATE = 'date';
	const GROUP_BY_PROJECT = 'project';
	const GROUP_BY_USER = 'user';
	
	/**
	 * Run report
	 *
	 * @param User $user
	 * @return array
	 */
	function run(IUser $user, $filter_data = null, $order_by = null) {
		
		if ($user instanceof User) {
			// Handling for_ids
			$for_ids = '';
			$is_from_session = false;
			if (isset ( $_GET ['for_ids'] ) && ((isset ( $_GET ['project_id'] ) && $_GET ['for_ids'] != 'all') || (! isset ( $_GET ['project_id'] )))) {
				$for_ids = $_GET ['for_ids'];
				$_SESSION ['for_ids'] = $for_ids;
			} else if (isset ( $_SESSION ['for_ids'] )  )  {
			// } else if (isset($_SESSION ['for_ids']) && (isset($_GET['from_trp_invoice']) && $_GET['from_trp_invoice'] == 1) )  {
				$for_ids = $_SESSION ['for_ids'];
				$is_from_session = true;
			} else {
				//little hack: if for_ids not set in get or session then take all ids 
				$for_ids = (isset ( $_GET ['project_id'] ) && ! empty ( $_GET ['project_id'] )) ? 'all,project,' . $_GET ['project_id'] : 'all';
			}
			$time_record_ids = array();
			$conditions = array();
			if (strlen ( $for_ids ) > 0 && ! isset ( $order_by )) {
				$time_record_ids = explode ( ',', $for_ids );
				$conditions  = array('time_record_ids' => $time_record_ids );
				return $this->runList ( $user, $conditions );
			} else if(isset($filter_data ) && isset ( $order_by )) {
				$conditions = array('conditions' => $this->prepareConditions($user),
									'order_by'=> $order_by
									);
				if ($conditions !== false) {
					$time_records_table = TABLE_PREFIX . 'time_records';
					$this->fixReportConditions ( $conditions ['conditions'], $time_records_table, $filter_data );
					return $this->runList ( $user, $conditions );
				} else {
					return null;
				} // if
			}
		} else {
			throw new InvalidParamError ( 'user', $user, 'User' );
		} // if
	} // run
	

	function runList($user, $conditions) {
		
		$time_records_table = TABLE_PREFIX . 'time_records';
		$project_objects_table = TABLE_PREFIX . 'project_objects';
		$project_table = TABLE_PREFIX . 'projects';
		
		$time_records = array();
		
		// fetch time records to generate the invoice
		if (isset ($conditions ['time_record_ids']) && is_foreachable($conditions ['time_record_ids'] )) {
			$result = parent::run ( $user );
			if (is_foreachable($result) && isset ($result[0] ['records'] ) && is_foreachable ( $result [0] ['records'] )) {
				if (! in_array ( 'all', $conditions ['time_record_ids'] )) {
					foreach ( $result[0]['records'] as $key => $row ) {
						if (! in_array ( $row ['id'], $conditions ['time_record_ids'] )) {
							unset ( $result [0] ['records'] [$key] );
						}
					}
				} elseif (in_array ( 'project', $conditions ['time_record_ids'] )) {
					foreach ( $result [0] ['records'] as $key => $row ) {
						if (($row ['project_id'] != $conditions ['time_record_ids'] [2])) {
							unset ( $result [0] ['records'] [$key] );
						}
					}
				
				}
				
				$time_records = $result;
			}
		} else if (! is_null ( $conditions ['conditions'] ) && strlen ( trim ( $conditions ['conditions'] ) ) > 0) {
			if ($this->getJobTypeFilter () == TrackingReport::JOB_TYPE_FILTER_SELECTED) {
				if (is_foreachable ( $this->getJobTypeIds () )) {
					$conditions ['conditions'] .= DB::prepare ( ' AND tr.job_type_id IN ( ? ) ', $this->getJobTypeIds() );
				} else {
					$conditions ['conditions'] .= ' AND 0 ';
				}
			}
			$query = "SELECT tr.id as id,
				    'TimeRecord' as type,
					CASE WHEN tr.parent_type = 'Project' THEN 0 ELSE tr.parent_id END AS parent_id,
					tr.parent_type as parent_type,
					tr.summary as summary,
					tr.job_type_id as group_id,
					tr.job_type_id as job_type_id,
					tr.user_id as integer_field_1,
					IFNULL(tr.billable_status, 0) as integer_field_2,
					tr.record_date as date_field_1,
					tr.user_name as user_name,
					tr.user_email as email,
					tr.value as hours,
					IFNULL(po1.name, 'Unknown Milestone') as milestone_name,
					CASE WHEN tr.billable_status >= " . BILLABLE_STATUS_BILLABLE . " THEN 'Yes' ELSE 'No' END as billable,
					CASE WHEN tr.billable_status >= " . BILLABLE_STATUS_PAID . " THEN 'Yes' ELSE 'No' END as billed,
					tr.billable_status as billed_status,
					CASE WHEN tr.parent_type = 'Task' THEN tr.parent_id ELSE 0 END AS tcp_id,
					CASE WHEN tr.parent_type = 'Task' THEN po.name ELSE 'Others' END AS tcp_name,
					CASE WHEN tr.parent_type = 'Project' THEN tr.parent_id ELSE po.project_id END AS project_id,
					CASE WHEN tr.parent_type = 'Task' THEN IFNULL(po.category_id, 0) ELSE 0 END AS task_category_id,
					CASE WHEN tr.parent_type IN ('Task','Project') THEN IFNULL(p.category_id, 0) ELSE 0 END AS project_category_id, 
					CASE WHEN tr.parent_type = 'Project' THEN 0 ELSE IFNULL(po.milestone_id, 0) END AS milestone_id,
					CASE WHEN tr.parent_type = 'Task' THEN 'Task' ELSE '' END AS tcp_type,
					CASE WHEN tr.parent_type = 'Task' THEN po.integer_field_1 ELSE 0 END as parent_local_id,
					tr.billable_status as billable_status,
					p.slug as project_slug,
					p.company_id as company_id
				 FROM $time_records_table tr 
				 LEFT JOIN $project_objects_table po ON tr.parent_id = po.id AND tr.parent_type = 'Task'
				 LEFT JOIN $project_objects_table po1 ON po.milestone_id = po1.id
				 LEFT JOIN $project_table p ON ((tr.parent_type = 'Project' AND tr.parent_id = p.id) OR (tr.parent_type != 'Project' AND po.project_id = p.id)) 
				 WHERE $conditions[conditions] ";
			
			if ($conditions ['order_by'] != '') {
				$query .= " ORDER BY $conditions[order_by] ";
			}
			
			$rows = DB::execute ( $query );
			if ($rows) {
				$rows->setCasting ( array ('id' => DBResult::CAST_INT, 'parent_id' => DBResult::CAST_INT, 'project' => DBResult::CAST_INT, 'group_id' => DBResult::CAST_INT, 'user_id' => DBResult::CAST_INT, 'job_type_id' => DBResult::CAST_INT, 'company_id' => DBResult::CAST_INT, 'date_field_1' => DBResult::CAST_DATE, 'billable_status' => DBResult::CAST_INT, 'hours' => DBResult::CAST_FLOAT ) );
				
				$time_records = $rows->toArray ();
			}
		}
		
		return $time_records;
	
	}
	
	/**
	 * Return array or property => value pairs that describes this object
	 *
	 * $user is an instance of user who requested description - it's used to get
	 * only the data this user can see
	 *
	 * @param IUser $user
	 * @param boolean $detailed
	 * @param boolean $for_interface
	 * @return array
	 */
	function describe(IUser $user, $detailed = false, $for_interface = false) {
		$result = parent::describe ( $user, $detailed, $for_interface );
		
		$result ['sum_by_1'] = $this->getAdditionalProperty ( 'sum_by_1' );
		$result ['sum_by_2'] = $this->getAdditionalProperty ( 'sum_by_2' );
		$result ['sum_by_3'] = $this->getAdditionalProperty ( 'sum_by_3' );
		$result ['show_time_records'] = $this->getAdditionalProperty ( 'show_time_records' );
		
		return $result;
	} // describe
	

	// ---------------------------------------------------
	//  Getters, setters and attributes
	// ---------------------------------------------------
	

	/**
	 * Set attributes
	 *
	 * @param array $attributes
	 */
	function setAttributes($attributes) {
		parent::setAttributes ( $attributes );
		
		if (isset ( $attributes ['sum_by_1'] )) {
			$this->setAdditionalProperty ( 'sum_by_1', $attributes ['sum_by_1'] );
		} // if
		

		if (isset ( $attributes ['sum_by_2'] )) {
			$this->setAdditionalProperty ( 'sum_by_2', $attributes ['sum_by_2'] );
		} // if
		

		if (isset ( $attributes ['sum_by_3'] )) {
			$this->setAdditionalProperty ( 'sum_by_3', $attributes ['sum_by_3'] );
		} // if
		

		if (isset ( $attributes ['show_time_records'] )) {
			$this->setAdditionalProperty ( 'show_time_records', $attributes ['show_time_records'] );
		} // if
	

	} // setAttributes
	

	/**
	 * Return report paramters, used for GET in URL-s
	 *
	 * @return array
	 */
	protected function getReportParams() {
		$result = array ('report[user_filter]' => $this->getUserFilter (), 'report[date_filter]' => $this->getDateFilter (), 'report[project_filter]' => $this->getProjectFilter (), 'report[billable_status_filter]' => $this->getBillableStatusFilter (), 'report[type_filter]' => $this->getTypeFilter (), 'report[sum_by_user]' => $this->getSumByUser (), 'report[group_by]' => $this->getGroupBy () );
		
		// User filter
		switch ($this->getUserFilter ()) {
			case TrackingReport::USER_FILTER_COMPANY :
				$result ['report[company_id]'] = ( integer ) $this->getUserFilterCompanyId ();
				break;
			
			case TrackingReport::USER_FILTER_SELECTED :
				$result ['report[user_ids]'] = $this->getUserFilterSelectedUsers ();
				break;
		} // switch
		

		// Date filter
		switch ($this->getDateFilter ()) {
			case Trackingreport::DATE_FILTER_SELECTED_DATE :
				$result ['report[date_on]'] = $this->getDateFilterSelectedDate () instanceof DateValue ? $this->getDateFilterSelectedDate ()
					->toMySql () : null;
				break;
			
			case Trackingreport::DATE_FILTER_SELECTED_RANGE :
				list ( $date_from, $date_to ) = $this->getDateFilterSelectedRange ();
				
				$result ['report[date_from]'] = $date_from instanceof DateValue ? $date_from->toMySQL () : null;
				$result ['report[date_to]'] = $date_to instanceof DateValue ? $date_to->toMySQL () : null;
				
				break;
		} // switch
		

		// Project filter
		switch ($this->getProjectFilter ()) {
			case TrackingReport::PROJECT_FILTER_CATEGORY :
				$result ['report[project_category_id]'] = $this->getProjectCategoryId ();
				break;
			case TrackingReport::PROJECT_FILTER_CLIENT :
				$result ['report[project_client_id]'] = $this->getProjectClientId ();
				break;
			case TrackingReport::PROJECT_FILTER_SELECTED :
				$result ['report[project_ids]'] = $this->getProjectIds ();
				break;
		} // switch
		

		return $result;
	} // getReportParams
	

	function fixReportConditions(&$conditions, &$time_records_table, &$report_filter_data) {
		
		$time_records_table = TABLE_PREFIX . 'time_records';
		//state >= '2' AND ((parent_type = 'Project' AND parent_id IN ('5', '6', '7', '8', '42', '43')) OR (parent_type = 'Task' AND parent_id IN ('4', '5', '6', '7', '9', '13', '14', '15'))) AND user_id IN (1) AND (record_date >= '2012-04-30 00:00:00' AND record_date <= '2012-05-06 23:59:59')
		

		$conditions = ' ' . $conditions;
		
		// Search "state" string
		$search_subject = ' state ';
		$search_pos = strpos ( $conditions, $search_subject );
		if ($search_pos !== false) {
			$conditions = substr_replace ( $conditions, " $time_records_table.state ", strpos ( $conditions, $search_subject ), strlen ( $search_subject ) );
		}
		
		// parent_type 
		$search_subject = ' parent_type ';
		$search_pos = strpos ( $conditions, $search_subject );
		if ($search_pos !== false) {
			$conditions = substr_replace ( $conditions, " $time_records_table.parent_type ", strpos ( $conditions, $search_subject ), strlen ( $search_subject ) );
		}
		
		//Parent_id
		$search_subject = ' parent_id ';
		$search_pos = strpos ( $conditions, $search_subject );
		if ($search_pos !== false) {
			$conditions = substr_replace ( $conditions, " $time_records_table.parent_id ", strpos ( $conditions, $search_subject ), strlen ( $search_subject ) );
		}
		
		$search_subject = ' user_id ';
		$search_pos = strpos ( $conditions, $search_subject );
		if ($search_pos !== false) {
			$conditions = substr_replace ( $conditions, " $time_records_table.user_id ", strpos ( $conditions, $search_subject ), strlen ( $search_subject ) );
		}
		
		$search_subject = ' record_date ';
		$search_pos = strpos ( $conditions, $search_subject );
		if ($search_pos !== false) {
			$conditions = substr_replace ( $conditions, " $time_records_table.record_date ", strpos ( $conditions, $search_subject ), strlen ( $search_subject ) );
		}
		
		/*
		$search_subject = ' type =';
		$search_pos = strpos($conditions, $search_subject);
		if ($search_pos !== false) {
			$conditions = substr_replace($conditions, " $project_objects_table.type =", strpos($conditions, $search_subject), strlen($search_subject));
		}
		$search_subject = ' project_id =';
		$search_pos = strpos($conditions, $search_subject);
		if ($search_pos !== false) {
			$conditions = substr_replace($conditions, " $project_objects_table.project_id =", strpos($conditions, $search_subject), strlen($search_subject));	
		}
		
		if (isset($report_filter_data['company_id']) && $report_filter_data['company_id'] > 0) {
			$conditions .= DB::prepareConditions(' AND p.company_id = ? ', $report_filter_data['company_id']);
		}
		*/
		
		$conditions = str_replace ( $time_records_table . '.', 'tr.', $conditions );
	}
	
	function getEditUrl() {
		return Router::assemble ( 'time_reports_plus_edit', array ('tracking_report_id' => $this->getId () ) );
	}
	
	/**
	 * Return ID name map of Tracking Reports
	 * 
	 * 
	 * @return array
	 */
	static function getIdNameMap() {
		
		if (IS_GRETER_THAN_33 == true) {
			$rows = DB::execute ( "SELECT id, name FROM " . TABLE_PREFIX . 'data_filters' . " WHERE type = 'TrackingReport' " );
		} else {
			$rows = DB::execute ( 'SELECT id, name FROM ' . TABLE_PREFIX . 'data_filters' );
		}
		
		if ($rows) {
			$result = array ();
			
			foreach ( $rows as $row ) {
				$result [( integer ) $row ['id']] = $row ['name'];
			} // foreach
			

			return $result;
		} else {
			return null;
		} // if
	} // getIdNameMap
	

	public static function getVisibleProjectIds($user) {
		
		$projects = Projects::findByUser ( $user );
		$visible_project_ids = array ();
		if ($projects instanceof DBResult) {
			foreach ( $projects as $project ) {
				if ($user->projects ()
					->getPermission ( 'tracking', $project ) > ProjectRole::PERMISSION_NONE) {
					$visible_project_ids [] = $project->getId ();
				}
			}
		}
		
		return $visible_project_ids;
	} //getVisibleProjectIds
	

	public static function getIdEstimateMap($task_id_for_estimate) {
		$sql = DB::prepare ( "SELECT parent_id, value FROM " . TABLE_PREFIX . "estimates WHERE  parent_id IN (?)", $task_id_for_estimate);
		$results = DB::execute ( $sql );
		if ($results instanceof DBResult) {
			foreach ( $results as $result ) {
				$task_id_estimate_map [( integer ) $result ['parent_id']] = $result ['value'];
			}
			return $task_id_estimate_map;
		} else {
			return null;
		}
	
	}
}
