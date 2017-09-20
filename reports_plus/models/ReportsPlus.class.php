<?php

/**
 * Reportslus class
 *
 * @package custom.modules.reports_plus
 * @subpackage models
 */
class ReportsPlus {

    const startTimeSuffix = ' 00:00:00';
    const endTimeSuffix = ' 23:59:59';

    /**
     * Get a list of projects this user has access to.
     *
     * @param User $user
     * @return array
     */
    function getProjects($user = null) {
        $projects = $project_ids = array();
        if (is_a($user, 'User')) {
            try {
                // Find all Template Projects
                $templateCategory = ConfigOptions::getValue("project_templates_category");
                $templateProjectIds = array();
                if ($templateCategory) {
                    $templateProjects = Projects::find(array('conditions' => array('category_id = ? ', $templateCategory), 'order_by' => 'created_on DESC'));

                    if (is_foreachable($templateProjects)) {
                        foreach ($templateProjects as $project) {
                            $templateProjectIds [] = $project->getId();
                        }
                    }
                }
            } catch (ConfigOptionDnxError $e) {
                
            }
            //fix for trashed project
            $matches = Projects::findByUser($user, true, 'state >= ' . STATE_VISIBLE);
            if (is_foreachable($matches)) {
                foreach ($matches as $project) {
                    // Discard Template Projects
                    if (in_array($project->getId(), $templateProjectIds)) {
                        continue;
                    }
                    $info ['label'] = $project->getName();
                    $info ['data'] = $project->getId();
                    $info ['status'] = strtolower($project->getVerboseStatus());
                    $info ['company_id'] = $project->getCompanyId();
                    $info ['group_id'] = $project->getCategoryId();
                    $info ['leader_id'] = $project->getLeaderId();
                    if ($info ['status'] == strtolower(lang('Active'))) {
                        $project_ids [] = $project->getId();
                        $info ['status'] = 'active';
                    } else {
                        $info ['status'] = 'completed';
                    }
                    $projects [] = $info;
                }
            }
            array_unshift($projects, array('label' => lang('All Projects'), 'data' => implode(",", $project_ids), 'category_id' => '0', 'company_id' => '0', 'status' => 'active'));
        }
        return $projects;
    }

    function parseFilterDate($date) {
        $final_date = trim($date);
        if (strpos($date, '/') > 0) {
            $parts = explode('/', trim($date));
            $date_format = ConfigOptions::getValue("reports_date_format");
            if (strlen($parts [2]) == 2)
                $parts [2] = '20' . $parts [2];
            if ($date_format == 'MM/DD/YY') {
                $final_date = $parts [2] . '-' . $parts [0] . '-' . $parts [1];
            } else {
                $final_date = $parts [2] . '-' . $parts [1] . '-' . $parts [0];
            }
        }
        return $final_date;
    }

    function parseFilterDates($date) {
        $final_date = trim($date);
        if (strpos($date, '/') > 0) {
            $parts = explode('/', trim($date));
            $date_format = ConfigOptions::getValue("reports_date_format");
            if (strlen($parts [2]) == 2)
                $parts [2] = '20' . $parts [2];
            $final_date = $parts [2] . '-' . $parts [1] . '-' . $parts [0];
        }
        return $final_date;
    }

    /**
     * Get localized labels for UI
     *
     * @return array
     */
    function getLabelsAndSettings(&$user) {
        $result = array();

        // Available reports
        $result ['settings'] ['tabs'] ['projects'] = 1;
        $result ['settings'] ['tabs'] ['people'] = 0;
        $result ['settings'] ['tabs'] ['companies'] = 0;
        if ($user->isAdministrator() == true || (boolean) $user->getSystemPermission('can_see_reports_people')) {
            $result ['settings'] ['tabs'] ['people'] = 1;
        }
        if ($user->isAdministrator() == true || (boolean) $user->getSystemPermission('can_see_reports_companies')) {
            $result ['settings'] ['tabs'] ['companies'] = 1;
        }

        // Time for some settings now
        // NOTE: This format is for Flex.
        $date_format = ConfigOptions::getValue("reports_date_format");
        $result ['settings'] ['date_format'] = $date_format;
        $result ['settings'] ['chart_date_format'] = ($date_format == 'MM/DD/YY') ? 'MM/DD' : 'DD/MM';

        // First get the dictionary
        $dict = include REPORTS_PLUS_MODULE_PATH . '/resources/dictionary.serverside.php';

        // Now get translation for each dictionary item and pass on
        if (is_foreachable($dict)) {
            foreach ($dict as $key) {
                $item ['label'] = lang($key);
                $item ['key'] = $key;
                $labels [] = $item;
            }
        }
        $result ['labels'] = $labels;

        $m = new ReportsPlusModule ();
        $result ['settings'] ['version'] = $m->getVersion();

        return $result;
    }

    /**
     * Add url key to the array based on object's type
     *
     * @param array $data
     */
    function addObjectURL(&$data) {
        $result = array();
        // Add object URL
        if (is_foreachable($data)) {
            foreach ($data as $key => $item) {
                $url = '';
                switch ($item ['type']) {
                    case 'Milestone' :
                        $url = Router::assemble('project_milestone', array('project_slug' => $item ['project_slug'], 'milestone_id' => $item ['id']));
                        break;
                    case 'Task' :
                        $url = Router::assemble('project_task', array('project_slug' => $item ['project_slug'], 'task_id' => ($item ['integer_field_1'] > 0) ? $item ['integer_field_1'] : $item ['id']));
                        break;
                    case 'TodoList' :
                        $url = Router::assemble('project_todo_list', array('project_slug' => $item ['project_slug'], 'todo_list_id' => $item ['id']));
                        break;
                    case 'Notebook' :
                        $url = Router::assemble('project_notebook', array('project_slug' => $item ['project_slug'], 'notebook_id' => $item ['id']));
                        break;
                    case 'Discussion' :
                        $url = Router::assemble('project_discussion', array('project_slug' => $item ['project_slug'], 'discussion_id' => $item ['id']));
                        break;
                    case 'File' :
                        $url = Router::assemble('project_assets_file', array('project_slug' => $item ['project_slug'], 'asset_id' => $item ['id']));
                        break;

                    // TODO: we need to add cases for SubTasks, Pages, TimeRecords, Comments etc.
                }
                $item ['url'] = $url;
                $result [$key] = $item;
            }
        }
        $data = $result;
    }

    /**
     * Get project stats
     *
     * @param string $projects
     * @param array $options
     * @param array $charts
     * @return array
     */
    function getProjectStats($projects = '', $options = null, $user = null) {
        $chart_type = '';
        $params = array();

        if ($options == null) {
            $options = array();
        }

        $iteration_days = ConfigOptions::getValue("reports_iteration_days");
        // If start / end date are not specified, use last 3 months period
        $start = DateValue::makeFromString(array_var($options, 'start_date', '-3 months'));
        $end = DateValue::makeFromString(array_var($options, 'end_date', 'now'));
        $params ['start'] = $start->toMySQL();
        $params ['end'] = $end->toMySQL();
        $before = $start->advance($iteration_days * - 86400, false);
        $params ['before_start'] = $before->toMySQL();
        $params ['visibility_condition'] = $options ['visibility_condition'];

        // Collect projects in one string
        if (strlen(trim($projects)) > 0) {
            $params ['projects'] = $projects;
        } else {
            $userProjects = Projects::findIdsByUser($user, true, TABLE_PREFIX . "projects.completed_on IS NULL");
            $params ['projects'] = implode(',', $userProjects);
        }
        // We may still not have any projects here - user does not have any projects assigned
        if (strlen(trim($params ['projects'])) == 0) {
            $params ['projects'] = '0';
        }

        // By default, show all charts
        $charts = array('primary', 'secondary');

        /*         * ************ Now get data for charts ***************** */
        $master_categories = ConfigOptions::getValue("reports_master_categories");
        $master_categories_count = count($master_categories);

        // Initialize result
        $result = array();
        $result ['params'] = $params;

        /**
         * Primary Charts
         */
        $chart_type = 'primary';
        if (in_array($chart_type, $charts)) {

            // Fields to sum
            $sum_fields = array('total', 'open', 'closed', 'created_in_iteration', 'closed_in_iteration', 'reopened_in_iteration', 'deleted_in_iteration', 'actual_hours', 'hours_in_iteration', 'others_opened', 'others_closed');

            // Append master category count fields
            for ($i = 0; $i < $master_categories_count; $i ++) {
                $sum_fields [] = 'cat' . $i . '_opened';
                $sum_fields [] = 'cat' . $i . '_closed';
            }

            // Sum estimates
            $sum_fields [] = 'estimated_hours';

            $query = 'SELECT date, iteration';
            foreach ($sum_fields as $field) {
                $query .= ", SUM($field) AS $field ";
            }
            $query .= 'FROM `' . TABLE_PREFIX . 'rep_tickets_snapshot`
					   WHERE date between ? and ?';
            if ($params ['projects'] != '') {
                $query .= ' AND project_id in (' . $params ['projects'] . ') ';
            }
            $query .= ' GROUP BY date ORDER BY date ASC';
            $data = DB::execute($query, $params ['before_start'], $params ['end']);
            // Process this data - subtract last iterations counts from this
            // so that we can get counts specific to this iteration
            $new_data = array();
            if (count($data) > 0) {
                $index = 0;

                // If we do not have any iterations before the start date (project started
                // in between the given period), we may use a dummy iteration as last iteration
                $last_iteration = $data [$index];
                if ($last_iteration ['date'] >= $params ['start']) {
                    $index = 0;
                    foreach ($last_iteration as $key => $value) {
                        $last_iteration [$key] = 0;
                    }
                } else {
                    // We don't store first row in final result - since it's one week
                    // before starting date. But we store its counts for subtraction later
                    $index ++;
                }
                // Loop and store the data after deductions
                for ($i = $index; $i < count($data); $i ++) {
                    $temp_row = $data [$i];
                    // Calculate velocity for the iteration
                    $temp_row ['velocity'] = $temp_row ['closed_in_iteration'] - $temp_row ['reopened_in_iteration'];
                    $new_data [] = $temp_row;
                    $last_iteration = $data [$i];
                }
            }
            $result [$chart_type] ['iterations'] = $new_data;

            // Last milestone scheduled date
            $query = 'SELECT due_on
					   FROM `' . TABLE_PREFIX . 'project_objects` po 
					   WHERE type = "Milestone" AND due_on IS NOT NULL AND state >= ? ' . $params ['visibility_condition'];
            if ($params ['projects'] != '') {
                $query .= ' AND project_id in (' . $params ['projects'] . ') ';
            }
            $query .= ' ORDER BY due_on DESC LIMIT 0,1';
            $data = DB::executeFirstRow($query, STATE_VISIBLE);
            $scheduled_date = $data ['due_on'];

            // Calculate forecast date
            // Logic: Calculate average productivity for last 3 iterations
            // and extrapolate it to find how many more iterations it will take
            // to complete.
            $num_iterations = count($new_data);
            $reports_num_weeks_for_average_velocity = ConfigOptions::getValue('reports_num_weeks_for_average_velocity');
            $iterations_to_average = ($num_iterations > $reports_num_weeks_for_average_velocity) ? $reports_num_weeks_for_average_velocity : $num_iterations;
            $average_velocity = 0;
            for ($i = $num_iterations - 1; $i >= $num_iterations - $iterations_to_average; $i --) {
                $average_velocity += ($new_data [$i] ['velocity'] - $new_data [$i] ['created_in_iteration'] + $new_data [$i] ['deleted_in_iteration']);
            }
            $average_velocity = round($average_velocity / $iterations_to_average, 2);
            $open_items = $last_iteration ['open'];
            $iterations_to_close = ($average_velocity <= 0) ? - 1 : $open_items / $average_velocity;
            if ($iterations_to_close < 0) {
                $result [$chart_type] ['forecast'] ['forecasted'] = - 1;
            } else {
                $forecast_date = DateValue::makeFromString($last_iteration ['date']);
                $forecast_date->advance($iteration_days * 86400 * $iterations_to_close);
                $result [$chart_type] ['forecast'] ['forecasted'] = $forecast_date->toMySQL();
            }
            $result [$chart_type] ['forecast'] ['scheduled'] = $scheduled_date;
            $result [$chart_type] ['forecast'] ['average_velocity'] = $average_velocity;
            //$result[$chart_type]['forecast']['iterations_to_close'] = $iterations_to_close;
            //$result[$chart_type]['forecast']['open_items'] = $open_items;
            //$result[$chart_type]['forecast']['last_iteration_date'] = $last_iteration['date'];
            // Include category names
            foreach ($master_categories as $i => $category) {
                $result [$chart_type] ['labels'] ['categories'] ['items'] [] = array('data' => $i, 'label' => clean($category));
            }
        }

        /**
         * Secondary Charts
         */
        $chart_type = 'secondary';
        if (in_array($chart_type, $charts)) {
            /**
             * Most Active and Open Tasks / Discussions / Files
             */
            $weight_comments = ConfigOptions::getValue('reports_formula_weight_most_active_comments');
            $weight_hours = ConfigOptions::getValue('reports_formula_weight_most_active_hours');
            $weight_version = str_replace(',', '.', ConfigOptions::getValue('reports_formula_weight_most_active_version'));

            $tracking_module_loaded = (AngieApplication::isModuleLoaded('tracking')) ? true : false;

            $query = 'SELECT *, ROUND(';
            $query .= ($tracking_module_loaded) ? ' (' . $weight_hours . ' * SUM(hours) ) + ' : '';
            $query .= '(' . $weight_comments . ' * SUM(comments) ) + (' . $weight_version . ' * version ) ) AS score,';
            $query .= ($tracking_module_loaded) ? ' SUM(hours) as hours,' : '';
            $query .= ' SUM(comments) as comments
							FROM (
							SELECT ';
            $query .= ($tracking_module_loaded) ? 'IFNULL(t.value, 0) AS hours,' : '';
            $query .= ' CASE WHEN po.type = "Discussion" THEN 0 WHEN c.id > 0 THEN 1 ELSE 0 END AS comments,
							po.version AS version,
							CASE WHEN po.completed_on IS NOT NULL THEN 1 ELSE 0 END as completed,
							po.name as name, 
							po.type as type, 
							po.id as id,
							po.project_id as project_id,
							p.slug as project_slug, 
							po.integer_field_1
						  	FROM ' . TABLE_PREFIX . 'project_objects po
						  	LEFT JOIN ' . TABLE_PREFIX . 'projects p ON po.project_id = p.id 
							LEFT JOIN ' . TABLE_PREFIX . 'comments c ON c.parent_type = po.type
							AND c.parent_id = po.id
							AND c.state >= ' . STATE_VISIBLE;
            $query .= ($tracking_module_loaded) ? ' LEFT JOIN ' . TABLE_PREFIX . 'time_records t ON t.parent_type = po.type AND t.parent_id = po.id AND t.state >= ' . STATE_VISIBLE : '';

            /* $query .= ' WHERE po.state >= '.STATE_VISIBLE.' '.$params['visibility_condition'].'
              AND po.type IN ("Discussion", "File", "Task", "TodoList", "Notebook") '; */

            $query .= ' WHERE po.state >= ' . STATE_VISIBLE . ' ' . $params ['visibility_condition'] . '
						  	AND po.type IN ("Discussion", "File", "Task", "TodoList") ';

            if ($params ['projects'] != '') {
                $query .= ' AND project_id in (' . $params ['projects'] . ') ';
            }
            $query .= ' AND po.project_id in (' . $params ['projects'] . ')
						  	AND po.completed_on IS NULL
						  	AND (po.created_on BETWEEN ? AND ? OR po.updated_on BETWEEN ? AND ?)
						  	) AS items  
						  GROUP BY id
						  ORDER BY score DESC
						  LIMIT 0,5';
            $data = DB::execute($query, $params ['start'] . self::startTimeSuffix, $params ['end'] . self::endTimeSuffix, $params ['start'] . self::startTimeSuffix, $params ['end'] . self::endTimeSuffix);

            $this->addObjectURL($data);
            $result [$chart_type] ['active'] = (is_foreachable($data) ? $data : null);

            /**
             * Longest Open Items
             */
            /*
              $query = 'SELECT
              p.slug as project_slug,
              po.name as name,
              po.id as id, project_id,
              po.type, po.integer_field_1,
              (TO_DAYS(NOW()) - TO_DAYS(po.created_on)) as days
              FROM ' . TABLE_PREFIX . 'project_objects po
              LEFT JOIN ' . TABLE_PREFIX . 'projects p ON po.project_id = p.id
              WHERE po.completed_on IS NULL
              AND po.state >= ' . STATE_VISIBLE . ' ' . $params ['visibility_condition'];
              if ($params ['projects'] != '') {
              $query .= ' AND po.project_id in (' . $params ['projects'] . ') ';
              }
              $query .= ' AND (po.created_on BETWEEN ? AND ? OR po.updated_on BETWEEN ? AND ?)';
              $query .= ' ORDER by po.created_on ASC LIMIT 0,5';
              $data = DB::execute ( $query, $params ['start'] . self::startTimeSuffix, $params ['end'] . self::endTimeSuffix, $params ['start'] . self::startTimeSuffix, $params ['end'] . self::endTimeSuffix );
             */

            /**
             * Longest Overdue_on
             */
            $query = 'SELECT
					  p.slug as project_slug,
					  po.name as name,
					  po.id as id, project_id,
					  po.type, po.integer_field_1,
					  (TO_DAYS(NOW()) - TO_DAYS(po.due_on)) as days
					  FROM ' . TABLE_PREFIX . 'project_objects po
					  LEFT JOIN ' . TABLE_PREFIX . 'projects p ON po.project_id = p.id
					  WHERE po.due_on IS NOT NULL
					  AND po.completed_on IS NULL
					  AND po.state >= ' . STATE_VISIBLE . ' ' . $params ['visibility_condition'];
            if ($params ['projects'] != '') {
                $query .= ' AND po.project_id in (' . $params ['projects'] . ') ';
            }
            $query .= ' AND (po.created_on BETWEEN ? AND ? )';
            $query .= ' ORDER by days DESC LIMIT 0,5';
            $query = DB::prepare($query, $params ['start'] . self::startTimeSuffix, $params ['end'] . self::endTimeSuffix);
            $data = DB::execute($query);
            $this->addObjectURL($data);
            $result [$chart_type] ['due_on'] = (is_foreachable($data) ? $data : null);

            /**
             * Tasks Progress
             */
            $query = 'SELECT COUNT( * ) AS no_tasks, IFNULL(label_id, 0) as label_id
					 FROM ' . TABLE_PREFIX . 'project_objects po
					 WHERE type = "Task"
					 AND po.state >= ' . STATE_VISIBLE . ' ' . $params ['visibility_condition'];
            if ($params ['projects'] != '') {
                $query .= ' AND po.project_id in (' . $params ['projects'] . ') ';
            }
            $query .= ' AND (po.created_on BETWEEN ? AND ? ) 
					 GROUP BY label_id';
            $query = DB::prepare($query, $params ['start'] . self::startTimeSuffix, $params ['end'] . self::endTimeSuffix);
            $data = DB::execute($query);
            $data = ($data instanceof DBResult && $data->count() > 0 ) ? $data->toArray() : null;
            $result [$chart_type] ['task_in_progress'] = $data;
        } // secondary
        // Get Summary and summarize it
        $summary = $this->getProjectSummary(explode(",", str_replace(" ", "", $params ['projects'])), '', null, $params);
        if ($summary ['projects'] > 1) {
            $summarized = array();
            $summarized ['hours'] = $summary ['project_hours'];
            $summarized ['estimated_hours'] = $summary ['project_estimated_hours'];
            $summarized ['percent_done'] = $summarized ['completed_tasks_count'] = $summarized ['open_tasks_count'] = $summarized ['running_for_days'] = $summarized ['total_tasks_count'] = 0;
            foreach ($summary ['project_details'] as $project_id => $item) {
                $summarized ['completed_tasks_count'] += $item ['completed_tasks_count'];
                $summarized ['open_tasks_count'] += $item ['open_tasks_count'];
                $summarized ['total_tasks_count'] += $item ['total_tasks_count'];
                $summarized ['running_for_days'] = max($item ['running_for_days'], $summarized ['running_for_days']);

                if (isset($item ['next_milestone_days']) && (!isset($summarized ['next_milestone_days']) || $item ['next_milestone_days'] < $summarized ['next_milestone_days'])) {
                    $summarized ['next_milestone_days'] = $item ['next_milestone_days'];
                    $summarized ['next_milestone'] = $item ['next_milestone'];
                    $summarized ['next_milestone_project_id'] = $project_id;
                    $summarized ['next_milestone_url'] = $item ['next_milestone_url'];
                    $summarized ['next_milestone_date'] = $item ['next_milestone_date'];
                }
            }
            if ($summarized ['total_tasks_count'] == 0 || $summarized ['total_tasks_count'] == $summarized ['completed_tasks_count']) {
                $summarized ['percent_done'] = 100;
            } else if ($summarized ['completed_tasks_count'] > 0) {
                $summarized ['percent_done'] = ceil($summarized ['completed_tasks_count'] / $summarized ['total_tasks_count'] * 100);
            }
            $result ['summary'] = $summarized;
        } else {
            $result ['summary'] = array_shift($summary ['project_details']);
        }

        return $result;
    }

// getProjectStats

