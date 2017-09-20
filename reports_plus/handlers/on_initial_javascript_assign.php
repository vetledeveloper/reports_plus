<?php

  /**
   * on_initial_javascript_assign event handler
   *
   * @package activeCollab.modules.system
   * @subpackage handlers
   */

  /**
   * Populate initial JavaScript variables list
   *
   * @param $variables
   */
  function reports_plus_handle_on_initial_javascript_assign(&$variables) {
    $variables['user_score_update_url'] = Router::assemble('reports_user_score_update');
    $variables['leader_board_url'] = Router::assemble('reports_people_leader_board');
    $rp = new ReportsPlus();
    $variables['can_access_leader_board'] = $rp->canAccessLeaderBoard();
    
    if(!ConfigOptions::exists('reports_enable_leader_board')) {
        ConfigOptions::addOption( 'reports_enable_leader_board', REPORTS_PLUS_MODULE, true);
        ConfigOptions::setValue ( 'reports_enable_leader_board', true);
    }
    $variables['reports_enable_leader_board'] = ( boolean ) ConfigOptions::getValue('reports_enable_leader_board');

  } // system_handle_on_initial_javascript_assign