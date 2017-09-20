<?php

  /**
   * on_wireframe_updates event handler implementation
   *
   * @package custom.modules.reports_plus
   * @subpackage handlers
   */

  /**
   * Handle wireframe updates even
   *
   * @param array $wireframe_data
   * @param array $response_data
   * @param User $user
   */
  function reports_plus_handle_on_wireframe_updates(&$wireframe_data, &$response_data, $on_unload, &$user) {
    //TODO:: check the $on_unload Flag.
      // $score_data = ReportsPlus::getLeaderBoardData($user);
      // $response_data['menu_bar_badges']['profile'] = $score_data['rank'];
  } // reports_plus_handle_on_wireframe_updates