    /**
     * Take snapshot of an iteration - tasks open, closed etc
     *
     * @param array $master_categories_map
     * @param array $params
     * @param int $iteration_days
     * @return int
     */
    function takeIterationSnapshot(&$master_categories_map, &$params, $iteration_days = 7) {

        if (!isset($params ['start']) || !isset($params ['end'])) {
            return 0;
        }

        if (intval($iteration_days) == 0) {
            $iteration_days = 7;
        }
        // Get active projects
        // Include projects which were completed in last iteration as well!
        // This iteration # works because we are always running it on start of week
        $query = "SELECT id, CEIL((TO_DAYS(?) - TO_DAYS(created_on))/" . $iteration_days . ") as iteration
				  FROM " . TABLE_PREFIX . "projects
				  WHERE 1 ";
        if (!isset($params ['historical']) || (isset($params ['historical']) && $params ['historical'] != 1)) {
            $query .= " AND (state >= '" . STATE_VISIBLE . "' OR completed_on BETWEEN '" . $params ['start'] . self::startTimeSuffix . "' AND '" . $params ['end'] . self::endTimeSuffix . "') ";
        }
        $query .= " AND created_on < ?
					ORDER BY id";
        $result = DB::execute($query, $params ['end'], $params ['end'] . self::endTimeSuffix);

        $final = array();
        if (count($result) > 0) {
            foreach ($result as $item) {
                $final [$item ['id']] ['iteration'] = $item ['iteration'];
            }
            $active_projects = array_keys($final);
            $projects_str = implode(', ', $active_projects);

            if (count($active_projects) == 0) {
                return 0;
            }

            // $completable_types = get_completable_project_object_types ();
            $completable_types = array('Task');
            //fix for project_id null
            // Get overall item stats for these projects - only those created before the given end date
            $query = "SELECT count(*) as count,
		              CASE WHEN (project_id IS NULL) THEN 0 ELSE project_id END as project_id,
					  CASE WHEN (po.completed_on IS NOT NULL) THEN 1 ELSE 0 END as completed
						FROM `" . TABLE_PREFIX . "project_objects` po 
						WHERE po.type IN (?)
						AND po.state >= ?
						AND po.created_on <= ?
						AND po.project_id IN (" . $projects_str . ")
						GROUP by project_id, completed
					  UNION ALL
					  SELECT count(*) as count, po.project_id as project_id, 
					  CASE WHEN (s.completed_on IS NOT NULL) THEN 1 ELSE 0 END as completed
						FROM `" . TABLE_PREFIX . "project_objects` po 
						LEFT JOIN `" . TABLE_PREFIX . "subtasks` s 
							ON s.parent_id = po.id 
							AND s.parent_type = po.type 
							AND s.state >= ?
							AND s.created_on <= ? 
						WHERE po.type IN (?)
						AND po.state >= ?
						AND po.created_on <= ?
						AND s.id IS NOT NULL
						AND po.project_id IN (" . $projects_str . ")
					  GROUP by project_id, completed
					  ORDER by project_id, completed";
            $query = DB::prepare($query, $completable_types, STATE_ARCHIVED, $params ['end'] . self::endTimeSuffix, STATE_ARCHIVED, $params ['end'] . self::endTimeSuffix, $completable_types, STATE_ARCHIVED, $params ['end'] . self::endTimeSuffix);
            $result = DB::execute($query);

            if (count($result) > 0) {
                foreach ($result as $item) {
                    if (!isset($final [$item ['project_id']] ['total']))
                        $final [$item ['project_id']] ['total'] = 0;
                    if (!isset($final [$item ['project_id']] ['closed']))
                        $final [$item ['project_id']] ['closed'] = 0;
                    if (!isset($final [$item ['project_id']] ['open']))
                        $final [$item ['project_id']] ['open'] = 0;

                    $final [$item ['project_id']] ['total'] += $item ['count'];
                    if ($item ['completed'] > 0) {
                        $final [$item ['project_id']] ['closed'] += $item ['count'];
                    } else {
                        $final [$item ['project_id']] ['open'] += $item ['count'];
                    }
                } // foreach
            } // if
            unset($result);

            // Get detailed, category wise stats from activity logs - items created, completed, deleted, reopened
            $query = "SELECT po.project_id as project_id, IFNULL(tc.name, '') as category, count(po.id) as count,
						CASE
							WHEN (al.action LIKE '%/created') THEN 'created'
							WHEN (al.action LIKE '%/completed') THEN 'closed'
							WHEN (al.action LIKE '%/reopened') THEN 'reopened'
							WHEN (al.action LIKE '%/deleted') THEN 'deleted'
							WHEN (al.action LIKE '%/trashed') THEN 'deleted'
							WHEN (al.action LIKE '%/restored') THEN 'restored'
							ELSE 'unknown'
							END AS activity_type
						FROM `" . TABLE_PREFIX . "project_objects` po
						LEFT JOIN `" . TABLE_PREFIX . "activity_logs` al
							ON (CASE 
								WHEN al.subject_type IN ('ProjectObjectSubtask') THEN al.target_id
								ELSE al.subject_id 
								END) = po.id
							AND al.subject_type IN ('ProjectObjectSubtask', 'Task', 'TodoList')
						LEFT JOIN `" . TABLE_PREFIX . "categories` tc 
							ON tc.type IN ('TaskCategory', 'TodoListCategory') 
							AND tc.parent_type = 'Project' AND tc.parent_id = po.project_id
							AND po.category_id = tc.id
						WHERE po.type IN (?)
						AND (al.created_on BETWEEN ? AND ?)
						AND po.project_id IN (" . $projects_str . ")
						GROUP BY po.project_id, tc.name, activity_type
						ORDER BY po.project_id, category, activity_type";
            $query = DB::prepare($query, $completable_types, $params ['start'] . self::startTimeSuffix, $params ['end'] . self::endTimeSuffix);
            $result = DB::execute($query);
            $master_categories_count = count($master_categories_map);
            if (count($result) > 0) {
                foreach ($result as $item) {
                    if (isset($item ['category']) && isset($master_categories_map [strtolower(trim($item ['category']))])) {
                        $category_key_part = $master_categories_map [strtolower(trim($item ['category']))];
                    } // All categories we don't want to explicitly count, go in "others"
                    else {
                        $category_key_part = 'others';
                    }

                    $slot = '';
                    $type = '';
                    switch ($item ['activity_type']) {
                        case 'closed' :
                            $slot = 'closed';
                            $type = 'closed_in_iteration';
                            break;
                        case 'reopened' :
                            $slot = 'opened';
                            $type = 'reopened_in_iteration';
                            break;
                        case 'created' :
                            $slot = 'opened';
                            $type = 'created_in_iteration';
                            break;
                        case 'deleted' :
                            $slot = 'closed';
                            $type = 'deleted_in_iteration';
                            break;
                        case 'restored' :
                            $slot = 'opened';
                            $type = 'reopened_in_iteration';
                            break;
                    }

                    if ($slot == '' || $type == '') {
                        continue;
                    }

                    if (!isset($final [$item ['project_id']] [$category_key_part . '_' . $slot])) {
                        $final [$item ['project_id']] [$category_key_part . '_' . $slot] = 0;
                    }
                    if (!isset($final [$item ['project_id']] [$type])) {
                        $final [$item ['project_id']] [$type] = 0;
                    }
                    $final [$item ['project_id']] [$category_key_part . '_' . $slot] += $item ['count'];
                    $final [$item ['project_id']] [$type] += $item ['count'];
                }
            } // if
            // Get estimated time for this project from start till now
            $activeModules = AngieApplication::getEnabledModuleNames();
            if (AngieApplication::isModuleLoaded('tracking')) {
                if (in_array('planning', $activeModules) || in_array('tasks', $activeModules)) {
                    $result = self::getProjectEstimates($active_projects, $params);
                    if (count($result) > 0) {
                        foreach ($result as $item) {
                            $final [$item ['project_id']] ['estimated_hours'] = $item ['estimated_hours'];
                        }
                    } // if
                }

                // Get actual time spent on these projects from start till now
                $result = self::getProjectActualHours($active_projects, array('start' => '1970-01-01', 'end' => $params ['end']));
                if (count($result) > 0) {
                    foreach ($result as $item) {
                        $final [$item ['project_id']] ['actual_hours'] = $item ['actual_hours'];
                    }
                } // if
                // Get actual time spent during this week
                $result = self::getProjectActualHours($active_projects, array('start' => $params ['start'], 'end' => $params ['end']));
                if (count($result) > 0) {
                    foreach ($result as $item) {
                        $final [$item ['project_id']] ['hours_in_iteration'] = $item ['actual_hours'];
                    }
                } // if
            }

            // Now insert these values in the table
            $fields = array('date', 'project_id', 'iteration', 'total', 'open', 'closed', 'created_in_iteration', 'closed_in_iteration', 'reopened_in_iteration', 'deleted_in_iteration', 'estimated_hours', 'actual_hours', 'hours_in_iteration', 'others_opened', 'others_closed');
            // Append master category count fields
            for ($i = 0; $i < $master_categories_count; $i ++) {
                $fields [] = 'cat' . $i . '_opened';
                $fields [] = 'cat' . $i . '_closed';
            }

            $query = 'REPLACE INTO `' . TABLE_PREFIX . 'rep_tickets_snapshot` (' . implode(', ', $fields) . ") VALUES \r\n";
            $rows = array();
            foreach ($final as $project_id => $data) {
                $values = array();
                $data ['date'] = $params ['start'];
                $data ['project_id'] = $project_id;
                foreach ($fields as $field) {
                    if (!isset($data [$field]) || $data [$field] == '') {
                        $data [$field] = 0;
                    }
                    $values [] = DB::escape($data [$field]);
                }
                $rows [] = ' (' . implode(', ', $values) . ')';
            }
            $query .= implode(",\r\n", $rows);
            $result = DB::execute($query);
            unset($rows);
            unset($fields);

            $updated_items = DB::affectedRows();

            return $updated_items;
        }
        return 0;
    }

    function getProjectActualHours($projects, $params) {
        // Get actual time spent on given projects
        $query = "SELECT CASE WHEN t.parent_type = 'Project' THEN t.parent_id ELSE po.project_id END as project_id,
					IFNULL(SUM(IFNULL(t.value, 0)),0) AS 'actual_hours'
					FROM " . TABLE_PREFIX . "time_records t
					LEFT OUTER JOIN `" . TABLE_PREFIX . "project_objects` po ON po.id = t.parent_id and t.parent_type = po.type AND po.state >= ?  
					WHERE ( (t.parent_type = 'Project' AND t.parent_id IN (?)) 
						 	OR
						  	(t.parent_type NOT IN ('Project') AND po.project_id IN (?) ) 
						  )
						AND t.state >= ? 
					AND t.record_date BETWEEN ? AND ?
					GROUP BY (CASE WHEN t.parent_type = 'Project' THEN t.parent_id ELSE po.project_id END) ASC";
        $result = DB::execute($query, STATE_ARCHIVED, $projects, $projects, STATE_ARCHIVED, $params ['start'], $params ['end']);
        return $result;
    }

