<?php

// We need admin controller
AngieApplication::useController('admin');

/**
 * Manages Reports settings
 * 
 * @package custom.modules.planning
 * @subpackage controllers
 */
class ReportsPlusAdminController extends AdminController {

    var $controller_name = 'reports_plus_admin';

    /**
     * Main Reports Module Admin page
     *
     */
    function index() {

        $this->wireframe->setPageTitle(lang("Reports Plus Configuration"));
        $date_format_values = (array) ConfigOptions::getValue('reports_date_format_values');
        $weeks_for_average_velocity_values = array(1, 2, 3, 4, 6);
        $num_recent_items_values = array(6, 10, 15, 20);
        $panel = $this->getValueFromRequest('panel');

        if (!ConfigOptions::exists('reports_permissions')) {
            ConfigOptions::addOption('reports_permissions', REPORTS_PLUS_MODULE, array());
        }
        $roles_permissions = ConfigOptions::getValue('reports_permissions');

        if (!ConfigOptions::exists('reports_enable_leader_board')) {
            ConfigOptions::addOption('reports_enable_leader_board', REPORTS_PLUS_MODULE, true);
        }
        if (!ConfigOptions::exists('reports_leader_board_period')) {
            ConfigOptions::addOption('reports_leader_board_period', REPORTS_PLUS_MODULE, array('key' => 0));
        }
        if (!ConfigOptions::exists('companies_in_ranking_game')) {
            ConfigOptions::addOption('companies_in_ranking_game', REPORTS_PLUS_MODULE, array());
        }

        if (!ConfigOptions::exists('reports_update_on_access')) {
            ConfigOptions::addOption('reports_update_on_access', REPORTS_PLUS_MODULE, false);
        }

        $companies_in_ranking_game = ConfigOptions::getValue('companies_in_ranking_game');

        if ($this->request->isSubmitted()) {
            if ($panel == 'permissions') {
                $roles_permissions = $this->getValueFromRequest('roles_permissions');
                ConfigOptions::setValue('reports_permissions', $roles_permissions);
                ReportsPlus::setReportsPermissionsForUsers($roles_permissions);
            }

            if ($panel == 'settings') {
                $reports_data = $this->getValueFromRequest('reports');
                $leader_board_period = array('key' => $reports_data['leader_board_period']['key']);
                if ($reports_data['leader_board_period']['key'] == 5) {
                    $leader_board_period['start_date'] = $reports_data['leader_board_period']['start_date'];
                    $leader_board_period['end_date'] = $reports_data['leader_board_period']['end_date'];
                }
                ConfigOptions::setValue('reports_leader_board_period', $leader_board_period);

                ConfigOptions::setValue('reports_date_format', (string) array_var($reports_data, 'date_format', $date_format_values [0]));
                ConfigOptions::setValue('reports_num_recent_items', array_var($reports_data, 'num_recent_items', 6));
                ConfigOptions::setValue('reports_num_weeks_for_average_velocity', array_var($reports_data, 'num_weeks_for_average_velocity', 3));
                ConfigOptions::setValue('reports_formula_weight_most_active_comments', array_var($reports_data, 'formula_weight_most_active_comments', 2));
                ConfigOptions::setValue('reports_formula_weight_most_active_hours', array_var($reports_data, 'formula_weight_most_active_hours', 1));
                ConfigOptions::setValue('reports_formula_weight_most_active_version', array_var($reports_data, 'formula_weight_most_active_version', 0.25));
                ConfigOptions::setValue('reports_formula_weight_person_score_hours', array_var($reports_data, 'formula_weight_person_score_hours', 1));
                ConfigOptions::setValue('reports_formula_weight_person_score_completed', array_var($reports_data, 'formula_weight_person_score_completed', 3));
                ConfigOptions::setValue('reports_formula_weight_person_score_created', array_var($reports_data, 'formula_weight_person_score_created', 2));
                ConfigOptions::setValue('reports_update_on_access', (boolean) array_var($reports_data, 'reports_update_on_access', false));
                ConfigOptions::setValue('reports_enable_leader_board', (boolean) array_var($reports_data, 'reports_enable_leader_board', true));
                ConfigOptions::setValue('companies_in_ranking_game', array_var($reports_data, 'company_multiple', array()));

                $this->flash->success('Reports Module Configuration has been updated');
            }
        } // if

        $reports_data ['date_format'] = (string) ConfigOptions::getValue('reports_date_format');
        $reports_data ['leader_board_period'] = ConfigOptions::getValue('reports_leader_board_period');
        $reports_data ['num_recent_items'] = ConfigOptions::getValue('reports_num_recent_items');
        $reports_data ['num_weeks_for_average_velocity'] = ConfigOptions::getValue('reports_num_weeks_for_average_velocity');
        $reports_data ['formula_weight_most_active_comments'] = ConfigOptions::getValue('reports_formula_weight_most_active_comments');
        $reports_data ['formula_weight_most_active_hours'] = ConfigOptions::getValue('reports_formula_weight_most_active_hours');
        $reports_data ['formula_weight_most_active_version'] = ConfigOptions::getValue('reports_formula_weight_most_active_version');
        $reports_data ['formula_weight_person_score_hours'] = ConfigOptions::getValue('reports_formula_weight_person_score_hours');
        $reports_data ['formula_weight_person_score_completed'] = ConfigOptions::getValue('reports_formula_weight_person_score_completed');
        $reports_data ['formula_weight_person_score_created'] = ConfigOptions::getValue('reports_formula_weight_person_score_created');
        $reports_data ['reports_update_on_access'] = (boolean) ConfigOptions::getValue('reports_update_on_access');
        $reports_data ['reports_enable_leader_board'] = (boolean) ConfigOptions::getValue('reports_enable_leader_board');
        $reports_data ['company_multiple'] = ConfigOptions::getValue('companies_in_ranking_game');

        $data = array('reports_data' => $reports_data,
            'date_format_values' => $date_format_values,
            'weeks_for_average_velocity_values' => $weeks_for_average_velocity_values,
            'num_recent_items_values' => $num_recent_items_values,
            'roles' => Users::getAvailableUserClasses(),
            'roles_permissions' => $roles_permissions,
            'settings_url' => Router::assemble('reports_plus_admin', array('panel' => 'settings')),
            'permissions_url' => Router::assemble('reports_plus_admin', array('panel' => 'permissions'))
        );

        $this->smarty->assign($data);
    }

// index

    function getValueFromRequest($param) {
        $value = array_var($_POST, $param, NULL);
        if (is_null($value)) {
            $value = array_var($_GET, $param, NULL);
        }
        return $value;
    }

}
