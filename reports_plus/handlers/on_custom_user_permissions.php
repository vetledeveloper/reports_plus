<?php

  /**
   * Handle on_custom_user_permissions event
   *
   * @package custom.modules.reports_plus
   * @subpackage handlers
   */

  /**
   * Populate user permissions
   *
   * @param User $user
   * @param NamedList $permissions
   */
  function reports_plus_handle_on_custom_user_permissions(User &$user, NamedList &$permissions) {

      $permissions->add('can_use_reports', array(
        'name' => lang('Can Use Reports'),
        'description' => lang('Can this user access Reports Plus?'),
      ));

      $permissions->add('can_see_reports_people', array(
        'name' => lang('Can See People Reports'),
        'description' => lang('Can this user view People Reports?'),
      ));
      $permissions->add('can_see_reports_companies', array(
        'name' => lang('Can See Company Reports'),
        'description' => lang('Can this user view Company Reports?'),
      ));
      $permissions->add('can_see_time_reports', array(
        'name' => lang('Can See Time Reports'),
        'description' => lang('Can this user view Time Reports Plus?'),
      ));

      // if
  } // reports_plus_handle_on_custom_user_permissions