    function getProjectEstimates(&$projects, $params) {
        $result = array();
        if (!is_array($projects) || count($projects) == 0) {
            return $result;
        }

        $query = 'SELECT project_id, sum(ifnull(ie.estimate, 0)) as estimated_hours
				FROM ' . TABLE_PREFIX . 'project_objects po 
				LEFT JOIN (SELECT parent_id as item_id, value as estimate FROM 
					(SELECT parent_id, value, created_on FROM ' . TABLE_PREFIX . 'estimates ORDER BY created_on DESC) as estimates
					GROUP BY parent_id) as ie 
					ON ie.item_id = po.id
				WHERE po.state >= ? AND po.project_id IN(?) ';
        if ($params ['end'] != '') {
            $query .= " AND po.created_on <= '{$params['end']}{self::endTimeSuffix}' ";
        }
        $query .= '	GROUP BY project_id';
        $result = DB::execute($query, STATE_ARCHIVED, $projects);

        return $result;
    }

    /**
     * Get iteration start and end dates from given date
     *
     * @param DateValue $today
     * @return array
     */
    function getIterationDates(&$today, $iteration_days = 7, $frequency = 'D') {
        // Set start, end to be last week start and end dates
        // now will be set to this week's first day
        if (!is_a($today, 'DateValue') || $today == null) {
            $today = DateValue::makeFromString('now');
        }
        // Go to the beginning of week based on user settings
        $today_wday = $today->getWeekday();
        $week_start = ConfigOptions::getValue("time_first_week_day");

        $adjust_days = 0;
        if ($today_wday < $week_start) {
            $adjust_days = - 1 * (7 - ($week_start - $today_wday));
        } else if ($today_wday > $week_start) {
            $adjust_days = - 1 * ($today_wday - $week_start);
        }
        //echo "Today: $today_wday, Week Start: $week_start, Adjustment is $adjust_days. Today: ".$today->toMySQL();
        if ($adjust_days != 0) {
            $today->advance($adjust_days * 86400);
        }

        switch ($frequency) {
            case 'W' :
                $iteration_end = $today->advance(- 86400, false);
                $iteration_start = $iteration_end->advance(($iteration_days - 1) * - 86400, false);
                break;
            case 'D' :
            default :
                $iteration_start = $today;
                $iteration_end = $iteration_start->advance(($iteration_days - 1) * 86400, false);
                break;
        }
        //echo "Returning: ".$iteration_start->toMySQL()." - ".$iteration_end->toMySQL();
        return array('start' => $iteration_start, 'end' => $iteration_end);
    }

    /**
     * Creates missing activity log items. We do this so that reports
     * can show accurate results
     *
     * @return mixed
     */
    function createMissingActivityLogItems() {
        // TODO: Find best way to rebuild activity logs if needed - and for all items
        //ActivityLogsAdminController::rebuild();
        //ActivityLogs::rebuildProjectObjectActivityLogs(array('Task'), 'tasks', array('Task' => 'task/created'), 'task/completed', 'task/reopened', true, true);
        return true;
    }

    /**
     * Determine iteration period and take snapshot for it.
     * If no snapshots present, take snapshots from the earliest project.
     *
     */
    function dailyHandler() {

        // Increase limits so that operation does not time out in between
        @ini_set('memory_limit', '256M');
        @ini_set('max_execution_time', '6000');

        if (!ConfigOptions::exists('reports_snapshot_taken_on')) {
            ConfigOptions::addOption('reports_snapshot_taken_on', REPORTS_PLUS_MODULE, '');
            ConfigOptions::setValue('reports_snapshot_taken_on', '');
        }

        $frequency = ConfigOptions::getValue("reports_update_frequency");
        $iteration_days = ConfigOptions::getValue("reports_iteration_days");
        $today = DateValue::makeFromString('now');
        $dates = self::getIterationDates($today, $iteration_days, $frequency);
        $iteration_start = $dates ['start'];
        $iteration_end = $dates ['end'];
        $params ['start'] = $iteration_start->toMySQL();
        $params ['end'] = $iteration_end->toMySQL();

        $master_categories = ConfigOptions::getValue("reports_master_categories");
        $master_categories_count = count($master_categories);
        $master_categories_map = array();
        for ($i = 0; $i < $master_categories_count; $i ++) {
            $master_categories_map [strtolower(trim($master_categories [$i]))] = 'cat' . $i;
        }

        // If table is empty, populate it from the beginning
        $query = 'SELECT count(*) as count FROM ' . TABLE_PREFIX . 'rep_tickets_snapshot';
        $result = DB::executeFirstRow($query);
        if ($result ['count'] <= 0) {
            $query = 'SELECT created_on FROM ' . TABLE_PREFIX . 'projects WHERE created_on IS NOT NULL ORDER BY created_on ASC LIMIT 0,1';
            $result = DB::executeFirstRow($query);
            if (is_foreachable($result)) {
                $beginning = DateValue::makeFromString($result ['created_on']);

                // Take snapshot of last 6 months only
                // If you want take snapshot of your entire data,
                // comment out these lines
                $six_months_ago = DateValue::makeFromString("-6 months");
                if ($beginning->getTimestamp() < $six_months_ago->getTimestamp()) {
                    $beginning = $six_months_ago;
                }

                // Adjust beginning date if we don't have enough RAM
                $availableMemory = ini_get('memory_limit');
                $val = trim($availableMemory);
                $last = strtolower($val [strlen($val) - 1]);
                switch ($last) {
                    case 'g' :
                        $val *= 1024;
                    case 'm' :
                        $val *= 1024;
                    case 'k' :
                        $val *= 1024;
                }
                // Convert back to MB
                $availableMemory = round($val / 1024 / 1024);
                if ($availableMemory < 128) {
                    $weeksToProcess = round($availableMemory / 4);
                    $beginning = DateValue::makeFromString("-$weeksToProcess weeks");
                }

                // Adjust iteration start to week start from beginning date
                $dates = self::getIterationDates($beginning, $iteration_days, $frequency);
                $iteration_start = $dates ['start'];
                $iteration_end = $dates ['end'];

                while ($iteration_start->toMySQL() < $params ['start']) {
                    $iparams ['historical'] = 1;
                    $iparams ['start'] = $iteration_start->toMySQL();
                    $iteration_start->advance(($iteration_days * 86400));
                    $iparams ['end'] = $iteration_start->toMySQL();
                    $entries = self::takeIterationSnapshot($master_categories_map, $iparams, $iteration_days);
                    //echo "Populating - ".$iparams['start'].' - '.$iparams['end']." - $entries entries processed.<br />";
                    $iteration_start->advance(1);
                }
                ConfigOptions::setValue('reports_snapshot_taken_on', new DateTimeValue());
            }
        }
        if (AngieApplication::isModuleLoaded('tracking')) {
            // We are taking snapshot of all modified iterations	
            $reports_snapshot_taken_on = ConfigOptions::getValue('reports_snapshot_taken_on', false);
            $query = "SELECT record_date FROM `" . TABLE_PREFIX . "time_records` WHERE record_date < DATE(created_on)";
            if ($reports_snapshot_taken_on != '') {
                $query .= DB::prepare(" AND created_on BETWEEN  ?  AND ? AND created_on > ?", DateValue::makeFromString('-1 day')->format('Y-m-d H:i:s'), DateValue::makeFromString('now')->format('Y-m-d H:i:s'), $reports_snapshot_taken_on);
            }
            $query .= " GROUP BY record_date";
            $record_dates = DB::executeFirstColumn($query);

            if (is_foreachable($record_dates)) {
                $iterations_affected = array();
                foreach ($record_dates as $beginning) {
                    $dates = self::getIterationDates(DateValue::makeFromString($beginning), $iteration_days, $frequency);
                    $iterations_affected[$dates['start']->toMySQL()] = array('start' => $dates['start']->toMySQL(),
                        'end' => $dates['end']->toMySQL(),
                        'historical' => 1
                    );
                }
            }
            $iterations_affected[$params['start']] = $params;
            foreach ($iterations_affected as $vparams) {
                $entries = self::takeIterationSnapshot($master_categories_map, $vparams, $iteration_days);
            }
            ConfigOptions::setValue('reports_snapshot_taken_on', new DateTimeValue());
        } else {
            $entries = self::takeIterationSnapshot($master_categories_map, $params, $iteration_days);
        }


        //Creating & Updating financial summary table on scheduled jobs
        if (AngieApplication::isModuleLoaded('invoicing')) {
            self::updateFinancialSummary();
        }
        //echo "Reporting snapshots taken - ".$params['start'].' - '.$params['end'].' - '.$entries.' entries processed.<br/>';
        return;
    }

    function getProjectSummary($ids, $search_by = 'company', $user = null, $params = null) {
        $result = array();
        $result ['projects'] = 0;
        $result ['project_ids'] = array();
        $result ['project_details'] = array();

        if (!is_array($ids) || count($ids) == 0) {
            return $result;
        }

        // What type of projects to show - default - show only active projects
        $include_project_status = "AND completed_on IS NULL";
        $additional_conditions = TABLE_PREFIX . "projects.completed_on IS NULL";
        if (is_array($params) && isset($params ['status']) && $params ['status'] == 'all') {
            $include_project_status = '';
            $additional_conditions = null; // Getting completed projects form the Projects::findIdsByUser()
        }

        $projects = null;
        if (is_a($user, 'User')) {
            // Find projects this person can see
            $projects = Projects::findIdsByUser($user, true, $additional_conditions);
        }

        $owner_company_search_part = '';
        if ($search_by == 'company') {
            $owner_company = Companies::findOwnerCompany();
            if (in_array($owner_company->getId(), $ids)) {
                $owner_company_search_part = ' OR company_id = 0 ';
            }
        }

        //fix for trashed project
        if ($search_by != "") {
            $search_by .= "_";
        }
        $query = DB::prepare("SELECT id, slug,
						   CASE WHEN completed_on IS NULL THEN 'active' ELSE 'completed' END as status,
						   IFNULL(created_on, '') as created_on,
						   category_id, name, leader_name, leader_id
						   FROM `" . TABLE_PREFIX . "projects`
						   WHERE State >= " . STATE_VISIBLE . " AND ({$search_by}id IN (?)
						   $owner_company_search_part)
						   $include_project_status
						   ORDER BY name", $ids);
        $data = DB::execute($query);

        $now = ($params != null && strlen($params ['end']) > 4) ? DateValue::makeFromString($params ['end']) : DateValue::makeFromString('now');
        $now_ts = $now->getTimestamp();
        $users_id_details = Users::getIdDetailsMap(null, array('first_name', 'last_name'));
        if (is_foreachable($data)) {
            foreach ($data as $item) {
                if ($projects != null && !in_array($item ['id'], $projects)) {
                    continue;
                }
                $result ['project_ids'] [] = $item ['id'];
                list ( $item ['total_tasks_count'], $item ['open_tasks_count'] ) = ProjectProgress::getProjectProgress($item ['id']);

                $result ['projects'] = $result ['projects'] + 1;
                $item ['percent_done'] = 0;
                $item ['completed_tasks_count'] = $item ['total_tasks_count'] - $item ['open_tasks_count'];
                $item ['hours'] = 0;
                if (!empty($users_id_details[$item['leader_id']]['first_name'])) {
                    $item ['leader_name'] = $users_id_details[$item['leader_id']]['first_name'] . ' ' . $users_id_details[$item['leader_id']]['last_name'];
                }
                // $item ['leader_name'] = empty($users_id_details[$item['leader_id']]['first_name']) ? $item['leader_name'] : $users_id_details[$item['leader_id']]['first_name'] . ' ' . $users_id_details[$item['leader_id']]['last_name'] ;
                // Running days
                if ($item ['created_on'] != '') {
                    $starts = DateValue::makeFromString($item ['created_on']);
                    $item ['running_for_days'] = round(($now_ts - $starts->getTimestamp()) / 86400);
                }
                if ($item ['completed_tasks_count'] > 0 && $item ['total_tasks_count'] > 0) {
                    $item ['percent_done'] = floor($item ['completed_tasks_count'] / $item ['total_tasks_count'] * 100);
                } else if ($item ['open_tasks_count'] == 0 && $item ['total_tasks_count'] > 0) {
                    $item ['percent_done'] = 100;
                }
                // URL
                $item ['url'] = Router::assemble('project', array('project_slug' => $item ['slug']));
                $result ['project_details'] [$item ['id']] = $item;
            }
        }

        if ($result ['projects'] == 0) {
            return $result;
        }
        $ids = array_keys($result ['project_details']);

        // Total time spent
        $result ['project_hours'] = 0;
        $result ['project_estimated_hours'] = 0;
        if (AngieApplication::isModuleLoaded('tracking')) {
            $data = self::getProjectActualHours($ids, array('start' => '1970-01-01', 'end' => $now->toMySQL()));
            if (is_foreachable($data)) {
                foreach ($data as $item) {
                    $result ['project_hours'] += $item ['actual_hours'];
                    $result ['project_details'] [$item ['project_id']] ['hours'] = $item ['actual_hours'];
                }
            }

            // Estimated time for the project
            $date_range = 'AND date <= ';
            $date_range .= (is_array($params) && $params ['end'] != '') ? "'{$params['end']}' " : "NOW() ";

            $query = 'SELECT project_id, estimated_hours as total
				FROM `' . TABLE_PREFIX . 'rep_tickets_snapshot`
				WHERE project_id IN (?)
				' . $date_range . '
				ORDER BY date DESC, project_id
				LIMIT 0, ' . count($ids);
            $data = DB::execute($query, $ids);
            if (is_foreachable($data)) {
                $result ['project_estimated_hours'] = 0;
                foreach ($data as $item) {
                    if (!isset($result ['project_details'] [$item ['project_id']] ['estimated_hours'])) {
                        $result ['project_estimated_hours'] += $item ['total'];
                        $result ['project_details'] [$item ['project_id']] ['estimated_hours'] = $item ['total'];
                    }
                }
            }
        }

        // Next milestone
        $query = "SELECT id, project_id, name, IFNULL(due_on, 0) as due_on
				   FROM `" . TABLE_PREFIX . "project_objects` po 
				   WHERE type = 'Milestone'
				   AND completed_on IS NULL
				   AND project_id IN (?)
				   AND state >= ? " . $params ['visibility_condition'] . " 
				   ORDER BY project_id, due_on ASC";
        $data = DB::execute($query, $ids, STATE_VISIBLE);
        if (is_foreachable($data)) {
            $last_project = 0;
            foreach ($data as $item) {
                if ($last_project != $item ['project_id']) {
                    $last_project = $item ['project_id'];
                    $result ['project_details'] [$item ['project_id']] ['next_milestone'] = $item ['name'];
                    $result ['project_details'] [$item ['project_id']] ['next_milestone_date'] = $item ['due_on'];
                    $result ['project_details'] [$item ['project_id']] ['next_milestone_url'] = Router::assemble("project_milestone", array('project_slug' => $result ['project_details'] [$item ['project_id']] ['slug'], "milestone_id" => $item ['id']));
                    $next_milestone_date = DateValue::makeFromString($item ['due_on']);
                    if ($next_milestone_date != null) {
                        $offset = get_user_gmt_offset();
                        $now = new DateTimeValue ();
                        $now->advance($offset);
                        $now = $now->beginningOfDay();
                        $next_milestone_date = $next_milestone_date->beginningOfDay();
                        $diff = floor(($next_milestone_date->getTimestamp() - $now->getTimestamp()) / 86400);
                        $result ['project_details'] [$item ['project_id']] ['next_milestone_days'] = $diff;
                    } else {
                        $result ['project_details'] [$item ['project_id']] ['next_milestone_days'] = lang('TBD');
                    }
                }
            }
        }

        return $result;
    }

    function getProjectStatstics($search_ids, $search_by, &$user, &$options) {
        $result = array();
        $ids = (array) explode(",", trim($search_ids));
        if (!is_numeric($ids[0]) && $search_by == 'company') {
            $ids = array();
            // Find all companies visible to this user
            $ids = $user->visibleCompanyIds();
        }

        if (count($ids) == 0) {
            return $result;
        }
        $search_by == 'company';

        if ($search_by == 'company') {
            // Companies
            $result ['companies'] = count($ids);
            $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users WHERE company_id IN (?)";
            $project_stats = $this->getProjectSummary($ids, 'company', $user, $options);
        } else {

            $query = "SELECT COUNT(DISTINCT(company_id)) AS total FROM " . TABLE_PREFIX . "projects WHERE id IN (?)";
            $data = DB::executeFirstRow($query, $ids);
            $result ['companies'] = ($data ['total'] > 0) ? $data ['total'] : 0;

            $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "project_users WHERE project_id IN (?)";
            $project_stats = $this->getProjectSummary($ids, '', $user, $options);
        }
        // Users
        $data = DB::executeFirstRow($query, $ids);
        $result ['users'] = ($data ['total'] > 0) ? $data ['total'] : 0;

        // Projects
        $result ['projects'] = $project_stats ['projects'];
        $result ['project_details'] = $project_stats ['project_details'];
        $result ['hours'] = $project_stats ['project_hours'];
        $projects = $project_stats ['project_ids'];

        if (count($projects) > 0) {
            // Files - all kinds - attachments, revisions
            // File size - all kinds - attachments, revisions
            $query = "SELECT IFNULL(count(*), 0) AS total, sum(IFNULL(size, 0)) AS total_size
					   FROM `" . TABLE_PREFIX . "attachments`
					   WHERE parent_id IN 
					   	(select id from `" . TABLE_PREFIX . "project_objects` po WHERE project_id IN (?) AND state >= ? {$options['visibility_condition']} )
					   UNION
					   SELECT IFNULL(count(*), 0) AS total, sum(IFNULL(size, 0)) AS total_size 
					   FROM `" . TABLE_PREFIX . "file_versions`
					   WHERE file_id IN
					   	(select id from `" . TABLE_PREFIX . "project_objects` po WHERE type = 'File' AND project_id IN (?) AND state >= ? {$options['visibility_condition']} )";
            $data = DB::execute($query, $projects, STATE_VISIBLE, $projects, STATE_VISIBLE);
            $result ['files'] = 0;
            $result ['filesize'] = 0;

            if (is_foreachable($data)) {
                foreach ($data as $item) {
                    $result ['files'] += $item ['total'];
                    $result ['filesize'] += $item ['total_size'];
                }
            }
            $result ['filesize'] = $this->formatFileSize($result ['filesize']);

            // Different project objects - completed and open, but non deleted
            // TODO: Add Subtasks and Notebook pages...
            $query = "SELECT `type`,
						CASE WHEN (completed_on IS NOT NULL)
							THEN 1
							ELSE 0
							END AS completed,
						count(id) as total
					FROM `" . TABLE_PREFIX . "project_objects` po 
					WHERE `state` >= ? AND project_id IN (?)
					{$options['visibility_condition']}
					GROUP BY `type`, completed
					ORDER BY `type`, completed";
            $data = DB::execute($query, STATE_VISIBLE, $projects);
            $result ['project_objects'] = array();
            if (is_foreachable($data)) {
                foreach ($data as $item) {
                    $key = ($item ['completed'] == 1) ? 'completed' : 'open';
                    $result ['project_objects'] [$item ['type']] [$key] = $item ['total'];
                }
            }
        }

        return $result;
    }

    function getCompanyStats($companies = '', &$user, &$options) {
        $result = array();
        $allCompanies = (array) explode(",", trim($companies));
        if (!is_numeric($allCompanies [0])) {
            $allCompanies = array();
            // Find all companies visible to this user
            $allCompanies = $user->visibleCompanyIds();
        }

        if (count($allCompanies) == 0) {
            return $result;
        }

        // Projects
        $project_stats = $this->getProjectSummary($allCompanies, 'company', $user, $options);
        $result ['projects'] = $project_stats ['projects'];
        $result ['project_details'] = $project_stats ['project_details'];
        $result ['hours'] = $project_stats ['project_hours'];
        $projects = $project_stats ['project_ids'];

        // Users
        $query = "SELECT count(*) AS total
				   FROM `" . TABLE_PREFIX . "users`
				   WHERE company_id IN (?)";
        $data = DB::executeFirstRow($query, $allCompanies);
        $result ['users'] = ($data ['total'] > 0) ? $data ['total'] : 0;

        // Companies
        $result ['companies'] = count($allCompanies);

        if (count($projects) > 0) {
            // Files - all kinds - attachments, revisions
            // File size - all kinds - attachments, revisions
            $query = "SELECT IFNULL(count(*), 0) AS total, sum(IFNULL(size, 0)) AS total_size
					   FROM `" . TABLE_PREFIX . "attachments`
					   WHERE parent_id IN 
					   	(select id from `" . TABLE_PREFIX . "project_objects` po WHERE project_id IN (?) AND state >= ? {$options['visibility_condition']} )
					   UNION
					   SELECT IFNULL(count(*), 0) AS total, sum(IFNULL(size, 0)) AS total_size 
					   FROM `" . TABLE_PREFIX . "file_versions`
					   WHERE file_id IN
					   	(select id from `" . TABLE_PREFIX . "project_objects` po WHERE type = 'File' AND project_id IN (?) AND state >= ? {$options['visibility_condition']} )";
            $data = DB::execute($query, $projects, STATE_VISIBLE, $projects, STATE_VISIBLE);
            $result ['files'] = 0;
            $result ['filesize'] = 0;

            if (is_foreachable($data)) {
                foreach ($data as $item) {
                    $result ['files'] += $item ['total'];
                    $result ['filesize'] += $item ['total_size'];
                }
            }
            $result ['filesize'] = $this->formatFileSize($result ['filesize']);

            // Different project objects - completed and open, but non deleted
            // TODO: Add Subtasks and Notebook pages...
            $query = "SELECT `type`,
						CASE WHEN (completed_on IS NOT NULL)
							THEN 1
							ELSE 0
							END AS completed,
						count(id) as total
					FROM `" . TABLE_PREFIX . "project_objects` po 
					WHERE `state` >= ? AND project_id IN (?)
					{$options['visibility_condition']}
					GROUP BY `type`, completed
					ORDER BY `type`, completed";
            $data = DB::execute($query, STATE_VISIBLE, $projects);
            $result ['project_objects'] = array();
            if (is_foreachable($data)) {
                foreach ($data as $item) {
                    $key = ($item ['completed'] == 1) ? 'completed' : 'open';
                    $result ['project_objects'] [$item ['type']] [$key] = $item ['total'];
                }
            }
        }
        return $result;
    }

    /**
     * Format filesize
     *
     * @param string $value
     * @return string
     */
    function formatFileSize($value) {
        $data = array('TB' => 1099511627776, 'GB' => 1073741824, 'MB' => 1048576, 'kb' => 1024);

        $value = (integer) $value;
        foreach ($data as $unit => $bytes) {
            $in_unit = $value / $bytes;
            if ($in_unit > 0.9) {
                // we are returning a file size and unit seprately
                return array('fsize' => number_format($in_unit, 0, '.', ','), 'unit' => $unit);
            } // if
        } // foreach


        return $value . 'b';
    }

// filesize

    /**
     * Calculates Leader board Start and End date based on the option selected
     *
     * @param string $value
     * @return string
     */
    function getLeaderBoardPeriodDates() {
        if (!ConfigOptions::exists('reports_leader_board_period')) {
            ConfigOptions::addOption('reports_leader_board_period', REPORTS_PLUS_MODULE, array('key' => 0));
            ConfigOptions::setValue('reports_leader_board_period', array('key' => 0));
        }
        $leader_board_period_options = ConfigOptions::getValue('reports_leader_board_period');

        $now = DateValue::makeFromString('now');
        $dates['end'] = $now->format("Y-m-d");
        switch ($leader_board_period_options['key']) {
            case 0:
                $dummy = $now->beginningOfWeek(1);
                $dates['start'] = $dummy->format("Y-m-d");
                break;
            case 1:
                $dates['start'] = DateValue::makeFromString('now -1 week')->beginningOfWeek(1)->format("Y-m-d");
                break;
            case 2:
                $dummy = $now->beginningOfMonth($now->getMonth(), $now->getYear());
                $dates['start'] = $dummy->format("Y-m-d");
                break;
            case 3:
                $dummy = DateValue::makeFromString('now -2 month');
                $dates['start'] = $dummy->beginningOfMonth($dummy->getMonth(), $dummy->getYear())->format("Y-m-d");
                break;
            case 4:
                $dummy = DateValue::makeFromString('now -11 month');
                $dates['start'] = $dummy->beginningOfMonth($dummy->getMonth(), $dummy->getYear())->format("Y-m-d");
                break;
            case 5:
                $dates['start'] = DateValue::makeFromString($leader_board_period_options['start_date'])->format("Y-m-d");
                $dates['end'] = DateValue::makeFromString($leader_board_period_options['end_date'])->format("Y-m-d");
                break;
        }
        unset($now);
        unset($dummy);

        return $dates;
    }

    function getLeaderBoardData($user, $options = null) {

        $params = array();
        $params = self::getLeaderBoardPeriodDates();

        $activeModules = AngieApplication::getEnabledModuleNames();

        $company_ids = array();
        if (ConfigOptions::exists('companies_in_ranking_game')) {
            $company_ids = ConfigOptions::getValue('companies_in_ranking_game');
            if (in_array(-1, $company_ids)) {
                $company_ids = array_keys(Companies::getIdNameMap());
            }
        }
        if (empty($company_ids)) {
            $company = Companies::findOwnerCompany();
            if ($company instanceof Company) {
                $company_ids = array($company->getId());
            }
        }

        $projects = DB::executeFirstColumn("SELECT id FROM " . TABLE_PREFIX . "projects WHERE completed_on IS NULL AND state > " . STATE_ARCHIVED);
        $visible_ids = Users::findVisibleUserIds($user);
        $ids = DB::executeFirstColumn("SELECT id FROM " . TABLE_PREFIX . "users WHERE state > " . STATE_ARCHIVED);

        $query = "SELECT id as data,
				   CASE WHEN (IFNULL(first_name, '') = '' and IFNULl(last_name,'') = '')
				   THEN email
				   ELSE CONCAT(first_name, ' ', last_name)
				   END as label 
				   FROM `" . TABLE_PREFIX . "users`
				   WHERE id IN (?)
				   AND company_id IN (?)
				   AND state > ?
				   ORDER BY label, id";

        $users = DB::execute($query, $ids, $company_ids, STATE_ARCHIVED);
        $hours_map = $created_map = $completed_map = array();

        if (count($projects) > 0) {
            // Hours Worked
            if (AngieApplication::isModuleLoaded('tracking')) {
                $query = "SELECT user_id as id, SUM(value) as total
						  FROM `" . TABLE_PREFIX . "time_records` t 
						  WHERE state >= ?  
						  AND record_date BETWEEN ? AND ?
						  AND user_id IN (?)
						  AND ( (parent_type = 'Project' AND parent_id IN (?)) 
						  OR (parent_type != 'Project' AND parent_id IN (SELECT id FROM `" . TABLE_PREFIX . "project_objects` po
				    	  WHERE state >= ? {$options['visibility_condition']} AND project_id IN (?))) ) 
						  GROUP BY user_id
						  ORDER BY user_id";
                $query = DB::prepare($query, STATE_VISIBLE, $params ['start'], $params ['end'], $ids, $projects, STATE_VISIBLE, $projects);
                $data = DB::execute($query);

                if (is_foreachable($data)) {
                    foreach ($data as $item) {
                        $hours_map [$item ['id']] = $item ['total'];
                    }
                }
            }

            $data = self::getPersonActivityCounts($ids, $projects, array_merge($params, is_array($options) ? $options : array() ));
            foreach ($data as $item) {
                if (!isset($created_map [$item ['person_id']])) {
                    $created_map [$item ['person_id']] = 0;
                }
                if (!isset($completed_map [$item ['person_id']])) {
                    $completed_map [$item ['person_id']] = 0;
                }

                $created_map [$item ['person_id']] += (isset($item ['created']) ? $item ['created'] : 0);
                $completed_map [$item ['person_id']] += (isset($item ['completed']) ? $item ['completed'] : 0);
            }
        }

        // $total_activity_score = $total_active_users = 0;
        $dummy_user = Users::getUserInstance(); // This will create User with the default Role::Client

        $scores_map = array();
        $result = array();
        if (is_foreachable($users)) {
            foreach ($users as $key => $item) {
                $dummy_user->setId($item ['data']);
                $hours = (isset($hours_map [$item ['data']])) ? $hours_map [$item ['data']] : 0;
                $completed = (isset($completed_map [$item ['data']])) ? $completed_map [$item ['data']] : 0;
                $created = (isset($created_map [$item ['data']])) ? $created_map [$item ['data']] : 0;
                $score = $this->calculatePersonActivityScore($hours, $completed, $created);
                if ($score == '') {
                    $score = 0;
                }
                $key = $item ['data'];
                $result [$key] = $item;
                // hiding invisbile to user from the list
                $result [$key] ['hide'] = (in_array($item ['data'], $visible_ids)) ? false : true;
                $result [$key] ['score'] = $score;
                $result [$key] ['avatar'] = $dummy_user->avatar()->getUrl(IUserAvatarImplementation::SIZE_BIG);
                $scores_map [$key] = $score;
            }
        }

        // Sort the result
        $logged_user_rank['score'] = $scores_map[$user->getId()];
        array_multisort($scores_map, SORT_DESC, $result);
        $logged_user_rank['rank'] = array_search($logged_user_rank['score'], $scores_map) + 1;
        $logged_user_rank['total_users'] = count($result);

        $leader_board_title = lang('Leader Board for :start_date to :end_date', array('start_date' => DateValue::makeFromString($params['start'])->formatForUser(), 'end_date' => DateValue::makeFromString($params['end'])->formatForUser()));
        return array('leader_board_list' => $result, 'logged_user_rank' => empty($logged_user_rank) ? null : $logged_user_rank, 'leader_board_title' => $leader_board_title);
    }

    function getPeople(&$user, $options = null) {
        $params = array();
        $start = DateValue::makeFromString(array_var($options, 'start_date', '-1 week'));
        $end = DateValue::makeFromString(array_var($options, 'end_date', 'now'));
        $params ['start'] = $start->toMySQL();
        $params ['end'] = $end->toMySQL();
        $activeModules = AngieApplication::getEnabledModuleNames();

        // Find projects this person can see
        $projects = Projects::findIdsByUser($user, true, TABLE_PREFIX . "projects.completed_on IS NULL");
        // Find people this person can see
        $ids = Users::findVisibleUserIds($user);

        // $query = "SELECT id as data,
        // 		   CASE WHEN (IFNULL(first_name, '') = '' and IFNULl(last_name,'') = '')
        // 		   	THEN email
        // 		   ELSE CONCAT(first_name, ' ', last_name)
        // 		    END as label, company_id, role_id 
        // 		   FROM `" . TABLE_PREFIX . "users`
        // 		   WHERE id in (?)
        // 		   AND role_id > 0
        // 		   ORDER BY label, id";
        //Ani..
        $query = "SELECT id as data,
				   CASE WHEN (IFNULL(first_name, '') = '' and IFNULl(last_name,'') = '')
				   THEN email
				   ELSE CONCAT(first_name, ' ', last_name)
				   END as label, company_id, type 
				   FROM `" . TABLE_PREFIX . "users`
				   WHERE id IN (?)
				   AND state > ?
				   ORDER BY label, id";

        $users = DB::execute($query, $ids, STATE_ARCHIVED);

        $hours_map = $created_map = $completed_map = array();

        if (count($projects) > 0) {
            // Hours Worked
            if (AngieApplication::isModuleLoaded('tracking')) {
                $query = "SELECT user_id as id, SUM(value) as total
						  FROM `" . TABLE_PREFIX . "time_records` t 
						  WHERE state >= ?  
						  AND record_date BETWEEN ? AND ?
						  AND user_id IN (?)
						  AND ( (parent_type = 'Project' AND parent_id IN (?)) 
						  OR (parent_type != 'Project' AND parent_id IN (SELECT id FROM `" . TABLE_PREFIX . "project_objects` po
				    	  WHERE state >= ? {$options['visibility_condition']} AND project_id IN (?))) ) 
						  GROUP BY user_id
						  ORDER BY user_id";
                $data = DB::execute($query, STATE_VISIBLE, $params ['start'], $params ['end'], $ids, $projects, STATE_VISIBLE, $projects);

                if (is_foreachable($data)) {
                    foreach ($data as $item) {
                        $hours_map [$item ['id']] = $item ['total'];
                    }
                }
            }

            $data = self::getPersonActivityCounts($ids, $projects, array_merge($params, is_array($options) ? $options : array() ));
            foreach ($data as $item) {
                if (!isset($created_map [$item ['person_id']])) {
                    $created_map [$item ['person_id']] = 0;
                }
                if (!isset($completed_map [$item ['person_id']])) {
                    $completed_map [$item ['person_id']] = 0;
                }

                $created_map [$item ['person_id']] += (isset($item ['created']) ? $item ['created'] : 0);
                $completed_map [$item ['person_id']] += (isset($item ['completed']) ? $item ['completed'] : 0);
            }
        }

        $total_activity_score = $total_active_users = 0;
        $dummy_user = Users::getUserInstance(); // This will create User with the default Role::Client

        $scores_map = array();
        $result = array();
        if (is_foreachable($users)) {
            foreach ($users as $key => $item) {
                $dummy_user->setId($item ['data']);
                $hours = (isset($hours_map [$item ['data']])) ? $hours_map [$item ['data']] : 0;
                $completed = (isset($completed_map [$item ['data']])) ? $completed_map [$item ['data']] : 0;
                $created = (isset($created_map [$item ['data']])) ? $created_map [$item ['data']] : 0;
                $score = $this->calculatePersonActivityScore($hours, $completed, $created);
                if ($score == '')
                    $score = 0;
                if ($score > 0) {
                    $total_activity_score += $score;
                    $total_active_users ++;
                }
                $result [$key] = $item;
                $result [$key] ['score'] = $score;
                $result [$key] ['avatar'] = $dummy_user->avatar()->getUrl(IUserAvatarImplementation::SIZE_BIG);
                $scores_map [$key] = $score;
            }
        }

        // Sort the result
        if (!isset($options ['sort']) || (isset($options ['sort']) && $options ['sort'] == 'score')) {
            array_multisort($scores_map, SORT_DESC, $result);
        }
        $summary = array();
        $summary ['average_score'] = round($total_activity_score / $total_active_users);

        return array('people' => $result, 'summary' => $summary);
    }

    function calculatePersonActivityScore($hours, $completed, $created) {

        // OVERALL SCORE
        // Hours logged + (Items Closed * 3) + (objects created except time records * 2)
        // Indicates work done
        $weight_hours = ConfigOptions::getValue('reports_formula_weight_person_score_hours');
        $weight_completed = ConfigOptions::getValue('reports_formula_weight_person_score_completed');
        $weight_created = ConfigOptions::getValue('reports_formula_weight_person_score_created');
        return round(($weight_hours * $hours) + ($weight_completed * $completed) + ($weight_created * $created));
    }

    function getCompanies(&$user) {
        if ($user == null || !is_a($user, 'User')) {
            return array();
        }
        $ids = $user->visibleCompanyIds();

        // We want to find companies along with their owners
        $company_owners = array();
        $query = "SELECT id, company_id, raw_additional_properties
				  FROM `" . TABLE_PREFIX . "users`
				  WHERE type IN (?) ";

        $data = DB::execute($query, 'Client');
        if ($data instanceof DBResult && $data->count() > 0) {
            foreach ($data as $item) {
                // TODO::validate the custom permissions 
                // We are Checking custom permissions fot the Client to make sure, he can be client Manager
                $custom_parmissions = empty($item['raw_additional_properties']) ? array() : unserialize($item['raw_additional_properties']);
                if (in_array('can_request_project', $custom_parmissions['custom_permissions']) || in_array('can_manage_client_finances', $custom_parmissions['custom_permissions'])) {
                    $company_owners [$item ['company_id']] = $item ['id'];
                }
                // we dont need permission any more 
                unset($item['raw_additional_properties']);
            }
        }
        $query = "SELECT id as data, name as label, is_owner
				   FROM `" . TABLE_PREFIX . "companies`
				   WHERE state >= ?
				   AND id IN (?)
				   ORDER BY name";
        $data = DB::execute($query, STATE_VISIBLE, $ids);
        $final_ids = array();
        $owner_company = array();
        $final_companies = array();
        if (count($data) > 0) {
            foreach ($data as $key => $row) {
                if (!isset($row ['data']) || !isset($row ['label'])) {
                    continue;
                }
                $item ['data'] = $row ['data'];
                $item ['label'] = $row ['label'];
                $owner = isset($company_owners [$item ['data']]) ? $company_owners [$item ['data']] : '';
                if ($owner != '') {
                    $item ['owner_id'] = $owner;
                }
                if ($row ['is_owner'] == 1 && count($data) > 10) {
                    $owner_company = $item;
                    $owner_company ['label'] .= ' - ' . lang("Owner Company");
                }
                $final_ids [] = $item ['data'];
                $final_companies [] = $item;
            }
            if (isset($owner_company ['data']) && $owner_company ['data'] > 0) {
                array_unshift($final_companies, $owner_company);
            }
        }
        array_unshift($final_companies, array('data' => implode(',', $final_ids), 'label' => lang('All')));

        return $final_companies;
    }

    function getRoles() {
        $roles = Users::getAvailableUserClasses();
        $result = array(array('data' => '', 'label' => lang('All')));
        foreach ($roles as $role) {
            $result [] = array('data' => $role, 'label' => $role);
        }
        return $result;
    }

    function getProjectGroups() {
        $query = "SELECT id as data, name as label
				   FROM `" . TABLE_PREFIX . "categories`
				   WHERE type = 'ProjectCategory'
				   ORDER BY id";
        $data = DB::execute($query);
        $result = array(array('data' => '', 'label' => lang('All Projects')));
        if (is_foreachable($data)) {
            foreach ($data as $item) {
                $result [] = $item;
            }
        }
        return $result;
    }

    function getFilterTypes() {
        $data = array();
        $data ['projects'] [] = array('data' => 'projects', 'label' => lang('Project'));
        $data ['projects'] [] = array('data' => 'project_groups', 'label' => lang('Category'));
        $data ['projects'] [] = array('data' => 'companies', 'label' => lang('Company'));

        $data ['people'] [] = array('data' => 'label', 'label' => lang('Name'));
        // $data ['people'] [] = array ('data' => 'role_type', 'label' => lang ( 'Role' ) );
        $data ['people'] [] = array('data' => 'company_id', 'label' => lang('Company'));
        $data ['people_sort_types'] [] = array('data' => 'score', 'label' => lang('Score'));
        $data ['people_sort_types'] [] = array('data' => 'label', 'label' => lang('Name'));

        $data ['companies'] [] = array('data' => 'company', 'label' => lang('Company'));
        $data ['companies_project_statuses'] [] = array('data' => 'active', 'label' => lang('Active') . ' ' . lang('Projects'));
        $data ['companies_project_statuses'] [] = array('data' => 'all', 'label' => lang('All') . ' ' . lang('Projects'));

        return $data;
    }

    function getDates(&$user) {
        $today = new DateValue(time() + get_user_gmt_offset($user));
        $data [] = array('data' => 'today', 'label' => lang('Today'), 'start' => $today->toMySQL(), 'end' => $today->toMySQL());

        $yesterday = $today->advance(- 86400, false);
        $data [] = array('data' => 'yesterday', 'label' => lang('Yesterday'), 'start' => $yesterday->toMySQL(), 'end' => $yesterday->toMySQL());

        $first_day_sunday = ConfigOptions::getValueFor('time_first_week_day', $user, 0) == 0;
        $week_start = $today->beginningOfWeek($first_day_sunday);
        $week_end = $today->endOfWeek($first_day_sunday);
        $data [] = array('data' => 'thisweek', 'label' => lang('This Week'), 'start' => $week_start->toMySQL(), 'end' => $week_end->toMySQL());

        $oneWeekAgo = $today->advance(- 86400 * 7, false);
        $week_start = $oneWeekAgo->beginningOfWeek($first_day_sunday);
        $week_end = $oneWeekAgo->endOfWeek($first_day_sunday);
        $data [] = array('data' => 'lastweek', 'label' => lang('Last Week'), 'start' => $week_start->toMySQL(), 'end' => $week_end->toMySQL());

        $month_start = DateTimeValue::beginningOfMonth($today->getMonth(), $today->getYear());
        $month_end = DateTimeValue::endOfMonth($today->getMonth(), $today->getYear());
        $data [] = array('data' => 'thismonth', 'label' => lang('This Month'), 'start' => $month_start->toMySQL(), 'end' => $month_end->toMySQL());

        $oneMonthAgo = DateTimeValue::makeFromString("-1 month");
        $month_start = DateTimeValue::beginningOfMonth($oneMonthAgo->getMonth(), $oneMonthAgo->getYear());
        $month_end = DateTimeValue::endOfMonth($oneMonthAgo->getMonth(), $oneMonthAgo->getYear());
        $data [] = array('data' => 'lastmonth', 'label' => lang('Last Month'), 'start' => $month_start->toMySQL(), 'end' => $month_end->toMySQL());

        $threeMonthAgo = DateTimeValue::makeFromString('-3 months');
        $month_end = DateTimeValue::makeFromString('now');
        $data [] = array('data' => 'these3months', 'label' => lang('These 3 months'), 'start' => $threeMonthAgo->toMySQL(), 'end' => $month_end->toMySQL());

        $month_start = DateTimeValue::beginningOfMonth($threeMonthAgo->getMonth(), $threeMonthAgo->getYear());
        $month_end = DateTimeValue::endOfMonth($oneMonthAgo->getMonth(), $oneMonthAgo->getYear());
        $data [] = array('data' => 'last3months', 'label' => lang('Last 3 Months'), 'start' => $month_start->toMySQL(), 'end' => $month_end->toMySQL());

        $sixMonthAgo = DateTimeValue::makeFromString("-6 month");
        $month_start = DateTimeValue::beginningOfMonth($sixMonthAgo->getMonth(), $sixMonthAgo->getYear());
        $month_end = DateTimeValue::endOfMonth($oneMonthAgo->getMonth(), $oneMonthAgo->getYear());
        $data [] = array('data' => 'last6months', 'label' => lang('Last 6 Months'), 'start' => $month_start->toMySQL(), 'end' => $month_end->toMySQL());

        $firstDate = DB::execute("SELECT date FROM " . TABLE_PREFIX . 'rep_tickets_snapshot ORDER BY date ASC LIMIT 0,1');
        if (count($firstDate) == 1) {
            $firstDateValue = $firstDate [0] ['date'] . ' 00:00:00';
            $endDate = DateTimeValue::now();
            $data [] = array('data' => 'alldates', 'label' => lang('All Dates'), 'start' => $firstDateValue, 'end' => $endDate->toMySQL());
        }

        return array('projects' => array_slice($data, 2), 'people' => $data, 'finance' => array_slice($data, 4, 5));
    }

    function getPersonStats($id, &$options, &$user) {
        $result = array();
        $person = Users::findById($id);
        // Person not found
        if ($person == null) {
            return $result;
        }

        // Find projects this person can see
        $active_user = Users::findById($id);
        $projects = Projects::findIdsByUser($active_user, true, TABLE_PREFIX . "projects.completed_on IS NULL AND " . TABLE_PREFIX . "projects.state >= " . STATE_VISIBLE);

        $params = array();
        // If start / end date are not specified, use last 1 week period
        $start = DateValue::makeFromString(array_var($options, 'start_date', '-1 week'));
        $end = DateValue::makeFromString(array_var($options, 'end_date', 'now'));
        $params ['start'] = $start->toMySQL();
        $params ['end'] = $end->toMySQL();
        $result ['params'] = $params;

        // First Name, Last Name, Title, Company Name, Avatar image
        // Last active / Last seen
        $result ['name'] = $person->getDisplayName();
        $result ['id'] = $person->getId();
        $result ['email'] = $person->getEmail();
        $result ['avatar'] = $person->avatar()->getUrl(IUserAvatarImplementation::SIZE_BIG);
        $result ['company'] = $person->getCompanyName();
        $result ['title'] = $person->getConfigValue('title');
        if ($result ['title'] == 0) {
            $result ['title'] = '';
        }
        // $result ['role'] = $person->getRole ()->getName ();
        $result ['role'] = get_class($person);

        $result ['projects'] = array();
        $result ['projects'] ['ids'] = array();
        $result ['items'] = array();
        $result ['hours'] = 0;
        $result ['projects'] ['hours'] = array();
        $result ['time_entries'] = $result ['projects'] ['leading'] = $result ['projects'] ['assigned'] = 0;
        $result ['items'] ['created'] = 0;
        $result ['items'] ['created_types'] = array();
        $result ['items'] ['completed'] = 0;
        $result ['items'] ['completed_types'] = array();
        $result ['items'] ['leading'] = 0;
        $result ['items'] ['assigned'] = 0;
        $result ['projects'] ['items_assigned'] = array();
        $result ['projects'] ['worked'] = 0;
        $result ['items'] ['worked'] = 0;
        $result ['time_entries'] = 0;

        if (count($projects) > 0) {
            $result ['projects'] ['ids'] = $projects;

            // Hours Worked and # of time entries

            if (AngieApplication::isModuleLoaded('tracking')) {
                $query = "SELECT SUM(t.value) as total, COUNT(t.id) as count,
						  (CASE WHEN t.parent_type = 'Project' THEN t.parent_id ELSE po.project_id END) as project_id 
						  FROM `" . TABLE_PREFIX . "time_records` t 
						  LEFT JOIN `" . TABLE_PREFIX . "project_objects` po 
						  	ON (t.parent_type = po.type and t.parent_id = po.id and po.state >= ? {$params['visibility_condition']})
						  WHERE
						  t.user_id = ?  
						  AND t.record_date BETWEEN ? AND ? 
						  AND ( (t.parent_type = 'Project' AND t.parent_id IN (?)) OR (t.parent_type != 'Project' AND po.project_id IN (?)) )
						  AND t.state >= ?  
						  GROUP BY project_id
						  ORDER BY project_id";
                $data = DB::execute($query, STATE_VISIBLE, $id, $params ['start'], $params ['end'], $projects, $projects, STATE_VISIBLE);
            }
            if (is_foreachable($data)) {
                foreach ($data as $item) {
                    $result ['hours'] += $item ['total'];
                    $result ['projects'] ['hours'] [] = array('id' => $item ['project_id'], 'total' => $item ['total']);
                    $result ['time_entries'] += $item ['count'];
                }
            }

            // Responsible for # of projects
            $query = "SELECT COUNT(id) as total
					  FROM `" . TABLE_PREFIX . "projects`
					  WHERE leader_id = ?
					  AND state >= ? 
					  AND (completed_on IS NULL OR completed_on BETWEEN ? AND ?)
					  AND id IN (?)";
            $data = DB::executeFirstRow($query, $id, STATE_VISIBLE, $params ['start'] . self::startTimeSuffix, $params ['end'] . self::endTimeSuffix, $projects);
            $result ['projects'] ['leading'] = ($data ['total'] > 0) ? $data ['total'] : 0;

            // Assigned # of active projects
            $query = "SELECT COUNT(pu.project_id) as total
					  FROM `" . TABLE_PREFIX . "project_users` pu, `" . TABLE_PREFIX . "projects` p
					  WHERE pu.user_id = ?
					  AND p.id = pu.project_id
					  AND p.state > ? 
					  AND (p.completed_on IS NULL OR p.completed_on BETWEEN ? AND ?)
					  AND p.id IN (?)";
            $data = DB::executeFirstRow($query, $id, STATE_VISIBLE, $params ['start'] . self::startTimeSuffix, $params ['end'] . self::endTimeSuffix, $projects);
            $result ['projects'] ['assigned'] = ($data ['total'] > 0) ? $data ['total'] : 0;

            // ACTIVITY COUNTS
            // Items Assigned on different projects
            // Responsible for / Assigned # of items
            // Overdue items assigned to this person
            // Items Worked On - created / completed / tracked time on / updated
            // Projects Worked On (different from projects assigned!)
            $worked_projects = array();
            $data = self::getPersonActivityCounts(array($id), $projects, array_merge($options, $params));
            foreach ($data as $item) {
                $result ['items'] ['created'] += $item ['created'];
                $result ['items'] ['completed'] += $item ['completed'];

                if (!isset($result ['items'] ['created_types'] [$item ['type']])) {
                    $result ['items'] ['created_types'] [$item ['type']] = array('type' => $item ['type'], 'total' => $item ['created']);
                } else {
                    $result ['items'] ['created_types'] [$item ['type']] ['total'] += $item ['created'];
                }

                if (!isset($result ['items'] ['completed_types'] [$item ['type']])) {
                    $result ['items'] ['completed_types'] [$item ['type']] = array('type' => $item ['type'], 'total' => $item ['completed']);
                } else {
                    $result ['items'] ['completed_types'] [$item ['type']] ['total'] += $item ['completed'];
                }

                if ($item ['project_id'] > 0) {
                    if (!isset($result ['projects'] ['items_assigned'] [$item ['project_id']])) {
                        $result ['projects'] ['items_assigned'] [$item ['project_id']] = array('id' => $item ['project_id'], 'total' => ($item ['assigned'] + $item ['responsible']));
                    } else {
                        $result ['projects'] ['items_assigned'] [$item ['project_id']] ['total'] += ($item ['assigned'] + $item ['responsible']);
                    }

                    // If user did some work on this project - except time tracked - add to worked list..
                    if ($item ['created'] > 0 || $item ['completed'] > 0 || $item ['updated'] > 0) {
                        $worked_projects [] = $item ['project_id'];
                    }
                }

                $result ['items'] ['leading'] += $item ['responsible'];
                $result ['items'] ['assigned'] += $item ['assigned'];
                $result ['items'] ['overdue'] += $item ['overdue'];
                $result ['items'] ['worked'] += ($item ['created'] + $item ['completed'] + $item ['updated']);
            }

            $result ['items'] ['created_types'] = array_values($result ['items'] ['created_types']);
            $result ['items'] ['completed_types'] = array_values($result ['items'] ['completed_types']);
            $result ['projects'] ['items_assigned'] = array_values($result ['projects'] ['items_assigned']);

            // Make unique the project_id list
            // Will be used for colors
            $worked_projects = array_unique(array_merge($result ['projects'] ['ids'], $worked_projects));
            $assigned_projects = array_unique($assigned_projects);
            $result ['projects'] ['worked'] = count($worked_projects);
            $result ['projects'] ['ids'] = $worked_projects;
        }

        // TODO:
        // % Items completed within estimation
        // Avg % Deviation from Estimation
        // OVERALL SCORE
        $result ['score'] = $this->calculatePersonActivityScore($result ['hours'], $result ['items'] ['completed'], $result ['items'] ['created']);

        return $result;
    }

    function getPersonActivityCounts($users, &$projects, &$options) {
        /* Things we want to find...
         * Items assigned
         * Items responsible for
         * Items worked on (ie udpated / created / completed)
         * Projects assigned
         * Projects worked on
         * Items completed
         * Items created
         */

        $params = array();
        $timeRangeStr = ' BETWEEN "' . $options ['start'] . self::startTimeSuffix . '" AND "' . $options ['end'] . self::endTimeSuffix . '" ';
        $projectsStr = "'" . implode("', '", $projects) . "'";
        $usersStr = "'" . implode("', '", $users) . "'";
        $visibilityCondition = "'" . STATE_VISIBLE . "' " . $options ['visibility_condition'];
        $activeModules = AngieApplication::getEnabledModuleNames();

        // Main query on users and project_objects
        $query = "SELECT
					u.id AS person_id,
					SUM( CASE WHEN u.id = po.created_by_id THEN 1 ELSE 0 END ) AS created, 
					SUM( CASE WHEN u.id = po.completed_by_id THEN 1 ELSE 0 END ) AS completed,
					SUM( CASE WHEN u.id = po.updated_by_id THEN 1 ELSE 0 END ) AS updated, 
					SUM( CASE WHEN u.id = po.assignee_id THEN 1 ELSE 0 END ) AS responsible,
					SUM( CASE WHEN (po.due_on IS NOT NULL AND po.due_on < NOW() AND po.completed_on IS NULL) THEN 1 ELSE 0 END ) AS overdue, 
					po.type as type,
					po.project_id as project_id 
					FROM `" . TABLE_PREFIX . "users` u 
					LEFT JOIN `" . TABLE_PREFIX . "project_objects` po 
					ON po.state >= {$visibilityCondition}  
					AND po.project_id IN ({$projectsStr})
					WHERE 
					u.id IN ({$usersStr}) AND 
						( (u.id = po.created_by_id AND po.created_on $timeRangeStr ) 
						OR (u.id = po.completed_by_id AND po.completed_on $timeRangeStr )  
						OR (u.id = po.updated_by_id AND po.updated_on $timeRangeStr ) 
						) 
					GROUP BY person_id, project_id, type ";

        // Sub tasks
        if (in_array('tasks', $activeModules) || in_array('todo', $activeModules)) {
            $query .= "UNION SELECT
					u.id as person_id,
					SUM( CASE WHEN u.id = st.created_by_id THEN 1 ELSE 0 END ) AS created, 
					SUM( CASE WHEN u.id = st.completed_by_id THEN 1 ELSE 0 END ) AS completed, 
					0 AS updated,
					SUM( CASE WHEN u.id = st.assignee_id THEN 1 ELSE 0 END ) AS responsible,
					SUM( CASE WHEN (st.due_on IS NOT NULL AND st.due_on < NOW() AND st.completed_on IS NULL) THEN 1 ELSE 0 END ) AS overdue,
					st.type as type,
					po.project_id as project_id  
					FROM `" . TABLE_PREFIX . "users` u 
					LEFT JOIN `" . TABLE_PREFIX . "project_objects` po 
					ON po.state >= {$visibilityCondition}  
					AND po.project_id IN ({$projectsStr})
					LEFT JOIN `" . TABLE_PREFIX . "subtasks` st 
					ON st.parent_type = po.type AND st.parent_id = po.id 
					WHERE 
					u.id IN ({$usersStr}) AND 
						( (u.id = st.created_by_id AND st.created_on $timeRangeStr ) 
						OR (u.id = st.completed_by_id AND st.completed_on $timeRangeStr ) 
						) 
					GROUP BY person_id, project_id, type ";
        }

        // Notebook pages
        if (in_array('notebooks', $activeModules)) {
            $query .= "UNION SELECT
					u.id as person_id,
					SUM( CASE WHEN u.id = pg.created_by_id THEN 1 ELSE 0 END ) AS created, 
					0 AS completed,
					SUM( CASE WHEN u.id IN (pg.updated_by_id, pg.last_version_by_id) THEN 1 ELSE 0 END ) AS updated, 
					0 AS responsible,
					0 AS overdue,
					'NotebookPage' as type,
					po.project_id as project_id  
					FROM `" . TABLE_PREFIX . "users` u 
					LEFT JOIN `" . TABLE_PREFIX . "project_objects` po 
					ON po.state >= {$visibilityCondition}  
					AND po.project_id IN ({$projectsStr})
					LEFT JOIN `" . TABLE_PREFIX . "notebook_pages` pg 
					ON pg.parent_type = po.type AND pg.parent_id = po.id 
					WHERE 
					u.id IN ({$usersStr}) AND 
						( (u.id = pg.created_by_id AND pg.created_on $timeRangeStr ) 
						OR (u.id = pg.updated_by_id AND pg.updated_on $timeRangeStr ) 
						OR (u.id = pg.last_version_by_id AND pg.last_version_on $timeRangeStr ) 
						) 
					GROUP BY person_id, project_id, type ";
        }

        // Status Updates
        if (in_array('status', $activeModules)) {
            $query .= "UNION SELECT
					su.created_by_id as person_id,
					COUNT(id) AS created, 
					0 AS completed,
					0 AS updated, 
					0 AS responsible,
					0 AS overdue,
					'StatusUpdate' as type,
					0 as project_id  
					FROM `" . TABLE_PREFIX . "status_updates` su 
					WHERE 
					su.created_by_id IN ({$usersStr}) 
					AND su.created_on $timeRangeStr 
					GROUP BY person_id, project_id, type ";
        }

        // Comments
        $query .= "UNION SELECT
					u.id as person_id,
					SUM( CASE WHEN u.id = c.created_by_id THEN 1 ELSE 0 END ) AS created, 
					0 AS completed,
					SUM( CASE WHEN u.id = c.updated_by_id THEN 1 ELSE 0 END ) AS updated, 
					0 AS responsible,
					0 AS overdue,
					'Comment' as type,
					po.project_id as project_id  
					FROM `" . TABLE_PREFIX . "users` u 
					LEFT JOIN `" . TABLE_PREFIX . "project_objects` po 
					ON po.state >= {$visibilityCondition}  
					AND po.project_id IN ({$projectsStr})
					LEFT JOIN `" . TABLE_PREFIX . "comments` c 
					ON c.parent_type = po.type AND c.parent_id = po.id  
					WHERE 
					u.id IN ({$usersStr}) AND 
						( (u.id = c.created_by_id AND c.created_on $timeRangeStr ) 
						OR (u.id = c.updated_by_id AND c.updated_on $timeRangeStr ) 
						) 
					GROUP BY person_id, project_id, type ";

        $query .= " ORDER BY person_id, project_id, type ";

        $query = DB::prepare($query, $params);
        $data = DB::execute($query);
        if ($data instanceof DBResult) {
            $data->setCasting(array('person_id' => DBResult::CAST_INT, 'created' => DBResult::CAST_INT, 'completed' => DBResult::CAST_INT, 'updated' => DBResult::CAST_INT, 'responsible' => DBResult::CAST_INT, 'overdue' => DBResult::CAST_INT, 'project_id' => DBResult::CAST_INT));
        }
        $result = array();
        if (is_foreachable($data)) {
            foreach ($data as $item) {
                $key = "U{$item['person_id']}P{$item['project_id']}T{$item['type']}";
                $result [$key] = $item;
                $result [$key] ['assigned'] = 0;
            }
        }

        // Pulling items assigned - separate query because joining this to main query complicates
        // things a lot and gives incorrect results
        $query = "SELECT
					a.user_id AS person_id,
					SUM( CASE WHEN po.id > 0 THEN 1 ELSE 0 END ) AS assigned, 
					po.type as type,
					po.project_id as project_id 
					FROM `" . TABLE_PREFIX . "assignments` a  
					LEFT JOIN `" . TABLE_PREFIX . "project_objects` po
					ON po.state >= {$visibilityCondition}  
					AND po.project_id IN ({$projectsStr})
					AND a.parent_type = po.type AND a.parent_id = po.id 
					WHERE 
					a.user_id IN ({$usersStr}) 
					GROUP BY person_id, project_id, type 
					HAVING assigned > 0";

        $data = DB::execute($query);
        if ($data instanceof DBResult) {
            $data->setCasting(array('person_id' => DBResult::CAST_INT, 'assigned' => DBResult::CAST_INT, 'project_id' => DBResult::CAST_INT));
        }

        if (is_foreachable($data)) {
            foreach ($data as $item) {
                $key = "U{$item['person_id']}P{$item['project_id']}T{$item['type']}";
                if (!array_key_exists($key, $result)) {
                    $result [$key] = array('person_id' => $item ['person_id'], 'project_id' => '' . $item ['project_id'], 'created' => 0, 'completed' => 0, 'updated' => 0, 'responsible' => 0, 'overdue' => 0);
                }
                $result [$key] ['assigned'] = $item ['assigned'];
            }
        }
        return array_values($result);
    }

    function getPersonActivity($id, $options, &$user) {
        $result = array();
        $person = Users::findById($id);
        // Person not found
        if ($person == null) {
            return $result;
        }

        $params = array();
        // If start / end date are not specified, use last 1 week period
        $start = DateValue::makeFromString(array_var($options, 'start_date', '-1 week'));
        $end = DateValue::makeFromString(array_var($options, 'end_date', 'now'));
        $params ['start'] = $start->toMySQL();
        $params ['end'] = $end->toMySQL();

        // Find visible projects for current user
        $projects = Projects::findIdsByUser($user, true, TABLE_PREFIX . "projects.completed_on IS NULL");

        $result ['recent'] = array();
        $result ['upcoming'] = array();
        $result ['timelog'] = array();

        if (count($projects) > 0) {
            // Recent Activities - discard time records for this
            $limit = ConfigOptions::getValue('reports_num_recent_items');
            //Discard Documnets
            $excludeTypes = array('TimeRecord', 'Document');
            $projectsStr = "'" . implode("', '", $projects) . "'";

            list ( $contexts, $ignore_contexts ) = ApplicationObjects::getVisibileContexts($user);
            if ($contexts) {
                // Copied from FwActivityLogs.class
                $conditions = array();
                foreach ($contexts as $context) {
                    $conditions [] = DB::prepare('subject_context LIKE ?', $context);
                } // foreach
                $conditions = '(' . implode(' OR ', $conditions) . ')';
                if (is_foreachable($ignore_contexts)) {
                    $ignore_conditions = array();
                    foreach ($ignore_contexts as $context) {
                        $ignore_conditions [] = DB::prepare('subject_context LIKE ?', $context);
                    } // foreach
                    $conditions .= ' AND NOT (' . implode(' OR ', $ignore_conditions) . ')';
                } // if


                $data = DB::execute('SELECT * FROM ' . TABLE_PREFIX . 'activity_logs
        			WHERE subject_type NOT IN(?) 
        			AND created_by_id = ? 
        			AND (' . $conditions . ') 
        			ORDER BY created_on DESC 
        			LIMIT 0, ' . $limit, $excludeTypes, $person->getId());
                if ($data instanceof DBResult) {
                    $data->setCasting(array('id' => DBResult::CAST_INT, 'subject_id' => DBResult::CAST_INT, 'target_id' => DBResult::CAST_INT, 'created_on' => DBResult::CAST_DATETIME, 'created_by_id' => DBResult::CAST_INT));
                } // if
            }

            if (is_foreachable($data)) {
                foreach ($data as $item) {
                    try {
                        $log = new ActivityLog ();
                        $log->loadFromRow($item);
                        //$values['icon'] = $log->getIconUrl();
                        $action = $log->getAction();
                        $actionParts = explode("/", $action);
                        $shortAction = array_pop($actionParts);
                        if ($shortAction == 'moved_to_trash') {
                            $shortAction = 'trashed';
                        }
                        $values ['action'] = ucfirst($shortAction);
                        $object = $log->getSubject();
                        $values ['url'] = $object->getViewUrl();
                        $values ['name'] = $object->getName();
                        $values ['type'] = $object->getVerboseType(false, $user->getLanguage());
                        //TODO fix for getProject undefine
                        if ($object instanceof Project) {
                            $project = $object;
                        } else {
                            $project = $object->getProject();
                        }

                        if ($project instanceof Project) {
                            $values ['project_id'] = $project->getId();
                            $result ['recent'] [] = $values;
                        }
                    } catch (Exception $e) {
                        
                    }
                }
            }

            // TODO: This URL is correct but does not work in a new window, only works from ajax
            /* $result['recent_url'] =  Router::assemble('people_company_user_recent_activities', array(
              'company_id' => $person->getCompanyId(),
              'user_id' => $person->getId()));
             */

            // Upcoming Items
            $activeModules = AngieApplication::getEnabledModuleNames();
            $query = "SELECT po.id, po.type, po.due_on, po.project_id, po.name,
						'' as parent_type, 0 as parent_id, po.integer_field_1, p.slug as project_slug  
						FROM " . TABLE_PREFIX . "project_objects po
						LEFT JOIN " . TABLE_PREFIX . "projects p ON po.project_id = p.id
						LEFT OUTER JOIN " . TABLE_PREFIX . "assignments a
						ON a.parent_id = po.id AND a.parent_type = po.type 
						WHERE (po.assignee_id = '" . $person->getId() . "' OR a.user_id = '" . $person->getId() . "') 
						AND po.completed_on IS NULL 
						AND po.due_on >= '" . $params ['start'] . "'  
						AND po.state >= '" . STATE_VISIBLE . "' {$params['visibility_condition']}
						AND po.project_id IN ({$projectsStr})   
						";

            if (in_array('tasks', $activeModules) || in_array('todo', $activeModules)) {
                $query .= " UNION
							SELECT st.id, st.type, st.due_on, po.project_id, st.body as name,
							po.type as parent_type, po.id as parent_id, po.integer_field_1, p.slug as project_slug 
							FROM " . TABLE_PREFIX . "subtasks st LEFT JOIN " . TABLE_PREFIX . "project_objects po
							ON (
								po.id = st.parent_id AND po.type = st.parent_type 
								AND po.state >= '" . STATE_VISIBLE . "' {$params['visibility_condition']}
								AND po.project_id IN ({$projectsStr})
								)  
							LEFT JOIN " . TABLE_PREFIX . "projects p ON po.project_id = p.id
							WHERE st.assignee_id = '" . $person->getId() . "' 
							AND st.completed_on IS NULL AND st.due_on >= '" . $params ['start'] . "' 
							AND st.parent_type in ('Task', 'TodoList')
							AND st.state >= '" . STATE_VISIBLE . "' ";
            }

            $query .= "ORDER BY due_on ASC
						LIMIT 0, {$limit} ";
            $data = DB::execute($query);
            if (is_foreachable($data)) {
                self::addObjectURL($data);
                $result ['upcoming'] = $data;
            }

            if (in_array("calendar", $activeModules)) {
                $result ['upcoming_url'] = Router::assemble('profile_calendar', array('company_id' => $person->getCompanyId(), 'user_id' => $person->getId()));
            }

            // Time log
            if (AngieApplication::isModuleLoaded('tracking')) {
                $query = "SELECT t.id, t.record_date as date, t.value as hours, t.summary as name,
							CASE WHEN t.parent_type = 'Project' THEN t.parent_id ELSE po.project_id END AS project_id,
							CASE WHEN t.parent_type = 'Project' THEN 0 ELSE t.parent_id END AS parent_id,
							po.name as parent, p.slug as project_slug 
						  FROM `" . TABLE_PREFIX . "time_records` t
						  LEFT JOIN `" . TABLE_PREFIX . "project_objects` po ON (t.parent_type != 'Project' AND t.parent_id = po.id AND po.project_id IN (?) AND po.state >= ? {$params['visibility_condition']})
						  LEFT JOIN " . TABLE_PREFIX . "projects p ON ((t.parent_type != 'Project' AND po.project_id = p.id) OR (t.parent_type = 'Project' AND t.parent_id = p.id)) AND p.id IN (?)
						  WHERE t.user_id = ?
						  AND t.record_date BETWEEN ? AND ?
						  AND t.state >= ? 
						  ORDER BY t.record_date DESC, t.created_on DESC 
						  LIMIT 0, " . $limit;

                $data = DB::execute($query, $projects, STATE_VISIBLE, $projects, $id, $params ['start'], $params ['end'], STATE_VISIBLE);
                if (is_foreachable($data)) {
                    self::addObjectURL($data);
                    $result ['timelog'] = $data;
                }
            }
        }
        return $result;
    }

    function getPeopleMap() {
        $users = $details = array();
        // $query = "SELECT id, first_name, last_name, email
        // 		   FROM `" . TABLE_PREFIX . "users`
        // 		   WHERE role_id > 0
        // 		   ORDER BY first_name, last_name, email, id";
        //Ani..
        $query = "SELECT id, first_name, last_name, email
				  FROM `" . TABLE_PREFIX . "users`
				  WHERE state > ?
				  ORDER BY first_name, last_name, email, id";

        $result = DB::execute($query, STATE_ARCHIVED);
        if (is_foreachable($result)) {
            foreach ($result as $user) {
                $id = $user ['id'];

                /**
                 * Intelligent User Matching - Match User for the following values
                 * FirstName LastName
                 * LastName FirstName
                 * FirstName
                 * LastName
                 * FirstInitial LastInitial
                 * FirstInitialLastInitial
                 * FirstInitial LastName
                 * FirstName LastInitial
                 * Email
                 * Email without Domain
                 * UserID
                 */
                $first_name = strtolower($user ['first_name']);
                $first_i = substr($first_name, 0, 1);
                $last_name = strtolower($user ['last_name']);
                $last_i = substr($last_name, 0, 1);
                $email = strtolower($user ['email']);
                $email_user = substr($email, 0, strpos($email, '@'));

                $users [trim("$first_name $last_name")] = $id;
                $users [trim("$last_name $first_name")] = $id;
                $users [trim("$first_name")] = $id;
                $users [trim("$last_name")] = $id;
                $users [trim("$first_i $last_i")] = $id;
                $users [trim("$first_i")] = $id;
                $users [trim("$first_i$last_i")] = $id;
                $users [trim("$first_i $last_name")] = $id;
                $users [trim("$first_name $last_i")] = $id;
                $users [trim("$email")] = $id;
                $users [trim("$email_user")] = $id;
                $users [$id] = $id;

                $details [$id] = $user;
            } // foreach
        } // if
        return array('details' => $details, 'map' => $users);
    }

    function sendReminder(&$user, $to, $subject, $body) {
        // Load users
        $people_map = $this->getPeopleMap(true);
        $users = $people_map ['map'];
        $details = $people_map ['details'];
        $user_ids = array();
        $recipient_names = explode(',', $to);
        if (is_foreachable($recipient_names)) {
            foreach ($recipient_names as $name) {
                // Look up in our people map and get user ID
                $user_id = $users [trim(strtolower($name))];
                if ($user_id > 0) {
                    $user_ids [] = $user_id;
                }
            }
        }
        $user_ids = array_unique($user_ids);
        $sent = 0;

        // We have some recipients - we can send the feedback
        if (count($user_ids) > 0) {
            $recipients = array();
            foreach ($user_ids as $id) {
                $dummy_user = new User ();
                $dummy_user->loadFromRow($details [$id]);
                $recipients [] = $dummy_user;
            }

            if (!is_valid_email($user->getEmail())) {
                $user->setEmail(ADMIN_EMAIL);
            } // if
            // Send out the emails
            $sentMessages = ApplicationMailer::send($recipients, $subject, $body, array('sender' => $user, 'decorate' => false));
            $sent = count($sentMessages);
        }
        return $sent;
    }

    function getFinanceStats($options = null, $user = null) {

        $params = array();
        $interface = AngieApplication::getPreferedInterface();
        if ($options == null) {
            $options = array();
        }
        $start = DateValue::makeFromString(array_var($options, 'start_date', '-12 months'));
        $end = DateValue::makeFromString(array_var($options, 'end_date', 'now'));
        $params ['start'] = $start->toMySQL();
        $params ['end'] = $end->toMySQL();

        $data = array();
        $params ['invoice_start'] = DateValue::makeFromString($start . '-1 month')->toMySQL();

        $monthly_date_diff = date_diff(new DateTime($params ['invoice_start']), new DateTime($params ['end']));
        $interval = $monthly_date_diff->format('%y') * 12 + $monthly_date_diff->format('%m');
        $params ['total_months'] = $interval;
        $data ['params'] = $params;

        // Not requesting data for the phone 
        if ($interface == AngieApplication::INTERFACE_PHONE) {
            $query = "SELECT SUM(`total_amount`) total_amount , 
                            SUM(`paid_amount`) paid_amount, 
                            SUM(`outstanding_amount`) outstanding_amount, 
                            SUM(`overdue_amount`) overdue_amount
                            FROM `" . TABLE_PREFIX . "rep_finance_summary`
                            WHERE issued_on BETWEEN (?) AND (?)";
            $results = DB::executeFirstRow($query, DateValue::makeFromString($params ['start']), DateValue::makeFromString($params ['end']));
            $data ['all'] = $results;
        } else {

            $query = "SELECT * FROM `" . TABLE_PREFIX . "rep_finance_summary` WHERE issued_on BETWEEN (?)  AND (?) ORDER BY issued_on DESC";
            $results = DB::execute($query, $params ['invoice_start'], $params ['end']);

            if (is_foreachable($results)) {

                $data ['invoice_stats'] = array();
                $monthly_invoices = array();
                $data ['all'] ['label'] = lang('All');

                for ($i = 0; $i <= $interval; $i ++) {
                    $invoice_month = DateValue::makeFromString($params ['end'] . '-' . $i . ' month')->format('Y-m');
                    $monthly_invoices [$invoice_month] = array();
                    $monthly_invoices [$invoice_month] ['label'] = DateValue::makeFromString($params ['end'] . '-' . $i . 'month')->format('F Y');
                    $monthly_invoices [$invoice_month] ['total_amount'] = 0;
                    $monthly_invoices [$invoice_month] ['paid_amount'] = 0;
                    $monthly_invoices [$invoice_month] ['outstanding_amount'] = 0;
                    $monthly_invoices [$invoice_month] ['overdue_amount'] = 0;
                }

                foreach ($results as $result) {
                    $issued_month = DateValue::makeFromString($result ['issued_on'])->format('Y-m');

                    $monthly_invoices [$issued_month] ['total_amount'] += $result ['total_amount'];
                    $monthly_invoices [$issued_month] ['paid_amount'] += $result ['paid_amount'];
                    $monthly_invoices [$issued_month] ['outstanding_amount'] += $result ['outstanding_amount'];
                    $monthly_invoices [$issued_month] ['overdue_amount'] += $result ['overdue_amount'];
                    $invoice = Invoices::findById($result ['id']);
                    $monthly_invoices [$issued_month] ['invoices'] [] = array_merge($result, array('view_url' => ($invoice instanceof Invoice) ? $invoice->getViewUrl() : null), array('resend_email_url' => ($invoice instanceof Invoice && $invoice->isOverdue()) ? $invoice->getNotifyUrl() : null));
                }

                // Do not count amount of one month, which is not in the date range 
                foreach ($monthly_invoices as $month => $month_amount) {
                    if ($month != DateValue::makeFromString($params ['invoice_start'])->format('Y-m')) {
                        $data ['all'] ['total_amount'] += $month_amount ['total_amount'];
                        $data ['all'] ['paid_amount'] += $month_amount ['paid_amount'];
                        $data ['all'] ['outstanding_amount'] += $month_amount ['outstanding_amount'];
                        $data ['all'] ['overdue_amount'] += $month_amount ['overdue_amount'];
                    }
                }
            }

            $data ['invoice_stats'] = $monthly_invoices;

            $data ['projects'] = Projects::getIdNameMap($user);
            $data ['clients'] = Companies::getIdNameMap();
        }

        $query = "SELECT SUM(`total_amount`) total_amount , 
                            SUM(`paid_amount`) paid_amount, 
                            SUM(`outstanding_amount`) outstanding_amount, 
                            SUM(`overdue_amount`) overdue_amount
                            FROM `" . TABLE_PREFIX . "rep_finance_summary`
                            WHERE issued_on BETWEEN (?) AND (?)";
        $results = DB::executeFirstRow($query, DateValue::makeFromString($params ['start'] . '-' . $interval . 'month'), DateValue::makeFromString($params ['end'] . '-' . $interval . 'month'));
        $data ['prev_invoice_stats'] = $results;
        return $data;
    }

    function getFinanceSummary($options = null) {
        $interface = AngieApplication::getPreferedInterface();
        $end = DateValue::makeFromString('now');
        $start = DateValue::makeFromString($end . '-12 months');
        $end_prev = DateValue::makeFromString($start . '-1 months');
        $start_prev = DateValue::makeFromString($end_prev . '-12 months');

        // year to date and month to date calulation
        $query = "SELECT SUM(CASE WHEN `paid_on` BETWEEN DATE_FORMAT(NOW(), '%Y-%m-01') AND DATE_FORMAT(NOW() , '%Y-%m-%d')THEN `amount` ELSE 0 END) cur_mtd, 
                        SUM(CASE WHEN `paid_on` BETWEEN DATE_SUB( DATE_FORMAT( NOW() , '%Y-%m-01' ) , INTERVAL 12 MONTH) AND DATE_SUB(DATE_FORMAT(NOW() , '%Y-%m-%d') , INTERVAL 12 MONTH)
                        THEN (CASE `amount` WHEN NULL THEN 0 ELSE amount END)ELSE 0 END) prev_mtd, 
                        SUM( CASE WHEN `paid_on` BETWEEN DATE_FORMAT( NOW() , '%Y-01-01') AND DATE_FORMAT( NOW() , '%Y-%m-%d') THEN `amount` ELSE 0 END ) cur_ytd, 
                        SUM(CASE WHEN `paid_on` BETWEEN DATE_SUB( DATE_FORMAT( NOW() , '%Y-01-01' ) , INTERVAL 12 MONTH ) AND DATE_SUB(DATE_FORMAT( NOW() , '%Y-%m-%d') , INTERVAL 12 MONTH ) 
                        THEN (CASE `amount` WHEN NULL THEN 0 ELSE amount END)ELSE 0 END ) prev_ytd
                        FROM " . TABLE_PREFIX . "payments";


        $results = DB::executeFirstRow($query);
        $data = array();
        $empty = true;
        if (isset($results)) {
            $data ['year_to_date'] = array('label' => lang('Year to Date'), 'current' => round($results ['cur_ytd']), 'previous' => round($results ['prev_ytd']));
            $data ['month_to_date'] = array('label' => lang('Month to Date'), 'current' => round($results ['cur_mtd']), 'previous' => round($results ['prev_mtd']));
            $empty = false;
        }

        // Paid on Time (in pecentage)
        $query = "SELECT SUM(CASE WHEN STATUS = (?) AND `closed_on` < `due_on` THEN `paid_amount` ELSE 0 END ) / (CASE WHEN sum( `total_amount` ) > 0 THEN sum( `total_amount` ) ELSE 1 END ) * 100 as paid_on_time
                       FROM " . TABLE_PREFIX . "rep_finance_summary WHERE issued_on BETWEEN (?)  AND (?)";
        $results = DB::execute($query, INVOICE_STATUS_PAID, $start->toMySQL(), $end->toMySQL());

        if ($results instanceof DBResult) {
            $data['paid_on_time'] = array('label' => lang('Paid On Time'), 'value' => round($results[0]['paid_on_time'], 2));
            $empty = false;
        }
        $query = "SELECT SUM(CASE WHEN STATUS = ? AND `closed_on` < `due_on` THEN `paid_amount` ELSE 0 END ) / (CASE WHEN sum( `total_amount` ) > 0 THEN sum( `total_amount` ) ELSE 1 END )  * 100 as paid_on_time
                       FROM " . TABLE_PREFIX . "rep_finance_summary WHERE issued_on BETWEEN ?  AND ?";

        $results = DB::execute($query, INVOICE_STATUS_PAID, $start_prev->toMySQL(), $end_prev->toMySQL());

        if ($results instanceof DBResult) {
            $data['paid_on_time']['value_prev'] = round($results[0]['paid_on_time'], 2);
            $empty = false;
        }

        // Not requesting data for the phone 
        if ($interface != AngieApplication::INTERFACE_PHONE) {
            // Unpaid amount calculations
            $query = "SELECT `invoice_age` , sum(`outstanding_amount`) as amount
                FROM `" . TABLE_PREFIX . "rep_finance_summary`
                GROUP BY `invoice_age`";

            //setting zero as default value
            $invoice_aging = array();
            $invoice_aging[0] = array('label' => lang('0 to 30 days'), 'value' => 0);
            $invoice_aging[30] = array('label' => lang('30 to 60 days'), 'value' => 0);
            $invoice_aging[60] = array('label' => lang('60 to 90 days'), 'value' => 0);
            $invoice_aging[90] = array('label' => lang('> 90 days'), 'value' => 0);

            $results = DB::execute($query);
            if ($results instanceof DBResult && is_foreachable($results)) {
                $empty = false;
                $invoice_aging_array = $results->toArray();
                foreach ($invoice_aging_array as $row) {
                    $dummy[$row['invoice_age']] = round($row['amount']);
                }
                foreach ($invoice_aging as $key => $value) {
                    $invoice_aging[$key]['value'] = (isset($dummy[$key])) ? round($dummy[$key]) : 0;
                }
            }
            $data['un_paid'] = $invoice_aging;
            unset($invoice_aging);
            unset($invoice_aging_array);
            unset($dummy);

            //Top five clients
            $query = "SELECT `company_id`, sum( `paid_amount` ) as amount
                           FROM `" . TABLE_PREFIX . "rep_finance_summary`
                           WHERE `company_id` != 0
                           GROUP BY `company_id`
                           ORDER BY sum( `paid_amount` ) DESC
                           LIMIT 0 , 5";

            $results = DB::execute($query);

            $top_five_client = array();
            if ($results instanceof DBResult && is_foreachable($results)) {
                $empty = false;
                $companies_map = Companies::getIdNameMap();
                foreach ($results as $result) {
                    $top_five_client[] = array('id' => $result['company_id'],
                        'name' => $companies_map[$result['company_id']],
                        'amount' => round($result['amount']));
                }
                unset($companies_map);
            } else {
                $top_five_client = null;
            }

            $data['top_five_client'] = $top_five_client;
            unset($top_five_client);
            unset($results);
        }
        $data['empty'] = $empty;
        return $data;
    }

    function updateFinancialSummary() {

        try {
            if (AngieApplication::isModuleLoaded('invoicing')) {

                if (!DB::tableExists(TABLE_PREFIX . 'rep_finance_summary')) {

                    $query = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "rep_finance_summary` (
		                            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		                            `company_id` int(5) unsigned NOT NULL DEFAULT '0',
		                            `project_id` int(5) unsigned DEFAULT NULL,
		                            `currency_id` int(4) NOT NULL DEFAULT '0',
		                            `number` varchar(50) DEFAULT NULL,
		                            `total_amount` decimal(12,3) DEFAULT NULL,
		                            `paid_amount` decimal(12,3) DEFAULT NULL,
		                            `outstanding_amount` decimal(12,3) DEFAULT NULL,
		                            `overdue_amount` decimal(12,3) DEFAULT NULL,
		                            `overdue_days` int(5),
		                            `invoice_age` int(5) NOT NULL DEFAULT '0',
		                            `status` int(4) NOT NULL DEFAULT '0',
		                            `issued_on` datetime DEFAULT NULL,
		                            `due_on` date DEFAULT NULL,
		                            `closed_on` datetime DEFAULT NULL,
		                            `created_on` datetime DEFAULT NULL,
		                            `issued_to_id` int(11) DEFAULT NULL,";

                    $query .= " PRIMARY KEY (`id`),
		                            KEY `issued_on` (`issued_on`)
					) $engine $charset COMMENT='financial reports summary Table'";

                    // Create Summary table
                    DB::execute($query);
                }

                $query = "SELECT invo_details.id, 
                        invo_details.company_id,  
                        invo_details.project_id, 
                        pay_details.currency_id, 
                        invo_details.varchar_field_1 as number, 
                        pay_details.total_amount,
                        pay_details.paid_amount, 
                        pay_details.outstanding_amount, 
                        (CASE WHEN DATEDIFF(NOW(),invo_details.date_field_1)  >= 0 THEN pay_details.outstanding_amount ELSE 0 END) as overdue_amount,
                        (CASE WHEN invo_details.status  < ?  THEN DATEDIFF(NOW(),invo_details.date_field_1)  ELSE 0 END) as overdue_days,
                        CASE WHEN DATEDIFF( NOW( ) , invo_details.date_field_2 ) BETWEEN 0 AND 30 THEN 0
                        WHEN DATEDIFF( NOW( ) , invo_details.date_field_2 ) BETWEEN 30 AND 60 THEN 30
                        WHEN DATEDIFF( NOW( ) , invo_details.date_field_2 ) BETWEEN 60 AND 90 THEN 60 
                        ELSE 90
                        END AS invoice_age,
                        invo_details.status,
                        invo_details.date_field_2 as issued_on,
                        invo_details.date_field_1 as due_on, 
                        invo_details.closed_on, 
                        invo_details.created_on ,
                        invo_details.integer_field_2 as issued_to_id
                        from " . TABLE_PREFIX . "invoice_objects invo_details 
                        LEFT JOIN (SELECT inv_item.parent_id, total_amount,
                        (CASE WHEN pay.parent_id IS NULL THEN 0 ELSE currency_id END) AS currency_id,
                        (CASE WHEN pay.parent_id IS NULL THEN 0 ELSE paid END) AS paid_amount,
                        (CASE WHEN pay.parent_id IS NULL THEN total_amount ELSE (total_amount - paid)END) AS outstanding_amount
                        FROM
                        (SELECT in_obj_item.parent_id, sum(in_obj_item.total) as total_amount FROM " . TABLE_PREFIX . "invoice_object_items as in_obj_item group by in_obj_item.parent_id) AS inv_item
                        LEFT JOIN
                        (SELECT paid_amt.parent_id, sum( paid_amt.amount ) AS paid, paid_amt.currency_id AS currency_id
                        FROM " . TABLE_PREFIX . "payments as paid_amt
                        WHERE paid_amt.parent_type = 'invoice' AND paid_amt.status = 'Paid'
                        GROUP BY paid_amt.parent_id) AS pay ON pay.parent_id = inv_item.parent_id) as pay_details
                        ON invo_details.id = pay_details.parent_id
                        WHERE invo_details.status IN ( ? )
                        ORDER BY invo_details.date_field_2 ASC, pay_details.total_amount DESC";

                $result = DB::execute($query, INVOICE_STATUS_PAID, array(INVOICE_STATUS_ISSUED, INVOICE_STATUS_PAID, INVOICE_STATUS_CANCELED));

                if ($result instanceof DBResult && $result->count() > 0) {
                    // Now insert these values in the table
                    $fields = array('id', 'company_id', 'project_id', 'currency_id', 'number', 'total_amount', 'paid_amount', 'outstanding_amount', 'overdue_amount', 'overdue_days', 'invoice_age', 'status', 'issued_on', 'due_on', 'closed_on', 'created_on', 'issued_to_id');

                    $query = "REPLACE INTO `" . TABLE_PREFIX . "rep_finance_summary` (" . implode(', ', $fields) . ") VALUES \r\n";
                    $rows = array();
                    foreach ($result as $value) {
                        $values = array();
                        $values [] = DB::escape($value);
                        $rows [] = " (" . implode(', ', $values) . ")";
                    }
                    $query .= implode(",\r\n", $rows);

                    unset($rows);
                    unset($fields);
                    DB::execute($query);
                }

                //Delete deleted invoices from summary table
                DB::execute('DELETE FROM ' . TABLE_PREFIX . 'rep_finance_summary WHERE status NOT IN ( ? ) ', array(INVOICE_STATUS_ISSUED, INVOICE_STATUS_PAID));
            }
        } catch (Exception $e) {
            
        }
    }

    function getExportData(User $user, $params) {

        // Adding a condition if we need a project data based on the companies.
        $additional_conditions = isset($params['companies']) ? (strpos(trim(($params['companies'])), ',') == false ? " company_id = " . trim($params['companies']) : null) : null;
        if (isset($params['status']) && $params['status'] != 'active') {
            $projects = array_keys(Projects::getIdNameMap($user, STATE_ARCHIVED, null, $additional_conditions, true));
        } else {
            $projects = array_keys(Projects::getActiveIdNameMap($user, null, $additional_conditions, true));
        }

        $project_fields = array('id', 'company_id', 'category_id', 'label_id', 'currency_id', 'budget', 'name', 'leader_id', 'leader_name', 'leader_email', 'completed_on', 'created_on', 'created_by_id', 'created_by_email', 'custom_field_1', 'custom_field_2', 'custom_field_3');
        $projects_details = Projects::getIdDetailsMap($project_fields, $projects);
        $projects_hours = self::getProjectActualHours($projects, array('start' => '1970-01-01', 'end' => DateValue::makeFromString('now')->toMySQL()));
        if (is_foreachable($projects_hours)) {
            foreach ($projects_hours as $hour) {
                $projects_id_hours_map[$hour['project_id']] = $hour['actual_hours'];
            }
        }

        $date_format = ConfigOptions::getValue("reports_date_format");
        $labels = Labels::getIdNameMap('ProjectLabel', true);
        $users = Users::getIdNameMap(null, false);
        $companies = Companies::getIdNameMap(null, STATE_VISIBLE);
        $categories = Categories::getIdNameMap(null, 'ProjectCategory');
        $currancies = Currencies::getIdDetailsMap();

        //TODO :: IS IT USEFULL INFORMATION OR MAY CREATE CONFUSION FOR THE CLIENT AS IT ONLY SHOWS
        // PAID AMOUNT OF INVOICES CREATED ON PROJECTS
        // Getting a paid amount for the invoices based on the projects
        $projects_paid_amounts = array();
        //check if finance data exists or not.
        if (DB::tableExists(TABLE_PREFIX . 'rep_finance_summary')) {
            $query = DB::prepare("SELECT project_id, SUM(paid_amount) as paid_amount FROM `" . TABLE_PREFIX . "rep_finance_summary` WHERE project_id IS NOT NULL and project_id != 0 GROUP BY project_id");
            $paid_amounts = DB::execute($query);
            if ($paid_amounts instanceof DBResult && $paid_amounts->count() > 0) {
                foreach ($paid_amounts as $paid_amount) {
                    $projects_paid_amounts[$paid_amount['project_id']] = $paid_amount['paid_amount'];
                }
            }
        }

        //Heading for the CSV
        $headings = array(lang('Id #'),
            lang('Name'),
            lang('Created On'),
            lang('Created By'),
            lang('Created By Email ID'),
            lang('Category'),
            lang('Hours'),
            lang('Progress(%)'),
            lang('Total Tasks'),
            lang('Open Tasks'),
            lang('Completed Tasks'),
            lang('Client'),
            lang('Leader'),
            lang('Leader Email ID'),
            lang('Label'),
            lang('Status'),
            lang('Budget'),
            lang('Budget Currency'),
            lang('Paid Amounts'),
        );

        $custom_fields = CustomFields::getEnabledCustomFieldsByType('Project');
        if (is_foreachable($custom_fields)) {
            foreach ($custom_fields as $field_name => $details) {
                $headings[] = $details['label'];
            }
        }



        if (is_foreachable($projects_details)) {
            $data = array();
            $i = 0;
            foreach ($projects_details as $id => $details) {

                // Calculation for the project progress
                list ( $task_total, $task_open ) = ProjectProgress::getProjectProgress($id);
                $percent_done = 0;
                $task_completed = $task_total - $task_open;

                if ($task_completed > 0 && $task_total > 0) {
                    $percent_done = ceil($task_completed / $task_total * 100);
                } else if ($task_open == 0 && $task_total > 0) {
                    $percent_done = 100;
                }

                // Preparing array for the CSV
                $data[$i] = array(
                    'id' => $id,
                    'name' => $details['name'],
                    'created_on' => (!empty($details['created_on'])) ? ( ($date_format == 'MM/DD/YY') ? DateValue::makeFromString($details['created_on'])->format('m/d/y') : DateValue::makeFromString($details['created_on'])->format('d/m/y')) : '-',
                    'created_by_name' => $users[$details['created_by_id']],
                    'created_by_email' => $details['created_by_email'],
                    'category' => ($details['category_id'] != 0) ? $categories[$details['category_id']] : '',
                    'hours' => (isset($projects_id_hours_map[$id])) ? $projects_id_hours_map[$id] : '',
                    'percent_done' => $percent_done,
                    'total_tasks_count' => $task_total,
                    'open_tasks_count' => $task_open,
                    'completed_tasks_count' => $task_completed,
                    'client' => $companies[$details['company_id']],
                    'leader_name' => $users[$details['leader_id']],
                    'leader_email' => $details['leader_email'],
                    'label' => ($details['label_id'] != 0) ? $labels[$details['label_id']] : '',
                    'status' => ($details['completed_on'] == NULL) ? 'Active' : 'Completed',
                    'budget' => ($details['budget'] != Null ) ? Round($details['budget'], 2) : '',
                    'currency' => ($details['budget'] != Null ) ? $currancies[$details['currency_id']]['code'] : '',
                    'paid_amounts' => (isset($projects_paid_amounts[$id])) ? $projects_paid_amounts[$id] : '',
                );

                if (is_foreachable($custom_fields)) {
                    foreach ($custom_fields as $field_name => $custom_field_data) {
                        $data[$i][$custom_field_data['label']] = $details[$field_name];
                    }
                }

                $i++;
            } //for each
            array_unshift($data, $headings);
        } //if
        return $data;
    }

    function checkReportsPermission($permission, $user) {
        $roles_permissions = ConfigOptions::getValue('reports_permissions');
        return in_array(get_class($user), $roles_permissions[$permission]);
    }

    function setReportsPermissionsForUsers($roles_permissions) {

        $all_permissons = array('can_use_reports',
            'can_see_reports_people',
            'can_see_reports_companies',
            'can_see_time_reports'
        );
        $roles_permissions['Administrator'] = $all_permissons;
        $roles_permissions = array_merge(array_flip(Users::getAvailableUserClasses()), $roles_permissions);

        foreach ($roles_permissions as $role => $permissions) {
            $users = Users::findByType($role);
            if ($users instanceof DBResult && $users->count() > 0) {
                foreach ($users as $user) {
                    $user_existing_permissions = $user->getSystemPermissions();
                    $custom_parmissions = is_foreachable($permissions) ? array_keys($permissions) : array();
                    foreach ($all_permissons as $permission) {
                        $ext_pos = array_search($permission, $user_existing_permissions);
                        $cust_pos = array_search($permission, $custom_parmissions);
                        if (is_numeric($ext_pos) && $cust_pos === false) {
                            unset($user_existing_permissions[$ext_pos]);
                        }
                        if ($ext_pos === false && is_numeric($cust_pos)) {
                            $user_existing_permissions[] = $permission;
                        }
                    }
                    $user->setAdditionalProperty('custom_permissions', $user_existing_permissions);
                    $user->save();
                }
            }
        }
    }

    function canAccessLeaderBoard() {
        $result = false;
        $user = Authentication::getLoggedUser();
        if ($user instanceof User) {
            if (ConfigOptions::exists('companies_in_ranking_game')) {
                $company_ids = ConfigOptions::getValue('companies_in_ranking_game');
                $result = in_array(-1, $company_ids) || in_array($user->getCompany()->getId(), $company_ids);
            } else {
                $result = $user->getCompany()->getId() == Companies::findOwnerCompany()->getId();
            }
            $result = $user->isAdministrator() ||
                    ( $result && ($user->canUseReports() || $user->getSystemPermission('can_use_reports')) ) ? true : false;
        }
        return $result;
    }

}

// Reports
?>
